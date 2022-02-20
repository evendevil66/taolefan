<?php


namespace App\Http\Controllers;
use App\Http\Controllers\WeChatController;
use Log;
use App\Models\Users;
use Illuminate\Support\Facades\Request;
use TopClient;
use TbkItemInfoGetRequest;
use TopAuthTokenCreateRequest;
use TbkScPublisherInfoSaveRequest;
use TbkOrderDetailsGetRequest;

class TaokeController extends Controller
{
    /**
     * 发起get请求
     * @param $url
     * @param $method
     * @param int $post_data
     * @return bool|string
     */
    public function curlGet($url,$method,$post_data = 0){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }elseif($method == 'get'){
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 参数加密
     * @param $data
     * @return string
     */
    function makeSign($data)
    {
        $appSecret=config('config.dtkAppSecret');
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {

            $str .= '&' . $k . '=' . $v;
        }
        $str = trim($str, '&');
        $sign = strtoupper(md5($str . '&key=' . $appSecret));
        return $sign;
    }

    /***
     * 淘口令解析并调用转链函数-大淘客接口
     * @param $user  传入提交转链用户的信息
     * @param $content 传入用户的完整消息
     * @return 返回转链后的文本信息
     */
    public function parse($user,$content)
    {

        //$user = app(UserController::class) -> getUser($openid);
        $rate = 0.8; //后期数据从$user用户对象中获取
        $host = "https://openapi.dataoke.com/api/tb-service/parse-taokouling";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.0.0',
            'content'=>$content
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host .'?'. http_build_query($data);
        //var_dump($url);
        //处理大淘客解析请求url
        $output = $this->curlGet($url,'get');
        //调用统一请求函数

        $dataArr = json_decode($output, true);//将返回数据转为数组

        $status = $dataArr['code'];
        Log::info($dataArr);
        Log::info($status);
        switch ($status){
            case "0":
                $goodsid = $dataArr['data']['goodsId'];
                $dataArr = null;
                if($user->special_id !=null && $user->special_id !=""){
                    Log::info("进入带有会员id的转换");
                    Log::info($user->special_id);
                    $dataArr = $this->privilegeLinkBySpecialId($goodsid,$user->special_id);
                }else{
                    Log::info("进入不带会员id的转换");
                    $dataArr = $this->privilegeLink($goodsid);
                }

                Log::info($dataArr);
                if ($dataArr['code'] == '0') {
                    $tbArr = $this->aliParse($goodsid);
                    $title = $tbArr['results']['n_tbk_item'][0]['title']; //商品标题
                    $price = $tbArr['results']['n_tbk_item'][0]['zk_final_price']; //商品价格
                    $couponInfo = "商品无优惠券";
                    $amount = "0";
                    if($dataArr['data']['couponInfo'] != null){
                        $couponInfo = $dataArr['data']['couponInfo']; //优惠券信息
                        $start= (strpos($couponInfo,"元"));
                        $ci= mb_substr($couponInfo,$start);
                        //return $ci;
                        $end= (strpos($ci,"元"));
                        $amount= mb_substr($ci,0,$end);
                    }
                    $tpwd = $dataArr['data']['tpwd']; //淘口令
                    $estimate = $price - $amount; //预估付款金额
                    //$longTpwd = $dataArr['data']['longTpwd']; //长淘口令
                    //$start= (strpos($longTpwd,"【"));
                    //$end= (strpos($longTpwd,"】"));
                    //$title= substr($longTpwd,$start+1,$end-$start-1);
                    $maxCommissionRate = $dataArr['data']['maxCommissionRate']==""||null?$dataArr['data']['minCommissionRate']:$dataArr['data']['maxCommissionRate']; //佣金比例
                    if($user->special_id !=null && $user->special_id !=""){
                        return
                            "1".$title . "\n".
                            "售价：" . $price . "元\n".
                            "优惠券：" . $couponInfo . "\n".
                            "预计付款金额：" . $estimate . "元\n".
                            "商品返现比例：" . $maxCommissionRate*0.8 . "%\n". //用户返现比例为0.8 后续将从用户表中获取
                            "预计返现金额：" . ($estimate * $rate * ($maxCommissionRate / 100)) . "元\n".
                            "返现计算：实付款 * " . $maxCommissionRate*0.8 . "%\n\n".
                            "复制" . $tpwd . "打开淘宝下单后将订单号发送至公众号即可绑定返现\n\n".
                            "您已绑定过淘宝账号，下单后系统将尝试自动跟单，如1小时后仍查询不到，您可以手动绑定订单。";
                    }else{
                        return
                            "1".$title . "\n".
                            "售价：" . $price . "元\n".
                            "优惠券：" . $couponInfo . "\n".
                            "预计付款金额：" . $estimate . "元\n".
                            "商品返现比例：" . $maxCommissionRate*0.8 . "%\n". //用户返现比例为0.8 后续将从用户表中获取
                            "预计返现金额：" . ($estimate * $rate * ($maxCommissionRate / 100)) . "元\n".
                            "返现计算：实付款 * " . $maxCommissionRate*0.8 . "%\n\n".
                            "复制" . $tpwd . "打开淘宝下单后将订单号发送至公众号即可绑定返现\n\n".
                            "点击下方账号管理，绑定淘宝账号，下单后系统将支持自动同步，无需回传订单号（个别情况自动同步未成功可提交订单号手动绑定）";
                    }


                }else {
                    return "出现未知异常，请稍后再试或联系客服000";
                }
            case "-1":
                return "哎呀，服务器出错了，请您再发送尝试一次或稍后再试";
                break;
            case "20002" || "200001":
                return "您发送的信息解析失败，可能不是有效口令，请检查";
                break;
            case "20001" || "200003":
                return "您发送的信息解析失败，可能是商品无任何饭粒活动";
                break;
            case "25003":
                return "券信息解析失败，请确保您发送的链接为商品链接，如链接中包含优惠券（如导购群链接等）请先进入商品再分享链接到公众号转链（注意：不要领取第三方淘礼金否则无法返利）";
                break;
            default:
                return "出现未知异常，请稍后再试或联系客服";
                break;
        }

    }

    /**
     * 未绑定会员id的用户通过商品id获取链接信息
     * @param $goodsid 传入预转链的商品id
     */
    public function privilegeLink($goodsid){
        $host = "https://openapi.dataoke.com/api/tb-service/get-privilege-link";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.3.1',
            'goodsId'=>$goodsid,
            'pid'=>config('config.pubpid')
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host .'?'. http_build_query($data);
        var_dump($url);
        //处理大淘客解析请求url
        $output = $this->curlGet($url,'get');
        $data = json_decode($output, true);//将返回数据转为数组
        return $data;
    }

    /**
     * 未绑定会员id的用户通过商品id和会员id获取链接信息
     * @param $goodsid 传入预转链的商品id
     */
    public function privilegeLinkBySpecialId($goodsid,$specialId){
        $host = "https://openapi.dataoke.com/api/tb-service/get-privilege-link";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.3.1',
            'goodsId'=>$goodsid,
            'pid'=>config('config.pubpid'),
            'specialId'=>$specialId
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host .'?'. http_build_query($data);
        var_dump($url);
        //处理大淘客解析请求url
        $output = $this->curlGet($url,'get');
        $data = json_decode($output, true);//将返回数据转为数组
        return $data;
    }

    /**
     * 通过商品id获取商品信息-联盟接口
     * @param $goodsid
     * @return 调用淘宝联盟官方接口获取商品信息后返回
     */
    public function aliParse($goodsid){
        $c = new TopClient;
        $c->appkey = config('config.aliAppKey');
        $c->secretKey = config('config.aliAppSecret');
        $c->format = "json";
        $req = new TbkItemInfoGetRequest;
        $req->setNumIids($goodsid);
        $req->setPlatform("2");
        $resp = $c->execute($req);
        $Jsondata= json_encode($resp, true);
        $data  = json_decode($Jsondata, true);
        return $data;
    }

    /**
     * 通过用户授权获得的code换取sessionid-联盟接口
     * @param $code
     * @return mixed 返回处理结果
     */
    public function getUserSessionId($code){
        try{
        $c = new TopClient;
        $c->appkey = config('config.aliAppKey');
        $c->secretKey = config('config.aliAppSecret');
        $c->format = "json";
        $req = new TopAuthTokenCreateRequest;
        $req->setCode($code);
        $resp = $c->execute($req);
        $Jsondata= json_encode($resp, true);
        $data  = json_decode($Jsondata, true);
        $data = json_decode($data['token_result'],true);
        return $data['access_token'];
        }catch (\Exception $e){
            return false;
        }
    }

    /***
     * 绑定会员返回会员id-联盟接口获取
     * @param $openid
     * @param $code
     */
    public function regMember($openid,$code){
        $sessionKey=$this->getUserSessionId($code);
        if($sessionKey == false){
            return "<script >alert('绑定出错，请联系客服处理！')</script><h1>绑定出错，请联系客服处理！</h1>";
        }
        try {
            $c = new TopClient;
            $c->appkey = config('config.aliAppKey');
            $c->secretKey = config('config.aliAppSecret');
            $c->format = "json";
            $req = new TbkScPublisherInfoSaveRequest;
            $req->setInviterCode(config('config.inviter_code'));
            $req->setInfoType("1");
            $req->setNote($openid);
            $resp = $c->execute($req, $sessionKey);
            $Jsondata= json_encode($resp, true);
            $data  = json_decode($Jsondata, true);
            Log::info($data);
            if($data['data']['special_id']!=null){
                $special_id = $data['data']['special_id'];
                $flag = app(Users::class)->updateSpecial_id($openid,$special_id);
                if($flag==1){
                    return "<script >alert('绑定成功，您的会员ID为".$special_id."')</script><h1>绑定成功，您的会员ID为".$special_id."</h1>";
                }else{
                    return "<script >alert('绑定成功但保存失败，您的会员ID为".$special_id."。您可以联系重试或联系客服提供该ID进行处理')</script><h1>绑定成功但保存失败，您的会员ID为".$special_id."。您可以联系重试或联系客服提供该ID进行处理</h1>";
                }

            }else{
                return "<script >alert('绑定出错，请联系客服处理！')</script><h1>绑定出错，请联系客服处理！</h1>";
            }
        }catch (\Exception $e){
            return "<script >alert('绑定出错，请联系客服处理！')</script><h1>绑定出错，请联系客服处理！</h1>";
        }


    }

    /**
     * 获取订单信息-联盟接口
     */
    public function getOrderList(){
        $timeQuantum = 60*100;  //默认100分钟
        $promotion = Request::post("promotion");//通过Request获取url中的promotion参数
        if($promotion == null){ //为防止报类型错误先判断是否为null
        }else if($promotion == 1 || $promotion =="1"){
            $timeQuantum = 60*15; //如果当前状态为大促期间 间隔时间缩短为15分钟
        }
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai
        $c = new TopClient;
        $c->appkey = config('config.aliAppKey');
        $c->secretKey = config('config.aliAppSecret');

        $pageNo=1;
        $testStr="";
        while (true){
            $req = new TbkOrderDetailsGetRequest;
            $req->setQueryType("1");
            $req->setPageSize("100");
            $req->setTkStatus("12");
            $req->setEndTime(date("Y-m-d H:i:s",time()));
            $req->setStartTime(date("Y-m-d H:i:s",time()-$timeQuantum));
            //开始当前时间-时间间隔，结束为当前时间，传入大促状态默认15分钟，其余时间默认100分钟
            $req->setPageNo("1");
            $req->setOrderScene("1");
            $resp = $c->execute($req);
            $Jsondata= json_encode($resp, true);
            $data  = json_decode($Jsondata, true);

            if($data['data']['has_next']=='false'){
                break;
            }else{
                $pageNo++;
            }
            $publisher_order_dto = $data['data']['results']['publisher_order_dto'];
            for($i=0;$i<sizeof($publisher_order_dto);$i++){
                $testStr=$testStr."检测到第".($i+1)."个订单\n";
                $trade_parent_id=$publisher_order_dto[i]['trade_parent_id'];
                $tk_paid_time=$publisher_order_dto[i]['tk_paid_time'];
                $item_title=$publisher_order_dto[i]['item_title'];
                $alipay_total_price=$publisher_order_dto[i]['alipay_total_price'];
                $pub_share_pre_fee=$publisher_order_dto[i]['pub_share_pre_fee'];
                $testStr=$testStr."订单ID".$trade_parent_id."\n付款时间".$tk_paid_time."\n商品标题".$item_title."\n付款金额".$alipay_total_price."\预估佣金".$pub_share_pre_fee."\n\n";
            }
            return $testStr;

        }


        $dataStr = "普通订单：\n".$Jsondata;
        $req = new TbkOrderDetailsGetRequest;
        $req->setQueryType("1");
        $req->setPageSize("100");
        $req->setTkStatus("12");
        $req->setEndTime(date("Y-m-d H:i:s",time()));
        $req->setStartTime(date("Y-m-d H:i:s",time()-$timeQuantum));
        //开始当前时间-时间间隔，结束为当前时间，传入大促参数默认15分钟，其余时间默认100分钟
        $req->setPageNo("1");
        $req->setOrderScene("3");
        $resp = $c->execute($req);
        $Jsondata= json_encode($resp, true);
        //$data  = json_decode($Jsondata, true);

        $dataStr = $dataStr."\n会员订单：".$Jsondata.date("Y-m-d h:i:s",time()).date("Y-m-d h:i:s",time()-900).time();
        return $dataStr;

    }


}

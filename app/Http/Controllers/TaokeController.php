<?php


namespace App\Http\Controllers;
use App\Http\Controllers\WeChatController;
use TopClient;
use TbkItemInfoGetRequest;
use Log;

class TaokeController extends Controller
{
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

    /**参数加密
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
     * @param $openid  传入提交转链用户的微信open
     * @param $content 传入用户的完整消息
     * @return 返回转链后的文本信息
     */
    public function parse($openid,$content)
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
                $dataArr = $this->privilegeLink($goodsid);
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
                    $maxCommissionRate = $dataArr['data']['maxCommissionRate']; //佣金比例
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
     * @param $goodsid 传入预转链的商品id
     */
    public function privilegeLink($goodsid){
        $host = "https://openapi.dataoke.com/api/tb-service/get-privilege-link";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.2.0',
            'goodsId'=>$goodsid
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


}

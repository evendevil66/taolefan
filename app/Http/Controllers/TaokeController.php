<?php


namespace App\Http\Controllers;

use App\Models\Users;
use App\Models\Orders;
use App\Http\Controllers\WeChatController;
use App\Models\BalanceRecord;
use Illuminate\Support\Facades\DB;
use Log;
use Illuminate\Support\Facades\Request;
use TopClient;
use TbkItemInfoGetRequest;
use TopAuthTokenCreateRequest;
use TbkScPublisherInfoSaveRequest;
use TbkOrderDetailsGetRequest;
use Illuminate\Support\Facades\Redis;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;


class TaokeController extends Controller
{
    /**
     * 发起get请求
     * @param $url
     * @param $method
     * @param int $post_data
     * @return bool|string
     */
    public function curlGet($url, $method, $post_data = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        } elseif ($method == 'get') {
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
        $appSecret = config('config.dtkAppSecret');
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
     * 解析并调用转链函数
     * @param $user  传入提交转链用户的信息
     * @param $content 传入用户的完整消息
     * @return 返回转链后的文本信息
     */
    public function parse($user, $content)
    {
        $rate = $user->rebate_ratio * 0.01; //返现比例
        $dataArr = $this->dtkParse($content);//调用大淘客淘宝转链接口
        $status = $dataArr['code']; //获取转链接口status
        //Log::info($dataArr);
        //return json_encode($dataArr['data']['originInfo']['title']);
        //return json_encode($dataArr);
        switch ($status) {
            case "0":
                $title = $dataArr['data']['originInfo']['title'];
                $price = $dataArr['data']['originInfo']['price'];
                $goodsid = $dataArr['data']['goodsId'];
                $image = $dataArr['data']['originInfo']['image'];
                $dataArr = null;
                if ($user->special_id != null && $user->special_id != "") {
                    $dataArr = $this->privilegeLinkBySpecialId($goodsid, $user->special_id);
                } else {
                    $dataArr = $this->privilegeLink($goodsid);
                }
                return $this->formatDataByTb($user, $rate, $dataArr, $title, $price,$image);
            case "-1":
                return "哎呀，服务器出错了，请您再发送尝试一次或稍后再试";
            case "20002":
            case "200002":
            case "200001":
            case "20001":
            case "200003":
            case "200004":
                if (strlen($content) > 10) {
                    return $this->jdParse($content, $rate, $user);
                }
            case "25003":
                return "信息解析失败，请确保您发送的链接为淘口令或商品链接";
            default:
                return "出现未知异常，请稍后再试或联系客服";
        }

    }

    /**
     * 京东商品转链
     * @param $url
     * @param $rate
     * @return string
     */
    public function jdParse($url, $rate, $user)
    {
        $skuId = $this->getJdSku($url);
        if (!$skuId) {
            return "您发送的商品无饭粒活动，或链接不支持，目前支持淘宝商品分享，京东链接分享，京东商品全名方式搜索返利";
        }
        $dataArr = $this->getJdDetails($skuId, $rate, $user);
        if (!$dataArr) {
            return "您发送的京东商品无饭粒活动哦";
        }

        $title = $dataArr["skuName"];
        $price = $dataArr["originPrice"];
        $actualPrice = $dataArr["actualPrice"];
        $couponInfo1 = $dataArr["couponAmount"];
        $couponInfo2 = $dataArr["couponConditions"];
        $commissionShare = $dataArr["commissionShare"];
        $openid = Redis::get($title);
        if ($openid != null && $openid != "" && $openid != $user->id) {
            Redis::setex($title, 1800, "repeat");
        } else {
            Redis::setex($title, 600, $user->id);
        }
        $url = $this->getJdUrl($url);
        $couponInfo = "商品无优惠券";
        if ($couponInfo1 != (-1)) {
            $couponInfo=$couponInfo2."减".$couponInfo1."元优惠券";
            $price=$price>$couponInfo1?$price-$couponInfo1:$price;
        }
        $commissionShare= round( $commissionShare * $rate,2);
        $estimate = round(($price * ($commissionShare / 100)),2);
        $image= $dataArr["picMain"];
        if (!$url) {
            $url = $this->getJdUrl($skuId);
        }
        if (!$url) {
            return "获取京东链接失败，请稍后重试或尝试其他商品，如仍无法正常转链可联系客服";
        }
        $items = [
            new NewsItem([
                'title'       => $title,
                'description' => $couponInfo.'，返现比例'.$commissionShare.'%，预计付款'.$price.'，预计返现'.$estimate.'，点击查看下单',
                'url'         => config('app.url').'/jdzjy?title='.$title.'&url='.$url.'&couponInfo='.$couponInfo.'&maxCommissionRate='.$commissionShare.'&rate='.$rate.'&estimate='.$estimate.'&image='.$image,
                'image'       => $image,
                // ...
            ]),
        ];
        $news = new News($items);
        app(WeChatController::class)->sendText($user->id, "您的商品链接已生成，10分钟内下单将自动绑定订单，如超时或未自动绑定，请复制您的订单号发送到公众号进行绑定");
        return $news;
        //return $str . "您可以点击下方链接下单，或点击下方链接加入购物车后再回到京东APP下单即可获得返利。\n" . $url . "\n\n自动跟单已开启，将在您下单后尝试自动跟单，您可以在支付2分钟后查询您的订单信息。如无法查询到订单，您可以手动发送订单号绑定。";


    }

    /**
     * 通过京东商品url或名称获取skuId
     * @param $url
     * @return false|mixed
     */
    public function getJdSku($url)
    {
        $host = "https://openapi.dataoke.com/api/dels/jd/kit/parseUrl";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.0.0',
            'url' => $url
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host . '?' . http_build_query($data);
        $output = $this->curlGet($url, 'get');
        $dataArr = json_decode($output, true);//将返回数据转为数组
        if (isset($dataArr["data"])) {
            return $dataArr["data"]["skuId"];
        } else {
            return false;
        }

    }

    /**
     * 通过skuId获取京东商品信息
     * @param $skuId
     * @param $rate
     * @return false|string
     */
    public function getJdDetails($skuId, $rate, $user)
    {
        $host = "https://openapi.dataoke.com/api/dels/jd/goods/get-details";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.0.0',
            'skuIds' => $skuId
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host . '?' . http_build_query($data);
        $output = $this->curlGet($url, 'get');
        $dataArr = json_decode($output, true);
        //Log::info($output);
        //Log::info($dataArr["data"]);
        if (isset($dataArr["data"][0])) {
            return $dataArr["data"][0];
            /*$title = $dataArr["data"][0]["skuName"];
            $price = $dataArr["data"][0]["originPrice"];
            $actualPrice = $dataArr["data"][0]["actualPrice"];
            $couponInfo1 = $dataArr["data"][0]["couponAmount"];
            $couponInfo2 = $dataArr["data"][0]["couponConditions"];
            $commissionShare = $dataArr["data"][0]["commissionShare"];
            $openid = Redis::get($title);
            if ($openid != null && $openid != "" && $openid != $user->id) {
                Redis::setex($title, 1800, "repeat");
            } else {
                Redis::setex($title, 600, $user->id);
            }
            if ($couponInfo1 == (-1)) {
                return $title . "\n" .
                    "售价：" . $price . "元\n" .
                    "商品暂无无优惠券\n" .
                    "预计付款金额：" . $price . "元\n" .
                    "商品返现比例：" . $commissionShare * $rate . "%\n" . //用户返现比例为0.8 后续将从用户表中获取
                    "预计返现金额：" . round(($price * $rate * ($commissionShare / 100)), 2) . "元\n" .
                    "返现计算：实付款 * " . $commissionShare * $rate . "%\n\n";
            } else {
                return $title . "\n" .
                    "售价：" . $price . "元\n" .
                    "优惠券：" . "满" . $couponInfo2 . "-" . $couponInfo1 . "元" . "\n" .
                    "预计付款金额：" . $actualPrice . "元\n" .
                    "商品返现比例：" . $commissionShare * $rate . "%\n" . //用户返现比例为0.8 后续将从用户表中获取
                    "预计返现金额：" . round(($actualPrice * $rate * ($commissionShare / 100)), 2) . "元\n" .
                    "返现计算：实付款 * " . $commissionShare * $rate . "%\n\n";
            }*/
        } else {
            return false;
        }
    }

    /**
     * 通过京东url或skuId获取转链后的链接
     * @param $url
     * @return false|mixed
     */
    public function getJdUrl($url)
    {
        $host = "https://openapi.dataoke.com/api/dels/jd/kit/promotion-union-convert";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.0.0',
            'unionId' => config('config.unionId'),
            'materialId' => $url
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host . '?' . http_build_query($data);
        $output = $this->curlGet($url, 'get');
        $dataArr = json_decode($output, true);//将返回数据转为数组
        if (($dataArr["code"]) == 0) {
            return $dataArr["data"]["shortUrl"];
        } else {
            return false;
        }
    }

    /**
     * 调用大淘客淘宝转链接口转换链接-淘宝大淘客接口
     * @param $content
     * @return mixed
     */
    public function dtkParse($content)
    {
        $host = "https://openapi.dataoke.com/api/tb-service/parse-content";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.0.0',
            'content' => $content
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host . '?' . http_build_query($data);
        //var_dump($url);
        //处理大淘客解析请求url
        $output = $this->curlGet($url, 'get');
        //调用统一请求函数
        $data = json_decode($output, true);
        return $data;
    }

    /**
     * 格式化商品返利信息并返回
     * @param $user
     * @param $goodsid
     * @param $rate
     * @param $dataArr
     * @return string
     */
    public function formatDataByTb($user, $rate, $dataArr, $title, $price,$image)
    {
        if ($dataArr['code'] == '0') {
            //$tbArr = $this->aliParse($goodsid);
            //$title = $tbArr['results']['n_tbk_item'][0]['title']; //商品标题
            //$price = $tbArr['results']['n_tbk_item'][0]['zk_final_price']; //商品价格
            $couponInfo = "商品无优惠券";
            $amount = "0";
            $startFee="0";
            if ($dataArr['data']['couponInfo'] != null) {
                $couponInfo = $dataArr['data']['couponInfo']; //优惠券信息
                $start = (strpos($couponInfo, "元"));
                $ci = mb_substr($couponInfo, $start);
                //return $ci;
                $end = (strpos($ci, "元"));
                $amount = mb_substr($ci, 0, $end);
                $end = (strpos($couponInfo, "元减"));
                $startFee = mb_substr($couponInfo, 1, $end-3);
                //return $startFee;
            }
            $tpwd = $dataArr['data']['tpwd']; //淘口令
            $kuaiZhanUrl = $dataArr['data']['kuaiZhanUrl']; //快站链接
            $estimate = $price>=$startFee?$price - $amount:$price; //预估付款金额
            //return $estimate."...".$price."...".$amount;
            //$longTpwd = $dataArr['data']['longTpwd']; //长淘口令
            //$start= (strpos($longTpwd,"【"));
            //$end= (strpos($longTpwd,"】"));
            //$title= substr($longTpwd,$start+1,$end-$start-1);
            $maxCommissionRate = $dataArr['data']['maxCommissionRate'] == "" || null ? $dataArr['data']['minCommissionRate'] : $dataArr['data']['maxCommissionRate']; //佣金比例
            $kuaiZhanUrl = $dataArr['data']['kuaiZhanUrl']; //商品的快站链接
            $openid = Redis::get($title);
            if ($openid != null && $openid != "" && $openid != $user->id) {
                Redis::setex($title, 1800, "repeat");
            } else {
                Redis::setex($title, 600, $user->id);
            }
            /*if($kuaiZhanUrl!=null&&$kuaiZhanUrl!=""){
                $items = [
                    new NewsItem([
                        'title'       => $title,
                        'description' => $couponInfo.'，返现比例'.round($maxCommissionRate* $rate,2).'%，预计付款'.$estimate.'，预计返现'.round(($estimate * $rate * ($maxCommissionRate / 100)), 2).'，点击查看下单',
                        'url'         => $kuaiZhanUrl,
                        'image'       => $image,
                        // ...
                    ]),
                ];
                $news = new News($items);
                app(WeChatController::class)->sendText($user->id, "您的商品链接已生成，10分钟内下单将自动绑定订单，如超时或未自动绑定，请复制您的订单号发送到公众号进行绑定");
                return $news;
            }else{*/
                $items = [
                    new NewsItem([
                        'title'       => $title,
                        'description' => $couponInfo.'，返现比例'.round($maxCommissionRate* $rate,2).'%，预计付款'.$estimate.'，预计返现'.round(($estimate * $rate * ($maxCommissionRate / 100)), 2).'，点击查看下单',
                        'url'         => config('app.url').'/tklzjy?title='.$title.'&tpwd='.$tpwd.'&couponInfo='.$couponInfo.'&maxCommissionRate='.round($maxCommissionRate * $rate,2).'&rate='.$rate.'&estimate='.round(($estimate * $rate * ($maxCommissionRate / 100)), 2).'&image='.$image,
                        'image'       => $image,
                        // ...
                    ]),
                ];
                $news = new News($items);
                app(WeChatController::class)->sendText($user->id, "您的商品链接已生成，10分钟内下单将自动绑定订单，如超时或未自动绑定，请复制您的订单号发送到公众号进行绑定");
                return $news;
                /*return
                    "1" . $title . "\n" .
                    "售价：" . $price . "元\n" .
                    "优惠券：" . $couponInfo . "\n" .
                    "预计付款金额：" . $estimate . "元\n" .
                    "商品返现比例：" . round($maxCommissionRate * $rate,2) . "%\n" . //用户返现比例为0.8 后续将从用户表中获取
                    "预计返现金额：" . round(($estimate * $rate * ($maxCommissionRate / 100)), 2) . "元\n" .
                    "返现计算：实付款 * " . $maxCommissionRate * $rate . "%\n\n" .
                    "复制" . $tpwd . "打开淘宝下单后将订单号发送至公众号即可绑定返现\n\n" .
                    "自动跟单已开启，将在您下单后尝试自动跟单，您可以在支付2分钟后查询您的订单信息。如无法查询到订单，您可以手动发送订单号绑定。";
            }*/
        } else if ($dataArr['code'] == '10006') {
            return "您发送的淘宝商品没有返利活动哦~";
        } else {
            return "出现未知异常，请稍后再试或联系客服000";
        }
    }

    /**
     * 未绑定会员id的用户通过商品id获取链接信息-淘宝大淘客接口
     * @param $goodsid 传入预转链的商品id
     */
    public function privilegeLink($goodsid)
    {
        $host = "https://openapi.dataoke.com/api/tb-service/get-privilege-link";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.3.1',
            'goodsId' => $goodsid,
            'pid' => config('config.pubpid')
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host . '?' . http_build_query($data);
        //var_dump($url);
        //处理大淘客解析请求url
        $output = $this->curlGet($url, 'get');
        $data = json_decode($output, true);//将返回数据转为数组
        return $data;
    }

    /**
     * 绑定会员id的用户通过商品id和会员id获取链接信息-淘宝大淘客接口
     * @param $goodsid 传入预转链的商品id
     */
    public function privilegeLinkBySpecialId($goodsid, $specialId)
    {
        $host = "https://openapi.dataoke.com/api/tb-service/get-privilege-link";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.3.1',
            'goodsId' => $goodsid,
            'specialId' => $specialId,
            'pid' => config('config.specialpid')
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host . '?' . http_build_query($data);
        //var_dump($url);
        //处理大淘客解析请求url
        $output = $this->curlGet($url, 'get');
        $data = json_decode($output, true);//将返回数据转为数组
        return $data;
    }

    /**
     * 通过商品id获取商品信息-淘宝联盟接口（停用）
     * 注意：本项目已不使用该函数，保留仅用于备用
     * @param $goodsid
     * @return 调用淘宝联盟官方接口获取商品信息后返回
     */
    public function aliParse($goodsid)
    {
        $c = new TopClient;
        $c->appkey = config('config.aliAppKey');
        $c->secretKey = config('config.aliAppSecret');
        $c->format = "json";
        $req = new TbkItemInfoGetRequest;
        $req->setNumIids($goodsid);
        $req->setPlatform("2");
        $resp = $c->execute($req);
        $Jsondata = json_encode($resp, true);
        $data = json_decode($Jsondata, true);
        return $data;
    }

    /**
     * 通过用户授权获得的code换取sessionid-淘宝联盟接口
     * @param $code
     * @return mixed 返回处理结果
     */
    public function getUserSessionId($code)
    {
        try {
            $c = new TopClient;
            $c->appkey = config('config.aliAppKey');
            $c->secretKey = config('config.aliAppSecret');
            $c->format = "json";
            $req = new TopAuthTokenCreateRequest;
            $req->setCode($code);
            $resp = $c->execute($req);
            $Jsondata = json_encode($resp, true);
            $data = json_decode($Jsondata, true);
            $data = json_decode($data['token_result'], true);
            return $data['access_token'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /***
     * 绑定会员返回会员id-淘宝联盟接口获取
     * @param $openid
     * @param $code
     */
    public function regMember($openid, $code)
    {
        $sessionKey = $this->getUserSessionId($code);
        if ($sessionKey == false) {
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
            $Jsondata = json_encode($resp, true);
            $data = json_decode($Jsondata, true);
            Log::info($data);
            if ($data['data']['special_id'] != null) {
                $special_id = $data['data']['special_id'];
                $flag = app(Users::class)->updateSpecial_id($openid, $special_id);
                if ($flag == 1) {
                    return "<script >alert('绑定成功，您的会员ID为" . $special_id . "')</script><h1>绑定成功，您的会员ID为" . $special_id . "</h1>";
                } else {
                    return "<script >alert('绑定成功但保存失败，您的会员ID为" . $special_id . "。您可以联系重试或联系客服提供该ID进行处理')</script><h1>绑定成功但保存失败，您的会员ID为" . $special_id . "。您可以联系重试或联系客服提供该ID进行处理</h1>";
                }

            } else {
                return "<script >alert('绑定出错，请联系客服处理！')</script><h1>绑定出错，请联系客服处理！</h1>";
            }
        } catch (\Exception $e) {
            return "<script >alert('绑定出错，请联系客服处理！')</script><h1>绑定出错，请联系客服处理！</h1>";
        }


    }


    /**
     * 获取订单信息
     * @return string
     */
    public function getOrderList()
    {
        $str = $this->getOrderListByTaobao();
        $str = $str . "\n" . $this->getOrderListByJd();
        return $str;
    }

    /**
     * 获取并存储订单信息-淘宝联盟（停用）
     * 注意：本项目已不使用该函数，保留仅用于备用
     */
    public function getOrderListByTaobaoTblm()
    {
        $count = 0;
        $timeQuantum = 90;  //默认1分30秒用于冗余以免漏单
        /**
         * 用于区分大促和非大促的代码，已废弃
         * $promotion = Request::post("promotion");//通过Request获取url中的promotion参数
         * if($promotion == null){ //为防止报类型错误先判断是否为null
         * }else if($promotion == 1 || $promotion =="1"){
         * $timeQuantum = 60*15; //如果当前状态为大促期间 间隔时间缩短为15分钟
         * }**/
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai

        $c = new TopClient;
        $c->appkey = config('config.aliAppKey');
        $c->secretKey = config('config.aliAppSecret');
        $flag = true;
        //开始处理未包含会员运营ID的普通订单
        $pageNo = 1;
        while ($flag) {
            $req = new TbkOrderDetailsGetRequest;
            $req->setQueryType("1");
            $req->setPageSize("100");
            //$req->setTkStatus("12"); 淘客订单状态，11-拍下未付款，12-付款，13-关闭，14-确认收货，3-结算成功;不传，表示所有状态
            $req->setEndTime(date("Y-m-d H:i:s", time()));
            $req->setStartTime(date("Y-m-d H:i:s", time() - $timeQuantum));
            //$req->setStartTime("2022-04-05 21:00:00");
            //$req->setEndTime("2022-04-05 21:30:00");

            //开始为（当前时间-时间间隔），结束为当前时间
            $req->setPageNo($pageNo);
            $req->setOrderScene("1");
            $resp = $c->execute($req);
            $Jsondata = json_encode($resp, true);
            $data = json_decode($Jsondata, true);
            if ($data['data']['has_next'] == 'false') {
                $flag = false;
            } else {
                $pageNo++;
            } //如不包含下一页，则本次执行结束后终止循环
            if (isset($data['data']['results'])) {
                $publisher_order_dto = $data['data']['results']['publisher_order_dto'];
                if (isset($publisher_order_dto[0])) {
                    for ($i = 0; $i < sizeof($publisher_order_dto); $i++) {
                        $count++;
                        //$testStr=$testStr."检测到第".($i+1)."个订单\n";
                        $trade_parent_id = $publisher_order_dto[$i]['trade_parent_id']; //订单号
                        $item_title = $publisher_order_dto[$i]['item_title'];//商品名称
                        $tk_paid_time = $publisher_order_dto[$i]['tk_paid_time'];//付款时间
                        $tk_status = $publisher_order_dto[$i]['tk_status'];//订单状态
                        $alipay_total_price = $publisher_order_dto[$i]['alipay_total_price'];//付款金额
                        $pub_share_pre_fee = $publisher_order_dto[$i]['pub_share_pre_fee'];//付款预估收入
                        $tk_commission_pre_fee_for_media_platform = $publisher_order_dto[$i]['tk_commission_pre_fee_for_media_platform'];//预估内容专项服务费
                        $rebate_pre_fee = 0; //预估返利金额
                        app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, -1);

                        //$testStr=$testStr."订单ID".$trade_parent_id."\n付款时间".$tk_paid_time."\n商品标题".$item_title."\n付款金额".$alipay_total_price."\预估佣金".$pub_share_pre_fee."\n\n";*/
                    }
                    //return $testStr;
                } else {
                    $count++;
                    //$testStr=$testStr."检测到第".($i+1)."个订单\n";
                    $trade_parent_id = $publisher_order_dto['trade_parent_id']; //订单号
                    $item_title = $publisher_order_dto['item_title'];//商品名称
                    $tk_paid_time = $publisher_order_dto['tk_paid_time'];//付款时间
                    $tk_status = $publisher_order_dto['tk_status'];//订单状态
                    $alipay_total_price = $publisher_order_dto['alipay_total_price'];//付款金额
                    $pub_share_pre_fee = $publisher_order_dto['pub_share_pre_fee'];//付款预估收入
                    $tk_commission_pre_fee_for_media_platform = $publisher_order_dto['tk_commission_pre_fee_for_media_platform'];//预估内容专项服务费
                    $rebate_pre_fee = 0; //预估返利金额
                    app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, -1);
                }
            }


        }
        //开始处理包含会员运营ID的订单
        $flag = true;
        $pageNo = 1;
        while ($flag) {
            $req = new TbkOrderDetailsGetRequest;
            $req->setQueryType("1");
            $req->setPageSize("100");
            //$req->setTkStatus("12"); 淘客订单状态，11-拍下未付款，12-付款，13-关闭，14-确认收货，3-结算成功;不传，表示所有状态
            $req->setEndTime(date("Y-m-d H:i:s", time()));
            $req->setStartTime(date("Y-m-d H:i:s", time() - $timeQuantum));
            $req->setPageNo("1");
            $req->setOrderScene("3");
            $resp = $c->execute($req);
            $Jsondata = json_encode($resp, true);
            $data = json_decode($Jsondata, true);
            if ($data['data']['has_next'] == 'false') {
                $flag = false;
            } else {
                $pageNo++;
            } //如不包含下一页，则本次执行结束后终止循环
            if (isset($data['data']['results'])) {
                $publisher_order_dto = $data['data']['results']['publisher_order_dto'];
                if (isset($publisher_order_dto[0])) {
                    for ($i = 0; $i < sizeof($publisher_order_dto); $i++) {
                        $count++;
                        //$testStr=$testStr."检测到第".($i+1)."个订单\n";
                        $trade_parent_id = $publisher_order_dto[$i]['trade_parent_id']; //订单号
                        $item_title = $publisher_order_dto[$i]['item_title'];//商品名称
                        $tk_paid_time = $publisher_order_dto[$i]['tk_paid_time'];//付款时间
                        $tk_status = $publisher_order_dto[$i]['tk_status'];//订单状态
                        $alipay_total_price = $publisher_order_dto[$i]['alipay_total_price'];//付款金额
                        $pub_share_pre_fee = $publisher_order_dto[$i]['pub_share_pre_fee'];//付款预估收入
                        $tk_commission_pre_fee_for_media_platform = $publisher_order_dto[$i]['tk_commission_pre_fee_for_media_platform'];//预估内容专项服务费
                        $rebate_pre_fee = 0; //预估返利金额
                        $special_id = $publisher_order_dto[$i]['special_id'];
                        app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, $special_id);

                        //$testStr=$testStr."订单ID".$trade_parent_id."\n付款时间".$tk_paid_time."\n商品标题".$item_title."\n付款金额".$alipay_total_price."\n预估佣金".$pub_share_pre_fee."\n会员ID".$special_id."\n\n";
                    }
                } else {
                    $count++;
                    //$testStr=$testStr."检测到第".($i+1)."个订单\n";
                    $trade_parent_id = $publisher_order_dto['trade_parent_id']; //订单号
                    $item_title = $publisher_order_dto['item_title'];//商品名称
                    $tk_paid_time = $publisher_order_dto['tk_paid_time'];//付款时间
                    $tk_status = $publisher_order_dto['tk_status'];//订单状态
                    $alipay_total_price = $publisher_order_dto['alipay_total_price'];//付款金额
                    $pub_share_pre_fee = $publisher_order_dto['pub_share_pre_fee'];//付款预估收入
                    $tk_commission_pre_fee_for_media_platform = $publisher_order_dto['tk_commission_pre_fee_for_media_platform'];//预估内容专项服务费
                    $rebate_pre_fee = 0; //预估返利金额
                    $special_id = $publisher_order_dto['special_id'];
                    app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, $special_id);
                }
            }

        }
        return "成功处理订单数量：" . $count;

    }

    /**
     * 获取并存储订单信息-淘宝大淘客接口
     */
    public function getOrderListByTaobao()
    {
        $count = 0;
        $timeQuantum = 90;  //默认1分30秒用于冗余以免漏单
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai
        $host = "https://openapi.dataoke.com/api/tb-service/get-order-details";
        $flag = true;
        //开始处理未包含会员运营ID的普通订单
        $pageNo = 1;
        while ($flag) {
            $endTime = date("Y-m-d H:i:s", time());
            $startTime = date("Y-m-d H:i:s", time() - $timeQuantum);
            //$startTime="2022-04-05 21:00:00";
            //$endTime="2022-04-05 21:30:00";
            $data = [
                'appKey' => config('config.dtkAppKey'),
                'version' => '1.0.0',
                'queryType' => 2,//按照付款时间查询
                'startTime' => $startTime,
                'endTime' => $endTime,
                'pageNo' => $pageNo,
                'orderScene' => 1,
                'pageSize' => 100
            ];
            $data['sign'] = $this->makeSign($data);
            $url = $host . '?' . http_build_query($data);
            $output = $this->curlGet($url, 'get');
            $data = json_decode($output, true);//将返回数据转为数组
            //return json_encode($data);
            if (json_encode($data['data']['has_next']) == 'false') {
                $flag = false;
            } else {
                $pageNo++;
            } //如不包含下一页，则本次执行结束后终止循环
            if (isset($data['data']['results'])) {
                $publisher_order_dto = $data['data']['results']['publisher_order_dto'];
                if (isset($publisher_order_dto[0])) {
                    for ($i = 0; $i < sizeof($publisher_order_dto); $i++) {
                        $count++;
                        //$testStr=$testStr."检测到第".($i+1)."个订单\n";
                        $trade_parent_id = $publisher_order_dto[$i]['trade_parent_id']; //订单号
                        $item_title = $publisher_order_dto[$i]['item_title'];//商品名称
                        $tk_paid_time = $publisher_order_dto[$i]['tk_paid_time'];//付款时间
                        $tk_status = $publisher_order_dto[$i]['tk_status'];//订单状态
                        $alipay_total_price = $publisher_order_dto[$i]['alipay_total_price'];//付款金额
                        $pub_share_pre_fee = $publisher_order_dto[$i]['pub_share_pre_fee'];//付款预估收入
                        $tk_commission_pre_fee_for_media_platform = $publisher_order_dto[$i]['tk_commission_pre_fee_for_media_platform'];//预估内容专项服务费
                        $rebate_pre_fee = 0; //预估返利金额
                        app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, -1);

                        //$testStr=$testStr."订单ID".$trade_parent_id."\n付款时间".$tk_paid_time."\n商品标题".$item_title."\n付款金额".$alipay_total_price."\预估佣金".$pub_share_pre_fee."\n\n";*/
                    }
                    //return $testStr;
                } else {
                    if (!isset($publisher_order_dto['trade_parent_id'])) {
                        break;
                    }
                    $count++;
                    //$testStr=$testStr."检测到第".($i+1)."个订单\n";
                    $trade_parent_id = $publisher_order_dto['trade_parent_id']; //订单号
                    $item_title = $publisher_order_dto['item_title'];//商品名称
                    $tk_paid_time = $publisher_order_dto['tk_paid_time'];//付款时间
                    $tk_status = $publisher_order_dto['tk_status'];//订单状态
                    $alipay_total_price = $publisher_order_dto['alipay_total_price'];//付款金额
                    $pub_share_pre_fee = $publisher_order_dto['pub_share_pre_fee'];//付款预估收入
                    $tk_commission_pre_fee_for_media_platform = $publisher_order_dto['tk_commission_pre_fee_for_media_platform'];//预估内容专项服务费
                    $rebate_pre_fee = 0; //预估返利金额
                    app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, -1);
                }
            }


        }
        //开始处理包含会员运营ID的订单
        $flag = true;
        $pageNo = 1;
        while ($flag) {
            $endTime = date("Y-m-d H:i:s", time());
            $startTime = date("Y-m-d H:i:s", time() - $timeQuantum);
            //$startTime="2022-04-05 21:00:00";
            //$endTime="2022-04-05 21:30:00";
            $data = [
                'appKey' => config('config.dtkAppKey'),
                'version' => '1.0.0',
                'queryType' => 2,//按照付款时间查询
                'startTime' => $startTime,
                'endTime' => $endTime,
                'pageNo' => $pageNo,
                'orderScene' => 3,
                'pageSize' => 100
            ];
            $data['sign'] = $this->makeSign($data);
            $url = $host . '?' . http_build_query($data);
            $output = $this->curlGet($url, 'get');
            $data = json_decode($output, true);//将返回数据转为数组
            if (json_encode($data['data']['has_next']) == 'false') {
                $flag = false;
            } else {
                $pageNo++;
            } //如不包含下一页，则本次执行结束后终止循环
            if (isset($data['data']['results'])) {
                $publisher_order_dto = $data['data']['results']['publisher_order_dto'];
                if (isset($publisher_order_dto[0])) {
                    for ($i = 0; $i < sizeof($publisher_order_dto); $i++) {
                        $count++;
                        //$testStr=$testStr."检测到第".($i+1)."个订单\n";
                        $trade_parent_id = $publisher_order_dto[$i]['trade_parent_id']; //订单号
                        $item_title = $publisher_order_dto[$i]['item_title'];//商品名称
                        $tk_paid_time = $publisher_order_dto[$i]['tk_paid_time'];//付款时间
                        $tk_status = $publisher_order_dto[$i]['tk_status'];//订单状态
                        $alipay_total_price = $publisher_order_dto[$i]['alipay_total_price'];//付款金额
                        $pub_share_pre_fee = $publisher_order_dto[$i]['pub_share_pre_fee'];//付款预估收入
                        $tk_commission_pre_fee_for_media_platform = $publisher_order_dto[$i]['tk_commission_pre_fee_for_media_platform'];//预估内容专项服务费
                        $rebate_pre_fee = 0; //预估返利金额
                        $special_id = $publisher_order_dto[$i]['special_id'];
                        app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, $special_id);

                        //$testStr=$testStr."订单ID".$trade_parent_id."\n付款时间".$tk_paid_time."\n商品标题".$item_title."\n付款金额".$alipay_total_price."\n预估佣金".$pub_share_pre_fee."\n会员ID".$special_id."\n\n";
                    }
                } else {
                    if (!isset($publisher_order_dto['trade_parent_id'])) {
                        break;
                    }
                    $count++;
                    //$testStr=$testStr."检测到第".($i+1)."个订单\n";
                    $trade_parent_id = $publisher_order_dto['trade_parent_id']; //订单号
                    $item_title = $publisher_order_dto['item_title'];//商品名称
                    $tk_paid_time = $publisher_order_dto['tk_paid_time'];//付款时间
                    $tk_status = $publisher_order_dto['tk_status'];//订单状态
                    $alipay_total_price = $publisher_order_dto['alipay_total_price'];//付款金额
                    $pub_share_pre_fee = $publisher_order_dto['pub_share_pre_fee'];//付款预估收入
                    $tk_commission_pre_fee_for_media_platform = $publisher_order_dto['tk_commission_pre_fee_for_media_platform'];//预估内容专项服务费
                    $rebate_pre_fee = 0; //预估返利金额
                    $special_id = $publisher_order_dto['special_id'];
                    app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, $special_id);
                }
            }

        }
        return "成功处理订单数量：" . $count;

    }

    /**
     * 获取并存储订单信息-京东联盟
     */
    public function getOrderListByJd()
    {
        $count = 0;
        $timeQuantum = 90;  //默认1分30秒用于冗余以免漏单
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai
        $flag = true;
        $pageNo = 1;
        $host = "https://openapi.dataoke.com/api/dels/jd/order/get-official-order-list";
        while (true) {
            $data = [
                'appKey' => config('config.dtkAppKey'),
                'version' => '1.0.0',
                'key' => config('config.jdApiKey'),
                'startTime' => date("Y-m-d H:i:s", time() - $timeQuantum),
                'endTime' => (date("Y-m-d H:i:s", time())),
                'type' => 3,
                'pageNo' => $pageNo,
            ];
            $data['sign'] = $this->makeSign($data);
            $url = $host . '?' . http_build_query($data);
            $output = $this->curlGet($url, 'get');
            $dataArr = json_decode($output, true);//将返回数据转为数组
            if ($dataArr["data"] != null) {
                foreach ($dataArr["data"] as $data) {
                    $trade_parent_id = $data["orderId"];
                    $item_title = $data["skuName"];
                    $tk_paid_time = $data["orderTime"];
                    $tk_status = $data["validCode"];
                    switch ($tk_status) {
                        case 15:
                            break;
                        case 16:
                            $tk_status = 12;
                            break;
                        case 17:
                            $tk_status = 3;
                            break;
                        default:
                            $tk_status = 13;
                    }
                    $alipay_total_price = $data["estimateCosPrice"];
                    $pub_share_pre_fee = $data["estimateFee"];
                    $tk_commission_pre_fee_for_media_platform = 0;
                    $rebate_pre_fee = 0;
                    app(Orders::class)->saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $alipay_total_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, -1);
                    $count++;
                }
                $pageNo++;
            } else {
                $flag = false;
            }
            return "成功处理订单数量：" . $count;
        }

    }

    /**
     * 查询用户1个月内订单并重新获取状态
     * @param $openid
     * @return int
     */
    public function updateOrder($openid)
    {
        $orders = app(Orders::class)->getAllWithinOneMonthByOpenid($openid);
        if ($orders == null || !isset($orders[0])) {
            return 0;
        }
        $user = app(Users::class)->getUserById($orders[0]->openid);
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai
        foreach ($orders as $order) {

            if ($order->tk_status == 13 || $order->tlf_status == 2 || $order->tlf_status == -1) {
                continue;
            }
            $flag = true;
            $pageNo = 1;
            while ($flag && strlen($order->trade_parent_id) > 13) {
                $host = "https://openapi.dataoke.com/api/tb-service/get-order-details";
                $orderScene = 1;
                if ($order->special_id == null || trim($order->special_id) == "") {
                    $orderScene = 1;
                } else {
                    $orderScene = 3;
                }
                $endTime = date("Y-m-d H:i:s", strtotime($order->tk_paid_time) + 60);
                $startTime = date("Y-m-d H:i:s", strtotime($order->tk_paid_time) - 60);
                $data = [
                    'appKey' => config('config.dtkAppKey'),
                    'version' => '1.0.0',
                    'queryType' => 2,//按照付款时间查询
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'pageNo' => $pageNo,
                    'orderScene' => $orderScene,
                    'pageSize' => 100
                ];
                $data['sign'] = $this->makeSign($data);
                $url = $host . '?' . http_build_query($data);
                $output = $this->curlGet($url, 'get');
                $data = json_decode($output, true);//将返回数据转为数组
                if (json_encode($data['data']['has_next']) == 'false') {
                    $flag = false;
                } else {
                    $pageNo++;
                } //如不包含下一页，则本次执行结束后终止循环
                if (isset($data['data']['results'])) {
                    $publisher_order_dto = $data['data']['results']['publisher_order_dto'];
                    if (isset($publisher_order_dto[0])) {
                        for ($i = 0; $i < sizeof($publisher_order_dto); $i++) {
                            $trade_parent_id = $publisher_order_dto[$i]['trade_parent_id']; //订单号
                            $item_title = $publisher_order_dto[$i]['item_title']; //商品名称
                            if ($trade_parent_id != $order->trade_parent_id || $item_title != $order->item_title) {
                                continue;
                            }
                            $tk_status = $publisher_order_dto[$i]['tk_status'];//订单状态
                            $refund_tag = $publisher_order_dto[$i]['refund_tag'];
                            $tk_earning_time = null;
                            if ($tk_status == 13 || $refund_tag == 1) {
                                //已退款，处理扣除金额
                                try {
                                    DB::beginTransaction();
                                    app(Orders::class)->changeStatusAndEarningTimeById($order->id, 13, $tk_earning_time, $refund_tag);
                                    app(BalanceRecord::class)->setRecord($order->openid, "订单" . $trade_parent_id . "退款扣除返利" . $order->rebate_pre_fee, ($order->rebate_pre_fee) * (-1));
                                    app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                    DB::commit();
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                }
                            } else if ($tk_status != $order->tk_status) {
                                try {
                                    if ($tk_status == 3) {
                                        $tk_earning_time = $publisher_order_dto[$i]['tk_earning_time'];
                                        if ($user->invite_id != null && $user->invite_id != "" && config('config.invite') == 1) {
                                            if ($user->invitation_reward == 1) {
                                                $nickname = $user->nickname == null ? "未设置昵称" : $user->nickname;
                                                app(BalanceRecord::class)->setRecord($user->invite_id, "邀请好友" . $nickname . "首次下单获得奖励" . config('config.invite_rewards') . "元", config('config.invite_rewards'));
                                                app(Users::class)->updateAvailable_balance($user->invite_id, config('config.invite_rewards'));
                                                app(Users::class)->updateInvitationReward($user->openid);
                                                $inUser = app(\App\Models\Users::class)->getUserById($user->invite_id);
                                                app(Controllers\WeChatController::class)->sendInviteTemplateMessage($user->invite_id, $nickname, $inUser->nickname, "您的好友已首单，您获得首单奖励" . config('config.invite_rewards') . "元\n永久返利奖励将在你的好友订单确认收货次月一并发放哦");
                                            }
                                        }
                                    }
                                    //处理变更状态
                                    app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $tk_earning_time);
                                    DB::commit();
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                }

                            }
                            $flag = false;
                            break;
                        }
                    } else {
                        if (!isset($publisher_order_dto['trade_parent_id'])) {
                            break;
                        }
                        $trade_parent_id = $publisher_order_dto['trade_parent_id']; //订单号
                        $item_title = $publisher_order_dto['item_title']; //商品名称
                        if ($trade_parent_id != $order->trade_parent_id || $item_title != $order->item_title) {
                            continue;
                        }
                        $tk_status = $publisher_order_dto['tk_status'];//订单状态
                        $refund_tag = $publisher_order_dto['refund_tag'];
                        $tk_earning_time = null;
                        if ($tk_status == 13 || $refund_tag == 1) {
                            //已退款，处理扣除金额
                            try {
                                $user = app(Users::class)->getUserById($order->openid);
                                DB::beginTransaction();
                                app(Orders::class)->changeStatusAndEarningTimeById($order->id, 13, $tk_earning_time);
                                app(BalanceRecord::class)->setRecord($order->openid, "订单" . $trade_parent_id . "退款扣除返利" . $order->rebate_pre_fee, ($order->rebate_pre_fee) * (-1));
                                app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollBack();
                            }
                        } else if ($tk_status != $order->tk_status) {
                            try {
                                if ($tk_status == 3) {
                                    $tk_earning_time = $publisher_order_dto['tk_earning_time'];
                                    if ($user->invite_id != null && $user->invite_id != "" && config('config.invite') == 1) {
                                        if ($user->invitation_reward == 1) {
                                            $nickname = $user->nickname == null ? "未设置昵称" : $user->nickname;
                                            app(BalanceRecord::class)->setRecord($user->invite_id, "邀请好友" . $nickname . "首次下单获得奖励" . config('config.invite_rewards') . "元", config('config.invite_rewards'));
                                            app(Users::class)->updateAvailable_balance($user->invite_id, config('config.invite_rewards'));
                                        }
                                    }
                                }
                                app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $tk_earning_time);
                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollBack();
                            }

                        }
                        $flag = false;
                        break;
                    }
                }
            }

            $flag = true;
            $pageNo = 1;
            while ($flag && strlen($order->trade_parent_id) < 17) {
                $host = "https://openapi.dataoke.com/api/dels/jd/order/get-official-order-list";
                $data = [
                    'appKey' => config('config.dtkAppKey'),
                    'version' => '1.0.0',
                    'key' => config('config.jdApiKey'),
                    'startTime' => date("Y-m-d H:i:s", strtotime($order->tk_paid_time) - 60),
                    'endTime' => date("Y-m-d H:i:s", strtotime($order->tk_paid_time) + 60),
                    'type' => 1,
                    'pageNo' => $pageNo,
                ];
                $data['sign'] = $this->makeSign($data);
                $url = $host . '?' . http_build_query($data);
                $output = $this->curlGet($url, 'get');
                $dataArr = json_decode($output, true);//将返回数据转为数组
                if ($dataArr["data"] != null) {
                    foreach ($dataArr["data"] as $data) {
                        $trade_parent_id = $data["orderId"];
                        $item_title = $data['skuName']; //商品名称
                        if ($trade_parent_id != $order->trade_parent_id || $item_title != $order->item_title) {
                            continue;
                        }
                        $tk_status = $data["validCode"];
                        $finishTime = null;

                        switch ($tk_status) {
                            case 15:
                                break;
                            case 16:
                                $tk_status = 12;
                                break;
                            case 17:
                                $tk_status = 3;
                                break;
                            default:
                                try {
                                    $user = app(Users::class)->getUserById($order->openid);
                                    DB::beginTransaction();
                                    app(Orders::class)->changeStatusAndEarningTimeById($order->id, 13, $finishTime);
                                    app(BalanceRecord::class)->setRecord($order->openid, "订单" . $trade_parent_id . "退款扣除返利" . $order->rebate_pre_fee, ($order->rebate_pre_fee) * (-1));
                                    app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                    DB::commit();
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                }
                                $tk_status = 13;
                                break;
                        }
                        if ($tk_status != 13 && $tk_status != $order->tk_status) {
                            try {
                                if ($tk_status == 3) {
                                    $finishTime = $data["finishTime"];
                                    if ($user->invite_id != null && $user->invite_id != "") {
                                        if ($user->invitation_reward == 1) {
                                            $nickname = $user->nickname == null ? "未设置昵称" : $user->nickname;
                                            app(BalanceRecord::class)->setRecord($user->invite_id, "邀请好友" . $nickname . "首次下单获得奖励" . config('config.invite_rewards') . "元", config('config.invite_rewards'));
                                            app(Users::class)->updateAvailable_balance($user->invite_id, config('config.invite_rewards'));
                                        }
                                    }
                                }
                                app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $finishTime);
                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollBack();
                            }
                        }
                    }
                    $pageNo++;
                } else {
                    $flag = false;
                }
            }
        }
    }

    /**
     * 获取上个月全部订单（建议每月执行多次）
     */
    public function updateOrderAll(): string
    {
        $tb = $this->updateOrderTb();
        $jd = $this->updateOrderJd();
        return "淘宝" . $tb . "----京东" . $jd;
    }

    /**
     * 获取上个月全部订单并更新结算状态-淘宝（建议每月执行多次）
     * @return int
     */
    public function updateOrderTb()
    {
        $count = 0;
        $orders = app(Orders::class)->getAllWithinLastMonth();
        if ($orders == null || sizeof($orders) == 0) {
            return "上月无订单";
        }
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai
        $host = "https://openapi.dataoke.com/api/tb-service/get-order-details";
        foreach ($orders as $order) {
            //如果订单状态为已退款或已结算，则跳过
            if ($order->tk_status == 13 || $order->tlf_status == 2 || $order->tlf_status == -1) {
                continue;
            }
            //如果订单号不足17位，则非淘宝订单，跳过
            if (strlen($order->trade_parent_id) < 17) {
                continue;
            }
            //如果order中不含openid，则订单未被绑定，跳过
            if ($order->openid == "" || $order->openid == null) {
                continue;
            }
            $flag = true;
            $pageNo = 1;
            while ($flag) {
                $endTime = date("Y-m-d H:i:s", strtotime($order->tk_paid_time) + 60);
                $startTime = date("Y-m-d H:i:s", strtotime($order->tk_paid_time) - 60);
                //如果订单包含运营id，则按运营订单查询。否则按常规订单查询
                if ($order->special_id == null || trim($order->special_id) == "") {
                    $orderScene = 1;
                } else {
                    $orderScene = 3;
                }
                $data = [
                    'appKey' => config('config.dtkAppKey'),
                    'version' => '1.0.0',
                    'queryType' => 2,//按照付款时间查询
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'pageNo' => $pageNo,
                    'orderScene' => $orderScene,
                    'pageSize' => 100
                ];
                $data['sign'] = $this->makeSign($data);
                $url = $host . '?' . http_build_query($data);
                $output = $this->curlGet($url, 'get');
                $data = json_decode($output, true);//将返回数据转为数组
                if (isset($data['data']['results'])) {
                    if (json_encode($data['data']['has_next']) == 'false') {
                        $flag = false;
                    } else {
                        $pageNo++;
                    } //如不包含下一页，则本次执行结束后终止循环
                    $publisher_order_dto = $data['data']['results']['publisher_order_dto'];
                    if (isset($publisher_order_dto[0])) {
                        for ($i = 0; $i < sizeof($publisher_order_dto); $i++) {
                            $trade_parent_id = $publisher_order_dto[$i]['trade_parent_id']; //订单号
                            $item_title = $publisher_order_dto[$i]['item_title']; //商品名称
                            if ($trade_parent_id != $order->trade_parent_id || $item_title != $order->item_title) {
                                continue;
                            }
                            $tk_status = $publisher_order_dto[$i]['tk_status'];//订单状态
                            $refund_tag = $publisher_order_dto[$i]['refund_tag'];
                            $tk_earning_time = null;
                            $user = app(Users::class)->getUserById($order->openid);

                            //如果status为13为已退款，refund_tag为1则已维权，扣除返利处理。
                            if ($tk_status == 13 || $refund_tag == 1) {
                                //已退款，处理扣除金额
                                try {
                                    DB::beginTransaction();
                                    app(Orders::class)->changeStatusAndEarningTimeById($order->id, 13, $tk_earning_time);
                                    app(BalanceRecord::class)->setRecord($order->openid, "订单" . $trade_parent_id . "退款扣除返利" . $order->rebate_pre_fee, ($order->rebate_pre_fee) * (-1));
                                    app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                    DB::commit();
                                    $count++;
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                }
                            } else if ($tk_status == 3) {
                                //如果订单状态为淘宝已结算（3），且站点未结算（！=2）
                                try {
                                    DB::beginTransaction();
                                    $tk_earning_time = $publisher_order_dto[$i]['tk_earning_time'];
                                    //获取淘宝结算时间
                                    $month = date("m", time());
                                    if ($month == 1) {
                                        $month = 12;
                                    } else {
                                        $month = (int)$month - 1;
                                    }
                                    $lastMonth = $month == 1 ? 12 : $month - 1;
                                    //获取上月及上上月 月份
                                    if ($month == date('m', strtotime($tk_earning_time)) || $lastMonth == date('m', strtotime($tk_earning_time))) {
                                        return date('m', strtotime($tk_earning_time)) . "1......" . $trade_parent_id;
                                        //判断如果结算时间为上月或上上月，处理结算。
                                        app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                        app(Users::class)->updateAvailable_balance($order->openid, $user->available_balance + $order->rebate_pre_fee);
                                        app(Orders::class)->changeTlfStatus($trade_parent_id, 2);

                                        //如果用户为被邀请，则更新邀请人的可用余额
                                        if ($user->invite_id != null && $user->invite_id != "" && config('config.invite') == 1) {
                                            $invite_user = app(Users::class)->getUserById($user->invite_id);
                                            app(Users::class)->updateAvailable_balance($invite_user->openid, $invite_user->available_balance + ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01));
                                            $nickname = $user->nickname == null ? "未设置昵称" : $user->nickname;
                                            app(BalanceRecord::class)->setRecord($user->invite_id, "好友" . $nickname . "下单获得提成" . ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01) . "元", ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01));
                                        }

                                    }
                                    //处理变更状态
                                    if ($order->tk_status != 3) {
                                        //如果订单状态并非已结算或退款，且发生变化，处理变更。
                                        app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $tk_earning_time);
                                    }
                                    DB::commit();
                                    $count++;
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                    return $e;
                                }
                            }
                            $flag = false;
                            break;
                        }
                    } else {
                        if (!isset($publisher_order_dto['trade_parent_id'])) {
                            break;
                        }
                        $trade_parent_id = $publisher_order_dto['trade_parent_id']; //订单号
                        $item_title = $publisher_order_dto['item_title']; //商品名称
                        if ($trade_parent_id != $order->trade_parent_id || $item_title != $order->item_title) {
                            continue;
                        }
                        $tk_status = $publisher_order_dto['tk_status'];//订单状态
                        $tk_earning_time = null;
                        $refund_tag = $publisher_order_dto['refund_tag'];
                        $user = app(Users::class)->getUserById($order->openid);

                        //如果status为13为已退款，refund_tag为1则已维权，扣除返利处理。
                        if ($tk_status == 13 || $refund_tag == 1) {
                            //已退款，处理扣除金额
                            try {
                                DB::beginTransaction();
                                app(Orders::class)->changeStatusAndEarningTimeById($order->id, 13, $tk_earning_time);
                                app(BalanceRecord::class)->setRecord($order->openid, "订单" . $trade_parent_id . "退款扣除返利" . $order->rebate_pre_fee, ($order->rebate_pre_fee) * (-1));
                                app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                DB::commit();
                                $count++;
                            } catch (\Exception $e) {
                                DB::rollBack();
                            }
                        } else if ($tk_status == 3) {
                            //如果订单状态为淘宝已结算（3），且站点未结算（！=2）
                            try {
                                DB::beginTransaction();
                                $tk_earning_time = $publisher_order_dto['tk_earning_time'];
                                //获取淘宝结算时间
                                $month = date("m", time());
                                if ($month == 1) {
                                    $month = 12;
                                } else {
                                    $month = (int)$month - 1;
                                }
                                $lastMonth = $month == 1 ? 12 : $month - 1;
                                //获取上月及上上月 月份
                                if ($month == date('m', strtotime($tk_earning_time)) || $lastMonth == date('m', strtotime($tk_earning_time))) {
                                    //判断如果结算时间为上月或上上月，处理结算。
                                    app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                    app(Users::class)->updateAvailable_balance($order->openid, $user->available_balance + $order->rebate_pre_fee);
                                    app(Orders::class)->changeTlfStatus($trade_parent_id, 2);
                                    //如果用户为被邀请，则更新邀请人的可用余额
                                    if ($user->invite_id != null && $user->invite_id != "" && config('config.invite') == 1) {
                                        $invite_user = app(Users::class)->getUserById($user->invite_id);
                                        app(Users::class)->updateAvailable_balance($invite_user->openid, $invite_user->available_balance + ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01));
                                        $nickname = $user->nickname == null ? "未设置昵称" : $user->nickname;
                                        app(BalanceRecord::class)->setRecord($user->invite_id, "好友" . $nickname . "下单获得提成" . ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01) . "元", ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01));
                                    }
                                }
                                //处理变更状态
                                if ($order->tk_status != 3) {
                                    //如果订单状态并非已结算或退款，且发生变化，处理变更。
                                    app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $tk_earning_time);
                                }
                                DB::commit();
                                $count++;
                            } catch (\Exception $e) {
                                DB::rollBack();
                                return $e;
                            }

                        }
                        $flag = false;
                        break;
                    }
                }
                $flag = false;
            }
        }
        return "处理成功" . $count . "条订单";
    }

    /**
     * 获取上个月全部订单并更新结算状态-淘宝（停用）
     * 注意：该函数使用淘宝联盟接口，已停用，此处仅作为备用
     * @return int
     */
    public function updateOrderTbTblm()
    {
        $count = 0;
        $orders = app(Orders::class)->getAllWithinLastMonth();
        if ($orders == null || sizeof($orders) == 0) {
            return "上月无订单";
        }
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai
        $c = new TopClient;
        $c->appkey = config('config.aliAppKey');
        $c->secretKey = config('config.aliAppSecret');
        foreach ($orders as $order) {
            //如果订单状态为已退款或已结算，则跳过
            if ($order->tk_status == 13 || $order->tlf_status == 2 || $order->tlf_status == -1) {
                continue;
            }
            //如果订单号不足17位，则非淘宝订单，跳过
            if (strlen($order->trade_parent_id) < 17) {
                continue;
            }
            //如果order中不含openid，则订单未被绑定，跳过
            if ($order->openid == "" || $order->openid == null) {
                continue;
            }
            $flag = true;
            $pageNo = 1;
            while ($flag) {
                $req = new TbkOrderDetailsGetRequest;
                $req->setQueryType("1");
                $req->setPageSize("10");
                //$req->setTkStatus("12"); 淘客订单状态，11-拍下未付款，12-付款，13-关闭，14-确认收货，3-结算成功;不传，表示所有状态
                $req->setEndTime(date("Y-m-d H:i:s", strtotime($order->tk_paid_time) + 60));
                $req->setStartTime(date("Y-m-d H:i:s", strtotime($order->tk_paid_time) - 60));
                $req->setPageNo($pageNo);
                //如果订单包含运营id，则按运营订单查询。否则按常规订单查询
                if ($order->special_id == null || trim($order->special_id) == "") {
                    $req->setOrderScene("1");
                } else {
                    $req->setOrderScene("3");
                }
                $resp = $c->execute($req);
                $Jsondata = json_encode($resp, true);
                $data = json_decode($Jsondata, true);
                if (isset($data['data']['results'])) {
                    if ($data['data']['has_next'] == 'false') {
                        $flag = false;
                    } else {
                        $pageNo++;
                    } //如不包含下一页，则本次执行结束后终止循环
                    $publisher_order_dto = $data['data']['results']['publisher_order_dto'];
                    if (isset($publisher_order_dto[0])) {
                        for ($i = 0; $i < sizeof($publisher_order_dto); $i++) {
                            $trade_parent_id = $publisher_order_dto[$i]['trade_parent_id']; //订单号
                            $item_title = $publisher_order_dto[$i]['item_title']; //商品名称
                            if ($trade_parent_id != $order->trade_parent_id || $item_title != $order->item_title) {
                                continue;
                            }
                            $tk_status = $publisher_order_dto[$i]['tk_status'];//订单状态
                            $refund_tag = $publisher_order_dto[$i]['refund_tag'];
                            $tk_earning_time = null;
                            $user = app(Users::class)->getUserById($order->openid);

                            //如果status为13为已退款，refund_tag为1则已维权，扣除返利处理。
                            if ($tk_status == 13 || $refund_tag == 1) {
                                //已退款，处理扣除金额
                                try {
                                    DB::beginTransaction();
                                    app(Orders::class)->changeStatusAndEarningTimeById($order->id, 13, $tk_earning_time);
                                    app(BalanceRecord::class)->setRecord($order->openid, "订单" . $trade_parent_id . "退款扣除返利" . $order->rebate_pre_fee, ($order->rebate_pre_fee) * (-1));
                                    app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                    DB::commit();
                                    $count++;
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                }
                            } else if ($tk_status == 3) {
                                //如果订单状态为淘宝已结算（3），且站点未结算（！=2）
                                try {
                                    DB::beginTransaction();
                                    $tk_earning_time = $publisher_order_dto[$i]['tk_earning_time'];
                                    //获取淘宝结算时间
                                    $month = date("m", time());
                                    if ($month == 1) {
                                        $month = 12;
                                    } else {
                                        $month = (int)$month - 1;
                                    }
                                    $lastMonth = $month == 1 ? 12 : $month - 1;
                                    //获取上月及上上月 月份
                                    if ($month == date('m', strtotime($tk_earning_time)) || $lastMonth == date('m', strtotime($tk_earning_time))) {
                                        //判断如果结算时间为上月或上上月，处理结算。
                                        app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                        app(Users::class)->updateAvailable_balance($order->openid, $user->available_balance + $order->rebate_pre_fee);
                                        app(Orders::class)->changeTlfStatus($trade_parent_id, 2);
                                    }
                                    //处理变更状态
                                    if ($order->tk_status != 3) {
                                        //如果订单状态并非已结算或退款，且发生变化，处理变更。
                                        app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $tk_earning_time);
                                    }
                                    DB::commit();
                                    $count++;
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                    return $e;
                                }
                            }
                            $flag = false;
                            break;
                        }
                    } else {
                        $trade_parent_id = $publisher_order_dto['trade_parent_id']; //订单号
                        $item_title = $publisher_order_dto['item_title']; //商品名称
                        if ($trade_parent_id != $order->trade_parent_id || $item_title != $order->item_title) {
                            continue;
                        }
                        $tk_status = $publisher_order_dto['tk_status'];//订单状态
                        $tk_earning_time = null;
                        $refund_tag = $publisher_order_dto['refund_tag'];
                        $user = app(Users::class)->getUserById($order->openid);

                        //如果status为13为已退款，refund_tag为1则已维权，扣除返利处理。
                        if ($tk_status == 13 || $refund_tag == 1) {
                            //已退款，处理扣除金额
                            try {
                                DB::beginTransaction();
                                app(Orders::class)->changeStatusAndEarningTimeById($order->id, 13, $tk_earning_time);
                                app(BalanceRecord::class)->setRecord($order->openid, "订单" . $trade_parent_id . "退款扣除返利" . $order->rebate_pre_fee, ($order->rebate_pre_fee) * (-1));
                                app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                DB::commit();
                                $count++;
                            } catch (\Exception $e) {
                                DB::rollBack();
                            }
                        } else if ($tk_status == 3) {
                            //如果订单状态为淘宝已结算（3），且站点未结算（！=2）
                            try {
                                DB::beginTransaction();
                                $tk_earning_time = $publisher_order_dto['tk_earning_time'];
                                //获取淘宝结算时间
                                $month = date("m", time());
                                if ($month == 1) {
                                    $month = 12;
                                } else {
                                    $month = (int)$month - 1;
                                }
                                $lastMonth = $month == 1 ? 12 : $month - 1;
                                //获取上月及上上月 月份
                                if ($month == date('m', strtotime($tk_earning_time)) || $lastMonth == date('m', strtotime($tk_earning_time))) {
                                    //判断如果结算时间为上月或上上月，处理结算。
                                    app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                    app(Users::class)->updateAvailable_balance($order->openid, $user->available_balance + $order->rebate_pre_fee);
                                    app(Orders::class)->changeTlfStatus($trade_parent_id, 2);
                                }
                                //处理变更状态
                                if ($order->tk_status != 3) {
                                    //如果订单状态并非已结算或退款，且发生变化，处理变更。
                                    app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $tk_earning_time);
                                }
                                DB::commit();
                                $count++;
                            } catch (\Exception $e) {
                                DB::rollBack();
                                return $e;
                            }

                        }
                        $flag = false;
                        break;
                    }
                }
                $flag = false;
            }
        }
        return "处理成功" . $count . "条订单";
    }

    /**
     * 获取上个月全部订单并更新结算状态-京东（建议每月执行多次）
     * @return int
     */
    public function updateOrderJd()
    {
        $count = 0;
        $orders = app(Orders::class)->getAllWithinLastMonth();
        $host = "https://openapi.dataoke.com/api/dels/jd/order/get-official-order-list";
        if ($orders == null || sizeof($orders) == 0) {
            return "上月无订单";
        }
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai
        foreach ($orders as $order) {
            //如果订单已退款或已结算，跳过
            if ($order->tk_status == 13 || $order->tlf_status == 2 || $order->tlf_status == -1) {
                continue;
            }
            //如果订单变化大于13位，则非京东订单，跳过
            if (strlen($order->trade_parent_id) > 13) {
                continue;
            }
            //如果order中不含openid，则订单未被绑定，跳过
            if ($order->openid == "" || $order->openid == null) {
                continue;
            }
            $flag = true;
            $pageNo = 1;
            while ($flag) {
                $data = [
                    'appKey' => config('config.dtkAppKey'),
                    'version' => '1.0.0',
                    'key' => config('config.jdApiKey'),
                    'startTime' => date("Y-m-d H:i:s", strtotime($order->tk_paid_time) - 60),
                    'endTime' => date("Y-m-d H:i:s", strtotime($order->tk_paid_time) + 60),
                    'type' => 1,
                    'pageNo' => $pageNo,
                ];
                $data['sign'] = $this->makeSign($data);
                $url = $host . '?' . http_build_query($data);
                $output = $this->curlGet($url, 'get');
                $dataArr = json_decode($output, true);//将返回数据转为数组
                if ($dataArr["data"] != null) {
                    foreach ($dataArr["data"] as $data) {
                        $trade_parent_id = $data["orderId"];
                        $item_title = $data['skuName']; //商品名称
                        //如果订单号与订单名称无法匹配，跳过
                        if ($trade_parent_id != $order->trade_parent_id || $item_title != $order->item_title) {
                            continue;
                        }
                        $tk_status = $data["validCode"];
                        $finishTime = null;
                        switch ($tk_status) {
                            case 15:
                                break;
                            case 16:
                                $tk_status = 12;
                                break;
                            case 17:
                                //京东状态码17则表示已确认收货
                                $tk_status = 3;
                                $user = app(Users::class)->getUserById($order->openid);
                                try {
                                    DB::beginTransaction();
                                    $tk_earning_time = $data['finishTime'];
                                    $month = date("m", time());
                                    if ($month == 1) {
                                        $month = 12;
                                    } else {
                                        $month = (int)$month - 1;
                                    }
                                    $lastMonth = $month == 1 ? 12 : $month - 1;
                                    if ($month == date('m', strtotime($tk_earning_time)) || $lastMonth == date('m', strtotime($tk_earning_time))) {
                                        app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                        app(Users::class)->updateAvailable_balance($order->openid, $user->available_balance + $order->rebate_pre_fee);
                                        app(Orders::class)->changeTlfStatus($trade_parent_id, 2);

                                        //如果用户为被邀请，则更新邀请人的可用余额
                                        if ($user->invite_id != null && $user->invite_id != "" && config('config.invite') == 1) {
                                            $invite_user = app(Users::class)->getUserById($user->invite_id);
                                            app(Users::class)->updateAvailable_balance($invite_user->openid, $invite_user->available_balance + ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01));
                                            $nickname = $user->nickname == null ? "未设置昵称" : $user->nickname;
                                            app(BalanceRecord::class)->setRecord($user->invite_id, "好友" . $nickname . "下单获得提成" . ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01) . "元", ($order->rebate_pre_fee * config('config.invite_ratio') * 0.01));
                                        }
                                    }
                                    //处理变更状态
                                    if ($order->tk_status != 3) {
                                        app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $tk_earning_time);
                                    }
                                    DB::commit();
                                    $count++;
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                    return $e;
                                }
                                break;
                            default:
                                try {
                                    $user = app(Users::class)->getUserById($order->openid);
                                    DB::beginTransaction();
                                    app(Orders::class)->changeStatusAndEarningTimeById($order->id, 13, $finishTime);
                                    app(BalanceRecord::class)->setRecord($order->openid, "订单" . $trade_parent_id . "退款扣除返利" . $order->rebate_pre_fee, ($order->rebate_pre_fee) * (-1));
                                    app(Users::class)->updateUnsettled_balance($order->openid, $user->unsettled_balance - $order->rebate_pre_fee);
                                    DB::commit();
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                }
                                $tk_status = 13;
                                break;
                        }
                        if ($tk_status != 13 && $tk_status != $order->tk_status) {
                            if ($tk_status == 3) {
                                $finishTime = $data["finishTime"];
                            }
                            app(Orders::class)->changeStatusAndEarningTimeById($order->id, $tk_status, $finishTime);
                        }
                    }
                    $pageNo++;
                } else {
                    $flag = false;
                }
            }
        }
        return "处理成功" . $count . "条订单";
    }


    /**
     * 获取饿了么推广淘口令信息
     * @return string
     */
    public function getElmTkl()
    {
        $host = "https://openapi.dataoke.com/api/tb-service/activity-link";
        $data = [
            'appKey' => config('config.dtkAppKey'),
            'version' => '1.0.0',
            'promotionSceneId' => "20150318020002597", //饿了么会场固定编号
            'pid' => config('config.pubpid')
        ];
        $data['sign'] = $this->makeSign($data);
        $url = $host . '?' . http_build_query($data);
        //var_dump($url);
        //处理大淘客解析请求url
        $output = $this->curlGet($url, 'get');
        $data = json_decode($output, true);//将返回数据转为数组
        $str = $data['data']['tpwd'] . "\n\n复制该信息到淘宝领取饿了么红包" . "，之后通过淘宝或相同账号的饿了么APP下单，下单2分钟后将订单号回传，即可获得返利，具体返利金额以系统最终结果为准。\n\n注意：只要通过该口令进入饿了么会场，无论是否使用红包，无论使用任何类型的红包，均可获得返利";
        return $str;
    }
}

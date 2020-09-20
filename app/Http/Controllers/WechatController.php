<?php
namespace App\Http\Controllers;
use Log;
use App\Http\Controllers\TaokeController;

class WeChatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.'); # 日志组建调用自Laravel，非EasyWechat

        $app = app('wechat.official_account');
        $app->server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    return '收到事件消息，系统暂时不处理该业务！请进行其他操作';
                    break;
                case 'text':
                    $content = $message['Content'];
                    $openid = $message['FromUserName'];
                    if (stristr($content,'关键词') != false) {
                        return
                            "1、发送包含【淘口令】的内容，系统将自动转链为返利链接回复\n".
                            "2、发送您的【淘宝订单号】或【饿了么订单号】，系统将自动匹配您的下单信息并记录\n".
                            "3、发送【订单查询】，可查询已绑定订单的状态\n".
                            "4、发送【余额查询】，可查询待结算和可结算的返利金额\n".
                            "5、发送【提现】，可将可结算金额提现至支付宝";
                    }else if(stristr($content,'订单查询') != false){
                        //调用订单查询函数，查询指定openid下的最近订单
                        return "进入订单查询模块,openid:".$openid ;
                    }else if(stristr($content,'余额查询') != false){
                        //调用余额查询函数，查询指定openid下的余额状态
                        return "进入余额查询模块,openid:".$openid ;
                    }else if(stristr($content,'提现') != false){
                        //调用提现函数，清除对应openid下的可结算金额，记录提现后台等待人工返现
                        return "进入提现模块,openid:".$openid ;
                    }else if(preg_match("/^\d{17,20}$/",$content)){
                        //调用淘宝联盟订单查询函数，查询对应订单号，如结果不为false，则存入用户订单列表，并返回预估返现信息
                        return "进入订单绑定模块,openid:".$openid ;
                    }else{
                        //调用大淘客接口对所收到的信息进行解析转链，并将优惠券、返利信息返回
                        return app(TaokeController::class) -> parse($openid,$content);
                    }
                    break;
                default:
                    return '哎呀,' . config('config.name') .
                        '暂时还不支持此类消息呢，你可以发送淘口令、订单号或指定关键词给我哦！
                        如不知道关键词是什么，可回复[关键词]给我获取哦';
                    break;
            }
        });
        return $app->server->serve();
    }
}


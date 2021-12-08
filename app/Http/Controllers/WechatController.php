<?php

namespace App\Http\Controllers;

use EasyWeChat\OfficialAccount\Application;
use Log;
use App\Http\Controllers\TaokeController;
use App\Models\Users;

class WeChatController extends Controller
{
    protected $buttons = [
        [
            "name" => "使用教程",
            "sub_button" => [
                [
                    "type" => "click",
                    "name" => "使用教程",
                    "key" => "Course"
                ],
                [
                    "type" => "click",
                    "name" => "联系客服",
                    "key" => "Contact"
                ],
            ],
        ],
        [
            "name" => "饭粒中心",
            "sub_button" => [
                [
                    "type" => "click",
                    "name" => "订单查询",
                    "key" => "Order"
                ],
                [
                    "type" => "click",
                    "name" => "余额查询",
                    "key" => "Price"
                ],
                [
                    "type" => "click",
                    "name" => "提现",
                    "key" => "Receive"
                ],
            ],
        ],
        [
            "name" => "个人中心",
            "sub_button" => [
                [
                    "type" => "click",
                    "name" => "资料补全",
                    "key" => "Supplement"
                ],
                [
                    "type" => "click",
                    "name" => "手动注册",
                    "key" => "Manual"
                ],
                [
                    "type" => "click",
                    "name" => "绑定淘宝",
                    "key" => "Bind"
                ],
            ],
        ],
    ];

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
            $openid = $message['FromUserName'];
            //访问数据库获取openid对应用户信息并返回用户对象
            $user = app(Users::class)->getUserById($openid);
            switch ($message['MsgType']) {
                case 'event':
                    if ($message['Event'] == 'subscribe') {
                        if ($user == null) {
                            $reg = app(Users::class)->userRegistration($openid);
                            if ($reg == 1) {
                                return "欢迎关注" . config('config.name') . "，已为您自动注册成功。" . "\n建议您点击下方个人中心-资料补全，更新资料后开始省钱之旅";
                            } else {
                                return "欢迎关注" . config('config.name') . "您的自动注册请求失败，可能系统出现异常，您可以点击下方个人中心-快速注册进行手动注册";
                            }
                        } else {
                            return "欢迎回到" . config('config.name') . "，已为您恢复账号。" . "\n您可以点击下方个人中心-资料补全，确保资料完整即可开始省钱之旅";
                        }
                    }
                    switch ($message['EventKey']) {
                        case 'Course':
                            return
                                "您可以直接将想购买的商品淘口令发送到这里，系统即可自动转链哦，复制转链后的淘口令打开淘宝继续购物，即可享受优惠和饭粒啦！\n\n" .
                                "首次提现前，请先在个人中心-补全资料处，补齐提现资料，即可进行提现\n\n" .
                                "另外建议您点击个人中心-绑定淘宝，绑定常购物的淘宝账号，即可自动跟踪订单，无需返回公众号填写订单号哦";
                        case 'Contact':
                            return "wxid_ts6hzzyc160x22\n" . "您可以复制上方微信号，添加客服微信咨询";
                        case 'Supplement':
                            $url = config('config.apiUrl') . "/reg/" . $openid . "?username=" . $user->username . "&nickname=" . $user->nickname . "&alipay=" . $user->alipay_id;
                            if ($user->username != null && $user->username != "") {
                                return "您已填写过资料，<a href=\"" . $url . "\">请点此进行资料修改</a>";
                            } else {
                                return "<a href=\"" . $url . "\">请点此进行资料补全</a>";
                            }


                        case 'Manual':
                            return "注册请求已提交，请等待人工为您处理。";
                        case 'Bind':
                            $url = config('config.apiUrl') . "/bind/" . $openid;
                            if($user->special_id == null && $user->special_id == ""){
                                return "<a href=\"" . $url . "\">请点此进行淘宝绑定</a>，如提示复制到浏览器打开，可在浏览器继续完成绑定";
                            }else{
                                return "您已经绑定过淘宝账号了，<a href=\"" . $url . "\">点此进行更换或重新绑定</a>，如提示复制到浏览器打开，可在浏览器继续完成绑定";
                            }

                        case 'Receive':
                            if ($user->alipay_id != null && $user->alipay_id != "") {
                                return "您还没有绑定提现账号，请在资料补全功能下填写";
                            } else {
                                return "提现请求发起成功，提现成功0元，将在24小时发送到您的支付宝账号：" . $user->alipay_id;
                            }
                        case 'Order':
                            $url = config('config.apiUrl') . "/order/" . $openid;
                            return "<a href=\"" . $url . "\">点击此处快速查询订单</a>";
                        case 'Price':
                            return "您当前可提现余额0元，待结算金额0元，待结算金额将在您的订单确认收货第10天进行结算。";
                        default:
                            return "您的请求暂时无法处理，如有疑问请联系客服";
                    }

                case 'text':
                    $content = $message['Content'];

                    //通过用户对象检测空信息，如有信息为空，则依次使用$content填充
                    //if()
                    if (stristr($content, '关键词') != false) {
                        return
                            "1、发送包含【淘口令】的内容，系统将自动转链为返利链接回复\n" .
                            "2、发送您的【淘宝订单号】或【饿了么订单号】，系统将自动匹配您的下单信息并记录\n" .
                            "3、发送【订单查询】，可查询已绑定订单的状态\n" .
                            "4、发送【余额查询】，可查询待结算和可结算的返利金额\n" .
                            "5、发送【提现】，可将可结算金额提现至支付宝";
                    } else if (stristr($content, '订单查询') != false) {
                        //调用订单查询函数，查询指定openid下的最近订单
                        return "进入订单查询模块,openid:" . $openid;
                    } else if (stristr($content, '余额查询') != false) {
                        //调用余额查询函数，查询指定openid下的余额状态
                        return "进入余额查询模块,openid:" . $openid;
                    } else if (stristr($content, '提现') != false) {
                        //调用提现函数，清除对应openid下的可结算金额，记录提现后台等待人工返现
                        return "进入提现模块,openid:" . $openid;
                    } else if (stristr($content, '注册') != false) {
                        //调用提现函数，清除对应openid下的可结算金额，记录提现后台等待人工返现
                        $url = config('config.apiUrl') . "/reg/" . $openid;
                        return "<a href=\"" . $url . "\">请点此进行注册</a>";
                    } else if (preg_match("/^\d{17,20}$/", $content)) {
                        //调用淘宝联盟订单查询函数，查询对应订单号，如结果不为false，则存入用户订单列表，并返回预估返现信息
                        return "进入订单绑定模块,openid:" . $openid;
                    } else if (stristr($content, '创建菜单') != false) {
                        $this->setButton();
                        return "设置菜单";
                    } else {
                        //调用大淘客接口对所收到的信息进行解析转链，并将优惠券、返利信息返回
                        return app(TaokeController::class)->parse($user, $content);
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

    public function setButton()
    {
        $app = app('wechat.official_account');
        $app->menu->create($this->buttons);
    }
}


<?php

namespace App\Http\Controllers;

use App\Http\Controllers\TaokeController;
use App\Models\BalanceRecord;
use App\Models\Receive;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use App\Models\Orders;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Media;
use App\Packages\tools\ImageProcess;

class WeChatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        $app = app('wechat.official_account');
        $app->server->push(function ($message) use ($app) {
            $openid = $message['FromUserName'];
            //访问数据库获取openid对应用户信息并返回用户对象
            $user = app(Users::class)->getUserById($openid);
            //$vuser = $app->user->get($openid);
            switch ($message['MsgType']) {
                case 'event':
                    if ($message['Event'] == 'subscribe') {
                        if ($message['EventKey'] == null || $message['EventKey'] == "") {
                            if ($user == null) {
                                $reg = app(Users::class)->userRegistration($openid, null);
                                if ($reg == 1) {
                                    return "欢迎关注" . config('config.name') . "，已为您自动注册成功。" . "\n建议您<a href='".config('config.apiUrl') . "/reg"."'>点击此处进行资料补全</a>，更新资料后开始省钱之旅";
                                } else {
                                    return "欢迎关注" . config('config.name') . "您的自动注册请求失败，可能系统出现异常，您可以重新关注公众号或联系客服";
                                }
                            } else {
                                return "欢迎回到" . config('config.name') . "，已为您恢复账号。" . "\n您可以点击下方个人中心-资料补全，确保资料完整即可开始省钱之旅";
                            }
                        } else {
                            $invite_id = substr($message['EventKey'], 8);
                            if ($invite_id == $openid) {
                                return "欢迎回来，您不能邀请自己哦";
                            } else if ($user != null) {
                                return "您已经是我们的老用户了，不能再被邀请了哦~";
                            } else {
                                $reg = app(Users::class)->userRegistration($openid, $invite_id);
                                if ($reg == 1) {
                                    return "欢迎关注" . config('config.name') . "，已为您自动注册成功。" . "\n建议您<a href='".config('config.apiUrl') . "/reg"."'>点击此处进行资料补全</a>，更新资料后开始省钱之旅";
                                } else {
                                    return "欢迎关注" . config('config.name') . "您的自动注册请求失败，可能系统出现异常，您可以重新关注公众号或联系客服";
                                }
                            }
                        }


                    }
                    //return $message['EventKey'];
                    switch ($message['EventKey']) {
                        case 'Course':
                            return
                                "您可以直接将想购买的商品淘宝（淘口令），京东（链接或商品名称）发送到这里，系统即可自动转链哦，复制转链后的淘口令打开淘宝继续购物，或点击京东链接购物，即可享受优惠和饭粒啦！\n\n" .
                                "首次提现前，请先在个人中心-补全资料处，补齐提现资料，即可进行提现\n\n" .
                                "另外您点击个人中心-绑定淘宝，绑定常购物的淘宝账号，即可自动跟踪订单，无需返回公众号填写订单号哦（测试中，可能暂时无法正常自动跟单）";
                        case 'Contact':
                            if (config('config.contactType') == 1) {
                                return new Image(config('config.contactMediaId'));
                            }
                            return config('config.contactId') . "\n" . "您可以复制上方微信号，添加客服微信咨询";
                        /*case 'Supplement':
                            $url = config('config.apiUrl') . "/reg/" . $openid . "?username=" . $user->username . "&nickname=" . $user->nickname . "&alipay=" . $user->alipay_id;
                            if ($user->username != null && $user->username != "") {
                                return "您已填写过资料，<a href=\"" . $url . "\">请点此进行资料修改</a>";
                            } else {
                                return "<a href=\"" . $url . "\">请点此进行资料补全</a>";
                            }
                        case 'Manual':
                            return "注册请求已提交，请等待人工为您处理。";*/
                        case 'Bind':
                            $url = config('config.apiUrl') . "/bind/" . $openid;
                            if ($user->special_id == null && $user->special_id == "") {
                                return "<a href=\"" . $url . "\">请点此进行淘宝绑定</a>，如提示复制到浏览器打开，可在浏览器继续完成绑定";
                            } else {
                                return "您已经绑定过淘宝账号了，<a href=\"" . $url . "\">点此进行更换或重新绑定</a>，如提示复制到浏览器打开，可在浏览器继续完成绑定";
                            }
                        case 'Receive':
                            if ($user->alipay_id == null || $user->alipay_id == "") {
                                return "您还没有绑定提现账号，请在资料补全功能下填写";
                            } else {
                                $day = date("d", time());
                                if ($day >= 5 && $day <= 31) {
                                    if ($user->available_balance > 0.1) {
                                        try {
                                            DB::beginTransaction();
                                            app(Users::class)->updateAvailable_balance($user->id, 0);
                                            app(Receive::class)->applyReceive($user->id, $user->available_balance, $user->nickname);
                                            app(BalanceRecord::class)->setRecord($user->id, "提现申请扣除余额" . $user->available_balance . "元", (double)($user->available_balance) * (-1.00));
                                            DB::commit();
                                            //return "提现请求发起成功，提现成功" . $user->available_balance . "元，将在24小时发送到您的支付宝账号：" . $user->alipay_id;
                                        } catch (\Exception $e) {
                                            DB::rollBack();
                                            return "提现请求出错，请重试或联系客服";
                                        }
                                    } else {
                                        return "可用余额大于0.1元才可以提现哦，您当前可提现余额" . $user->available_balance . "元，待结算金额" . $user->unsettled_balance . "元";
                                    }
                                }else{
                                    return "平台每月5日前为对账期，暂时关闭提现，请在本月5日后进行提现";
                                }

                            }
                        case 'ReceiveStatus':
                            $receive = app(Receive::class)->getReceiveStatus($user->id);
                            if ($receive != null) {
                                if ($receive->status == 0) {
                                    return "您最近一次提现时间为：" . $receive->receive_date . "\n提现金额为：" . $receive->amount . "元\n目前仍在处理请耐心等待";
                                } else if ($receive->status == 1) {
                                    return "您最近一次提现时间为：" . $receive->receive_date . "\n提现金额为：" . $receive->amount . "元\n已于" . $receive->process_time . "处理完成，如未收到请联系客服";
                                } else {
                                    return "您最近一次提现时间为：" . $receive->receive_date . "\n提现金额为：" . $receive->amount . "元\n于" . $receive->process_time . "被拒绝\n拒绝原因为：" . $receive->reason . "\n请根据原因处理后重新提现，或联系客服";
                                }
                            } else {
                                return "您还没有提现过哦～";
                            }
                        /*case 'Order':
                            $url = config('config.apiUrl') . "/loading?openid=" . $openid;
                            return "<a href=\"" . $url . "\">点击此处快速查询订单</a>";*/
                        case 'Price':
                            return "您当前可提现余额" . $user->available_balance . "元，待结算金额" . $user->unsettled_balance . "元。待结算余额会在您确认收货次月到账可提现余额哦～\n".
                                "如需查询余额变动明细，<a href='".config('config.apiUrl')."/balanceRecord'>请点击此处</a>";
                        /*case 'BalanceRecord':
                            $url = config('config.apiUrl') . "/balanceRecord?openid=" . $openid;
                            return "<a href=\"" . $url . "\">点击此处查询余额变动</a>";*/
                        case 'ElmTb':
                            return app(TaokeController::class)->getElmTkl();
                        case 'ElmWx':
                            /*$items = [
                                new NewsItem([
                                    'title'       => "饿了么饭粒微信小贴士",
                                    'description' => '点击进入页面查询',
                                    'url'         => config('config.eleme_newsUrl'),
                                    'image'       => config('config.eleme_imgUrl'),
                                    // ...
                                ]),
                            ];
                            $news = new News($items);
                            return $news;*/
                            return "<a data-miniprogram-appid=\"wxece3a9a4c82f58c9\" data-miniprogram-path=\"" . config('config.eleme_url') . "\">点击进入饿了么下单</a>\n\n通过小程序下单后，将订单号发送至公众号即可跟单成功哦，在小程序中还有机会领到大额优惠券。 ";
                        case 'Invite':
                            if (config('config.invite') != 1) {
                                return "暂时不开放邀请功能";
                            }
                            //生成qrcode 有效期7天
                            $result = $app->qrcode->temporary($openid, 7 * 24 * 3600);
                            $ticket = $result["ticket"];
                            $url = $app->qrcode->url($ticket);
                            $content = file_get_contents($url);
                            file_put_contents('./code.jpg', $content); // 写入文件
                            if(config('config.createPoster')==0){
                                $media = $app->media->uploadImage("./code.jpg");
                                $media_id = $media["media_id"];
                                $image = new Image($media_id);
                                $app->customer_service->message($image)->to($openid)->send();
                                unlink("./code.jpg");
                            }else{
                                app(ImageProcess::createPoster());
                                $media = $app->media->uploadImage("./posterCode.jpg");
                                $media_id = $media["media_id"];
                                $image = new Image($media_id);
                                $app->customer_service->message($image)->to($openid)->send();
                                unlink("./code.jpg");
                                unlink("./posterCode.jpg");
                            }


                            $app->customer_service->message("您的专属邀请码已生成\n您邀请的好友第一笔订单确认收货，您可以获得" . config('config.invite_rewards') . "元奖励，并永久享受分成。\n\n注意：当您的好友通过您的专属码关注，并首次打开资料补齐页面时，您才会收到邀请成功通知，但是即使未收到通知也不会影响奖励发放哦~")->to($openid)->send();
                            $count = app(Users::class)->getUserCountByInviteId($openid);
                            if($count>0){
                                $price = app(Users::class)->getUserCountByInviteIdAndInvitationReward($openid);
                                return "您已经邀请" . $count . "位好友，已完成确认收货并结算给您的邀新奖励为" . $price . "元，请继续努力哦！";

                            }
                            return "您已成功邀请0人，获得邀新奖励0元，请继续努力哦！";


                        default:
                            $user = app(Users::class)->getUserById($message['EventKey']);
                            if ($openid == $message['EventKey']) {
                                return "您已经是我们的老用户了，不能邀请自己哦！";
                            }
                            if ($user != null) {
                                return "您已经是我们的老用户了，不能被邀请了哦！";
                            }
                            return "您的请求暂时无法处理，如有疑问请联系客服";


                    }

                case 'text':
                    $content = $message['Content'];
                    if (stristr($content, '关键词') != false || stristr($content, '帮助') != false) {
                        return
                            "1、发送包含【淘口令】的内容，系统将自动转链为返利链接回复\n" .
                            "2、发送您的【淘宝订单号】，系统将自动匹配您的下单信息并记录\n" .
                            "3、发送【补全信息】，可补全您的个人资料和提现信息\n" .
                            "4、发送【绑定淘宝】，可绑定您的淘宝账号\n" .
                            "5、发送【订单查询】，可查询已绑定订单的状态\n" .
                            "6、发送【余额查询】，可查询待结算和可结算的返利金额\n" .
                            "7、发送【余额变动】，可查询余额变动记录\n" .
                            "8、发送【提现】，可将可结算金额提现至支付宝\n" .
                            "8、发送【饿了么】，可获得饿了么返利红包\n" .
                            "10、发送【提现状态】，可查询您最近一次提现的处理情况";
                    } else if (stristr($content, '补全信息') != false) {
                        $url = config('config.apiUrl') . "/reg/" . $openid . "?username=" . $user->username . "&nickname=" . $user->nickname . "&alipay=" . $user->alipay_id;
                        if ($user->username != null && $user->username != "") {
                            return "您已填写过资料，<a href=\"" . $url . "\">请点此进行资料修改</a>";
                        } else {
                            return "<a href=\"" . $url . "\">请点此进行资料补全</a>";
                        }
                    } else if (stristr($content, '绑定淘宝') != false) {
                        $url = config('config.apiUrl') . "/bind/" . $openid;
                        if ($user->special_id == null && $user->special_id == "") {
                            return "<a href=\"" . $url . "\">请点此进行淘宝绑定</a>，如提示复制到浏览器打开，可在浏览器继续完成绑定";
                        } else {
                            return "您已经绑定过淘宝账号了，<a href=\"" . $url . "\">点此进行更换或重新绑定</a>，如提示复制到浏览器打开，可在浏览器继续完成绑定";
                        }
                    } else if (stristr($content, '订单查询') != false) {
                        $url = config('config.apiUrl') . "/loading?openid=" . $openid;
                        return "<a href=\"" . $url . "\">点击此处快速查询订单</a>";
                    } else if (stristr($content, '余额查询') != false) {
                        return "您当前可提现余额" . $user->available_balance . "元，待结算金额" . $user->unsettled_balance . "元。待结算余额会在您确认收货次月到账可提现余额哦～";
                    } else if (stristr($content, '余额变动') != false) {
                        $url = config('config.apiUrl') . "/balanceRecord?openid=" . $openid;
                        return "<a href=\"" . $url . "\">点击此处查询余额变动</a>";
                    } else if (stristr($content, '提现') != false) {
                        if ($user->alipay_id == null || $user->alipay_id == "") {
                            return "您还没有绑定提现账号，请在资料补全功能下填写";
                        } else {
                            $day = date("d", time());
                            if ($day >= 5 && $day <= 31) {
                                if ($user->available_balance > 0.1) {
                                    try {
                                        DB::beginTransaction();
                                        app(Users::class)->updateAvailable_balance($user->id, 0);
                                        app(Receive::class)->applyReceive($user->id, $user->available_balance, $user->nickname);
                                        app(BalanceRecord::class)->setRecord($user->id, "提现申请扣除余额" . $user->available_balance . "元", (double)($user->available_balance) * (-1.00));
                                        DB::commit();
                                        //return "提现请求发起成功，提现成功" . $user->available_balance . "元，将在24小时发送到您的支付宝账号：" . $user->alipay_id;
                                    } catch (\Exception $e) {
                                        DB::rollBack();
                                        return "提现请求出错，请重试或联系客服";
                                    }
                                } else {
                                    return "可用余额大于0.1元才可以提现哦，您当前可提现余额" . $user->available_balance . "元，待结算金额" . $user->unsettled_balance . "元";
                                }
                            }else{
                                return "平台每月5日前为对账期，暂时关闭提现，请在本月5日后进行提现";
                            }

                        }
                    } else if (stristr($content, '提现状态') != false) {
                        $receive = app(Receive::class)->getReceiveStatus($user->id);
                        if ($receive != null) {
                            if ($receive->status == 0) {
                                return "您最近一次提现时间为：" . $receive->receive_date . "\n提现金额为：" . $receive->amount . "元\n目前仍在处理请耐心等待";
                            } else if ($receive->status == 1) {
                                return "您最近一次提现时间为：" . $receive->receive_date . "\n提现金额为：" . $receive->amount . "元\n已于" . $receive->process_time . "处理完成，如未收到请联系客服";
                            } else {
                                return "您最近一次提现时间为：" . $receive->receive_date . "\n提现金额为：" . $receive->amount . "元\n于" . $receive->process_time . "被拒绝\n拒绝原因为：" . $receive->reason . "\n请根据原因处理后重新提现，或联系客服";
                            }
                        } else {
                            return "您还没有提现过哦～";
                        }
                    } else if (preg_match("/^\d{10,20}$/", $content)) {
                        //根据数据库存储订单信息匹配订单并操作数据绑定
                        return app(Orders::class)->ModifyOpenIdByTradeParentIdAndModifyRebateAmountAccordingToRebateRatio(trim($content), $user);
                    } else if (stristr($content, '创建菜单') != false) {
                        $this->setButton();
                        return "设置菜单";
                    } else if (stristr($content, '饿了么') != false) {
                        return app(TaokeController::class)->getElmTkl();
                        //如需使用文章方式返回，请自行解除下方注释并注释上一条内容
                        /*$items = [
                            new NewsItem([
                                'title'       => "饿了么饭粒微信小贴士",
                                'description' => '点击进入页面查询',
                                'url'         => config('config.eleme_newsUrl'),
                                'image'       => config('config.eleme_imgUrl'),
                                // ...
                            ]),
                        ];
                        $news = new News($items);
                        return $news;*/

                    } /**else if (stristr($content, '酷友报道') != false) {
                     * $result = app(Users::class)->modifyRebateRatioById($user->id, 80);
                     * return $result==1?"欢迎酷友，您的返利比例已调整为80%":"欢迎酷友，您的返利比例调整失败，请确认您是否已经调整过返利比例或联系客服处理哦～";
                     * }**/
                    else {
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
        $buttons = [
            [
                "name" => "优惠汇总",
                "sub_button" => [
                    [
                        "type" => "click",
                        "name" => "饿了么饭粒",
                        "key" => "ElmWx"
                    ],
                    [
                        "type" => "view",
                        "name" => "高返活动商品",
                        "url" => config('config.url')
                    ],
                ],
            ],
            [
                "name" => "饭粒中心",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "订单查询",
                        "url" => config('config.apiUrl') . "/loading"
                    ],
                    [
                        "type" => "click",
                        "name" => "余额查询",
                        "key" => "Price"
                    ],
                    [
                        "type" => "click",
                        "name" => "饿了么",
                        "key" => "ElmWx"
                    ],
                    [
                        "type" => "click",
                        "name" => "提现",
                        "key" => "Receive"
                    ],
                    [
                        "type" => "click",
                        "name" => "提现结果查询",
                        "key" => "ReceiveStatus"
                    ],
                ],
            ],
            [
                "name" => "个人中心",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "资料补全",
                        "url" => config('config.apiUrl') . "/reg"
                    ],
                    [
                        "type" => "click",
                        "name" => "邀请好友",
                        "key" => "Invite"
                    ],
                    [
                        "type" => "click",
                        "name" => "绑定淘宝",
                        "key" => "Bind"
                    ],
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
        ];
        $app = app('wechat.official_account');
        $app->menu->create($buttons);
    }

    public function setIndustry()
    {
        $app = app('wechat.official_account');
        $app->template_message->setIndustry(1, 2);
        $app->template_message->addTemplate("OPENTM418431364");
        $app->template_message->addTemplate("OPENTM407328898");
        $app->template_message->addTemplate("OPENTM415143385");
    }

    /**
     * 发送订单通知模板消息
     * @param $openid
     * @param $platform
     * @param $title
     * @param $price
     * @param $rebate_pre_fee
     * @return void
     */
    public function sendTemplateMessage($openid, $platform, $title, $price, $rebate_pre_fee)
    {
        $app = app('wechat.official_account');
        $app->template_message->send([
            'touser' => $openid,
            'template_id' => config("config.template_id"),
            'url' => config("config.apiUrl") . "/loading",
            'data' => [
                'first' => '您的订单已绑定成功',
                'keyword1' => $platform,
                'keyword2' => $title,
                'keyword3' => $price,
                'keyword4' => $rebate_pre_fee,
                'remark' => "您的返利金额将于确认收货后次月转为可提现余额，如有疑问请联系客服",
            ],
        ]);
    }

    /**
     * 发送提现通知模板消息
     */
    public function sendReceiveTemplateMessage($openid, $title, $price, $remark)
    {
        $app = app('wechat.official_account');
        $app->template_message->send([
            'touser' => $openid,
            'template_id' => config("config.withdraw_template_id"),
            'url' => config("config.apiUrl") . "/loading",
            'data' => [
                'first' => $title,
                'keyword1' => $price,
                'keyword2' => $remark,
                'remark' => "如有疑问请联系客服",
            ],
        ]);

    }

    /**
     * 发送邀请好友成功通知模板消息
     */
    public function sendInviteTemplateMessage($openid, $nickname,$inNickname,$remark){
        $app = app('wechat.official_account');
        date_default_timezone_set("Asia/Shanghai");//设置当前时区为Shanghai
        $app->template_message->send([
            'touser' => $openid,
            'template_id' => config("config.invite_template_id"),
            'url' => config("config.apiUrl") . "/loading",
            'data' => [
                'first' => $remark,
                'keyword1' => $nickname,
                'keyword2' => date("Y-m-d H:i:s", time()),
                'keyword3' => $inNickname,
                'remark' => "请继续努力哦~",
            ],
        ]);
    }

    /**
     * 发送文本信息到微信
     */
    public function sendText($openid, $content)
    {
        $app = app('wechat.official_account');
        $app->customer_service->message($content)->to($openid)->send();
    }
}


<?php


namespace App\Models;

use App\Http\Controllers\WeChatController;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use mysql_xdevapi\Exception;
use Illuminate\Support\Facades\Redis;
use Log;


class Orders extends Model
{
    protected $table = "orders";

    /**
     * 获取所有订单信息（备用函数，一般使用时间段及分页查询）
     * @return \Illuminate\Support\Collection 返回订单对象
     */
    public function getAll()
    {
        return DB::table($this->table)->get();
    }

    /**
     * 储存订单
     * @param $trade_parent_id 订单号
     * @param $item_title 商品名称
     * @param $tk_paid_time 下单时间
     * @param $tk_status 订单状态 3：订单结算，12：订单付款， 13：订单失效，14：订单成功
     * @param $pay_price 付款金额
     * @param $pub_share_pre_fee 付款预估收入
     * @param $tk_commission_pre_fee_for_media_platform 预估内容专项服务费
     * @param $rebate_pre_fee 预估返利金额
     * @param $special_id 会员运营id
     * @return bool 如执行成功返回1
     */
    public function saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $pay_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $rebate_pre_fee, $special_id)
    {
        try {
            $orders = DB::table($this->table)->where('trade_parent_id', $trade_parent_id)->get();
            //查询系统内是否有相同订单号
            $flag = true;
            if(isset($orders->trade_parent_id)){//如果该项存在，则表示订单只有一条
                if ($orders->trade_parent_id == $trade_parent_id && $orders->item_title == $item_title && $orders->pay_price == $pay_price) {
                    //如果有相同订单号且商品名称相同，则不插入
                    $flag = false;
                }
            }else{//否则表示有多个订单
                foreach ($orders as $order) {
                    if ($order->trade_parent_id == $trade_parent_id && $order->item_title == $item_title && $order->pay_price == $pay_price) {
                        //如果有相同订单号且商品名称相同，则不插入
                        $flag = false;
                        break;
                    }
                }
            }

            //if ($order != null) {
            //    return false;
            //}

            if ($flag) {
                $flag = DB::table($this->table)->insert([
                    'trade_parent_id' => $trade_parent_id,
                    'item_title' => $item_title,
                    'tk_paid_time' => $tk_paid_time,
                    'tk_status' => $tk_status,
                    'pay_price' => $pay_price,
                    'pub_share_pre_fee' => $pub_share_pre_fee,
                    'tk_commission_pre_fee_for_media_platform' => $tk_commission_pre_fee_for_media_platform,
                    'rebate_pre_fee' => $rebate_pre_fee
                ]);
                //插入订单信息

            }

            $platform = "未知";
            //订单号大于17位，则认为是淘宝订单。小于13位，则认为是京东订单
            if(strlen($trade_parent_id)>17){
                $platform= "淘宝".$trade_parent_id;
            }else if(strlen($trade_parent_id)<13){
                $platform= "京东".$trade_parent_id;
            }


            if ($flag) {
                $openid = Redis::get($item_title);
                //Log::info($openid);
                //从Redis中获取商品名称转链的openid
                if($openid!=null && $openid!="" && $openid !="repeat"){
                    //如果Redis中存在该商品名称转链的openid信息，且不为repeat，则绑定订单信息
                    $user = app(\App\Models\Users::class)->getUserById($openid);
                    $this->ModifyOpenIdByTradeParentIdAndModifyRebateAmountAccordingToRebateRatio($trade_parent_id,$user);
                    //发送订单通知模板消息
                    app(WeChatController::class)->sendTemplateMessage($openid, $platform,$item_title,$pay_price,round(($user->rebate_ratio) * 0.01 * $pub_share_pre_fee,2));


                }else{
                    //如果Redis中不存在，则查询订单是否存在运营id，如果存在，则绑定订单信息
                    $order = DB::table($this->table)->where([
                        'trade_parent_id' => $trade_parent_id,
                        'item_title' => $item_title,
                        'pay_price' => $pay_price
                    ])->orderBy('id', 'desc')->first();
                    if ($special_id != -1 && $tk_status != 13) {
                        $user = app(Users::class)->getUserBySpecialId($special_id);
                        $this->findAndModifyOpenIdBySpecialIdAndModifyRebateAmountAccordingToRebateRatio($order->id, $trade_parent_id, $user, $pub_share_pre_fee);
                        //发送订单通知模板消息
                        app(WeChatController::class)->sendTemplateMessage($user->id, $platform,$item_title,$pay_price,round(($user->rebate_ratio) * 0.01 * $pub_share_pre_fee,2));
                    }
                }

            }
            return $flag;
        } catch (\Exception $e) {
            return false;
        }

    }


    /**
     * 根据订单内会员运营id，检索openid并绑定，并根据用户返利比例修改返利金额
     * @param $trade_parent_id 订单号
     * @param $special_id 会员运营id
     * @param $pub_share_pre_fee 联盟返利金额
     * @return int 检索成功并绑定返回1，否则为0
     */
    public function findAndModifyOpenIdBySpecialIdAndModifyRebateAmountAccordingToRebateRatio($id, $trade_parent_id, $user, $pub_share_pre_fee)
    {
        //DB::table($this->table)->where('special_id', $special_id)->first();//根据传入的会员运营id检索绑定该id的会员信息
        if ($user != null) {//判断是否成功获取到会员信息
            try {
                DB::beginTransaction();
                DB::table($this->table)
                    ->where('trade_parent_id', $trade_parent_id)->where('id', $id)->where("special_id",$user->special_id)
                    ->update([
                        'openid' => $user->id,
                        'rebate_pre_fee' => ($user->rebate_ratio) * 0.01 * $pub_share_pre_fee,
                        'tlf_status' => 1
                    ]);
                app(Users::class)->updateUnsettled_balance($user->id, ($user->unsettled_balance) + (($user->rebate_ratio) * 0.01 * $pub_share_pre_fee));
                app(BalanceRecord::class)->setRecord($user->id, "订单" . $trade_parent_id . "获得返利" . ($user->rebate_ratio) * 0.01 * $pub_share_pre_fee . "元", round(($user->rebate_ratio) * 0.01 * $pub_share_pre_fee,2));
                DB::commit();
                return 1;
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
        return 0;
    }

    /**
     * 根据订单号绑定openid，并根据用户返利比例修改返利金额
     * @param $trade_parent_id 订单号
     * @param $user 用户对象
     * @return string 返回处理结果文本
     */
    public function ModifyOpenIdByTradeParentIdAndModifyRebateAmountAccordingToRebateRatio($trade_parent_id, $user)
    {
        $orderFlag = DB::table($this->table)->where('trade_parent_id', $trade_parent_id)->orderBy('id', 'desc')->first(); //查询订单信息
        //$pay_price = DB::table($this->table)->where('trade_parent_id', $trade_parent_id)->sum("pay_price");
        //$pub_share_pre_fee = DB::table($this->table)->where('trade_parent_id', $trade_parent_id)->sum("pub_share_pre_fee");
        $orders = DB::table($this->table)->where('trade_parent_id', $trade_parent_id)->get();
        if ($orderFlag == null) {
            return "无法查询到订单信息，您可以5分钟后再尝试，如仍无法绑定可能是未通过链接下单，您可以退款后重新下单。\n注意：如使用了大促活动红包可能导致无法返利";
        }

        if ($orderFlag->tk_status == 13) {
            return "您的订单已退款，无法绑定";
        } else if ($orderFlag->openid != null && trim($orderFlag->openid) != "") {
            if ($orderFlag->openid == $user->id) {
                return "您的订单本次已成功自动跟单，您可在订单查询中自行查询";
            } else {
                return "您的订单已绑定过，如非您本人绑定请联系客服处理！";
            }
        }

        if ($user != null) {//判断是否成功获取到会员信息
            $pay_price=0;
            $pub_share_pre_fee=0;
            $platform = "未知";
            $item_title = "获取失败";
            try {
                DB::beginTransaction();
                foreach ($orders as $order){
                    //DB::table($this->table)->where('trade_parent_id', $trade_parent_id)->where('id', "<>", $orderFlag->id)->delete();
                    DB::table($this->table)
                        ->where('trade_parent_id', $trade_parent_id)->where('id',$order->id)
                        ->update([
                            'openid' => $user->id,
                            'rebate_pre_fee' => ($user->rebate_ratio) * 0.01 * ($order->pub_share_pre_fee),
                            'tlf_status' => 1
                        ]);
                    app(Users::class)->updateUnsettled_balance($user->id, ($user->unsettled_balance) + ($user->rebate_ratio) * 0.01 * ($order->pub_share_pre_fee));
                    app(BalanceRecord::class)->setRecord($user->id, "订单" . $trade_parent_id . "获得返利" . ($user->rebate_ratio) * 0.01 * ($order->pub_share_pre_fee) . "元", round(($user->rebate_ratio) * 0.01 * ($order->pub_share_pre_fee),2));
                    DB::commit();
                    $pay_price+=$order->pay_price;
                    $pub_share_pre_fee+=$order->pub_share_pre_fee;
                    //订单号大于17位，则认为是淘宝订单。小于13位，则认为是京东订单
                    if(strlen($trade_parent_id)>17){
                        $platform= "淘宝".$trade_parent_id;
                    }else if(strlen($trade_parent_id)<13){
                        $platform= "京东".$trade_parent_id;
                    }
                    $item_title=$order->item_title;
                }
                //发送订单通知模板消息
                app(WeChatController::class)->sendTemplateMessage($user->id, $platform,$item_title,$pay_price,round(($user->rebate_ratio) * 0.01 * $pub_share_pre_fee,2));
                //return "订单绑定成功，您的付款金额为" . $pay_price . "，返利金额为" . round(($user->rebate_ratio) * 0.01 * ($pub_share_pre_fee),2);
            } catch (\Exception $e) {
                DB::rollBack();
                return "系统错误，绑定失败，请稍后再试或联系客服";

            }
        } else {
            return "获取用户信息失败，请重新尝试绑定订单";
        }
    }

    /**
     * 获取当天的订单数量及返利金额等
     */
    public function getOrderCountAndFee()
    {
        $sql = "SELECT count(*) AS count, SUM(pub_share_pre_fee) AS pub_share_pre_fee, SUM(rebate_pre_fee) AS rebate_pre_fee FROM `orders` WHERE to_days(`tk_paid_time`) = to_days(now())";
        return DB::select($sql);
    }

    /**
     * 根据openid分页查询订单信息
     * @param $openid
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator 分页查询对象
     */
    public function getAllByPaginateInOpenid($openid)
    {
        return DB::table($this->table)
            ->where('openid', $openid)
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    /**
     * 分页查询订单信息
     * @param null $trade_parent_id 订单号
     * @param null $start 起始日期
     * @param null $end 结束日期
     * @param null $tk_status 订单状态
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator 分页查询对象
     */
    public function getAllByPaginate($trade_parent_id = null, $start = null, $end = null, $tk_status = null)
    {
        if ($trade_parent_id == null && $start == null && $end == null && $tk_status == null) {
            //判断是否有筛选条件，如所有筛选条件均为null，则直接查询所有
            return DB::table($this->table)->orderBy('id', 'desc')->paginate(10);
        } else {
            $orders = DB::table($this->table)
                ->where('trade_parent_id', 'like', trim($trade_parent_id) == "" ? "%" : $trade_parent_id)
                ->where('tk_status', 'like', trim($tk_status) == "" || $tk_status <= 0 ? "%" : $tk_status);
            //二次判断筛选内容是否为空字符串，如为空则直接%模糊查询
            if (trim($start) != "" && trim($end) != "") {
                //判断起始日期和终止日期是否都不为空
                return $orders
                    ->whereBetween('tk_paid_time', [$start, $end])
                    ->orderBy('id', 'desc')
                    ->paginate(10);
            } else {
                return $orders
                    ->orderBy('id', 'desc')
                    ->paginate(10);
            }

        }

    }

    /**
     * 根据openid获得最近一个月的订单信息
     * @param $openid
     * @return \Illuminate\Support\Collection
     */
    public function getAllWithinOneMonthByOpenid($openid)
    {
        date_default_timezone_set("Asia/Shanghai");
        $today = date("Y-m-d H:i:s", time());
        $queryday = date("Y-m-d H:i:s", strtotime("-1 month"));
        return DB::table($this->table)
            ->where('openid', $openid)
            ->whereBetween('tk_paid_time', [$queryday, $today])
            ->get();
    }

    /**
     * 获取上个月的订单信息
     * @return \Illuminate\Support\Collection
     */
    public function getAllWithinLastMonth()
    {
        date_default_timezone_set("Asia/Shanghai");
        $year = date("Y", time());
        $month = date("m", time());
        $day = 0;
        if ($month == 1) {
            $month = 11;
            $year = ((int)$year) - 1;
        } else if ($month == 2) {
            $month = 12;
            $year = ((int)$year) - 1;
        }else{
            $month-=2;
        }
        $toYear = $month == 12 ? ((int)$year) + 1 : $year;
        $toMonth = $month == 12 ? 1 : ($month + 1);
        switch ($toMonth) {
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                $day = 31;
                break;
            case 2:
                if (((int)$year % 4 == 0 && (int)$year % 100 != 0) || (int)$year % 400 == 0) {
                    $day = 29;
                } else {
                    $day = 28;
                }
                break;
            default:
                $day = 30;
                break;
        }

        return DB::table($this->table)
            ->whereBetween('tk_paid_time', [$year . "-" . $month . "-1 00:00:00", $toYear . "-" . $toMonth . "-" . $day . " 23:59:59"])
            ->get();
    }

    /**
     * 修改订单状态和结算时间
     * @param $trade_parent_id
     * @param $tk_status
     * @param $tk_earning_time
     * @return int
     */
    public function changeStatusAndEarningTimeById($id, $tk_status, $tk_earning_time,$refund_tag=0)
    {
        if ($tk_earning_time == null) {
            return DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'tk_status' => $tk_status,
                    'refund_tag' => $refund_tag
                ]);
        } else {
            return DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'tk_status' => $tk_status,
                    'tk_earning_time' => $tk_earning_time,
                    'refund_tag' => $refund_tag
                ]);
        }
    }

    public function changeTlfStatus($trade_parent_id, $tlf_status)
    {
        DB::table($this->table)
            ->where('trade_parent_id', $trade_parent_id)
            ->update([
                'tlf_status' => $tlf_status
            ]);
    }

}


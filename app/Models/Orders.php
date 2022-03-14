<?php


namespace App\Models;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use mysql_xdevapi\Exception;

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
     * @param $share_pre_fee 预估专项服务费
     * @param $rebate_pre_fee 预估返利金额
     * @param $special_id 会员运营id
     * @return bool 如执行成功返回1
     */
    public function saveOrder($trade_parent_id, $item_title, $tk_paid_time, $tk_status, $pay_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $share_pre_fee, $rebate_pre_fee, $special_id)
    {
        $order = DB::table($this->table)->where('trade_parent_id', $trade_parent_id)->first();
        if ($order != null) {
            return false;
        }
        $flag = DB::table($this->table)->insert([
            'trade_parent_id' => $trade_parent_id,
            'item_title' => $item_title,
            'tk_create_time' => $tk_paid_time,
            'tk_status' => $tk_status,
            'pay_price' => $pay_price,
            'pub_share_pre_fee' => $pub_share_pre_fee,
            'tk_commission_pre_fee_for_media_platform' => $tk_commission_pre_fee_for_media_platform,
            'share_pre_fee' => $share_pre_fee,
            'rebate_pre_fee' => $rebate_pre_fee
        ]);
        if ($flag) {
            if ($special_id != -1) {
                $this->findAndModifyOpenIdBySpecialIdAndModifyRebateAmountAccordingToRebateRatio($trade_parent_id, $special_id, $rebate_pre_fee);
            }
        }
        return $flag;
    }


    /**
     * 根据订单内会员运营id，检索openid并绑定，并根据用户返利比例修改返利金额
     * @param $trade_parent_id 订单号
     * @param $special_id 会员运营id
     * @param $rebate_pre_fee 联盟返利金额
     * @return int 检索成功并绑定返回1，否则为0
     */
    public function findAndModifyOpenIdBySpecialIdAndModifyRebateAmountAccordingToRebateRatio($trade_parent_id, $special_id, $rebate_pre_fee)
    {
        $user = app(Users::class)->getUserBySpecialId($special_id);
        //DB::table($this->table)->where('special_id', $special_id)->first();//根据传入的会员运营id检索绑定该id的会员信息
        if ($user != null) {//判断是否成功获取到会员信息
            try {
                DB::beginTransaction();
                DB::table($this->table)
                    ->where('trade_parent_id', $trade_parent_id)
                    ->update([
                        'openid' => $user->id,
                        'rebate_pre_fee' => ($user->rebate_ratio) * 0.01 * $rebate_pre_fee,
                        'special_id' => $special_id,
                        'tlf_status' => 1
                    ]);
                app(Users::class)->updateUnsettled_balance($user->id, ($user->unsettled_balance) + (($user->rebate_ratio) * 0.01 * $rebate_pre_fee));
                app(BalanceRecord::class)->setRecord($user->id, "订单" . $trade_parent_id . "获得返利" . ($user->rebate_ratio) * 0.01 * $rebate_pre_fee . "元", ($user->rebate_ratio) * 0.01 * $rebate_pre_fee);
                DB::commit();
                return 1;
            } catch (Exception $e) {
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
        $order = DB::table($this->table)->where('trade_parent_id', $trade_parent_id)->first(); //查询订单信息
        if ($order == null) {
            return "无法查询到订单信息，您可以5分钟后再尝试，如仍无法绑定可能是未通过链接下单，您可以退款后重新下单。\n注意：如使用了大促活动红包可能导致无法返利";
        } else if ($order->openid != null && trim($order->openid) != "") {
            if ($order->special_id != null && trim($order->special_id) != "") {
                return "您的下单淘宝账号已绑定过公众号，本次已成功自动跟单，您的付款金额为" . $order->pay_price . "，返利金额为" . $order->rebate_pre_fee;
            } else {
                return "您的订单已绑定过，如非您本人绑定请联系客服处理！";
            }
        }
        if ($user != null) {//判断是否成功获取到会员信息
            try {
                DB::beginTransaction();
                DB::table($this->table)
                    ->where('trade_parent_id', $trade_parent_id)
                    ->update([
                        'openid' => $user->id,
                        'rebate_pre_fee' => ($user->rebate_ratio) * 0.01 * ($order->rebate_pre_fee)
                    ]);
                app(Users::class)->updateUnsettled_balance($user->id, ($user->unsettled_balance) + ($user->rebate_ratio) * 0.01 * ($order->rebate_pre_fee));
                app(BalanceRecord::class)->setRecord($user->id, "订单" . $trade_parent_id . "获得返利" . ($user->rebate_ratio) * 0.01 * ($order->rebate_pre_fee) . "元", ($user->rebate_ratio) * 0.01 * ($order->rebate_pre_fee));
                DB::commit();
                return "订单绑定成功，您的付款金额为" . $order->pay_price . "，返利金额为" . ($user->rebate_ratio) * 0.01 * ($order->rebate_pre_fee);
            } catch (Exception $e) {
                DB::rollBack();
                return "系统错误，绑定失败，请稍后再试或联系客服";
            }
        } else {
            return "获取用户信息失败，请重新尝试绑定订单";
        }

        return "系统错误，绑定失败，请稍后再试或联系客服";
    }

    /**
     * 获取当天的订单数量及返利金额等
     */
    public function getOrderCountAndFee(){
        $sql = "SELECT count(*) AS count, SUM(pub_share_pre_fee) AS pub_share_pre_fee, SUM(rebate_pre_fee) AS rebate_pre_fee FROM `orders` WHERE to_days(`tk_paid_time`) = to_days(now())";
        return DB::select($sql);
    }
}


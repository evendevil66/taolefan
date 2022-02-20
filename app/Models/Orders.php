<?php


namespace App\Models;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
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
     * @param $tk_create_time 下单时间
     * @param $tk_status 订单状态 3：订单结算，12：订单付款， 13：订单失效，14：订单成功
     * @param $pay_price 付款金额
     * @param $pub_share_pre_fee 付款预估收入
     * @param $tk_commission_pre_fee_for_media_platform 预估内容专项服务费
     * @param $share_pre_fee 预估专项服务费
     * @param $rebate_pre_fee 预估返利金额
     * @param $special_id 会员运营id
     * @return bool 如执行成功返回1
     */
    public function saveOrder($trade_parent_id, $item_title, $tk_create_time, $tk_status, $pay_price, $pub_share_pre_fee, $tk_commission_pre_fee_for_media_platform, $share_pre_fee, $rebate_pre_fee, $special_id)
    {
        $flag = DB::table($this->table)->insert([
            'trade_parent_id' => $trade_parent_id,
            'item_title' => $item_title,
            'tk_create_time' => $tk_create_time,
            'tk_status' => $tk_status,
            'pay_price' => $pay_price,
            'pub_share_pre_fee' => $pub_share_pre_fee,
            'tk_commission_pre_fee_for_media_platform' => $tk_commission_pre_fee_for_media_platform,
            'share_pre_fee' => $share_pre_fee,
            'rebate_pre_fee' => $rebate_pre_fee
        ]);
        if ($flag) {
            if ($special_id != -1) {
                $this->findOpenIdBySpecialId($trade_parent_id, $special_id);
            }
        }
        return $flag;
    }


    /**
     * 根据订单号内会员运营id，检索openid并绑定
     * @param $trade_parent_id 订单号
     * @param $special_id 会员运营id
     * @return int 检索成功并绑定返回1，否则为0
     */
    public function findOpenIdBySpecialId($trade_parent_id, $special_id)
    {
        $user = DB::table($this->table)->where('special_id', $special_id)->first();//根据传入的会员运营id检索绑定该id的会员信息
        if ($user != null) {//判断是否成功获取到会员信息
            try {
                return DB::table($this->table)
                    ->where('trade_parent_id', $trade_parent_id)
                    ->update([
                        'openid' => $user->id
                    ]);
                //将会员信息中的openid补充到订单信息中
            } catch (Exception $e) {
                return 0;
            }
        }
        return 0;
    }

    /**
     * 用户信息补全更新 通过Request获取参数
     * $openid 微信openid
     * $nickname 用户填写的昵称
     * $username ～姓名
     * $alipay_id ～支付宝账号
     * @return int 如执行成功返回1
     */
    public function userUpdate()
    {
        $openid = Request::post("openid");
        $nickname = Request::post("nickname");
        $username = Request::post("username");
        $alipay_id = Request::post("alipay_id");
        return DB::table($this->table)
            ->where('id', $openid)
            ->update([
                'nickname' => $nickname,
                'username' => $username,
                'alipay_id' => $alipay_id
            ]);
    }
}


<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use mysql_xdevapi\Exception;

class Receive extends Model
{
    protected $table = "receive";


    /**
     * 获取当天的提现订单数量
     * @return array 返回数组中仅包含一条数据，其中该数据中的count为当日当单数量
     */
    public function getReceiveCountOfTheDay()
    {
        $sql = "SELECT count(*) AS count FROM `receive` WHERE to_days(`receive_date`) = to_days(now())";
        return DB::select($sql);
    }

    public function getAllByPaginate($openid = null, $status = null)
    {
        if ($openid == null && $status == null) {
            //判断是否有筛选条件，如所有筛选条件均为null，则直接查询所有
            return DB::table($this->table)->orderBy('id', 'desc')->paginate(10);
        } else {
            return DB::table($this->table)
                ->where('openid', 'like', trim($openid) == "" ? "%" : $openid)
                ->where('status', 'like', trim($status) == "" ? "%" : $status)
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

    }

    public function receivePass($id)
    {
        try {
            date_default_timezone_set("Asia/Shanghai");
            DB::beginTransaction();
            DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'status' => 1,
                    'process_time' => date('Y-m-d h:i:s', time())
                ]);
            DB::commit();
            return 1;
        } catch (Exception $e) {
            DB::rollBack();
        }
    }

    public function receiveRefuse($id,$reason)
    {
        $receive = DB::table($this->table)->where('id', $id)->first();
        $user = app(Users::class)->getUserById($receive->openid);
        try {
            date_default_timezone_set("Asia/Shanghai");
            $time = date('Y-m-d H:i:s', time());
            DB::beginTransaction();
            app(Users::class)->updateAvailable_balance($receive->openid,$user->available_balance+$receive->amount);
            app(BalanceRecord::class)->setRecord($user->id, "提现被拒绝返还".$receive->amount."元",$receive->amount);
            DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'status' => -1,
                    'process_time' => $time,
                    'reason' => $reason
                ]);
            DB::commit();
            return 1;
        } catch (Exception $e) {
            DB::rollBack();
            return 0;
        }
    }
}

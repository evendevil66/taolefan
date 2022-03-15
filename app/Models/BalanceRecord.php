<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class BalanceRecord extends Model
{
    protected $table = "balance_record";


    /**
     * 根据openid查询所有变动记录
     * @param $openid
     * @return object|null 返回用户对象或NULL
     */
    public function getRecord($openid){
        return DB::table($this->table)->where([
            'openid'=> $openid,]);
    }

    /**
     * 增加变动信息
     * @param $openid
     * @param $event 变动事件（原因） text
     * @param $change 变动金额 可为负数
     * @return bool 增加成功返回1否则0
     */
    public function setRecord($openid,$event,$change){
        return DB::table($this->table)
            ->insert([
                'openid' => $openid,
                'event' => $event,
                'change' => $change
            ]);
    }

    /**
     * 根据openid分页查询变动记录
     * @param $openid
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator 分页查询对象
     */
    public function getRecordByOpenid($openid)
    {
        return DB::table($this->table)
            ->where('openid', $openid)
            ->orderBy('id', 'desc')
            ->paginate(10);
    }
}

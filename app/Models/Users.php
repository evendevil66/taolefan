<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class Users extends Model
{
    protected $table = "users";

    /**
     * 获取所有用户
     * @return 返回用户表集合
     */
    public function getAll(){
        return DB::table($this->table)->get();
    }

    /**
     * 通过openid获取用户信息
     * @param $openid 微信openid
     * @return 返回用户对象或NULL
     */
    public function getUserById($openid){
        return DB::table($this->table)->where('id',$openid)->first();
    }

    /**
     * 注册用户
     * @param $openid 微信openid
     * @return 返回结果如果为true则调用成功
     */
    public function userReg($openid){
        return DB::table($this->table)->insert(['id' => $openid]);
    }

    /**
     * 用户信息补全更新 通过Request获取参数
     * @param $openid 微信openid
     * @param $nickname 用户填写的昵称
     * @param $username ～姓名
     * @param $alipay_id ～支付宝账号
     * @return 返回true或false
     */
    public function userUpdate(){
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

<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = "admin";


    /**
     * 根据用户名和密码查询用户是否存在
     * @param $username 用户名
     * @param $password 未加密的密码
     * @return object|null 返回用户对象或NULL
     */
    public function getAdmin($username,$password){
        return DB::table($this->table)->where([
            'username'=> $username,
            'password'=> md5($password)
            ])->first();
    }

    /**
     * 根据用户名查询用户是否存在
     * @param $username 用户名
     * @return object|null 返回用户对象或NULL
     */
    public function getAdminByUsername($username){
        return DB::table($this->table)->where([
            'username'=> $username,
        ])->first();
    }

    /**
     * 根据ID修改管理员账号和密码
     * @param $id
     * @param $username
     * @param $password
     * @return int 成功返回1 否则0
     */
    public function updateAdminById($id,$username,$password){
        try{
            return DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'username' => $username,
                    'password' => md5($password)
                ]);
        }catch (Exception $e){
            return 0;
        }

    }

    /**
     * 根据ID删除管理员
     * @param $id
     * @return int 成功返回1 否则0
     */
    public function delAdminById($id){
        try{
            return DB::table($this->table)
                ->where('id', $id)
                ->delete();
        }catch (Exception $e){
            return 0;
        }

    }

    /**
     * 新增管理员账号
     * @param $username
     * @param $password
     * @return int 成功返回1 否则0
     */
    public function addAdmin($username,$password){
        return DB::table($this->table)
            ->insert([
                'username' => $username,
                'password' => md5($password)
            ]);
    }

    /**
     * 获取所有管理信息
     * @return \Illuminate\Support\Collection 返回管理对象
     */
    public function getAll(){
        return DB::table($this->table)->get();
    }

}

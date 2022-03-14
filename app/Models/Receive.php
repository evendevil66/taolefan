<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Receive extends Model
{
    protected $table = "receive";


    public function getReceiveCountOfTheDay(){
        $sql = "SELECT count(*) AS count FROM `receive` WHERE to_days(`receive_date`) = to_days(now())";
        return DB::select($sql);
    }
}

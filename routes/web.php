<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Request;
use App\Models\Users;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::any('/wechat', [Controllers\WeChatController::class,'serve']);
Route::get('/reg/{openid}', function ($openid) {
    $name = config('config.name');
    $alert="";
    /** $ua = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($ua, 'MicroMessenger') == false && strpos($ua, 'Windows Phone') == false) {
    $name = config('config.name');
    } else {
    $name = "请使用浏览器打开再进行注册";
    $alert="alert(\"请点击右上角浏览器打开后再注册～\");";
    }**/
    $nickname = Request::get("nickname");
    $username = Request::get("username");
    $alipay = Request::get("alipay");
    return view('reg', [
        'title' => $name,
        'openid' => $openid,
        'alipay' => $alipay,
        'username' => $username,
        'nickname' => $nickname
    ]);
});

Route::post('/userUpdate', [App\Models\Users::class,'userUpdate']);

Route::get('/bind/{openid}', function ($openid) {
    Cookie::queue('openid', $openid, 60);
    $code = Request::get("code");
    if($code == null || $code == ""){
        return redirect('https://oauth.taobao.com/authorize?response_type=code&client_id='.config('config.aliAppKey')."&redirect_uri=".config('config.apiUrl')."/bind/".$openid."&view=wap");
    }else{
        return redirect("/bind/".$openid."/".$code);
    }

});

Route::get('/bind/{openid}/{code}', [Controllers\TaokeController::class,'regMember']);
Route::get('/getOrderList', [Controllers\TaokeController::class,'getOrderList']);

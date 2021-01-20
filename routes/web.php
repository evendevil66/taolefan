<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

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
    $name = "";
    $alert="";
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($ua, 'MicroMessenger') == false && strpos($ua, 'Windows Phone') == false) {
        $name = config('config.name');
    } else {
        $name = "请使用浏览器打开再进行注册";
        $alert="alert(\"请点击右上角浏览器打开后再注册～\");";
    }
    return view('reg', ['title' => $name,'openid' => $openid,'alert'=>$alert]);
});

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

Route::get('/admin/login', function () {
    return view('admin/login');
});
Route::get('/admin/error', function () {
    return view('admin/error');
});
Route::get('/admin/unicode', function () {
    return view('admin/unicode');
});

Route::middleware(['CheckAdminLogin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin/index');
    });

    Route::get('/admin/index', function () {
        return view('admin/index');
    });

    Route::get('/admin/admin-add', function () {
        return view('admin/admin-add');
    });

    Route::get('/admin/admin-edit', function () {
        return view('admin/admin-edit');
    });

    Route::get('/admin/admin-list', function () {
        return view('admin/admin-list');
    });

    Route::get('/admin/member-list', function () {
        return view('admin/member-list');
    });

    Route::get('/admin/order-list', function () {
        return view('admin/order-list');
    });

    Route::get('/admin/receive', function () {
        return view('admin/receive');
    });

    Route::get('/admin/welcome', function () {
        return view('admin/welcome');
    });
});

Route::post('/admin/getAdmin', function () {
    $admin = app(\App\Models\Admin::class)->getAdmin(Request::post("username"),Request::post("password"));
    if($admin!=null){
        return response('1')->cookie('username', $admin->username, 14400);
    }else{
        return 0;
    }
});

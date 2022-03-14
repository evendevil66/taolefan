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

Route::any('/wechat', [Controllers\WeChatController::class, 'serve']);
Route::get('/reg/{openid}', function ($openid) {
    $name = config('config.name');
    $alert = "";
    /** $ua = $_SERVER['HTTP_USER_AGENT'];
     * if (strpos($ua, 'MicroMessenger') == false && strpos($ua, 'Windows Phone') == false) {
     * $name = config('config.name');
     * } else {
     * $name = "请使用浏览器打开再进行注册";
     * $alert="alert(\"请点击右上角浏览器打开后再注册～\");";
     * }**/
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

Route::post('/userUpdate', [App\Models\Users::class, 'userUpdate']);

Route::get('/bind/{openid}', function ($openid) {
    Cookie::queue('openid', $openid, 60);
    $code = Request::get("code");
    if ($code == null || $code == "") {
        return redirect('https://oauth.taobao.com/authorize?response_type=code&client_id=' . config('config.aliAppKey') . "&redirect_uri=" . config('config.apiUrl') . "/bind/" . $openid . "&view=wap");
    } else {
        return redirect("/bind/" . $openid . "/" . $code);
    }

});

Route::get('/bind/{openid}/{code}', [Controllers\TaokeController::class, 'regMember']);
Route::get('/getOrderList', [Controllers\TaokeController::class, 'getOrderList']);

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
        $openid=Request::get("openid");
        $users =null;
        if($openid!=null){
            $users = app(\App\Models\Users::class)->getAllByPaginate($openid);
        }else{
            $users = app(\App\Models\Users::class)->getAllByPaginate();
        }

        return view('admin/member-list',['users' => $users]);
    });

    Route::get('/admin/member-edit', function () {
        $id=Request::get("id");
        $rebate_ratio=Request::get("rebate_ratio");
        $special_id=Request::get("special_id");
        return view('admin/member-edit', [
            'id' => $id,
            'rebate_ratio' => $rebate_ratio,
            'special_id' => $special_id
        ]);
    });

    Route::get('/admin/order-list', function () {
        $trade_parent_id=Request::get("trade_parent_id");
        $start=Request::get("start");
        $end=Request::get("end");
        $tk_status=Request::get("tk_status");
        $orders = app(\App\Models\Orders::class)->getAllByPaginate($trade_parent_id,$start,$end,$tk_status);
        return view('admin/order-list',[
            'orders' => $orders,
            'trade_parent_id' => $trade_parent_id,
            'start' => $start,
            'end' => $end,
            'tk_status' => $tk_status
        ]);
    });

    Route::get('/admin/receive', function () {
        return view('admin/receive');
    });

    Route::get('/admin/welcome', function () {
        $orderCount = app(\App\Models\Orders::class)->getOrderCountAndFee();
        $receiveCount = app(\App\Models\Receive::class)->getReceiveCountOfTheDay();
        if ($orderCount != null && count($orderCount) > 0) {
            return view('admin/welcome', [
                'count' => $orderCount[0]->count,
                'pub_share_pre_fee' => $orderCount[0]->pub_share_pre_fee,
                'rebate_pre_fee' => $orderCount[0]->rebate_pre_fee,
                'receiveCount' => $receiveCount[0]->count
            ]);
        } else {
            return view('admin/welcome', [
                'count' => 0,
                'pub_share_pre_fee' => 0,
                'rebate_pre_fee' => 0,
                'receiveCount' => $receiveCount[0]->count
            ]);
        }
    });

    Route::post('/admin/modifyUser', function () {
        $result = app(\App\Models\Users::class)->modifyUserById(Request::post("id"), Request::post("rebate_ratio"), Request::post("special_id"));
        if ($result>0) {
            return 1;
        } else {
            return 0;
        }
    });

});

Route::get('/admin/loginout', function () {
    Cookie::queue(Cookie::forget('username'));
    return view('admin/login');
});

Route::post('/admin/getAdmin', function () {
    $admin = app(\App\Models\Admin::class)->getAdmin(Request::post("username"), Request::post("password"));
    if ($admin != null) {
        return response('1')->cookie('username', $admin->username, 14400);
    } else {
        return 0;
    }
});

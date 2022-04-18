<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Redis;

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

Route::get('/testRed',function (){
    $title=Request::get('title');
    return Redis::get($title);
});

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
Route::get('/updateOrderAll', [Controllers\TaokeController::class, 'updateOrderAll']);

Route::get('/adminReg', function () {
    if(Storage::exists("admin.lock")){
        return "已创建过超级管理员，如忘记密码，请删除站点目录下/storage/app/admin.lock文件后，重新访问本页修改账号密码";
    }
    return view('/admin/reg');
});

//提交新增管理员post请求（初次）
Route::post('/admin/setAdmin', function () {
    $result = app(\App\Models\Admin::class)->setAdmin(Request::post("username"),Request::post("password"));
    if ($result>0) {
        Storage::disk('local')->put('admin.lock', "taolefan");
        return 1;
    } else {
        return 0;
    }
});


Route::get('/loading', function () {
    Cookie::queue('openid', Request::get("openid"), 60);
    return view('loading');
});

Route::get('/loadOrder', function () {
    app(\App\Http\Controllers\TaokeController::class)->updateOrder(Cookie::get('openid'));
    return redirect()->route('order');
});

Route::get('/order', function () {
    $openid=Cookie::get('openid');
    $orders = app(\App\Models\Orders::class)->getAllByPaginateInOpenid($openid);
    return view('/order',[
        'orders' => $orders,
        'openid' => $openid
    ]);
})->name('order');

Route::get('/balanceRecord', function () {
    $openid=Request::get('openid');
    $balanceRecord = app(\App\Models\BalanceRecord::class)->getRecord($openid);
    return view('/balanceRecord',[
        'balanceRecord' => $balanceRecord,
        'openid' => $openid
    ]);
});

Route::get('/admin/login', function () {
    return view('admin/login');
});
Route::get('/admin/error', function () {
    return view('admin/error');
});
Route::get('/admin/unicode', function () {
    return view('admin/unicode');
});

//淘口令中间页
Route::get('/tklzjy', function () {
    $title=Request::get("title");
    $couponInfo=Request::get("couponInfo");
    $maxCommissionRate=Request::get("maxCommissionRate");
    $estimate=Request::get("estimate");
    $tpwd=Request::get("tpwd");
    $image=Request::get("image");
    return view('/tklzjy',
        [
            'title' => $title,
            'couponInfo' => $couponInfo,
            'maxCommissionRate' => $maxCommissionRate,
            'estimate' => $estimate,
            'tpwd' => $tpwd,
            'image' => $image
        ]);
});

//京东中间页
Route::get('/jdzjy', function () {
    $title=Request::get("title");
    $couponInfo=Request::get("couponInfo");
    $maxCommissionRate=Request::get("maxCommissionRate");
    $estimate=Request::get("estimate");
    $url=Request::get("url");
    $image=Request::get("image");
    return view('/jdzjy',
        [
            'title' => $title,
            'couponInfo' => $couponInfo,
            'maxCommissionRate' => $maxCommissionRate,
            'estimate' => $estimate,
            'url' => $url,
            'image' => $image
        ]);
});

Route::middleware(['CheckAdminLogin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin/index');
    });

    //后台主页
    Route::get('/admin/index', function () {
        return view('admin/index');
    });
    //新增管理员小窗口
    Route::get('/admin/admin-add', function () {
        if(Cookie::get('adminId') == 1){
            return view('admin/admin-add');
        }else{
            return view('admin/error');
        }

    });
    //提交新增管理员post请求
    Route::post('/admin/admin-add', function () {
        $result = app(\App\Models\Admin::class)->addAdmin(Request::post("username"),Request::post("password"));
        if ($result>0) {
            return 1;
        } else {
            return 0;
        }
    });

    //提交删除管理员get请求
    Route::get('/admin/admin-del', function () {
        if(Cookie::get('adminId') == 1){
            $result = app(\App\Models\Admin::class)->delAdminById(Request::get("id"));
            if ($result>0) {
                return 1;
            } else {
                return 0;
            }
        }else{
            return -1;
        }

    });

    //管理员账号密码修改小窗
    Route::get('/admin/admin-edit', function () {
        if(Cookie::get('adminId') == 1 || Cookie::get('adminId') ==Request::get('id')){
            return view('admin/admin-edit', [
                'id' => Request::get('id') ,
                'username' => Request::get('username') ,
            ]);
        }else{
            return view('admin/error');
        }

    });
    //提交修改管理员账号密码post请求
    Route::post('/admin/admin-edit', function () {
        $result = app(\App\Models\Admin::class)->updateAdminById(Request::post("id"),Request::post("username"),Request::post("password"));
        if ($result>0) {
            return 1;
        } else {
            return 0;
        }
    });

    //管理员列表
    Route::get('/admin/admin-list', function () {
        $admins = app(\App\Models\Admin::class)->getAll();
        return view('admin/admin-list',[
            'admins' => $admins,
        ]);
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
        $openid=Request::get("openid");
        $status=Request::get("status");
        $receives = app(\App\Models\Receive::class)->getAllByPaginate($openid,$status);
        $alipays = app(\App\Models\Users::class)->getAlipayTraversalInReceive($receives);
        return view('admin/receive',[
            'receives' => $receives,
            'openid' => $openid,
            'status' => $status,
            'alipays' => $alipays
        ]);
    });
    Route::get('/admin/receivePass', function () {
        $id=Request::get("id");
        return app(\App\Models\Receive::class)->receivePass($id);
    });
    Route::get('/admin/receiveRefuse', function () {
        $id=Request::get("id");
        $reason=Request::get("reason");

        return app(\App\Models\Receive::class)->receiveRefuse($id,$reason);
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
        return response('1')->cookie('username', $admin->username, 14400)->cookie('adminId', $admin->id, 14400);
    } else {
        return 0;
    }
});

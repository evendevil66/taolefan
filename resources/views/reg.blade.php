<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{config('config.name')}}注册</title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" type="text/css" href="/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="/fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="/css/util.css">
    <link rel="stylesheet" type="text/css" href="/css/main.css">
</head>

<body>

<div class="limiter">
    <div class="container-login100" style="background-image: url('/images/bg-01.jpg');">
        <div class="wrap-login100 p-l-55 p-r-55 p-t-65 p-b-54">
            <form class="login100-form validate-form">
                @csrf
                <span class="login100-form-title p-b-49">{{$title}}</span>

                <div class="wrap-input100 validate-input m-b-23">
                    <span class="label-input100">openid</span>
                    <input class="input100" type="text" name="openid" id="openid" value="{{$openid}}" autocomplete="off" disabled="disabled">
                    <span class="focus-input100" data-symbol="&#xf206;"></span>
                </div>

                <div class="wrap-input100 validate-input m-b-23">
                    <span class="label-input100">昵称</span>
                    <input class="input100" type="text" name="nickname" id="nickname"   value="{{$nickname}}" placeholder="请输入昵称" autocomplete="off">
                    <span class="focus-input100" data-symbol="&#xf206;"></span>
                </div>

                <div class="wrap-input100 validate-input m-b-23">
                    <span class="label-input100">姓名</span>
                    <input class="input100" type="text" name="username" id="username"   placeholder="请输入真实姓名" value="{{$username}}" autocomplete="off">
                    <span class="focus-input100" data-symbol="&#xf190;"></span>
                </div>

                <div class="wrap-input100 validate-input" data-validate="">
                    <span class="label-input100">支付宝账号</span>
                    <input class="input100" type="text" name="alipay" id="alipay"   placeholder="请输入支付宝账号（用于提现）" value="{{$alipay}}">
                    <span class="focus-input100" data-symbol="&#xf190;"></span>
                </div>

                <div class="text-right p-t-8 p-b-31">
                </div>
                <div class="container-login100-form-btn">
                    <div class="wrap-login100-form-btn">
                        <div class="login100-form-bgbtn"></div>
                        <button class="login100-form-btn">确 认</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/vendor/jquery/jquery-3.2.1.min.js"></script>
<script src="/js/main.js"></script>
<script>
    $("form").submit(function(e){
        $.ajax({
            type: 'POST',
            url: '/userUpdate',
            data: {
                openid: $("#openid").val(),
                username: $("#username").val(),
                nickname: $("#nickname").val(),
                alipay_id: $("#alipay").val(),
                _token : $("[name=_token]").val()
            },
            dataType: "text",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {//res为相应体,function为回调函数
                if (data == 1 || data == "1") {
                    alert("修改成功！");
                    return false;
                } else {
                    alert("修改失败！如无变更请退出即可");
                    return false;
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert("未知错误，请联系客服！");
                return false;
            }
        });
        return false;
    });
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">

    <title>{{config('config.name')}}信息补全</title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />

    <link rel="stylesheet" type="text/css" href="/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="/fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="/css/util.css">
    <link rel="stylesheet" type="text/css" href="/css/main.css">
    <script src="/vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="/js/main.js"></script>
    <script>
        function reg(){
            let openid = $("#openid").val();
            let username = $("#username").val();
            let nickname = $("#nickname").val();
            let alipay = $("#alipay").val();
            let _token = $("[name='_token']").val();

            if(nickname.trim() == ""){
                alert("请输入昵称");
            }else if(username.trim() == ""){
                alert("请输入姓名");
            }else if(alipay.trim() == ""){
                alert("请输入支付宝账号");
            }else{
                $.ajax({
                    url: "/userUpdate",
                    data: {
                        'openid': openid,
                        'nickname':nickname,
                        'username':username,
                        'alipay_id':alipay,
                        '_token':_token
                    },
                    type: "POST",
                    dataType: "text",
                    headers:{
                        // 获取token
                        'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        if(data == "1"){
                            alert("更新成功");
                            $("[name='regBtn']").text('修改成功');
                            $("#nickname").attr("disabled","disabled");
                            $("#username").attr("disabled","disabled");
                            $("#alipay").attr("disabled","disabled");
                            $("[name='regBtn']").attr("disabled","disabled");
                        }else{
                            alert("操作失败，请检查是否有修改内容。如无法正常修改请联系客服");
                        }
                    },
                    error:function(xhr,state,errorThrown){
                        alert("操作失败，请检查是否有修改内容。如无法正常修改请联系客服");
                    }
                });
            }


        }
    </script>
</head>

<body>

<div class="limiter">
    <div class="container-login100" style="background-image: url('/images/bg-01.jpg');">
        <div class="wrap-login100 p-l-55 p-r-55 p-t-65 p-b-54">
            <form class="login100-form validate-form">
                <span class="login100-form-title p-b-49">{{$title}}</span>

                <div class="wrap-input100 validate-input m-b-23">
                    <span class="label-input100">openid（系统自动获取的微信ID 无需修改）</span>
                    <input id="openid" class="input100" type="text" name="openid" value="{{$openid}}" autocomplete="off" disabled="disabled">
                    <span class="focus-input100" data-symbol="&#xf206;"></span>
                </div>

                <div class="wrap-input100 validate-input m-b-23">
                    <span class="label-input100">昵称</span>
                    <input id="nickname" class="input100" type="text" name="nickname" value="{{$nickname}}" placeholder="请输入昵称" autocomplete="off">
                    <span class="focus-input100" data-symbol="&#xf206;"></span>
                </div>

                <div class="wrap-input100 validate-input m-b-23">
                    <span class="label-input100">姓名</span>
                    <input id="username" class="input100" type="text" name="name" value="{{$username}}" placeholder="请输入真实姓名（用于提现）" autocomplete="off">
                    <span class="focus-input100" data-symbol="&#xf190;"></span>
                </div>

                <div class="wrap-input100 validate-input" data-validate="">
                    <span class="label-input100">支付宝账号</span>
                    <input id="alipay" class="input100" type="text" name="alipay" value="{{$alipay}}" placeholder="请输入支付宝账号（用于提现）">
                    <span class="focus-input100" data-symbol="&#xf190;"></span>
                </div>

                <div class="text-right p-t-8 p-b-31">
                </div>

                @csrf

                <div class="container-login100-form-btn">
                    <div class="wrap-login100-form-btn">
                        <div class="login100-form-bgbtn"></div>
                        <button type="button" onclick="reg()" name="regBtn" class="login100-form-btn">确 认</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>

</html>

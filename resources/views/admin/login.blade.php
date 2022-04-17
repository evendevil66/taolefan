<!doctype html>
<html  class="x-admin-sm">
<head>
	<meta charset="UTF-8">
	<title>淘乐饭后台登录</title>
	<meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="./css/font.css">
    <link rel="stylesheet" href="./css/login.css">
	  <link rel="stylesheet" href="./css/xadmin.css">
    <script type="text/javascript" src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
    <script src="./lib/layui/layui.js" charset="utf-8"></script>
    <!--[if lt IE 9]>
      <script src="./js/html5.min.js"></script>
      <script src="./js/respond.min.js"></script>
    <![endif]-->
</head>
<body class="login-bg">

    <div class="login layui-anim layui-anim-up">
        <div class="message">淘乐饭-管理登录</div>
        <div id="darkbannerwrap"></div>

        <form method="post" class="layui-form" >
            @csrf
            <input name="username" placeholder="用户名"  type="text" lay-verify="required" class="layui-input" >
            <hr class="hr15">
            <input name="password" lay-verify="required" placeholder="密码"  type="password" class="layui-input" autocomplete="off">
            <hr class="hr15">
            <input value="登录" lay-submit lay-filter="login" style="width:100%;" type="submit">
            <hr class="hr20" >
        </form>
    </div>

    <script>
        $(function() {
            layui.use('form', function(){
              var form = layui.form;
              form.on('submit(login)', function(data){
                  $.ajax({
                      type: 'POST',
                      url: '/admin/getAdmin',
                      data:{
                          username: data.field.username,
                          password: data.field.password,
                          _token : data.field._token
                      },
                      dataType: "text",
                      headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      },
                      success: function (data) {//res为相应体,function为回调函数
                          if (data == 1 || data == "1") {
                              layer.msg("登陆成功",function(){
                                  location.href='index'
                              });
                          }else{
                              layer.msg("登陆失败，请检查账号密码");
                          }
                      },
                      error: function (XMLHttpRequest, textStatus, errorThrown) {
                          layer.alert('操作失败！！！' + XMLHttpRequest.status + "|" + XMLHttpRequest.readyState + "|" + textStatus, { icon: 5 });
                      }
                  });


                return false;
              });
            });
        })
    </script>
    <!-- 底部结束 -->
    <script>
    //百度统计可去掉
    var _hmt = _hmt || [];
    (function() {
      var hm = document.createElement("script");
      hm.src = "https://hm.baidu.com/hm.js?b393d153aeb26b46e9431fabaf0f6190";
      var s = document.getElementsByTagName("script")[0];
      s.parentNode.insertBefore(hm, s);
    })();
    </script>
</body>
</html>

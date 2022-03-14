<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>管理员账号管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi"/>
    <link rel="stylesheet" href="./css/font.css">
    <link rel="stylesheet" href="./css/xadmin.css">
    <script src="./lib/layui/layui.js" charset="utf-8"></script>
    <script type="text/javascript" src="./js/xadmin.js"></script>
    <!--[if lt IE 9]>
    <script src="./js/html5.min.js"></script>
    <script src="./js/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="x-nav">
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right"
       onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i></a>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                @if(Cookie::get('adminId') == 1)
                <div class="layui-card-header">
                    <button class="layui-btn" onclick="xadmin.open('添加管理员','./admin-add',600,400)"><i
                            class="layui-icon"></i>添加
                    </button>
                </div>
                @endif
                <div class="layui-card-body ">
                    <table class="layui-table layui-form">
                        <thead>

                        <tr>
                            <th>ID</th>
                            <th>登录名</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($admins as $admin)
                            <tr>
                                <td>{{$admin->id}}</td>
                                <td>{{$admin->username}}</td>
                                <td>
                                    @if(Cookie::get('adminId') == 1 || Cookie::get('adminId')==$admin->id)
                                        <a title="编辑" onclick="xadmin.open('编辑','admin-edit?id={{$admin->id}}&username={{$admin->username}}',600,400)"
                                           href="javascript:;">
                                            <i class="layui-icon">&#xe642;</i>
                                        </a>
                                        @if($admin->id !=1 && Cookie::get('adminId')!=$admin->id)
                                            <a title="删除" onclick="member_del(this,{{$admin->id}})" href="javascript:;">
                                                <i class="layui-icon">&#xe640;</i>
                                            </a>
                                        @endif
                                    @else
                                        仅限ID为1的超级管理员才能操作
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    /*用户-删除*/
    function member_del(obj, id) {
        layer.confirm('确认要删除吗？', function (index) {
            $.ajax({
                type: 'GET',
                url: '/admin/admin-del',
                data:{
                    id: id
                },
                dataType: "text",
                success: function (data) {//res为相应体,function为回调函数
                    if (data == 1 || data == "1") {
                        layer.msg('已删除!', {icon: 1, time: 1000});
                        $(obj).parents("tr").remove();
                    }else if(data == 0 || data == "0") {
                        layer.msg('删除失败!', {icon: 2, time: 1000});
                    }else{
                        layer.msg('越权操作!', {icon: 2, time: 1000});
                    }

                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.alert('操作失败！！！' + XMLHttpRequest.status + "|" + XMLHttpRequest.readyState + "|" + textStatus, { icon: 5 });
                }
            });


        });
    }
</script>
<script>var _hmt = _hmt || [];
    (function () {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?b393d153aeb26b46e9431fabaf0f6190";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();</script>
</html>

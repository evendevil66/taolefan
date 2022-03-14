<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>用户列表</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi"/>
    <link rel="stylesheet" href="./css/font.css">
    <link rel="stylesheet" href="./css/xadmin.css">
    <script src="./lib/layui/layui.js" charset="utf-8"></script>
    <script type="text/javascript" src="./js/xadmin.js"></script>
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
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
                <div class="layui-card-body ">
                    <form class="layui-form layui-col-space5">
                        <div class="layui-inline layui-show-xs-block">
                            <input type="text" name="openid" placeholder="昵称或openid" autocomplete="off"
                                   class="layui-input">
                        </div>
                        <div class="layui-inline layui-show-xs-block">
                            <button class="layui-btn" lay-submit lay-filter="sreach" type="submit"><i
                                    class="layui-icon">&#xe615;</i></button>&nbsp;&nbsp;昵称支持模糊搜索，openid仅支持精准搜索
                        </div>
                    </form>
                </div>
                <!--<div class="layui-card-header">
                    <button class="layui-btn layui-btn-danger" onclick="delAll()"><i class="layui-icon"></i>批量删除</button>
                    <button class="layui-btn" onclick="xadmin.open('添加用户','./member-add.html',600,400)"><i class="layui-icon"></i>添加</button>
                </div>-->
                <div class="layui-card-body layui-table-body layui-table-main">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th>openid</th>
                            <th>昵称</th>
                            <th>姓名</th>
                            <th>支付宝账号</th>
                            <th>返现比例</th>
                            <th>会员运营ID</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!--<tr>
                          <td>oTT_k5tQNfbBjxBMki0ERv3vMfxA</td>
                          <td>小赫</td>
                          <td>测试</td>
                          <td>13000000000</td>
                          <td>85%</td>
                          <td>2798429484</td>
                          <td class="td-manage">
                              <a onclick="member_stop(this,'10001')" href="javascript:;"  title="启用">
                                      <i class="layui-icon">&#xe601;</i>
                                  </a>
                            <a title="修改返现比例"  onclick="xadmin.open('修改返现比例','member-edit.html',600,400)" href="javascript:;">
                              <i class="layui-icon">&#xe642;</i>
                            </a>
                          </td>
                        </tr>-->
                        @foreach ($users as $user)

                            <tr>
                                <td>{{$user->id }}</td>
                                <td>{{$user->nickname}}</td>
                                <td>{{$user->username}}</td>
                                <td>{{$user->alipay_id}}</td>
                                <td>{{$user->rebate_ratio}}%</td>
                                <td>{{$user->special_id}}</td>
                                <td class="td-manage">
                                    <!--<a onclick="member_stop(this,'10001')" href="javascript:;"  title="启用">
                                        <i class="layui-icon">&#xe601;</i>
                                    </a>-->
                                    <a title="修改返现比例" onclick="xadmin.open('修改返现比例','member-edit?id={{$user->id}}&rebate_ratio={{$user->rebate_ratio}}&special_id={{$user->special_id}}',600,400)"
                                       href="javascript:;">
                                        <i class="layui-icon">&#xe642;</i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="layui-card-body ">
                    <div class="page">
                        <div>
                            @if(!$users->onFirstPage())
                                <a class="prev" href="{{$users->previousPageUrl()}}">&lt;&lt;</a>
                                @if($users->currentPage()-1>1)
                                    <a class="num" href="{{$users->url(1)}}">1</a>
                                @endif
                                <a class="num" href="{{$users->previousPageUrl()}}">{{$users->currentPage()-1}}</a>
                            @else
                                <a class="prev" href="" disabled="true">&lt;&lt;</a>
                            @endif
                            <span class="current">{{$users->currentPage()}}</span>
                            @if($users->currentPage()!=$users->lastPage())
                                <a class="num" href="{{$users->nextPageUrl()}}">{{$users->currentPage()+1}}</a>
                                @if($users->lastPage()-$users->currentPage()>1)
                                    <a class="num" href="{{$users->url($users->lastPage())}}">{{$users->lastPage()}}</a>
                                @endif
                                <a class="next" href="{{$users->nextPageUrl()}}">&gt;&gt;</a>
                            @else
                                <a class="next" href="" disabled="true">&gt;&gt;</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    $(function () {
        layui.use('form', function () {
            let form = layui.form;
            form.on('submit(sreach)', function (data) {
                location.replace("?openid="+data.field.openid);
            });
        });
    })
</script>
</html>

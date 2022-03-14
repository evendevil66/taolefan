<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>提现管理</title>
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
                <div class="layui-card-body ">
                    <form class="layui-form layui-col-space5">
                        <div class="layui-input-inline layui-show-xs-block">
                            <select name="status">
                                <option value="">提现处理状态</option>
                                <option value="0">未处理</option>
                                <option value="1">已处理</option>
                                <option value="-1">拒绝</option>
                            </select>
                            <!--订单状态 3：订单结算，12：订单付款， 13：订单失效，14：订单成功 -->
                        </div>
                        <div class="layui-input-inline layui-show-xs-block">
                            <input type="text" name="openid" placeholder="请输入openid" autocomplete="off"
                                   class="layui-input"></div>
                        <div class="layui-input-inline layui-show-xs-block">
                            <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                <i class="layui-icon">&#xe615;</i></button>
                        </div>
                    </form>
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>openid</th>
                            <th>昵称</th>
                            <th>提现金额</th>
                            <th>提现时间</th>
                            <th>处理状态</th>
                            <th>处理时间</th>
                            <th>拒绝原因</th>
                            <th>操作</th>
                        </thead>
                        <tbody>
                        @foreach ($receives as $receive)
                            <tr>
                                <td>{{$receive->id}}</td>
                                <td>{{$receive->openid}}</td>
                                <td>{{$receive->nickname}}</td>
                                <td>{{$receive->amount}}</td>
                                <td>{{$receive->receive_date}}</td>
                                <td>
                                    @if($receive->status == 0)
                                        未处理
                                    @elseif($receive->status == 1)
                                        已处理
                                    @elseif($receive->status == -1)
                                        已拒绝
                                    @else
                                        状态未知
                                    @endif
                                </td>
                                <td>{{$receive->process_time}}</td>
                                <td>{{$receive->reason}}</td>
                                <td>
                                    @if($receive->status == 0)
                                        <a title="pass" onclick="pass(this,{{$receive->id}})" href="javascript:;">
                                            <i class="layui-icon">&#x2714;</i>
                                        </a>
                                        <a title="拒绝" onclick="refuse(this,{{$receive->id}})" href="javascript:;">
                                            <i class="layui-icon">&#x2718;</i>
                                        </a>
                                    @else
                                        已处理不能再操作
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="layui-card-body ">
                    <div class="page">
                        <div>
                            @if(!$receives->onFirstPage())
                                <a class="prev" href="{{$receives->previousPageUrl()}}&openid={{$openid}}&status={{$status}}">&lt;&lt;</a>
                                @if($receives->currentPage()-1>1)
                                    <a class="num" href="{{$receives->url(1)}}&openid={{$openid}}&status={{$status}}">1</a>
                                @endif
                                <a class="num" href="{{$receives->previousPageUrl()}}&openid={{$openid}}&status={{$status}}">{{$receives->currentPage()-1}}</a>
                            @else
                                <a class="prev" href="" disabled="true">&lt;&lt;</a>
                            @endif
                            <span class="current">{{$receives->currentPage()}}</span>
                            @if($receives->currentPage()!=$receives->lastPage())
                                <a class="num" href="{{$receives->nextPageUrl()}}&openid={{$openid}}&status={{$status}}">{{$receives->currentPage()+1}}</a>
                                @if($receives->lastPage()-$receives->currentPage()>1)
                                    <a class="num" href="{{$receives->url($receives->lastPage())}}&openid={{$openid}}&status={{$status}}">{{$receives->lastPage()}}</a>
                                @endif
                                <a class="next" href="{{$receives->nextPageUrl()}}&openid={{$openid}}&status={{$status}}">&gt;&gt;</a>
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
    layui.use(['laydate', 'form']);

    $(function () {
        layui.use('form', function () {
            let form = layui.form;
            form.on('submit(sreach)', function (data) {
                location.replace("?openid=".data.field.trade_parent_id+"&status=".data.field.status);
            });
        });
    })

    function refuse(obj, id) {
        layer.confirm('确认要拒绝吗？', {
            content: "<p>确认要拒绝吗？</p><p>拒绝请输入原因，为保障资金安全拒绝后不可再次通过！</p><br/><input id='reason' type=\"text\" name=\"reason\" placeholder=\"请输入拒绝原因\" autocomplete=\"off\" class=\"layui-input\">"
        }, function (index) {
            $.ajax({
                type: 'GET',
                url: '/admin/receiveRefuse',
                data:{
                    id: id,
                    reason:document.getElementById("reason").value
                },
                dataType: "text",
                success: function (data) {//res为相应体,function为回调函数
                    if (data == 1 || data == "1") {
                        layer.msg('已拒绝!', {icon: 1, time: 1000},function (){
                            location.reload();
                        });
                    }else{
                        layer.msg('拒绝失败请刷新重试。', {icon: 2, time: 1000});
                    }

                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.alert('操作失败！！！' + XMLHttpRequest.status + "|" + XMLHttpRequest.readyState + "|" + textStatus, { icon: 5 });
                }
            });
        });
    }

    function pass(obj, id) {
        layer.confirm('确认已处理完成并设置为通过状态？', function (index) {
            $.ajax({
                type: 'GET',
                url: '/admin/receivePass',
                data:{
                    id: id,
                },
                dataType: "text",
                success: function (data) {//res为相应体,function为回调函数
                    if (data == 1 || data == "1") {
                        layer.msg('已通过!', {icon: 1, time: 1000},function (){
                            location.reload();
                        });
                    }else{
                        layer.msg('通过失败请刷新重试。', {icon: 2, time: 1000});
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

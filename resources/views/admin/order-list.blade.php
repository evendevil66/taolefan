<!DOCTYPE html>
<html class="x-admin-sm">

<head>
    <meta charset="UTF-8">
    <title>欢迎页面-X-admin2.2</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi"/>
    <link rel="stylesheet" href="./css/font.css">
    <link rel="stylesheet" href="./css/xadmin.css">
    <script src="./lib/layui/layui.js" charset="utf-8"></script>
    <script type="text/javascript" src="./js/xadmin.js"></script>
</head>

<body>
<div class="x-nav">
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right"
       onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body ">
                    <form class="layui-form layui-col-space5">
                        <div class="layui-input-inline layui-show-xs-block">
                            <input class="layui-input" placeholder="开始日" name="start" id="start"></div>
                        <div class="layui-input-inline layui-show-xs-block">
                            <input class="layui-input" placeholder="截止日" name="end" id="end"></div>
                        <div class="layui-input-inline layui-show-xs-block">
                            <select name="tk_status">
                                <option value="">订单状态</option>
                                <option value="12">已付款</option>
                                <option value="14">已收货</option>
                                <option value="3">联盟已结算</option>
                                <option value="13">已作废</option>
                            </select>
                            <!--订单状态 3：订单结算，12：订单付款， 13：订单失效，14：订单成功 -->
                        </div>
                        <div class="layui-input-inline layui-show-xs-block">
                            <input type="text" name="trade_parent_id" placeholder="请输入订单号" autocomplete="off"
                                   class="layui-input"></div>
                        <div class="layui-input-inline layui-show-xs-block">
                            <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                <i class="layui-icon">&#xe615;</i></button>
                        </div>
                    </form>
                </div>
                <div class="layui-card-body ">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th>订单编号</th>
                            <th>openid</th>
                            <th>商品名称</th>
                            <th>付款时间</th>
                            <th>订单状态</th>
                            <th>付款金额</th>
                            <th>联盟结算时间</th>
                            <th>预估收入</th>
                            <th>预估返利</th>
                            <th>站点结算状态</th>
                            <th>维权状态</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!--<tr>
                            <td>2022009171822298053</td>
                            <td>oTT_k5tQNfbBjxBMki0ERv3vMfxA</td>
                            <td>测试商品名称</td>
                            <td>2022-03-10 10:10:10</td>
                            <td>已付款</td>
                            <td>100.00</td>
                            <td>未结算</td>
                            <td>20.00</td>
                            <td>15.00</td>
                            <td class="td-status">
                                <span class="layui-btn layui-btn-normal layui-btn-mini">未维权</span></td>
                        </tr>-->
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{$order->trade_parent_id}}</td>
                                <td>{{$order->openid}}</td>
                                <td>{{$order->item_title}}</td>
                                <td>{{$order->tk_paid_time}}</td>
                                @if($order->tk_status==12||$order->tk_status=="12")
                                    <td>已付款</td>
                                @elseif($order->tk_status==13||$order->tk_status=="13")
                                    <td>已作废</td>
                                @elseif($order->tk_status==14||$order->tk_status=="15")
                                    <td>已收货</td>
                                @elseif($order->tk_status==3||$order->tk_status=="3")
                                    <td>已结算</td>
                                @else
                                    <td>未知</td>
                                @endif
                                <td>{{$order->pay_price}}</td>
                                <td>{{$order->tk_earning_time}}</td>
                                <td>{{$order->pub_share_pre_fee}}</td>
                                <td>{{$order->rebate_pre_fee}}</td>
                                @if($order->rebate_status==1||$order->rebate_status=="1")
                                    <td>已结算</td>
                                @else
                                    <td>未结算</td>
                                @endif
                                    <td class="td-status">
                                        @if($order->refund_tag==1||$order->tk_status=="1")
                                            <span class="layui-btn layui-btn-danger layui-btn-mini">已维权</span>
                                        @else
                                            <span class="layui-btn layui-btn-normal layui-btn-mini">未维权</span>
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
                            @if(!$orders->onFirstPage())
                                <a class="prev" href="{{$orders->previousPageUrl()}}&trade_parent_id={{$trade_parent_id}}&start={{$start}}&end={{$end}}&tk_status={{$tk_status}}">&lt;&lt;</a>
                                @if($orders->currentPage()-1>1)
                                    <a class="num" href="{{$orders->url(1)}}&trade_parent_id={{$trade_parent_id}}&start={{$start}}&end={{$end}}&tk_status={{$tk_status}}">1</a>
                                @endif
                                <a class="num" href="{{$orders->previousPageUrl()}}&trade_parent_id={{$trade_parent_id}}&start={{$start}}&end={{$end}}&tk_status={{$tk_status}}">{{$orders->currentPage()-1}}</a>
                            @else
                                <a class="prev" href="" disabled="true">&lt;&lt;</a>
                            @endif
                            <span class="current">{{$orders->currentPage()}}</span>
                            @if($orders->currentPage()!=$orders->lastPage())
                                <a class="num" href="{{$orders->nextPageUrl()}}&trade_parent_id={{$trade_parent_id}}&start={{$start}}&end={{$end}}&tk_status={{$tk_status}}">{{$orders->currentPage()+1}}</a>
                                @if($orders->lastPage()-$orders->currentPage()>1)
                                    <a class="num"
                                       href="{{$orders->url($orders->lastPage())}}&trade_parent_id={{$trade_parent_id}}&start={{$start}}&end={{$end}}&tk_status={{$tk_status}}">{{$orders->lastPage()}}</a>
                                @endif
                                <a class="next" href="{{$orders->nextPageUrl()}}&trade_parent_id={{$trade_parent_id}}&start={{$start}}&end={{$end}}&tk_status={{$tk_status}}">&gt;&gt;</a>
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
                location.replace("?trade_parent_id=".data.field.trade_parent_id+"&start="+data.field.start+"&end=".data.field.end+"&tk_status=".data.field.tk_status);
            });
        });
    })
</script>

</html>

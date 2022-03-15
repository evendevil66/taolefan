<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.bundle.min.js"></script>
    <title>订单查询</title>

</head>
<body>
<div class="row">
    <div class="col-4" style="text-align: center;color: coral;">订单号</div>
    <div class="col-4" style="text-align: center;color: coral;">商品名称<br/>付款金额</div>
    <div class="col-4" style="text-align: center;color: coral;">返利金额<br/>状态</div>
</div>
<hr/>
@foreach($orders as $order)
    <div class="row">
        <div class="col-4" style="text-align: center;word-wrap:break-word">{{$order->trade_parent_id}}</div>
        <div class="col-4" style="text-align: center;">{{mb_substr($order->item_title, 0, 10)}}<br/>{{$order->pay_price}}元
        </div>
        <div class="col-4" style="text-align: center;">{{$order->rebate_pre_fee}}元<br/>
            @switch($order->tk_status)
                @case(12)
                已付款
                @break
                @case(13)
                已失效
                @break
                @case(14)
                已收货
                @break
                @case(3)
                @if($order->tk_status==1)
                    待结算
                @else
                    已结算
                @endif
                @break
                @default
                未知
            @endswitch
        </div>
    </div>
    <hr/>
@endforeach
<br/>
<div class="row" style="text-align: center;">
    @if(!$orders->onFirstPage())
    <div class="col" style="text-align: center;"><a href="{{$orders->previousPageUrl()}}&openid={{$openid}}"><<</a></div>
    @else
        <div class="col" style="text-align: center;"><a disabled="true"><<</a></div>
    @endif
    <div class="col" style="text-align: center;"><span>{{$orders->currentPage()}}</span></div>
        @if($orders->currentPage()!=$orders->lastPage())
    <div class="col" style="text-align: center;"><a href="{{$orders->nextPageUrl()}}&openid={{$openid}}">{{$orders->currentPage()+1}}</a></div>
    <div class="col" style="text-align: center;"><a href="{{$orders->nextPageUrl()}}&openid={{$openid}}">>></a></div>
        @else
            <div class="col" style="text-align: center;"><a disabled="true">>></a></div>
        @endif

</div>
</body>
</html>

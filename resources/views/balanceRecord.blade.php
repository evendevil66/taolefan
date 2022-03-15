<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.bundle.min.js"></script>
    <title>余额变动查询</title>

</head>
<body>
<div class="row">
    <div class="col-4" style="text-align: center;color: coral;">变动日期</div>
    <div class="col-4" style="text-align: center;color: coral;">变动原因</div>
    <div class="col-4" style="text-align: center;color: coral;">变动金额</div>
</div>
<hr/>
@foreach($balanceRecord as $record)
    <div class="row">
        <div class="col-4" style="text-align: center;word-wrap:break-word">{{$record->createtime}}</div>
        <div class="col-4" style="text-align: center;word-wrap:break-word">{{$record->event}}
        </div>
        <div class="col-4" style="text-align: center;">{{$record->change}}元<br/>

        </div>
    </div>
    <hr/>
@endforeach
<br/>
<div class="row" style="text-align: center;">
    @if(!$balanceRecord->onFirstPage())
    <div class="col" style="text-align: center;"><a href="{{$balanceRecord->previousPageUrl()}}&openid={{$openid}}"><<</a></div>
    @else
        <div class="col" style="text-align: center;"><a disabled="true"><<</a></div>
    @endif
    <div class="col" style="text-align: center;"><span>{{$balanceRecord->currentPage()}}</span></div>
        @if($balanceRecord->currentPage()!=$balanceRecord->lastPage())
    <div class="col" style="text-align: center;"><a href="{{$balanceRecord->nextPageUrl()}}&openid={{$openid}}">{{$balanceRecord->currentPage()+1}}</a></div>
    <div class="col" style="text-align: center;"><a href="{{$balanceRecord->nextPageUrl()}}&openid={{$openid}}">>></a></div>
        @else
            <div class="col" style="text-align: center;"><a disabled="true">>></a></div>
        @endif

</div>
</body>
</html>

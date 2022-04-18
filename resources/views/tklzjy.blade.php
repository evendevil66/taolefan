<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <title>{{config("config.name")}}</title>
</head>
<body>
<style type="text/css">
    body {
        background: #eee;
        font-size: 14px;
    }

    .scenograph {
        padding: 16px 16px 0;
    }

    .scenograph #type-bimg {
        box-shadow: 0px 4px 8px rgba(21, 0, 71, .1);
        width: 100%;
        max-height: 442px;
        object-fit: cover;
    }

    .drugsword {
        margin: 14px;
        margin-top: 16px;
        text-align: center;
    }

    .pitreverse {
        line-height: 1.5;
        margin-bottom: 14px;
    }

    .medicine-bag {
        background: white;
        border: 1px dashed #fb6a65;
        padding: .5rem;
        border-radius: 5px;
    }

    .copy-tip {
        display: flex;
        justify-content: space-around;
        align-items: center;
        background: linear-gradient(to right, #f9c492, #fb6a65);
        color: white;
        font-size: 12px;
        height: 24px;
    }

    .jtone {
        position: relative;
        width: 0;
        height: 0;
        border-top: 12px solid transparent;
        border-left: 10px solid white;
        border-bottom: 12px solid transparent;
    }

    .jtone::before {
        content: "";
        position: absolute;
        top: -12px;
        left: -13px;
        width: 0;
        height: 0;
        border-top: 12px solid transparent;
        border-left: 10px solid #fab189;
        border-bottom: 12px solid transparent;
    }

    .jttwo {
        position: relative;
        width: 0;
        height: 0;
        border-top: 12px solid transparent;
        border-left: 10px solid white;
        border-bottom: 12px solid transparent;
    }

    .jttwo::before {
        content: "";
        position: absolute;
        top: -12px;
        left: -13px;
        width: 0;
        height: 0;
        border-top: 12px solid transparent;
        border-left: 10px solid #fa9b7e;
        border-bottom: 12px solid transparent;
    }

    .jtthree {
        position: relative;
        width: 0;
        height: 0;
        border-top: 12px solid transparent;
        border-left: 10px solid white;
        border-bottom: 12px solid transparent;
    }

    .jtthree::before {
        content: "";
        position: absolute;
        top: -12px;
        left: -13px;
        width: 0;
        height: 0;
        border-top: 12px solid transparent;
        border-left: 10px solid #fa8673;
        border-bottom: 12px solid transparent;
    }

    .icopy {
        background: #fb6a65;
        border-radius: 5px;
        color: white;
        font-size: 14px;
        margin: 16px auto;
        padding: 10px;
        margin-bottom: 2px;
    }

    .info-card{
        padding-left: 10px;
    }
</style>
<div class="scenograph" style="display: block;"> <img id="type-bimg" src="{{$image}}"> </div>
<div class="drugsword" style="display: block;">
    <div class="pitreverse"> {{$title}} </div>
    <div class="medicine-bag">
        <div>
            <div id="tkl" class="tkl">{{$tpwd}}</div>
        </div>
    </div>
    <div type="button" data-clipboard-text="" class="icopy" id="icopy" data-clipboard-target="#tkl" data-clipboard-action="copy">
        一键复制 </div>
    <div id="copy-tip" class="copy-tip"> <span>长按框内</span> <span class="jtone"></span> <span>全选</span> <span
            class="jttwo"></span> <span>复制</span> <span class="jtthree"></span> <span>打开APP</span> </div>
</div>
<div class="info-card" style="margin-top: 10px">
    <div class="box1 bottom">
        优惠券：{{$couponInfo}}<br>
        该商品返现比例：{{$maxCommissionRate}}%<br>
        预计返现金额：{{$estimate}}元<br>
        返现计算：实付款 * {{$maxCommissionRate}}%<br>
        注意：返现以实际付款金额*返现比例为准<br>
        <span style="color: red;">省钱卡/签到红包等相当于实付，不影响返现</span><br><br>
        ①使用大促期间的
        <span style="color: red;">超级红包，</span>可能会导致无法跟单。（本公众号领取的除外）<br>
        ②下单后2分钟内会收到<span style="color: red;">跟单成功通知</span>，如未收到或查询不到订单，请发订单号给公众号绑定<br>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"> </script>
<script>
    //复制文本
    var clipboard = new ClipboardJS('.icopy');
    clipboard.on('success', function(e) {
        document.getElementById('icopy').innerHTML = '复制成功';
        e.clearSelection();
        setTimeout(function() {
            document.getElementById('icopy').innerHTML = ' 一键复制 ';
        }, 2000);
    });
    clipboard.on('error', function(e) {
        console.log(e);
        document.getElementById('icopy').innerHTML = '复制失败，请长按复制';
        setTimeout(function() {
            document.getElementById('icopy').innerHTML = ' 一键复制 ';
        }, 2000);
    });
</script>
</body>
</html>

<!DOCTYPE html>
<html class="x-admin-sm">

    <head>
        <meta charset="UTF-8">
        <title>欢迎页面-X-admin2.2</title>
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
        <link rel="stylesheet" href="./css/font.css">
        <link rel="stylesheet" href="./css/xadmin.css">
        <script src="./lib/layui/layui.js" charset="utf-8"></script>
        <script type="text/javascript" src="./js/xadmin.js"></script>
    </head>

    <body>
        <div class="x-nav">
            <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
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
                                    <select name="contrller">
                                        <option value="">订单状态</option>
                                        <option value="12">已付款</option>
                                        <option value="14">已收货</option>
                                        <option value="3">联盟已结算</option>
                                        <option value="13">已作废</option>
                                    </select>
                                    <!--订单状态 3：订单结算，12：订单付款， 13：订单失效，14：订单成功 -->
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="trade_parent_id" placeholder="请输入订单号" autocomplete="off" class="layui-input"></div>
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
                                        <th>结算时间</th>
                                        <th>预估收入</th>
                                        <th>预估返利</th>
                                        <th>维权状态（点击修改）</th>
                                        </tr>
                                </thead>
                                <tbody>
                                    <tr>
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
                                    </tr>
                                    <tr>
                                        <td>2022009171822298053</td>
                                        <td>oTT_k5tQNfbBjxBMki0ERv3vMfxA</td>
                                        <td>测试商品名称</td>
                                        <td>2022-03-08 10:10:10</td>
                                        <td>已结算</td>
                                        <td>100.00</td>
                                        <td>2022-03-11 10:10:10</td>
                                        <td>20.00</td>
                                        <td>15.00</td>
                                        <td class="td-status">
                                            <span class="layui-btn layui-btn-normal layui-btn-mini">未维权</span></td>
                                    </tr>
                                    <tr>
                                        <td>2022009171822298053</td>
                                        <td>oTT_k5tQNfbBjxBMki0ERv3vMfxA</td>
                                        <td>测试商品名称</td>
                                        <td>2022-03-02 10:10:10</td>
                                        <td>已结算</td>
                                        <td>100.00</td>
                                        <td>2022-03-07 10:10:10</td>
                                        <td>20.00</td>
                                        <td>15.00</td>
                                        <td class="td-status">
                                            <span class="layui-btn layui-btn-danger layui-btn-mini">已维权</span></td>
                                        <!--<td class="td-manage">
                                            <a title="手动修改" onclick="xadmin.open('编辑','order-view.html')" href="javascript:;">
                                                <i class="layui-icon">&#xe63c;</i></a>
                                        </td>-->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="layui-card-body ">
                            <div class="page">
                                <div>
                                    <a class="prev" href="">&lt;&lt;</a>
                                    <span class="current" >1</span>
                                    <a class="num" href="">2</a>
                                    <a class="num" href="">3</a>
                                    <a class="num" href="">15</a>
                                    <a class="next" href="">&gt;&gt;</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script>layui.use(['laydate', 'form'],
        function() {
            var laydate = layui.laydate;

            //执行一个laydate实例
            laydate.render({
                elem: '#start' //指定元素
            });

            //执行一个laydate实例
            laydate.render({
                elem: '#end' //指定元素
            });
        });

        /*用户-停用*/
        function member_stop(obj, id) {
            layer.confirm('确认要停用吗？',
            function(index) {

                if ($(obj).attr('title') == '启用') {

                    //发异步把用户状态进行更改
                    $(obj).attr('title', '停用');
                    $(obj).find('i').html('&#xe62f;');

                    $(obj).parents("tr").find(".td-status").find('span').addClass('layui-btn-disabled').html('已停用');
                    layer.msg('已停用!', {
                        icon: 5,
                        time: 1000
                    });

                } else {
                    $(obj).attr('title', '启用');
                    $(obj).find('i').html('&#xe601;');

                    $(obj).parents("tr").find(".td-status").find('span').removeClass('layui-btn-disabled').html('已启用');
                    layer.msg('已启用!', {
                        icon: 5,
                        time: 1000
                    });
                }

            });
        }

        /*用户-删除*/
        function member_del(obj, id) {
            layer.confirm('确认要删除吗？',
            function(index) {
                //发异步删除数据
                $(obj).parents("tr").remove();
                layer.msg('已删除!', {
                    icon: 1,
                    time: 1000
                });
            });
        }

        function delAll(argument) {

            var data = tableCheck.getData();

            layer.confirm('确认要删除吗？' + data,
            function(index) {
                //捉到所有被选中的，发异步进行删除
                layer.msg('删除成功', {
                    icon: 1
                });
                $(".layui-form-checked").not('.header').parents('tr').remove();
            });
        }</script>

</html>

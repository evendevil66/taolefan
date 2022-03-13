<!DOCTYPE html>
<html class="x-admin-sm">
    <head>
        <meta charset="UTF-8">
        <title>提现管理</title>
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
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
          <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
            <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i></a>
        </div>
        <div class="layui-fluid">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body ">
                            <form class="layui-form layui-col-space5">
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name="contrller">
                                        <option value="">提现处理状态</option>
                                        <option value="0">未处理</option>
                                        <option value="1">已处理</option>
                                        <option value="-1">拒绝</option>
                                    </select>
                                    <!--订单状态 3：订单结算，12：订单付款， 13：订单失效，14：订单成功 -->
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="trade_parent_id" placeholder="请输入openid或昵称" autocomplete="off" class="layui-input"></div>
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
                                <tr>
                                  <td>1</td>
                                    <td>oTT_k5tQNfbBjxBMki0ERv3vMfxA</td>
                                  <td>小赫</td>
                                    <td>2022-03-10 10:10:10</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>
                                    <a title="pass"  onclick="pass(this,'要通过的id')" href="javascript:;">
                                      <i class="layui-icon">&#x2714;</i>
                                    </a>
                                    <a title="拒绝" onclick="refuse(this,'要拒绝的id')" href="javascript:;">
                                      <i class="layui-icon">&#x2718;</i>
                                    </a>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                        </div>
                        <div class="layui-card-body ">
                            <div class="page">
                                <div>
                                  <a class="prev" href="">&lt;&lt;</a>
                                  <a class="num" href="">1</a>
                                  <span class="current">2</span>
                                  <a class="num" href="">3</a>
                                  <a class="num" href="">489</a>
                                  <a class="next" href="">&gt;&gt;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script>
      layui.use(['laydate','form'], function(){
        var laydate = layui.laydate;
        var form = layui.form;

        //执行一个laydate实例
        laydate.render({
          elem: '#start' //指定元素
        });

        //执行一个laydate实例
        laydate.render({
          elem: '#end' //指定元素
        });
      });


      function refuse(obj,id){
          layer.confirm('确认要拒绝吗？',{
              content:"<p>确认要拒绝吗？</p><p>拒绝请输入原因，为保障资金安全拒绝后不可再次通过！</p><br/><input type=\"text\" name=\"reason\" placeholder=\"请输入拒绝原因\" autocomplete=\"off\" class=\"layui-input\">"
          },function(index){
              //发异步删除数据
              //$(obj).parents("tr").remove();
              layer.msg('已拒绝!',{icon:1,time:1000});
          });
      }

      function pass(obj,id){
          layer.confirm('确认已处理完成并设置为通过状态？',function(index){
              //发异步删除数据
              //$(obj).parents("tr").remove();
              layer.msg('已通过!',{icon:1,time:1000});
          });
      }
    </script>
    <script>var _hmt = _hmt || []; (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?b393d153aeb26b46e9431fabaf0f6190";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
      })();</script>
</html>

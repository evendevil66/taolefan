<!DOCTYPE html>
<html class="x-admin-sm">

    <head>
        <meta charset="UTF-8">
        <title>用户编辑</title>
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="./css/font.css">
        <link rel="stylesheet" href="./css/xadmin.css">
        <script type="text/javascript" src="./lib/layui/layui.js" charset="utf-8"></script>
        <script type="text/javascript" src="./js/xadmin.js"></script>
        <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
        <!--[if lt IE 9]>
            <script src="./js/html5.min.js"></script>
            <script src="./js/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="layui-fluid">
            <div class="layui-row">
                <form class="layui-form">
                    @csrf
                    <div class="layui-form-item">
                        <label for="id" class="layui-form-label">
                            <span class="x-red">*</span>openid
                        </label>
                        <div class="layui-input-inline">
                            <input value="{{$id}}" type="text" id="id" name="id" required="" lay-verify="id"
                                   autocomplete="off" class="layui-input" readonly="true" style="background:#CCCCCC">
                        </div>
                    </div>
                  <div class="layui-form-item">
                      <label for="rebate_ratio" class="layui-form-label">
                          <span class="x-red">*</span>返现比例
                      </label>
                      <div class="layui-input-inline">
                          <input value="{{$rebate_ratio}}" type="text" id="rebate_ratio" name="rebate_ratio" required="" lay-verify="rebate_ratio"
                          autocomplete="off" class="layui-input">
                      </div>
                  </div>
                    <div class="layui-form-item">
                        <label for="rebate_ratio" class="layui-form-label">
                            用户运营ID
                        </label>
                        <div class="layui-input-inline">
                            <input type="text" value="{{$special_id}}" id="special_id" name="special_id"
                                   autocomplete="off" class="layui-input" lay-verify="special_id">
                        </div>
                    </div>
                  <div class="layui-form-item">
                      <label for="L_repass" class="layui-form-label">
                      </label>
                      <button  class="layui-btn" lay-filter="modify" lay-submit="">
                          修改
                      </button>
                  </div>
              </form>
            </div>
        </div>
        <script>layui.use(['form', 'layer'],
            function() {
                $ = layui.jquery;
                var form = layui.form,
                layer = layui.layer;

                //自定义验证规则
                form.verify({
                    rebate_ratio: function(value) {
                        if(value === "" || value ==null){
                            return '请输入返现比例';
                        }
                        if(isNaN(value)){
                            return "返现比例必须为数字";
                        }
                        if (value>100) {
                            return '返现比例不能超过100%';
                        }
                        if (value<0) {
                            return '返现比例不能小于0';
                        }
                    },
                    special_id: function(value) {
                        if (value.length>20) {
                            return '运营id长度有误';
                        }
                    }
                });

                //监听提交
                form.on('submit(modify)', function(data) {
                    $.ajax({
                        type: 'POST',
                        url: '/admin/modifyUser',
                        data:{
                            id: data.field.id,
                            rebate_ratio: data.field.rebate_ratio,
                            special_id: data.field.special_id,
                            _token : data.field._token
                        },
                        dataType: "text",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (data) {//res为相应体,function为回调函数
                            if (data == 1 || data == "1") {
                                layer.alert("修改成功", {
                                        icon: 1
                                    },
                                    function() {
                                        //关闭当前frame
                                        xadmin.close();
                                        // 可以对父窗口进行刷新
                                        xadmin.father_reload();
                                    });
                            }else{
                                layer.alert("修改失败Error", {icon: 2});
                            }
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            layer.alert('操作失败！！！' + XMLHttpRequest.status + "|" + XMLHttpRequest.readyState + "|" + textStatus, { icon: 5 });
                        }
                    });
                    return false;
                });

            });</script>
        <script>var _hmt = _hmt || []; (function() {
                var hm = document.createElement("script");
                hm.src = "https://hm.baidu.com/hm.js?b393d153aeb26b46e9431fabaf0f6190";
                var s = document.getElementsByTagName("script")[0];
                s.parentNode.insertBefore(hm, s);
            })();</script>
    </body>

</html>

<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>欢迎页面</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
    <link rel="stylesheet" href="./css/font.css">
    <link rel="stylesheet" href="./css/xadmin.css">
    <script src="./lib/layui/layui.js" charset="utf-8"></script>
    <script type="text/javascript" src="./js/xadmin.js"></script>
    <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
    <!--[if lt IE 9]>
    <script src="./js/html5.min.js"></script>
    <script src="./js/respond.min.js"></script>
    <![endif]-->
    <script>
        window.onload = clock;
        self.setInterval("clock()",1000);
        function clock(){
            var myDate = new Date();
            var time = myDate.getFullYear()+"-"+(myDate.getMonth()+1)+"-"+myDate.getDate()+"  "+myDate.getHours()+":"+myDate.getMinutes()+":"+myDate.getSeconds();
            document.getElementById("time").innerHTML=time;
        }


    </script>
</head>
<body >
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body ">
                    <blockquote class="layui-elem-quote">欢迎管理员：
                        <span class="x-red">{{Cookie::get('username')}}</span>,当前时间:<span id="time"></span>
                    </blockquote>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">数据统计</div>
                <div class="layui-card-body ">
                    <ul class="layui-row layui-col-space10 layui-this x-admin-carousel x-admin-backlog">
                        <li class="layui-col-md3 layui-col-xs6">
                            <a href="javascript:;" class="x-admin-backlog-body">
                                <h3>今日订单数量</h3>
                                <p>
                                    <cite>{{$count}}</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-md3 layui-col-xs6">
                            <a href="javascript:;" class="x-admin-backlog-body">
                                <h3>今日预估总收入</h3>
                                <p>
                                    <cite>{{$pub_share_pre_fee}}</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-md3 layui-col-xs6">
                            <a href="javascript:;" class="x-admin-backlog-body">
                                <h3>今日预估返利金额</h3>
                                <p>
                                    <cite>{{$rebate_pre_fee}}</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-md3 layui-col-xs6">
                            <a href="javascript:;" class="x-admin-backlog-body">
                                <h3>今日提现数量</h3>
                                <p>
                                    <cite>{{$receiveCount}}</cite></p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--<div class="layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">下载
                    <span class="layui-badge layui-bg-cyan layuiadmin-badge">月</span></div>
                <div class="layui-card-body  ">
                    <p class="layuiadmin-big-font">33,555</p>
                    <p>新下载
                        <span class="layuiadmin-span-color">10%
                            <i class="layui-inline layui-icon layui-icon-face-smile-b"></i></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">下载
                    <span class="layui-badge layui-bg-cyan layuiadmin-badge">月</span></div>
                <div class="layui-card-body ">
                    <p class="layuiadmin-big-font">33,555</p>
                    <p>新下载
                        <span class="layuiadmin-span-color">10%
                            <i class="layui-inline layui-icon layui-icon-face-smile-b"></i></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">下载
                    <span class="layui-badge layui-bg-cyan layuiadmin-badge">月</span></div>
                <div class="layui-card-body ">
                    <p class="layuiadmin-big-font">33,555</p>
                    <p>新下载
                        <span class="layuiadmin-span-color">10%
                            <i class="layui-inline layui-icon layui-icon-face-smile-b"></i></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">下载
                    <span class="layui-badge layui-bg-cyan layuiadmin-badge">月</span></div>
                <div class="layui-card-body ">
                    <p class="layuiadmin-big-font">33,555</p>
                    <p>新下载
                        <span class="layuiadmin-span-color">10%
                            <i class="layui-inline layui-icon layui-icon-face-smile-b"></i></span>
                    </p>
                </div>
            </div>
        </div>-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">系统信息</div>
                <div class="layui-card-body ">
                    <table class="layui-table">
                        <tbody>
                        <tr>
                            <th>淘乐饭版本</th>
                            <td>1.0</td></tr>
                        <tr>
                            <th>服务器URL</th>
                            <td>
                                {{$_SERVER['SERVER_NAME']}}
                            </td></tr>
                        <tr>
                            <th>操作系统</th>
                            <td>{{php_uname()}}</td></tr>
                        <tr>
                            <th>运行环境</th>
                            <td>{{$_SERVER['SERVER_SOFTWARE']}}</td></tr>
                        <tr>
                            <th>PHP版本</th>
                            <td>{{phpversion()}}</td></tr>
                        <tr>
                            <th>PHP运行方式</th>
                            <td>{{php_sapi_name()}}</td></tr>
                        <tr>
                            <th>执行时间限制</th>
                            <td>{{ini_get('max_execution_time')}}</td></tr>
                        <tr>
                            <th>剩余空间</th>
                            <td>{{sprintf("%01.2f",(disk_free_space('/'))/1048576)}}MB</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">开发团队</div>
                <div class="layui-card-body ">
                    <table class="layui-table">
                        <tbody>
                        <tr>
                            <th>版权所有</th>
                            <td>杭州猫萌特科技有限公司
                                <a href="https://www.maomengte.com" target="_blank">访问官网</a></td>
                        </tr>
                        <tr>
                            <th>开发者</th>
                            <td>张嘉祺(zhangjiaqi@maomengte.com)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <style id="welcome_style"></style>
    </div>
</div>
</body>
</html>



## 关于项目
淘乐饭是一款基于PHP的微信公众号返利项目，支持认证或未认证的订阅号及服务号使用。  

本项目使用 [Laravel](https://laravel.com/) 作为主架构进行开发，公众号交互使用 [EasyWechat](https://www.easywechat.com) 实现，后端基于 [X-admin](http://x.xuebingsi.com)二次开发。
本项目使用GPLv3协议，允许复制、传播、修改及商业使用，禁止将修改后和衍生的代码做为闭源的商业软件发布和销售。

## 对接API

本项目主要使用 [淘宝联盟](https://pub.alimama.com/) 、 [大淘客](https://www.dataoke.com) 、[微信公众开放平台](https://mp.weixin.qq.com/) 等平台接口进行开发

## 主要配置文件
1、/config/config.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存站点/平台基本信息、淘宝联盟和大淘客APPKEY等信息  
2、/config/wechat.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存微信公众平台APPKEY相关信息  
2、.env &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存数据库相关信息

## 已实现功能
1、公众号转链（京东/淘宝），用户发送原始或其他淘客的淘口令到公众号，后台转链并计算出返利金额、获取优惠券信息返回给用户。  
2、注册功能，关注即可完成注册，绑定微信openID（微信唯一标识）。用户可以通过公众号菜单快速补全提现账户信息，还可以绑定淘宝账号，获取special_id（淘宝会员运营唯一标识）  
3、（非稳定版）自动获取订单及绑定订单，自动储存所有订单到数据，带有会员运营id的订单会自动绑定openid  
4、手动发送订单号绑定订单  
5、用户查询自己的订单信息及提现  
6、后台管理面板  
7、用户查询订单信息时自动刷新用户近一个月订单状态，每月自动刷新上两月订单状态，并对上月确认收货的订单进行结算

## TODO
1、认证服务号订单消息实时推送  
2、渠道管理  
3、全部转向大淘客接口开发  
更多功能仍在逐渐开发中，也可以自动去开发相关功能，大家的Star是我持续开发的动力

## Update
注意：小版本更新可能涉及数据库轻微变动，建议更新前备份数据并使用最新sql文件重新构建结构，再导入数据使用。  
  
2022.3.28 v1.1.2  
<font color="#ee4444">注意：更新本版本请务必安装Redis并为PHP安装Redis扩展，默认自动匹配本地无密码默认端口（6379）Redis，可在/config/database.php中自行修改</font>  
优化自动跟单体验 使用Redis存储信息 支持京东自动跟单  
解决了淘宝联盟会员跟单可能失败的问题  
  
2022.3.20 v1.1.1  
优化单订单多商品处理机制  
优化淘宝订单维权机制（后台显示已维权，默认取消返利）  
    
2022.3.19 v1.1.0  
新增京东返利功能，请注册京东联盟，授权至大淘客账号并在config中进行相关配置  
  
2022.3.19 v1.0.3  
新增饿了么返利功能  
如需开启微信饿了么返利功能需在config中配置返利图片（默认为您的域名下/eleme.jpeg，可自行修改），自行在微信公众号发布一篇带有使用方法和返利小程序的链接（可在淘宝联盟APP获取）  
淘宝饿了么返利无需其他操作 如不需要小程序返利请自行删除相关前端代码  
  
2022.3.18 v1.0.2  
修复商品标题过长无法保存订单的问题  
修复同一订单包含多个商品时无法计算返利的问题

2022.3.16 v1.0.1  
更新可通过config.php配置用户默认返现比例  

2022.3.16 v1.0.0  
发布第一版

## 部署方法
环境要求：PHP > 7 && PHP < 8  ｜ MySQL/MariaDB  
微信公众号：订阅号/服务号均可，服务号或认证订阅号可以使用自定义菜单，未认证订阅号仅可文本响应。

下载或clone项目代码到所需环境  
````PHP
#国外环境
git clone https://github.com/evendevil66/taolefan.git
#国内环境
git clone https://gitee.com/cdj8/taolefan.git
````
在项目目录下执行Composer命令安装依赖包及自动加载  
````shell script
composer install
composer dump-auto
````
复制.env.example文件为.env
````shell script
cp .env.example .env
````
修改.env中的数据库配置并导入项目根目录下的 taolefan.sql 到数据库
````text
DB_CONNECTION=mysql  #默认使用mysql请勿修改 可支持MariaDB
DB_HOST=127.0.0.1  #数据库连接地址
DB_PORT=3306  #数据库连接端口
DB_DATABASE=taolefan #数据库名
DB_USERNAME=root  #数据库用户名
DB_PASSWORD=  #数据库密码
````
如修改了Redis端口、密码等配置，请额外在config/database.php中修改Redis相关配置  
请将default和cache指向同一Redis服务器的不同database
````php
'default' => [
'url' => env('REDIS_URL'),
'host' => env('REDIS_HOST', '127.0.0.1'),
'password' => env('REDIS_PASSWORD', null),
'port' => env('REDIS_PORT', '6379'),
'database' => env('REDIS_DB', '1'),
],

'cache' => [
'url' => env('REDIS_URL'),
'host' => env('REDIS_HOST', '127.0.0.1'),
'password' => env('REDIS_PASSWORD', null),
'port' => env('REDIS_PORT', '6379'),
'database' => env('REDIS_CACHE_DB', '2'),
]
````

修改/config/wechat.php配置 根据微信公众平台内容修改
````php
'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'appid'),
'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'secret'), 
'token' => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'token'),
'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', 'aeskey')
````
  
接下来请先完成以下步骤：  
1、淘宝联盟开放平台 创建应用（应用类型可以选择网站） 获取AppKey [官网](https://aff-open.taobao.com)  
请自行在应用页面申请好"淘宝客【推广者】推广订单及数据查询"及"淘宝客【公用】物料信息查询"接口权限，需要申请理由的话，随便写个小作文就行了，秒批  
2、注册大淘客开放平台并授权淘宝联盟 获取Appkey  [官网](https://www.dataoke.com/kfpt/openapi.html)
3、如需淘宝私域管理功能（自动跟单），请在淘宝联盟申请好私域权限，申请邀请码。邀请码可通过调试 [官方接口](https://open.taobao.com/doc.htm?spm=a219a.15212433.0.0.4398669aXaoE2Y&docId=1&docType=15&apiName=taobao.tbk.sc.invitecode.get)
进行快速申请，调用接口请确保relation_app参数为common，code_type参数为3  
4、注册京东联盟并申请APIKey，授权绑定到大淘客  

修改/config/config.php配置
````php
'name' => "淘乐饭", //产品名称 会反应在用户交互等场景
'url' => "https://*.*.*", //站点url 如有饭粒网等网站可添加
'apiUrl' => "https://*.*.*", //APIurl 调用本程序使用的url
'dtkAppKey' => "****", //大淘客appKey 
'dtkAppSecret' => "****", //大淘客AppSecret
'aliAppKey' => "****", //淘宝联盟AppKey
'aliAppSecret' => "****", //淘宝联盟AppSecret
'pubpid' => 'mm_***_***_***', //公用PID 可与运营ID相同
'specialpid' => ' ******',//会员运营ID
'relationId'=>'****', //渠道ID 代理商使用 暂未开发
'inviter_code'=>'******' //会员私域邀请码
'default_rebate_ratio' => 65, //默认返利比例%,
'eleme_imgUrl' => "https://*.*.*/eleme.jpeg", //饿了么小程序码图片url
'eleme_newsUrl' => "https://xxx.xxx.xxx", //微信公众号文章URl
'unionId' => "******", //京东联盟ID
'jdApiKey' => "******" //京东联盟APIKey

````
设置好域名与SSL证书后，公众平台网址填写 你的域名/wechat  
例如：
````text
www.***.com/wechat
````
使用任意账号给公众号发送"创建菜单"即可创建自定义菜单（仅限服务号或认证订阅号）  
如需对菜单进行删改，请修改/app/Http/Controllers/WechatController.php中的$buttons变量  
未认证订阅号请在WechatController.php中找到case 'text'自行添加文本响应  
默认已经创建好了常用命令，可通过发送"帮助"给你的公众号查询使用方法。
````php
case 'text':
$content = $message['Content'];
if (stristr($content, '提现'){
    return "提现处理"
}
````
访问管理员注册页面创建超级管理员
````shell script
http://你的域名/adminReg
#该页面仅能创建一次超级管理员，如果后续忘记超级管理员账号密码
#删除站点目录下/storage/app/admin.lock文件后即可重新创建
````


设置定时器crontab用于查询并存储订单
````shell script
crontab -e
````
````PHP
* * * * * curl 你的域名/getOrderList
#每分钟查询一次订单信息并存入数据库
10 1 1,10,19,28 * * curl 你的域名/updateOrderAll
#每个月1、10、19、28日1点10分执行对上月及上上月订单的信息修改及结算等（仅联盟结算日期为上月的才会被结算）
````

至此，淘乐饭项目已经部署完成，可以正常使用了。如果在部署项目前已经关注过公众号，取关再次关注即可自动注册账号到数据库。





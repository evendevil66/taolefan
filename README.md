

## 关于项目
淘乐饭是一款基于PHP的微信公众号返利项目，支持认证或未认证的订阅号及服务号使用。  
**您当前查看的为2.X版本分支，如需未认证公众号使用，请切换至1.X分支查看。1.X于2.X并行开发，仅区分认证和未认证调用不同接口。**

本项目使用 [Laravel](https://laravel.com/) 作为主架构进行开发，公众号交互使用 [EasyWechat](https://www.easywechat.com) 实现，管理后台基于 [X-admin](http://x.xuebingsi.com)二次开发。
本项目使用GPLv3协议，允许复制、传播、修改及商业使用，禁止将修改后和衍生的代码做为闭源的商业软件发布和销售。

## 对接API

本项目主要使用 [淘宝联盟](https://pub.alimama.com/) 、 [大淘客](https://www.dataoke.com) 、[微信公众开放平台](https://mp.weixin.qq.com/) 等平台接口进行开发

## 项目体验
<h4>欢迎扫码体验本项目，您在本公众号的任何下单，都是对项目的支持</h4>

![Wechat](public/images/wechat.png)

## 主要配置文件
1、/config/config.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存站点/平台基本信息、淘宝联盟和大淘客APPKEY等信息  
2、/config/wechat.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存微信公众平台APPKEY相关信息  
2、.env &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存数据库相关信息

## 已实现功能
1、公众号转链（京东/淘宝），用户发送原始或其他淘客的淘口令到公众号，后台转链并计算出返利金额、获取优惠券信息返回给用户。  
2、注册功能，关注即可完成注册，绑定微信openID（微信唯一标识）。用户可以通过公众号菜单快速补全提现账户信息，还可以绑定淘宝账号，获取special_id（淘宝会员运营唯一标识）  
3、自动获取订单及绑定订单，自动储存所有订单到数据,~~带有会员运营id的订单会自动绑定openid(备用方案)~~ ,根据用户查商品情况自动跟踪订单，不同用户购买同一商品时暂停该商品自动跟单以免跟单错误  
4、手动发送订单号绑定订单  
5、用户查询自己的订单信息及提现  
6、后台管理面板  
7、用户查询订单信息时自动刷新用户近一个月订单状态，每月自动刷新上两月订单状态，并对上月确认收货的订单进行结算  
8、自动跟单、提现、邀请好友等自动消息通知

## TODO
更多功能仍在逐渐开发中，也可以自行去开发相关功能，大家的Star是我持续开发的动力

## Update
注意：  
*标更新表示数据库有轻微变动，更新前请备份数据并使用最新sql文件重新构建结构，再导入数据使用。  
从1.x版本更新为2.x 的用户，请务必更新数据库结果，并重新过一遍文档，将未处理的内容重新操作，以免出现异常  
更新后请在网页根目录执行以下命令清空缓存，以免因缓存导致部分业务无法访问
````shell script
php artisan cache:clear
php artisan route:cache
````

2022.4.17 v2.1.9  
修复了一些不影响使用的bug  
邀请二维码支持生成海报，请自行设计好海报后，预留二维码位置，配置config.php即可  

*2022.4.14 v2.1.8  
基于1.1.8版本更新，调用了部分高级接口  
支持邀请好友返利活动，可在config中设置相关信息  
部分页面转为菜单直接访问，网页授权方式获得openid  
自动跟单，提现反馈等均更换为模板信息发送  
注意：请在更新此版本后重新发送“创建菜单”指令  
数据库数据恢复完成后请执行以下SQL语句，以便对邀请功能的数据进行初始化（请务必操作）
```mysql
UPDATE users SET invite_id = NULL WHERE invite_id = '0'
```

## 部署方法
环境要求：PHP > 7 && PHP < 8  ｜ MySQL/MariaDB ｜ Redis  
微信公众号：已认证服务号(订阅号、未认证请使用1.x分支)  

下载或clone项目代码到所需环境  
````PHP
#国外环境
git clone -b master https://github.com/evendevil66/taolefan.git
#国内环境
git clone -b master https://gitee.com/cdj8/taolefan.git
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
修改.env中的数据库配置及Redis配置并导入项目根目录下的 taolefan.sql 到数据库  
````text
DB_CONNECTION=mysql  #默认使用mysql请勿修改 可支持MariaDB
DB_HOST=127.0.0.1  #数据库连接地址
DB_PORT=3306  #数据库连接端口
DB_DATABASE=taolefan #数据库名
DB_USERNAME=root  #数据库用户名
DB_PASSWORD=  #数据库密码

REDIS_HOST=127.0.0.1  #Redis连接地址
REDIS_PASSWORD=null #Redis密码 未设置默认为null
REDIS_PORT=6379 #Redis端口
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
请自行在应用页面申请好"~~淘宝客【推广者】推广订单及数据查询"及"淘宝客【公用】物料信息查询~~"(该接口已更换为大淘客接口处理)接口权限，需要申请理由的话，随便写个小作文就行了，秒批  
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
'eleme_url' => "taoke/pages/shopping-guide/index?scene=******",//饿了么小程序路径(后面会有获取方法)
'unionId' => "******", //京东联盟ID
'jdApiKey' => "******", //京东联盟APIKey
'contactType' => 1, //联系客服类型，为0返回微信号，为1返回二维码图片
'contactId' => "", //客服微信号
'contactMediaId' => "", //客服微信二维码图片MediaID（获取方式见Readme文档介绍）
'invite'=>1, //是否开启邀请 开启填写1 关闭填写0
'invite_ratio'=>10, //邀请返利比例%
'invite_rewards'=>1, //邀请奖励金额
'template_id'=>'******', //模板消息ID(后面会有获取方法)
'withdraw_template_id'=>'******'//提现模板消息ID(后面会有获取方法)
````
<span id="mediaId"></span>  
--mediaId获取方法  
使用[微信公众平台接口调试工具](https://mp.weixin.qq.com/debug)  
````text
首先调用获取access_token接口  
然后使用取得的token调用多媒体文件上传接口  
将获得的MediaId填写到config文件即可
````

设置好域名与SSL证书后，公众平台网址填写 你的域名/wechat  
并注意在微信公众号功能设置中设置好业务域名和授权域名（无需添加/wechat）
例如：
````text
www.***.com/wechat
````
使用任意账号给公众号发送"创建菜单"即可创建自定义菜单  
如需对菜单进行删改，请修改/app/Http/Controllers/WechatController.php中的$buttons变量

````
访问管理员注册页面创建超级管理员
````shell script
http://你的域名/adminReg
#该页面仅能创建一次超级管理员，如果后续忘记超级管理员账号密码
#删除站点目录下/storage/app/admin.lock文件后即可重新创建
````

登陆管理后台后访问以下地址获取template_id
````shell script
http://你的域名/setIndustry
#请确保访问该页面前，公众号已配置完成并已认证
#访问提示设置成功后，前往公众号后台->广告与服务->模板消息，获得创建的模板id，并分别填入config.php中
````

如需开通饿了么小程序返利，请在小程序中关联饿了么（APPID：wxece3a9a4c82f58c9）  
通过淘宝联盟APP-吃喝玩乐-饿了么微信小程序专属-分享小程序，获取专属路径，并配置到config.php中

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





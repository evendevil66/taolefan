

## 关于项目

本项目使用 [Laravel](https://laravel.com/) 作为主架构进行开发，公众号交互使用 [EasyWechat](https://www.easywechat.com) 实现。
本项目严格遵循GPL协议，允许复制、传播、修改，根据GPL要求，禁止将修改后和衍生的代码做为闭源的商业软件发布和销售。

## 对接API

本项目主要使用 [淘宝联盟](https://pub.alimama.com/) 、 [大淘客](https://www.dataoke.com) 、[微信公众开放平台](https://mp.weixin.qq.com/) 等平台接口进行开发

## 主要配置文件
1、/config/config.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存站点/平台基本信息、淘宝联盟和大淘客APPKEY等信息  
2、/config/wechat.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存微信公众平台APPKEY相关信息  
2、.env &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件保存数据库相关信息

## 已实现功能
1、公众号转链，用户发送原始或其他淘客的淘口令到公众号，后台转链并计算出返利金额、获取优惠券信息返回给用户。  
2、注册功能，关注即可完成注册，绑定微信openID（微信唯一标识）。用户可以通过公众号菜单快速补全提现账户信息，还可以绑定淘宝账号，获取special_id（淘宝会员运营唯一标识）

## 部署方法
环境要求：PHP > 7 && PHP < 8  ｜ MySQL  

下载或clone项目代码到所需环境  
````PHP
git clone https://github.com/evendevil66/taolefan.git
````
执行Composer命令安装依赖包及自动加载  
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

修改/config/wechat.php配置 根据微信公众平台内容修改
````php
'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'appid'),
'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'secret'), 
'token' => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'token'),
'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', 'aeskey')
````
  
接下来请先完成以下步骤：  
1、淘宝联盟开放平台创建应用 获取AppKey  
2、注册大淘客开放平台并授权淘宝联盟 获取Appkey  
3、如需淘宝私域管理功能（自动跟单），请在淘宝联盟申请好私域权限，申请邀请码。邀请码可通过调试 [官方接口](https://open.taobao.com/doc.htm?spm=a219a.15212433.0.0.4398669aXaoE2Y&docId=1&docType=15&apiName=taobao.tbk.sc.invitecode.get)
进行快速申请，调用接口请确保relation_app参数为common，code_type参数为3  

修改/config/config.php配置
````php
'name' => "淘乐饭", //产品名称 会反应在用户交互等场景
'url' => "https://fanli.mttgo.com", //站点url 如有饭粒网等网站可添加
'apiUrl' => "https://wechat.mttgo.com", //APIurl 调用本程序使用的url
'dtkAppKey' => "****", //大淘客appKey 使用大淘客接口快速解析商品信息
'dtkAppSecret' => "****", //大淘客AppSecret
'aliAppKey' => "****", //淘宝联盟AppKey
'aliAppSecret' => "****", //淘宝联盟AppSecret
'pubpid' => 'mm_***_***_***', //公用PID 未绑定淘宝账号的默认使用
'relationId'=>'****', //渠道ID 代理商使用 暂未开发
'inviter_code'=>'******' //会员私域邀请码
''
````
设置好域名与SSL证书后，公众平台网址填写 你的域名/wechat  
例如：
````text
www.***.com/wechat
````
使用任意账号给公众号发送"创建菜单"即可创建自定义菜单（仅限服务号或认证订阅号）  
如需对菜单进行删改，请修改/app/Http/Controllers/WechatController.php中的$buttons变量  
未认证订阅号请在WechatController.php中找到case 'text'自行添加文本响应  
添加方法如下
````php
case 'text':
$content = $message['Content'];
if (stristr($content, '提现'){
    return "提现处理"
}
````
具体处理代码可以在WechatController.php找到 switch ($message['EventKey'])  自行复制  

设置定时器crontab用于查询并存储订单
````PHP
0 * * * * curl 你的域名/wechat/getOrderList?promotion=0
#每小时执行一次，promotion为0表示非大促状态，程序默认查询最近80分钟订单信息
*/15 * * * * curl 你的域名/wechat/getOrderList?promotion=0
#每15分钟执行一次，promotion为1表示目前为大促状态，程序默认查询最近20分钟订单信息
#以上命令默认设置第一条即可，大促前一天改为第二条，大促结束后改回第一条，以减少服务器压力。
````

至此，淘乐饭项目已经部署完成，可以正常使用了。如果在部署项目前已经关注过公众号，取关再次关注即可自动注册账号到数据库。





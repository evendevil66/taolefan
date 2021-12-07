

## 关于项目

本项目使用 [Laravel](https://laravel.com/) 作为主架构进行开发，公众号交互使用 [EasyWechat](https://www.easywechat.com) 实现。
本项目严格遵循GPL协议，允许复制、传播、修改，根据GPL要求，禁止将修改后和衍生的代码做为闭源的商业软件发布和销售。

## 对接API

本项目主要使用 [淘宝联盟](https://pub.alimama.com/) 、 [大淘客](https://www.dataoke.com) 、[微信公众开放平台](https://mp.weixin.qq.com/) 等平台接口进行开发

## 主要配置文件
1、/config/config.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件主要保存站点/平台基本信息、数据库信息、淘宝联盟和大淘客APPKEY等信息  
2、/config/wechat.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件主要微信公众平台APPKEY相关信息  

## 已实现功能
1、公众号转链，用户发送原始或其他淘客的淘口令到公众号，后台转链并计算出返利金额、获取优惠券信息返回给用户。  
2、注册功能，关注即可完成注册，绑定微信openID（微信唯一标识）。用户可以通过公众号菜单快速补全提现账户信息，还可以绑定淘宝账号，获取special_id（淘宝会员运营唯一标识）

## 部署方法
环境要求：PHP > 7 && PHP < 8  ｜ MySQL  

下载或clone项目代码到所需环境  
````shell script
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
修改.env中的数据库配置
````shell script
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
修改/config/config.php配置



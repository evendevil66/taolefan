

## 关于项目

本项目使用 [Laravel](https://laravel.com/) 作为主架构进行开发，公众号交互使用 [EasyWechat](https://www.easywechat.com) 实现。
本项目严格遵循GPL协议，允许复制、传播、修改，但禁止第三方在未经授权下将该项目二次开发并倒卖

## 对接API

本项目主要使用 [淘宝联盟](https://pub.alimama.com/) 、 [大淘客](https://www.dataoke.com) 、[微信公众开放平台](https://mp.weixin.qq.com/) 等平台接口进行开发

## 主要配置文件
1、/config/config.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件主要保存站点/平台基本信息、淘宝联盟和大淘客APPKEY等信息
2、/config/wechat.php &nbsp;&nbsp;&nbsp;&nbsp; #本配置文件主要微信公众平台APPKEY相关信息

## 已实现功能
1、公众号转链，用户发送原始或其他淘客的淘口令到公众号，后台转链并计算出返利金额、获取优惠券信息返回给用户。
2、注册功能，用户通过公众号注册，绑定微信openID（微信唯一标识）和淘宝special_id（淘宝会员运营唯一标识）。

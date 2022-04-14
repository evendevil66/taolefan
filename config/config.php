<?php

return [

    'name' => "猫乐饭", //产品名称 会反应在用户交互等场景
    'url' => "https://xxx.xxx.xxx", //站点url
    'apiUrl' => "https://xxx.xxx.xxx", //微信url
    'dtkAppKey' => "******", //大淘客appKey 使用大淘客接口快速解析商品信息
    'dtkAppSecret' => "******", //大淘客AppSecret
    'aliAppKey' => "******", //淘宝联盟AppKey
    'aliAppSecret' => "******", //淘宝联盟AppSecret
    'pubpid' => '******', //公用PID
    'specialpid' => ' ******',//会员运营专用ID
    'relationId'=>'******', //渠道ID
    'inviter_code'=>'******', //会员管理邀请码
    'default_rebate_ratio' => 65,//默认返利比例%,
    'eleme_url' => "taoke/pages/shopping-guide/index?scene=******",//饿了么小程序路径
    'unionId' => "******", //京东联盟ID
    'jdApiKey' => "******", //京东联盟APIKey
    'contactType' => 1, //联系客服类型，为0返回微信号，为1返回二维码图片
    'contactId' => "", //客服微信号
    'contactMediaId' => "",//客服微信二维码图片MediaID（获取方式见Readme文档介绍）
    'invite'=>1, //是否开启邀请 开启填写1 关闭填写0
    'invite_ratio'=>10, //邀请返利比例%
    'invite_rewards'=>1, //邀请奖励金额
    'template_id'=>'******', //订单模板消息ID
    'withdraw_template_id'=>'******',//提现模板消息ID
    'invite_template_id'=>'******',//邀请好友模板消息ID
];


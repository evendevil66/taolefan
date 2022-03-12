/*
 Navicat Premium Data Transfer

 Source Server         : taolefan
 Source Server Type    : MySQL
 Source Server Version : 80025
 Source Host           : rm-2zeez207xz02337q8oo.mysql.rds.aliyuncs.com:3306
 Source Schema         : taolefan

 Target Server Type    : MySQL
 Target Server Version : 80025
 File Encoding         : 65001

 Date: 13/03/2022 01:25:20
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增id 自增主键',
  `openid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '微信openid',
  `trade_parent_id` varchar(32) NOT NULL COMMENT '订单号',
  `item_title` varchar(32) NOT NULL COMMENT '商品名称',
  `tk_paid_time` datetime DEFAULT NULL COMMENT '付款时间',
  `tk_earning_time` datetime DEFAULT NULL COMMENT '结算时间',
  `tk_status` int NOT NULL COMMENT '订单状态 3：订单结算，12：订单付款， 13：订单失效，14：订单成功',
  `pay_price` decimal(32,2) DEFAULT NULL COMMENT '付款金额',
  `pub_share_pre_fee` decimal(32,2) DEFAULT NULL COMMENT '付款预估收入',
  `pub_share_fee` decimal(32,2) DEFAULT NULL COMMENT '结算预估收入',
  `tk_commission_pre_fee_for_media_platform` decimal(32,2) DEFAULT NULL COMMENT '预估内容专项服务费',
  `tk_commission_fee_for_media_platform` decimal(32,2) DEFAULT NULL COMMENT '结算内容专项服务费',
  `share_pre_fee` decimal(32,2) DEFAULT NULL COMMENT '预估专项服务费',
  `share_fee` decimal(32,2) DEFAULT NULL COMMENT '结算专项服务费',
  `rebate_pre_fee` decimal(32,2) DEFAULT NULL COMMENT '预估返利金额',
  `rebate_fee` decimal(32,2) DEFAULT NULL COMMENT '结算返利金额',
  `refund_tag` varchar(1) DEFAULT NULL COMMENT '维权状态',
  `rebate_status` tinyint(1) NOT NULL COMMENT '结算状态',
  `receive_status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='订单表 ';

-- ----------------------------
-- Table structure for percentage
-- ----------------------------
DROP TABLE IF EXISTS `percentage`;
CREATE TABLE `percentage` (
  `id` int NOT NULL COMMENT '自增id',
  `openid` varchar(32) NOT NULL COMMENT '订单用户openid',
  `up_openid` varchar(32) NOT NULL COMMENT '上级openid',
  `orderid` varchar(32) NOT NULL COMMENT '订单id',
  `status` varchar(1) NOT NULL COMMENT '结算状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='提成统计表 ';

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` varchar(32) NOT NULL COMMENT '微信openid',
  `nickname` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '昵称 首次提现绑定',
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '姓名 首次提现绑定',
  `alipay_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '支付宝账号 首次提现绑定',
  `invite_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '上级邀请人openid 无邀请人默认0',
  `rebate_ratio` decimal(4,2) NOT NULL DEFAULT '55.00' COMMENT '返现比例',
  `special_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '粉丝运营ID',
  `user_pid` varchar(32) DEFAULT NULL COMMENT '用户pid 暂时不使用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='用户表 ';

SET FOREIGN_KEY_CHECKS = 1;

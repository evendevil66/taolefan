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

 Date: 18/03/2022 17:50:02
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Table structure for balance_record
-- ----------------------------
DROP TABLE IF EXISTS `balance_record`;
CREATE TABLE `balance_record` (
  `id` int NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) NOT NULL,
  `event` varchar(255) NOT NULL COMMENT '事件',
  `change` decimal(10,2) NOT NULL COMMENT '变动',
  `createtime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增id 自增主键',
  `openid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '微信openid',
  `trade_parent_id` varchar(32) NOT NULL COMMENT '订单号',
  `item_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品名称',
  `tk_paid_time` datetime DEFAULT NULL COMMENT '付款时间',
  `tk_earning_time` datetime DEFAULT NULL COMMENT '结算时间',
  `tk_status` int NOT NULL COMMENT '订单状态 3：订单结算，12：订单付款， 13：订单失效，14：订单成功',
  `pay_price` decimal(32,2) DEFAULT NULL COMMENT '付款金额',
  `pub_share_pre_fee` decimal(32,2) DEFAULT NULL COMMENT '付款预估收入',
  `pub_share_fee` decimal(32,2) DEFAULT NULL COMMENT '结算预估收入',
  `tk_commission_pre_fee_for_media_platform` decimal(32,2) DEFAULT NULL COMMENT '预估内容专项服务费',
  `tk_commission_fee_for_media_platform` decimal(32,2) DEFAULT NULL COMMENT '结算内容专项服务费',
  `rebate_pre_fee` decimal(32,2) DEFAULT NULL COMMENT '预估返利金额',
  `rebate_fee` decimal(32,2) DEFAULT NULL COMMENT '结算返利金额',
  `refund_tag` varchar(1) DEFAULT NULL COMMENT '维权状态',
  `rebate_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '结算状态',
  `receive_status` tinyint(1) DEFAULT NULL,
  `special_id` varchar(32) DEFAULT NULL,
  `tlf_status` int NOT NULL DEFAULT '0' COMMENT '订单状态标示，0仅录入订单，1计入未结算，2计入已结算，-1退款扣除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8mb3 COMMENT='订单表 ';

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
-- Table structure for receive
-- ----------------------------
DROP TABLE IF EXISTS `receive`;
CREATE TABLE `receive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` int NOT NULL DEFAULT '0' COMMENT '0未处理，1已处理，-1拒绝',
  `receive_date` datetime NOT NULL COMMENT '提现时间',
  `process_time` datetime DEFAULT NULL COMMENT '处理时间',
  `reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '拒绝原因',
  `nickname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=utf8mb3;

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
  `rebate_ratio` decimal(10,2) NOT NULL DEFAULT '55.00' COMMENT '返现比例',
  `special_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '粉丝运营ID',
  `user_pid` varchar(32) DEFAULT NULL COMMENT '用户pid 暂时不使用',
  `unsettled_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '未结算余额',
  `available_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '可用余额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='用户表 ';

SET FOREIGN_KEY_CHECKS = 1;

/*
Navicat MySQL Data Transfer

Source Server         : 192.168.33.15
Source Server Version : 50632
Source Host           : 192.168.33.15:3306
Source Database       : onethink

Target Server Type    : MYSQL
Target Server Version : 50632
File Encoding         : 65001

Date: 2017-01-16 11:46:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for trade_customer
-- ----------------------------
DROP TABLE IF EXISTS `trade_customer`;
CREATE TABLE `trade_customer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '交易服务器返回的用户id',
  `user_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '客户类别（1=>''个人客户'',2=>''会员客户'',3=>''高级代理客户'',4=>''代理客户''）',
  `login_name` varchar(50) NOT NULL COMMENT '客户登录微前端所用帐号，存客户注册时手机号就可以',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `operator_number` int(11) NOT NULL DEFAULT '0' COMMENT '所属运营商',
  `agent_id` int(11) NOT NULL DEFAULT '0' COMMENT '代理ID',
  `agent_number` int(11) NOT NULL DEFAULT '0' COMMENT '代理商编号',
  `bind_agent_number` int(11) NOT NULL DEFAULT '0' COMMENT '绑定代理商编号(客户同时为代理商的情况)',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `id_card_number` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证号码',
  `gender` tinyint(4) NOT NULL DEFAULT '0' COMMENT '性别（1男2女）',
  `marriage` tinyint(4) NOT NULL DEFAULT '0' COMMENT '婚姻状况(1未婚2已婚)',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '账户余额',
  `is_balance` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1:未入金 2:已入金 ',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '帐户状态，1预批，2已启用，3已禁用，4已注销',
  `is_signed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否签约银行，1已签约，0未签约，默认0',
  `funds_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '资金状态，模型文件定义',
  `user_from` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用户来源，模型文件定义',
  `email` varchar(150) DEFAULT '' COMMENT '邮箱',
  `id_image1` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证正面图片',
  `id_image2` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证反面图片',
  `id_image3` varchar(50) NOT NULL DEFAULT '' COMMENT '手持身份证图片',
  `admin_id` tinyint(10) NOT NULL DEFAULT '0' COMMENT '后台操作人员id',
  `approve_admin_id` tinyint(10) NOT NULL DEFAULT '0' COMMENT '审核后台操作人员id',
  `address` varchar(50) NOT NULL DEFAULT '' COMMENT '联系地址',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '注册ip地址',
  `country` varchar(50) NOT NULL DEFAULT '' COMMENT '国家',
  `province` varchar(50) NOT NULL DEFAULT '' COMMENT '省份',
  `parent1` int(11) NOT NULL DEFAULT '0' COMMENT '直接介绍人',
  `parent2` int(11) NOT NULL DEFAULT '0' COMMENT '间接介绍人',
  `is_reward` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否生成奖励记录 1:未生成  2:已生成',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `agent_member_number` int(10) NOT NULL DEFAULT '0' COMMENT '所属会员编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8000001 DEFAULT CHARSET=utf8 COMMENT='客户表';

-- ----------------------------
-- Records of trade_customer
-- ----------------------------

-- ----------------------------
-- Table structure for trade_deals
-- ----------------------------
DROP TABLE IF EXISTS `trade_deals`;
CREATE TABLE `trade_deals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `deals_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '成交类型',
  `customer_id` int(11) NOT NULL DEFAULT '0' COMMENT '交易账号',
  `customer_name` varchar(300) NOT NULL DEFAULT '',
  `customer_mobile` varchar(32) NOT NULL DEFAULT '0' COMMENT '会员手机号码',
  `product_number` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编号',
  `short_name` varchar(30) NOT NULL DEFAULT '' COMMENT '产品简称',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '产品id',
  `trade_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '交易金额',
  `volume` int(11) NOT NULL DEFAULT '0' COMMENT '成交数量',
  `remark` varchar(300) NOT NULL DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='成交记录';

-- ----------------------------
-- Records of trade_deals
-- ----------------------------

-- ----------------------------
-- Table structure for trade_follow
-- ----------------------------
DROP TABLE IF EXISTS `trade_follow`;
CREATE TABLE `trade_follow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `follow_number` varchar(50) NOT NULL DEFAULT '' COMMENT '交易流水号',
  `customer_id` int(11) NOT NULL DEFAULT '0' COMMENT '客户uid',
  `customer_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '客户手机号',
  `follow_type` int(11) NOT NULL DEFAULT '1' COMMENT '流水类型',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '流水金额',
  `new_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动后金额',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `customer_name` varchar(64) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `bussiness_desciption` varchar(64) NOT NULL DEFAULT '' COMMENT '交易描述',
  `freeze_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结资金',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='资金流水表';

-- ----------------------------
-- Records of trade_follow
-- ----------------------------

-- ----------------------------
-- Table structure for trade_product_order
-- ----------------------------
DROP TABLE IF EXISTS `trade_product_order`;
CREATE TABLE `trade_product_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `operator_number` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0' COMMENT '交易账号',
  `agent_number` int(11) NOT NULL DEFAULT '0',
  `customer_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '客户类型',
  `customer_name` varchar(300) NOT NULL DEFAULT '',
  `member_agent_number` int(11) NOT NULL DEFAULT '0' COMMENT '会员账号',
  `id_tree` varchar(50) NOT NULL DEFAULT '',
  `customer_mobile` varchar(32) NOT NULL DEFAULT '0' COMMENT '会员手机号码',
  `product_number` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编号',
  `product_name` varchar(30) NOT NULL DEFAULT '' COMMENT '产品名称',
  `short_name` varchar(30) NOT NULL DEFAULT '' COMMENT '产品简称',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '产品id',
  `trade_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '交易金额',
  `volume` int(11) NOT NULL DEFAULT '0' COMMENT '成交数量',
  `remark` varchar(300) NOT NULL DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='产品认购记录';

-- ----------------------------
-- Records of trade_product_order
-- ----------------------------

-- ----------------------------
-- Table structure for trade_recharge_admin
-- ----------------------------
DROP TABLE IF EXISTS `trade_recharge_admin`;
CREATE TABLE `trade_recharge_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `follow_number` varchar(50) NOT NULL DEFAULT '' COMMENT '交易流水号',
  `external_id` varchar(50) NOT NULL DEFAULT '' COMMENT '外部流水号(订单号，用于查询该笔交易的唯一标识)',
  `customer_id` int(11) NOT NULL DEFAULT '0' COMMENT '客户编号',
  `customer_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '客户手机号',
  `customer_type` int(11) NOT NULL DEFAULT '1' COMMENT '客户类型',
  `agent_number` varchar(10) NOT NULL DEFAULT '' COMMENT '直属代理编号',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际金额',
  `poundage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '交易状态',
  `admin_id` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人IP',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `reject_reason` varchar(600) NOT NULL DEFAULT '' COMMENT '驳回理由',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `customer_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户姓名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='后台充值表';

-- ----------------------------
-- Records of trade_recharge_admin
-- ----------------------------

DROP TABLE IF EXISTS `trade_position`;
CREATE TABLE `trade_position` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '产品id',
  `product_number` varchar(50) NOT NULL DEFAULT '' COMMENT '产品id',
  `short_name` varchar(128) NOT NULL DEFAULT '' COMMENT '产品简称',
  `customer_id` int(11) NOT NULL DEFAULT '0' COMMENT '客户uid',
  `customer_name` varchar(64) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `customer_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '客户手机号',
  `agent_number` varchar(50) NOT NULL DEFAULT '' COMMENT '代理编号',
  `volume` int(11) NOT NULL DEFAULT '0' COMMENT '持仓数量',
  `can_sell` int(11) NOT NULL DEFAULT '0' COMMENT '可卖数量',
  `now_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '当前价格',
  `average_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '平均价格',
  `last_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '产品状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户持仓表';


DROP TABLE IF EXISTS `trade_deals`;
CREATE TABLE `trade_deals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `deals_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '成交类型',
  `customer_id` int(11) NOT NULL DEFAULT '0' COMMENT '交易账号',
  `customer_name` varchar(128) NOT NULL DEFAULT '',
  `customer_mobile` varchar(32) NOT NULL DEFAULT '0' COMMENT '会员手机号码',
  `product_number` varchar(30) NOT NULL DEFAULT '' COMMENT '产品编号',
  `short_name` varchar(30) NOT NULL DEFAULT '' COMMENT '产品简称',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '产品id',
  `trade_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '交易金额',
  `volume` int(11) NOT NULL DEFAULT '0' COMMENT '成交数量',
  `remark` varchar(300) NOT NULL DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `other_id` varchar(6000) NOT NULL DEFAULT '0' COMMENT '对方客户id',
  `other_mobile` varchar(6000) NOT NULL DEFAULT '' COMMENT '对方客户手机号码',
  `other_name` varchar(6000) NOT NULL DEFAULT '' COMMENT '对方客户姓名',
  `gid` varchar(3000) NOT NULL DEFAULT '' COMMENT '挂单gid',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='成交记录';


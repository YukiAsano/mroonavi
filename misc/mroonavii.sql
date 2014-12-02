/*
 Navicat Premium Data Transfer

 Source Server         : MySQL LocalDB
 Source Server Type    : MySQL
 Source Server Version : 50537
 Source Host           : 192.168.33.10
 Source Database       : mroonavi

 Target Server Type    : MySQL
 Target Server Version : 50537
 File Encoding         : utf-8

 Date: 12/02/2014 22:45:04 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `tbl_shop_mecab`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_shop_mecab`;
CREATE TABLE `tbl_shop_mecab` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '店舗ID',
  `gnavi_id` varchar(20) DEFAULT NULL COMMENT 'ぐるなびID',
  `update_date` datetime DEFAULT NULL COMMENT '更新日時',
  `name` varchar(500) DEFAULT NULL,
  `name_kana` varchar(500) DEFAULT NULL,
  `business_hour` varchar(300) DEFAULT NULL,
  `holiday` varchar(300) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `tel` varchar(100) DEFAULT NULL,
  `fax` varchar(100) DEFAULT NULL,
  `pr_short` varchar(500) DEFAULT NULL,
  `pr_long` varchar(1000) DEFAULT NULL,
  `access` varchar(700) DEFAULT NULL,
  `budget` varchar(10) DEFAULT NULL,
  `category` varchar(200) DEFAULT NULL,
  `category_name_l_1` varchar(300) DEFAULT NULL,
  `category_name_l_2` varchar(300) DEFAULT NULL,
  `category_name_l_3` varchar(300) DEFAULT NULL,
  `category_name_l_4` varchar(300) DEFAULT NULL,
  `category_name_l_5` varchar(300) DEFAULT NULL,
  `category_name_s_1` varchar(300) DEFAULT NULL,
  `category_name_s_2` varchar(300) DEFAULT NULL,
  `category_name_s_3` varchar(300) DEFAULT NULL,
  `category_name_s_4` varchar(300) DEFAULT NULL,
  `category_name_s_5` varchar(300) DEFAULT NULL,
  `mobile_site` tinyint(1) unsigned DEFAULT NULL,
  `mobile_coupon` tinyint(1) unsigned DEFAULT NULL,
  `pc_coupon` tinyint(1) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude_wgs84` varchar(20) DEFAULT NULL,
  `longitude_wgs84` varchar(20) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `prefname` varchar(100) DEFAULT NULL,
  `areaname_s` varchar(200) DEFAULT NULL,
  `url` varchar(400) DEFAULT NULL,
  `url_mobile` varchar(400) DEFAULT NULL,
  `thumbnail` varchar(400) DEFAULT NULL,
  `qrcode` varchar(400) DEFAULT NULL,
  `searchword` text CHARACTER SET utf8 COLLATE utf8_bin,
  `latlng` geometry DEFAULT NULL,
  `area_cd` varchar(10) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `searchword` (`searchword`) COMMENT 'parser = "TokenMecab"',
  FULLTEXT KEY `name` (`name`,`name_kana`)
) ENGINE=Mroonga AUTO_INCREMENT=641745 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;

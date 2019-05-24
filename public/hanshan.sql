/**

 */
ALTER TABLE ml_tbl_wallet_details ADD COLUMN `order_num` VARCHAR(50) DEFAULT NULL COMMENT '订单号';
ALTER TABLE ml_tbl_user ADD COLUMN `headimg` VARCHAR(200) DEFAULT NULL COMMENT '头像';



CREATE TABLE `ml_tbl_goods_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT NULL,
  `content` varchar(255) not null default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 comment '商品分享文案';

CREATE TABLE `ml_tbl_goods_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) not null DEFAULT '0',
  `url` char(200) not null default '',
  PRIMARY KEY (`id`),
  key `gid` (`gid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 comment '商品分享文案';



ALTER TABLE ml_tbl_goods ADD COLUMN `goods_sort` int(2) not null DEFAULT 50 COMMENT '商品排序';



CREATE TABLE `` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) not null DEFAULT '0',
  `url` char(200) not null default '',
  PRIMARY KEY (`id`),
  key `gid` (`gid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 comment '商品分享文案';

CREATE TABLE `ml_xm_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) not null DEFAULT 0 COMMENT '使用者id,没有的则为 0',
  `discount` decimal(2,1)  DEFAULT NULL COMMENT '折扣',
  `par_value` decimal(10,2) not null DEFAULT 0 COMMENT '抵用金（默认为0）',
  `coupon_type` int(11) DEFAULT NULL COMMENT '优惠券类型（折扣、抵用）',
  `last_time` datetime DEFAULT NULL COMMENT '使用期限',
  `coupon_name` varchar(255) DEFAULT NULL COMMENT '优惠券名称',
  `coupon_value` varchar(255) DEFAULT NULL COMMENT '优惠券内容',
  `use_type` tinyint(1) not null default 1 COMMENT '优惠券种类 1-全商品,4-旅游5-亲子7-家政',
  `use_status` tinyint(1) not null default 1 COMMENT '使用状态 1-激活/未使用 2-未激活/已使用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `xm_tbl_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pro_id` int(11) DEFAULT NULL,
  `discount` float DEFAULT NULL COMMENT '折扣',
  `par_value` float DEFAULT '0' COMMENT '抵用金（默认为0）',
  `coupon_type` int(11) DEFAULT NULL COMMENT '优惠券类型（折扣、抵用）',
  `last_time` datetime DEFAULT NULL COMMENT '使用期限',
  `user_id` int(11) DEFAULT NULL,
  `coupon_name` varchar(255) DEFAULT NULL COMMENT '优惠券名称',
  `coupon_value` varchar(255) DEFAULT NULL COMMENT '优惠券内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE xm_tbl_coupon ADD COLUMN `use_type` tinyint(1) not null DEFAULT 1 COMMENT '优惠券种类 1-全商品,4-旅游5-亲子7-家政';
ALTER TABLE xm_tbl_coupon ADD COLUMN `use_status` tinyint(1) not null DEFAULT 1 COMMENT '使用状态 1-激活/未使用 2-未激活/已使用';


ALTER TABLE ml_tbl_goods ADD COLUMN `is_fixtime` tinyint(1) not null DEFAULT 2 COMMENT '是否指定时间 1-指定 2-未指定';
ALTER TABLE ml_tbl_goods ADD COLUMN `is_realname` tinyint(1) not null DEFAULT 2 COMMENT '是否需要实名 1-需要实名 2-不需要';
ALTER TABLE ml_tbl_order ADD COLUMN `fixtime` datetime  DEFAULT null COMMENT '指定时间';
ALTER TABLE ml_tbl_order ADD COLUMN `realname` char(20)  DEFAULT null COMMENT '真实姓名';
ALTER TABLE ml_tbl_order ADD COLUMN `id_card` char(18)  DEFAULT null COMMENT '身份证号';


ALTER TABLE ml_tbl_goods ADD COLUMN `share_img` varchar(255)  DEFAULT null COMMENT '分享图片';


ALTER TABLE ml_tbl_user ADD unique(`wechat_open_id`)


ALTER TABLE ml_tbl_goods ADD COLUMN `must_img` varchar(255)  DEFAULT null COMMENT '必买商品图片';


ALTER TABLE ml_tbl_goods_class ADD COLUMN `class_img` varchar(255)  DEFAULT null COMMENT '分类图片图片';

ALTER TABLE ml_tbl_goods_class ADD COLUMN `as_name` varchar(20)  DEFAULT null COMMENT '分类别名';



CREATE TABLE `ml_tbl_user_pact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_pact` longtext COMMENT '用户协议',
  `pact_name`  varchar(50) default null comment '协议名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE ml_tbl_goods ADD COLUMN `buy_limit` tinyint(1) not null DEFAULT 0 COMMENT '0-没有限购 1-最低限购 2-最高限购';
ALTER TABLE ml_tbl_goods ADD COLUMN `buy_limit_num` tinyint(3) not null DEFAULT 0 COMMENT '限购数量';


CREATE TABLE `ml_tbl_withdraw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) not null default 0 COMMENT '用户id',
  `order_no` char(20) default null comment '订单号',
  `amount` decimal(10,2) not null default 0 COMMENT '提现金额',
  `ctime` int(10) not null default 0 COMMENT '订单创建时间',
  `pay_time` int(10) not null default 0 COMMENT '打款时间',
  `desc` varchar(100) default null comment '打款备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 comment '用户提现';



ALTER TABLE ml_tbl_goods ADD COLUMN `sconde_bouns` decimal(10,2) not null DEFAULT 0 COMMENT '二级返佣';
ALTER TABLE ml_tbl_goods ADD COLUMN `third_bouns` decimal(10,2) not null DEFAULT 0 COMMENT '三级返佣';


CREATE TABLE `ml_tbl_user_bankCard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) not null default 0 COMMENT '用户id',
  `name` char(20) default null comment '用户真实姓名',
  `card_id` char(20) not null default 0 COMMENT '银行卡号',
  `tel` char(20) not null default 0 COMMENT '手机号',
  `ctime` int(10) not null default 0 COMMENT '订单创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 comment '用户银行卡';



--  TODO 大更新
CREATE TABLE `ml_tbl_goods_format` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) not null default 0 COMMENT '商品id',
  `format_id` int(20) default null comment '规格id',
  `format_name` varchar(100)  default null COMMENT '规格名称',
  `format_price` decimal(10,2) not null default 0 COMMENT '商品规格价格',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '商品规格表';

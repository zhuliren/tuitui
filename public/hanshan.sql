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


CREATE TABLE `ml_tbl_user_bank_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) not null default 0 COMMENT '用户id',
  `name` char(20) default null comment '用户真实姓名',
  `card_id` char(20) not null default 0 COMMENT '银行卡号',
  `tel` char(20) not null default 0 COMMENT '手机号',
  `ctime` int(10) not null default 0 COMMENT '订单创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 comment '用户银行卡';



--  TODO 大更新



ALTER TABLE ml_tbl_user_bank_card ADD COLUMN `bank` char(10) not null DEFAULT '' COMMENT '银行卡所属银行';
ALTER TABLE ml_tbl_order ADD COLUMN `clerk_id` int(11) not null DEFAULT 0 COMMENT '核销员id';
ALTER TABLE ml_tbl_order ADD COLUMN `clerk_time` int(10) not null DEFAULT 0 COMMENT '核销时间';


----------------------------------------------------------------------------------------------|  可能会用到的
CREATE TABLE `ml_tbl_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) not null DEFAULT 0 COMMENT '使用者id,没有的则为 0',
  `discount` decimal(2,1)  DEFAULT NULL COMMENT '折扣',
  `par_value` decimal(10,2) not null DEFAULT 0 COMMENT '抵用金（默认为0）',
  `coupon_type` int(11) DEFAULT NULL COMMENT '优惠券类型（折扣、抵用）',
  `ex_time` timestamp DEFAULT NULL COMMENT '使用期限',
  `coupon_name` varchar(255) DEFAULT NULL COMMENT '优惠券名称',
  `coupon_value` varchar(255) DEFAULT NULL COMMENT '优惠券内容',
  `use_type` tinyint(1) not null default 1 COMMENT '优惠券种类 1-全商品,4-旅游5-亲子7-家政',
  `use_status` tinyint(1) not null default 1 COMMENT '使用状态 1-激活/未使用 2-未激活/已使用',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  PRIMARY KEY (`id`),
  key `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
----------------------------------------------------------------------------------------------|


ALTER TABLE xm_tbl_coupon ADD COLUMN `business_id` int(11) not null DEFAULT 0 COMMENT '商户id';




CREATE TABLE `ml_tbl_goods_two` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `head_img` varchar(255) DEFAULT NULL COMMENT '头像',
  `goods_region` varchar(255) DEFAULT NULL COMMENT '商品地区',
  `goods_format` varchar(255) DEFAULT NULL COMMENT '商品规格',
  `goods_details` longtext COMMENT '商品详情',
  `creat_time` TIMESTAMP NOT NULL  DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `ex_time` datetime DEFAULT NULL COMMENT '到期时间',
  `is_online` TINYINT(1) unsigned DEFAULT '0' COMMENT '是否上架默认为0不上架 1上架',
  `goods_class` int(11) DEFAULT NULL COMMENT '商品分类',
  `type` int(11) DEFAULT NULL COMMENT '核销类型1、配送 2、扫码核销',
  `business_id` int(11) DEFAULT '0' COMMENT '商户id',
  `goods_sort` int(2) NOT NULL DEFAULT '50' COMMENT '商品排序',
  `third_id` varchar(255) DEFAULT NULL COMMENT '第三方系统编号',
  `share_img` varchar(255) DEFAULT NULL COMMENT '分享图片',
  `is_fixtime` TINYINT(1) unsigned NOT NULL DEFAULT '2' COMMENT '是否指定时间 1-指定 2-未指定',
  `is_realname` TINYINT(1) unsigned NOT NULL DEFAULT '2' COMMENT '是否需要实名 1-需要实名 2-不需要',
  `buy_limit` tinyint(1) not null DEFAULT 0 COMMENT '0-没有限购 1-最低限购 2-最高限购',
  `buy_limit_num` tinyint(3) not null DEFAULT 0 COMMENT '限购数量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4;

CREATE TABLE `ml_tbl_goods_format`(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) not null default 0 COMMENT '商品id',
  `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `goods_stock` int(11) DEFAULT NULL COMMENT '商品库存',
  `goods_price` decimal(10,2) DEFAULT '1' COMMENT '商品售格',
  `goods_original_price` decimal(10,2) DEFAULT '1' COMMENT '商品原价',
  `creat_time` TIMESTAMP NOT NULL  DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `ex_time` datetime DEFAULT NULL COMMENT '到期时间',
  `goods_sell_out` int(11) DEFAULT '0' COMMENT '售出数量',
  `third_number` varchar(255) DEFAULT NULL COMMENT '第三方系统子编号',
  `third_znumber` varchar(255) DEFAULT NULL COMMENT '规格编号',
  `is_online` TINYINT(1) unsigned DEFAULT '0' COMMENT '是否上架默认为0不上架 1上架',
  `first_bonus` DECIMAL(10,2) DEFAULT '0' COMMENT '分销价格（默认为0不参与分销）',
  `second_bonus` DECIMAL(10,2) DEFAULT '0' COMMENT '分销价格（默认为0不参与分销）',
  `third_bonus` DECIMAL(10,2) DEFAULT '0' COMMENT '分销价格（默认为0不参与分销）',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`) USING BTREE
)ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4;


CREATE TABLE `ml_tbl_order_realname` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) not null default 0 COMMENT '订单id',
  `realname` char(20) default null comment '用户真实姓名',
  `id_card` char(20) not null default 0 COMMENT '身份证号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '订单真实姓名信息表';


  ALTER TABLE ml_tbl_goods_two ADD COLUMN `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名';
  ALTER TABLE ml_tbl_goods_two ADD COLUMN `bonus_interval` char(50) DEFAULT NULL COMMENT '返佣区间';
  ALTER TABLE ml_tbl_goods_two ADD COLUMN `price_interval` char(50) DEFAULT NULL COMMENT '价格区间';
  ALTER TABLE ml_tbl_goods_two ADD COLUMN `goods_summary` varchar(255) DEFAULT NULL COMMENT '商品简介';
  ALTER TABLE ml_tbl_goods_two ADD COLUMN `goods_original_price` decimal(10,2) DEFAULT NULL COMMENT '商品原价';
  ALTER TABLE ml_tbl_goods_two ADD COLUMN `goods_sell_out` int(11) DEFAULT 0 COMMENT '商品售出数量';

  ALTER TABLE ml_tbl_order_details ADD COLUMN `format_id` int(11) NOT NULL DEFAULT 0 COMMENT '规格id';
  ALTER TABLE ml_tbl_order ADD COLUMN `format_id` int(11) NOT NULL DEFAULT 0 COMMENT '规格id';



  ALTER TABLE ml_tbl_business ADD COLUMN `pid` int(11) DEFAULT 0 COMMENT '父级id，0为商户'
  ALTER TABLE ml_tbl_business ADD COLUMN `business_hours` varchar(50)  DEFAULT '09:00-22:00' COMMENT '营业时间'


CREATE TABLE `ml_tbl_goods_tag`(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) not null default 0 COMMENT '商品id',
  `tag` varchar(50) default null COMMENT '标签信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '商品标签';



CREATE TABLE `ml_tbl_goods_format` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `goods_stock` int(11) DEFAULT NULL COMMENT '商品库存',
  `goods_price` decimal(10,2) DEFAULT '1.00' COMMENT '商品售格',
  `goods_original_price` decimal(10,2) DEFAULT '1.00' COMMENT '商品原价',
  `creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `ex_time` datetime DEFAULT NULL COMMENT '到期时间',
  `goods_sell_out` int(11) DEFAULT '0' COMMENT '售出数量',
  `third_number` varchar(255) DEFAULT NULL COMMENT '第三方系统子编号',
  `third_znumber` varchar(255) DEFAULT NULL COMMENT '规格编号',
  `is_online` tinyint(1) unsigned DEFAULT '0' COMMENT '是否上架默认为0不上架 1上架',
  `first_bonus` decimal(10,2) DEFAULT '0.00' COMMENT '分销价格（默认为0不参与分销）',
  `second_bonus` decimal(10,2) DEFAULT '0.00' COMMENT '分销价格（默认为0不参与分销）',
  `third_bonus` decimal(10,2) DEFAULT '0.00' COMMENT '分销价格（默认为0不参与分销）',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='商品规格表';


ALTER TABLE ml_tbl_order ADD COLUMN `order_state` varchar(20) not null DEFAULT 0 COMMENT '第三方状态';


ALTER TABLE ml_tbl_user ADD COLUMN `activity_mark` tinyint(1) not null DEFAULT 0 COMMENT '是否参加618 活动标识';
ALTER TABLE xm_tbl_coupon ADD COLUMN `coup_type` tinyint(1) not null DEFAULT 0 COMMENT '0-不是活动优惠券,1-618活动优惠券';


CREATE TABLE `ml_tbl_coupon_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '用户id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='优惠券临时表';

ALTER TABLE ml_tbl_user ADD COLUMN `salsman_type` tinyint(1) not null DEFAULT 0 COMMENT '快到期弹框展示 0-显示 1-不显示';


CREATE TABLE `ml_tbl_distributor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户id',
  `u_name` VARCHAR(50) DEFAULT NULL COMMENT '用户名',
  `tel` char(15)  DEFAULT null COMMENT '用户手机',
  `price` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '支付金额',
  `c_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `pay_time` int(10) NOT NULL DEFAULT 0 COMMENT '支付时间',
  `order_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '订单状态 1-待支付 2-支付完成',
  `order_num` char(25) NOT NULL DEFAULT 0 COMMENT '订单编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='分销员续费订单表';

ALTER TABLE ml_tbl_Distributor ADD COLUMN `order_num` char(25) not null DEFAULT 0 COMMENT '订单编号';


CREATE TABLE `ml_tbl_template_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL DEFAULT 0 ,
  `order_id` VARCHAR(50) DEFAULT NULL COMMENT '订单id',
  `c_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单模板消息';



ALTER TABLE tb_article MODIFY COLUMN NAME VARCHAR(50);
ALTER TABLE ml_tbl_wallet MODIFY balance DECIMAL(10,2)  NOT NULL DEFAULT 0;


ALTER TABLE ml_tbl_withdraw ADD COLUMN `code` char(8) NOT NULL DEFAULT 0 COMMENT '验证码';
ALTER TABLE ml_tbl_withdraw ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0-未打款,1-打款 2-拒绝';



CREATE TABLE `ml_tbl_event_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `goods_summary` varchar(255) DEFAULT NULL COMMENT '商品简介',
  `goods_stock` int(11) DEFAULT NULL COMMENT '商品库存',
  `goods_details` longtext COMMENT '商品详情',
  `creat_time` datetime DEFAULT NULL COMMENT '创建时间',
  `ex_time` datetime DEFAULT NULL COMMENT '到期时间',
  `is_online` TINYINT(1) DEFAULT '0' COMMENT '是否上架默认为0不上架 1上架',
  `goods_price` DECIMAL(10,2) DEFAULT '1' COMMENT '商品售格',
  `goods_original_price` float DEFAULT '1' COMMENT '商品原价',
  `share_img` varchar(255) DEFAULT NULL COMMENT '分享图片',
  `buy_limit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-没有限购 1-最低限购 2-最高限购',
  `buy_limit_num` tinyint(3) NOT NULL DEFAULT '0' COMMENT '限购数量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `ml_tbl_event_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL COMMENT '订单编号',
  `order_type` TINYINT(1) DEFAULT NULL COMMENT '订单状态1.待支付2.待发货3.待收货4.已完成5.已取消',
  `user_id` int(11) DEFAULT NULL,
  `coupon_id` int(11) DEFAULT NULL COMMENT '优惠券id',
  `goods_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品id',
  `goods_num` int(11) NOT NULL DEFAULT 0 COMMENT '商品数量',
  `express_no` char(30) NOT NULL DEFAULT 0 COMMENT '运单号',
  `freight` float DEFAULT NULL COMMENT '运费',
  `goods_price` float DEFAULT NULL COMMENT '商品价格',
  `pay_price` float DEFAULT NULL COMMENT '付款金额',
  `creat_time` datetime DEFAULT NULL COMMENT '创建时间',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `user_name` varchar(255) DEFAULT NULL COMMENT '联系人姓名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `ml_tbl_event_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(30) DEFAULT NULL COMMENT '用户名',
  `tel` char(14) NOT NULL DEFAULT 0 COMMENT '手机号',
  `address` varchar(255) NOT NULL DEFAULT 0 COMMENT '收货地址',
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '团长id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `ml_tbl_event_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;




ALTER TABLE ml_tbl_goods_format ADD COLUMN `business_price` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '商户结算价格';
ALTER TABLE ml_tbl_event_member ADD COLUMN `but_num` int(11) NOT NULL DEFAULT 0 COMMENT '购买分数';

ALTER TABLE ml_tbl_event_member ADD COLUMN `head_img` int(11) NOT NULL DEFAULT 0 COMMENT '头像';

ALTER TABLE ml_tbl_event_goods ADD COLUMN `head_img` varchar(255) NOT NULL DEFAULT 0 COMMENT '头像';
ALTER TABLE ml_tbl_event_goods ADD COLUMN `goods_bonus` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '返佣';

ALTER TABLE ml_tbl_event_goods ADD COLUMN `format` varchar(10) NOT NULL DEFAULT 0 COMMENT '规格';
ALTER TABLE ml_tbl_event_goods ADD COLUMN `content` varchar(255) NOT NULL DEFAULT 0 COMMENT '分享文本';
ALTER TABLE ml_tbl_event_order ADD COLUMN `arrival_time` varchar(255) NOT NULL DEFAULT '2019-07-01' COMMENT '到货时间';
ALTER TABLE ml_tbl_event_member ADD COLUMN `lng` char(50) NOT NULL DEFAULT '0.0' COMMENT '经度';
ALTER TABLE ml_tbl_event_member ADD COLUMN `lat` char(50) NOT NULL DEFAULT '0.0' COMMENT '纬度';


ALTER TABLE ml_tbl_user ADD COLUMN `from_id` char(100) NOT NULL DEFAULT 0 COMMENT '小程序formid';


ALTER TABLE ml_tbl_event_order ADD COLUMN `lead_id` int(11) NOT NULL DEFAULT 0 COMMENT '团长id';
ALTER TABLE ml_tbl_event_order ADD COLUMN `user_name` varchar(100)  DEFAULT null  COMMENT '用户名';
ALTER TABLE ml_tbl_event_order ADD COLUMN `tel` char(11) NOT NULL DEFAULT 0 COMMENT '电话';

CREATE TABLE `ml_tbl_event_rcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE `ml_tbl_event_lead` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(30) DEFAULT NULL COMMENT '用户名',
  `tel` char(14) NOT NULL DEFAULT 0 COMMENT '手机号',
  `address` varchar(255) NOT NULL DEFAULT 0 COMMENT '收货地址',
  `event_id` int(11) NOT NULL DEFAULT 1 COMMENT '活动届数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE `ml_tbl_event_push_king` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` int(11) DEFAULT NULL,
  `start_time` varchar(30) DEFAULT NULL COMMENT '用户名',
  `end_time` char(14) NOT NULL DEFAULT 0 COMMENT '手机号',
  `event_img` varchar(255) NOT NULL DEFAULT 0 COMMENT '收货地址',
  `event_id` int(11) NOT NULL DEFAULT 1 COMMENT '活动届数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


ALTER TABLE ml_tbl_business ADD COLUMN `device_num` char(100) NOT NULL DEFAULT 0 COMMENT '设备号';




CREATE TABLE `ml_tbl_open_interface` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL COMMENT '用户名',
  `secret` varchar(30) DEFAULT NULL COMMENT '密码',
  `ctime` int(10) default 0 COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
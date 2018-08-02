/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.5.53 : Database - cltphp_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`cltphp_db` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `cltphp_db`;

/*Table structure for table `clt_access` */

DROP TABLE IF EXISTS `clt_access`;

CREATE TABLE `clt_access` (
  `role_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `node_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `model` varchar(50) DEFAULT '',
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `clt_access` */

LOCK TABLES `clt_access` WRITE;

UNLOCK TABLES;

/*Table structure for table `clt_ad` */

DROP TABLE IF EXISTS `clt_ad`;

CREATE TABLE `clt_ad` (
  `ad_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '广告名称',
  `type_id` tinyint(5) NOT NULL COMMENT '所属位置',
  `pic` varchar(200) NOT NULL DEFAULT '' COMMENT '广告图片URL',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '广告链接',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `sort` int(11) NOT NULL COMMENT '排序',
  `open` tinyint(2) NOT NULL COMMENT '1=审核  0=未审核',
  PRIMARY KEY (`ad_id`),
  KEY `plug_ad_adtypeid` (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='广告';

/*Data for the table `clt_ad` */

LOCK TABLES `clt_ad` WRITE;

insert  into `clt_ad`(`ad_id`,`name`,`type_id`,`pic`,`url`,`addtime`,`sort`,`open`) values (15,'如何优雅的装饰客厅',1,'/uploads/20170223/6f728d80d0b9f26bfefafe49d5d99c2b.jpg','',1480909037,1,1),(16,'内页横幅',5,'/uploads/20161212/9000ba8250cccd2be494d4241c56afe2.jpg','',1480909132,50,1),(17,'如何优雅的装饰客厅01',1,'http://bpic.588ku.com/back_pic/03/71/89/8657b8591f90b53.jpg','',1481788850,2,1),(18,'如何优雅的装饰客厅02',1,'/uploads/20161215/08d9c6acbdc8035815acd3bb9acb3b49.jpg','',1481788869,3,1),(19,'如何优雅的装饰客厅03',1,'/uploads/20161215/08d9c6acbdc8035815acd3bb9acb3b49.jpg','',1481789413,50,1);

UNLOCK TABLES;

/*Table structure for table `clt_ad_type` */

DROP TABLE IF EXISTS `clt_ad_type`;

CREATE TABLE `clt_ad_type` (
  `type_id` tinyint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '广告位名称',
  `sort` int(11) NOT NULL COMMENT '广告位排序',
  PRIMARY KEY (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='广告类型';

/*Data for the table `clt_ad_type` */

LOCK TABLES `clt_ad_type` WRITE;

insert  into `clt_ad_type`(`type_id`,`name`,`sort`) values (1,'顶部轮播',1),(5,'内页横幅',2);

UNLOCK TABLES;

/*Table structure for table `clt_admin` */

DROP TABLE IF EXISTS `clt_admin`;

CREATE TABLE `clt_admin` (
  `admin_id` tinyint(4) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(20) NOT NULL COMMENT '管理员用户名',
  `pwd` varchar(70) NOT NULL COMMENT '管理员密码',
  `group_id` mediumint(8) DEFAULT NULL COMMENT '分组ID',
  `email` varchar(30) DEFAULT NULL COMMENT '邮箱',
  `realname` varchar(10) DEFAULT NULL COMMENT '真实姓名',
  `tel` varchar(30) DEFAULT NULL COMMENT '电话号码',
  `hits` int(8) NOT NULL DEFAULT '1' COMMENT '登陆次数',
  `ip` varchar(20) DEFAULT NULL COMMENT 'IP地址',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `mdemail` varchar(50) DEFAULT '0' COMMENT '传递修改密码参数加密',
  `is_open` tinyint(2) DEFAULT '0' COMMENT '审核状态',
  PRIMARY KEY (`admin_id`),
  KEY `admin_username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='管理员';

/*Data for the table `clt_admin` */

LOCK TABLES `clt_admin` WRITE;

insert  into `clt_admin`(`admin_id`,`username`,`pwd`,`group_id`,`email`,`realname`,`tel`,`hits`,`ip`,`add_time`,`mdemail`,`is_open`) values (1,'admin','0192023a7bbd73250516f069df18b500',1,'1109305987@qq.com','','18792402229',57,'127.0.0.1',1482132862,'0',1);

UNLOCK TABLES;

/*Table structure for table `clt_article` */

DROP TABLE IF EXISTS `clt_article`;

CREATE TABLE `clt_article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(40) NOT NULL DEFAULT '',
  `title` varchar(80) NOT NULL DEFAULT '',
  `title_style` varchar(40) NOT NULL DEFAULT '',
  `keywords` varchar(120) NOT NULL DEFAULT '',
  `copyfrom` varchar(40) NOT NULL DEFAULT '',
  `fromlink` varchar(80) NOT NULL DEFAULT '0',
  `description` mediumtext NOT NULL,
  `content` text NOT NULL,
  `template` varchar(30) NOT NULL DEFAULT '',
  `thumb` varchar(100) NOT NULL DEFAULT '',
  `posid` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `recommend` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `readgroup` varchar(255) NOT NULL DEFAULT '',
  `readpoint` int(10) unsigned NOT NULL DEFAULT '0',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(50) NOT NULL DEFAULT '',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `status` (`id`,`status`,`listorder`),
  KEY `catid` (`id`,`catid`,`status`),
  KEY `listorder` (`id`,`catid`,`status`,`listorder`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='文章模型表';

/*Data for the table `clt_article` */

LOCK TABLES `clt_article` WRITE;

insert  into `clt_article`(`id`,`catid`,`userid`,`username`,`title`,`title_style`,`keywords`,`copyfrom`,`fromlink`,`description`,`content`,`template`,`thumb`,`posid`,`status`,`recommend`,`readgroup`,`readpoint`,`listorder`,`url`,`hits`,`createtime`,`updatetime`) values (1,35,1,'admin','导致网站运营失败的主观原因','color:#555','导致网站运营失败的主观原因','','','很多站长在建设和运营网站的时候，大多带来自己主观的意识来进行操作，比如在网站建设上面，往往会觉得色彩绚丽，网站功能复杂，声效视频能够不断的冲击用户的眼球等等.很多站长在建设和运营网站的时候，大多带来自己主观的意识来进行操作，比如在网站建设上面，往往会觉得色彩绚丽，网站功能复杂，声效视频能够不断的冲击用户的眼球等等.','<p style=\"text-indent: 2em;\">很多站长在建设和运营网站的时候，大多带来自己主观的意识来进行操作，比如在网站建设上面，往往会觉得色彩绚丽，网站功能复杂，声效视频能够不断的冲击用户的眼球等等.很多站长在建设和运营网站的时候，大多带来自己主观的意识来进行操作，比如在网站建设上面，往往会觉得色彩绚丽，网站功能复杂，声效视频能够不断的冲击用户的眼球等等.</p>','0','/uploads/20170223/6f594f6bb2ab40ce8efb01f9cf64b845.jpg',3,1,1,'1,2,3,4',0,0,'',30,1481512316,1487826571),(2,35,1,'admin','整合网络营销的着眼点','color:#555','整合网络营销的着眼点','11111','11111','整合网络营销的着眼点','<p>整合网络营销的着眼点</p>','article-show','',0,1,1,'',0,0,'',14,1481619666,1481700537),(3,33,1,'admin','企业应该理性看待网站建设及其效果','color:#555','企业应该理性看待网站建设及其效果','','','企业应该理性看待网站建设及其效果','<p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">开展网站建设，投放网络广告信息，抢占网络客户资源，这是互联网社会时期各行业各企业进行得最多行为，也是目前市场竞价最重要的特征之一，那么企业应该如何看待这一切行为结果呢？<br/></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">是不是为了效果而不断的给员工压力呢？该不该因为竞争对手的强大优势而盲目的疯狂投资呢？绝对不行，面对网站建设与网络营销，企业一定要保持一颗平常的心态理性对待，找到问题所在，分析时局现状，稳速前进，循序渐进。 理性的前提是清楚自己的位置，看清自己与对手的差别在哪里。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">同样是选择网站建设方案，如果我们的竞争对手选择的是一个有利于网站优化的静态操作页面以及客户体验良好的网站设计表现形式，而我们选择的则是一个凑合的网站建设方案，试想，谁胜谁负？ 选好起点，重在执行。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">有了好的网站建设方案，有了高的起点并不意味着我们就一定能在网络营销的战役中胜出，只有良好的资源准备过程而没有切实的执行操作同样不会取得预想的成效，有些企业会选择成立专业的网络营销部门来对网站建设后期的维护工作全权负责，也有些企业会选择与专业网站建设公司合作，在网络营销专家的协助下将方案执行到底，有付出才会有收获，如果我们只是进行了营销网站建设就奢望着等待丰硕的结果是非常不实际的。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">把耐心放长，经得起考验和等待就一定能看到花儿绽放的美丽。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">无论是选择SEO技术进行网站建设后期的关键词优化还是选择立竿见影的网络广告投放，客户的转化过程都必须要经过一段时间的探索总结后才有可能见成效，所以千万不能急。网络上来的客户与线下开发的客户毕竟还是有许多不同之处的，要慢慢的去发现并找到相应的解决方案后才能将网站建设的成效与网络营销的转化完美的结合起来。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">网站建设后期效果与网络营销成效之间不存在绝对性。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">有些企业将网站建设后期的关键字排名效果归结于工作人员的能力，这样的结论太过于绝对，过于热门的关键字，没有时间的累积任何人都没有办法在很短的时间内做得明显的成效，所以广告投放与SEO结合才是最好的选择。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">而有些企业则将网站建设后期关键字排名效果好，但却迟迟不出业绩的责任放到了业务头上，这也实在是委屈了业务员。我们必须要清楚，有排名不意味着有流量（如果网站关键字很冷门，效果很容易出来，但没有搜索自己没有流量）；有了流量也不一定有咨询量（如果我们在网站建设后期的关键字分析方向上出现了差错，流量自然无法带来咨询量，当然，这属于少数；还有一个原因就是网站建设方案本身或许并不具有切实的转化率，客户看了产品图片都没能产生咨询购买的欲望）；有了咨询量也不一定会有销量，这就与产品质量，价格以及业务的谈判能力，企业的销售方案有着千丝万缕的联系了。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">所以说，绝对不能把网站建设后期效果与企业的网络营销业绩划上等号。</p><p><br/></p>','article-show','/uploads/20161214/6c6265ebab1d5fa656d36cc148e0760b.jpg',1,1,1,'',0,0,'',27,1481697179,1487827166),(4,35,1,'admin','站长盈利必不可少的条件','color:#555','站长盈利必不可少的条件','','','站长盈利必不可少的条件','','0','',0,1,1,'',0,0,'',12,1481700558,0),(5,35,1,'admin','网络营销要注意规避口碑营销','color:#555','网络营销要注意规避口碑营销','','','网络营销要注意规避口碑营销','<p>网络营销要注意规避口碑营销</p>','0','',0,1,1,'',0,0,'',61,1481700574,1484645988),(6,42,1,'admin','高薪前端开发工程师','color:#555','前端开发','','','前端开发人员3名','<p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"margin: 0px; padding: 0px; color: gray;\">岗位要求：<br/>1、18-30岁，专业不限，高中及以上学历者优先<br/>2、需对计算机、游戏开发、设计、网站建设有兴趣<br/>3、有一定的逻辑思维，会简单电脑操作<br/>4、面试通过，直接上岗，无经验者，人品优秀可先带薪实习后上岗<br/>福利待遇：<br/>1、周末双休，每天7小时上班时间，无夜班，不加班，<br/>2、大量有奖金项目可做，能力强者可不限量接受奖金项目，<br/>3、公司为员工购买五险一金，定期组织员工生日会、野外游玩等活动，<br/>4、提供良好的晋升机制，广阔的晋升空间。</span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"margin: 0px; padding: 0px; color: gray;\">注：有意者可直接投递简历，我们会在最短的时间与您取得联系，安排面试时间</span></p><p><br/></p>','0','',0,1,1,'',0,0,'',4,1484704707,0),(7,42,1,'admin','高薪PHP技术开发工程师','color:#555','高薪PHP技术开发工程师','','','高薪PHP技术开发工程师','<p style=\"margin: 0cm 0cm 0pt; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><strong><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\">岗位条件：<span style=\"margin: 0px; padding: 0px;\"></span></span></strong></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin: 0cm 0cm 0pt; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\">1</span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\">、<span style=\"margin: 0px; padding: 0px;\">18-29</span>岁，对计算机有浓厚的兴趣，喜欢互联网工作<span style=\"margin: 0px; padding: 0px;\"></span></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin: 0cm 0cm 0pt; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; text-indent: 0cm; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\"><span style=\"margin: 0px; padding: 0px;\">2、</span></span><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\">应往届毕业生均可，想转行的也可以，必须有积极向上的心态，公司有资深技术工程师带领操作真实项目<span style=\"margin: 0px; padding: 0px;\"></span></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin: 0cm 0cm 0pt; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; text-indent: 0cm; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\"><span style=\"margin: 0px; padding: 0px;\">3、</span></span><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\">有良好的沟通能力，<span style=\"margin: 0px; padding: 0px; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">做事踏实、悟性高、学习能力强、逻辑思维要活跃</span><span style=\"margin: 0px; padding: 0px;\"></span></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; text-align: justify; background: white;\"><span style=\"margin: 0px; padding: 0px;\"><strong><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">福利待遇：</span></strong></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\"></span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\"></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; text-align: justify; background: white;\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">1</span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">、享受法定休假，公司实行<span style=\"margin: 0px; padding: 0px;\">5</span>天<span style=\"margin: 0px; padding: 0px;\">7</span>小时工作制，无需加班；<span style=\"margin: 0px; padding: 0px;\"></span></span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\"></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; text-align: justify; background: white;\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">2</span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">、五险一金，年底双薪及丰厚福利待遇；<span style=\"margin: 0px; padding: 0px;\"></span></span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\"></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; text-align: justify; background: white;\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">3</span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">、高速成长的个人发展机会，积极进取的企业文化，与公司共同成长的挑战；<span style=\"margin: 0px; padding: 0px;\"></span></span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\"></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; text-align: justify; background: white;\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">4</span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">、公司工作环境优雅、氛围好，同事关系融洽，定期举行生日派对、聚餐等丰富活动。</span></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\"></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin: 0cm 0cm 0pt; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\"><span style=\"font-size:16px;font-family:Calibri\"></span></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin: 0cm 0cm 0pt; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"margin: 0px; padding: 0px;\"><strong><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\">备注：</span></strong></span><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt;\">实习期间待遇为基本工资<span style=\"margin: 0px; padding: 0px;\">+</span>项目奖金，如果你能力强工资上万不是问题，周末双休，带薪年假，提供吃住。<span style=\"margin: 0px; padding: 0px;\"></span></span></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"font-size:16px;font-family:宋体\"></span></p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px; color: rgb(40, 40, 40); font-family: Arial, 宋体, simsun, sans-serif; font-size: 14px; line-height: 28px; white-space: normal; text-align: justify; background: white;\"><span style=\"margin: 0px; padding: 0px;\"><span style=\"margin: 0px; padding: 0px; font-size: 14pt; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">由于投递数量简历较多，筛选需要时间。</span></span></p><p><br/></p>','0','',0,1,1,'',0,0,'',1,1484704857,0),(8,41,1,'admin','王重阳','color:#555','王重阳','','','王重阳，全真派创派祖师，天下五绝之首的“中神通” ，身材甚高，腰悬长剑，风姿飒爽，英气勃勃，飘逸绝伦。是“老顽童”周伯通的师兄，“全真七子”的师父。王重阳也是一个独步武林的奇才，具绝代天资，武功造诣深不可测，已经达到出神入化的地步，世称“天下第一”。','<p style=\"text-indent: 2em;\">王重阳，全真派创派祖师，天下五绝之首的“中神通” ，身材甚高，腰悬长剑，风姿飒爽，英气勃勃，飘逸绝伦。是“老顽童”周伯通的师兄，“全真七子”的师父。王重阳也是一个独步武林的奇才，具绝代天资，武功造诣深不可测，已经达到出神入化的地步，世称“天下第一”。</p>','0','/uploads/20170118/d6ddc4e2cb9499bb7c8b1b6f0265cba1.jpg',0,1,1,'',0,0,'',2,1484705278,1487827668),(9,41,1,'admin','黄蓉','color:#555','黄蓉','','','桃花岛主“东邪” 黄药师与冯衡的独生女，精通父亲传授的桃花岛武功、五行八卦阵和奇门遁甲之术。','<p style=\"text-indent: 2em;\">桃花岛主“东邪” 黄药师与冯衡的独生女，精通父亲传授的桃花岛武功、五行八卦阵和奇门遁甲之术。</p><p style=\"text-indent: 2em;\">黄蓉集天地灵气而于一身，艳绝天下、冰雪聪明、玲珑剔透，多才多艺、博古通今，精通琴棋书画、厨艺了得。</p><p style=\"text-indent: 2em;\">与郭靖一生相恋、患难与共，全心全意助旺他，后辅佐夫君保家卫国，竭尽所能。</p><p><br/></p>','0','/uploads/20170118/75fdc5fde04c722b21b1d8907544db39.jpg',0,1,1,'',0,0,'',0,1484705662,1487827677),(10,41,1,'admin','李寻欢','color:#555','李寻欢','','','曾是朝廷殿试第三名「探花」，故人称「小李探花」，而后厌倦功名，弃官归隐；小李飞刀，例不虚发，百晓生所作《兵器谱》上排名第三。后遭仇家夹击，重伤不支，被龙啸云搭救，与之成为最好的朋友。','<p style=\"text-indent: 2em;\">曾是朝廷殿试第三名「探花」，故人称「小李探花」，而后厌倦功名，弃官归隐；小李飞刀，例不虚发，百晓生所作《兵器谱》上排名第三。后遭仇家夹击，重伤不支，被龙啸云搭救，与之成为最好的朋友。</p><p style=\"text-indent: 2em;\">不幸龙啸云不知林诗音与李寻欢已定了亲，竟对其相思入骨，病入膏肓，李寻欢满怀矛盾，故作无情，之后林诗音嫁给龙啸云，便一个人萧然离去。&nbsp;</p><p style=\"text-indent: 2em;\">于关外隐居十年，后追查「金丝甲」、「梅花盗」之案，结识好友阿飞，再遇昔日故人，几翻转折，虽然水落石出，却又卷入江湖争斗中。最终击败上官金虹，瓦解「金钱帮」，与妻子孙小红隐居。</p><p><br/></p>','article-show','/uploads/20170118/b87a747a3d69614a9514772dc9441616.jpg',0,1,1,'',0,0,'',1,1484705821,1487827657);

UNLOCK TABLES;

/*Table structure for table `clt_attachment` */

DROP TABLE IF EXISTS `clt_attachment`;

CREATE TABLE `clt_attachment` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `moduleid` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id` int(8) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(50) NOT NULL DEFAULT '',
  `filepath` varchar(80) NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `fileext` char(10) NOT NULL DEFAULT '',
  `isimage` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isthumb` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  `uploadip` char(15) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;

/*Data for the table `clt_attachment` */

LOCK TABLES `clt_attachment` WRITE;

insert  into `clt_attachment`(`aid`,`moduleid`,`catid`,`id`,`filename`,`filepath`,`filesize`,`fileext`,`isimage`,`isthumb`,`userid`,`createtime`,`uploadip`,`status`) values (29,2,0,0,'42813102.jpg','/uploads/20161212/9000ba8250cccd2be494d4241c56afe2.jpg',88944,'jpg',1,1,1,1481513467,'127.0.0.1',0),(28,2,0,0,'42813100.jpg','/uploads/20161212/b0c7f902624b65fb3b11792518772907.jpg',80805,'jpg',1,1,1,1481513430,'127.0.0.1',0),(27,3,38,2,'zq-4.jpg','/uploads/20161212/575a28a15fa652b56fa7e1047470f90d.jpg',20241,'jpg',1,1,1,1481513129,'127.0.0.1',1),(26,3,38,2,'zq-3.jpg','/uploads/20161212/de0d2fadfa2db843e7486078653751d1.jpg',20805,'jpg',1,1,1,1481513129,'127.0.0.1',1),(25,3,38,2,'zq-2.jpg','/uploads/20161212/5178887e49aee17af23ed524a7a64a1b.jpg',18965,'jpg',1,1,1,1481513128,'127.0.0.1',1),(24,3,38,2,'zq-1.jpg','/uploads/20161212/fcaecaf933ea860f82fa58af585f8c75.jpg',17405,'jpg',1,1,1,1481513128,'127.0.0.1',1),(30,2,35,1,'01.jpg','/uploads/20161214/cf1bf117e22fed3df2070897ce480565.jpg',233094,'jpg',1,1,1,1481700427,'127.0.0.1',1),(31,4,1,1,'main_27_03.jpg','/uploads/20161214/6c6265ebab1d5fa656d36cc148e0760b.jpg',59567,'jpg',1,1,1,1481704688,'127.0.0.1',1),(32,2,0,0,'01.jpg','/uploads/20161215/08d9c6acbdc8035815acd3bb9acb3b49.jpg',233094,'jpg',1,1,1,1481788808,'127.0.0.1',0),(33,4,1,1,'08d9c6acbdc8035815acd3bb9acb3b49.jpg','/uploads/20161228/d14205195a5d7951856bfc0c2acf2134.jpg',233094,'jpg',1,1,1,1482896286,'0.0.0.0',1),(34,1,8,8,'main_14_09.jpg','/uploads/20161228/242e022489f552eb0f98c51567ba4f67.jpg',29779,'jpg',1,1,1,1482919227,'0.0.0.0',1),(35,2,41,8,'79f0f736afc379317564c09eecc4b74543a9117c.jpg','/uploads/20170118/d6ddc4e2cb9499bb7c8b1b6f0265cba1.jpg',59709,'jpg',1,1,1,1484705401,'0.0.0.0',1),(36,2,41,9,'timg.jpg','/uploads/20170118/75fdc5fde04c722b21b1d8907544db39.jpg',29516,'jpg',1,1,1,1484705691,'0.0.0.0',1),(37,2,41,10,'bd3eb13533fa828bde4e878bf41f4134970a5a6c.jpg','/uploads/20170118/b87a747a3d69614a9514772dc9441616.jpg',146137,'jpg',1,1,1,1484705890,'0.0.0.0',1),(38,2,0,0,'duihao.png','/uploads/20170215/a8cac7e408c6e0529d16e94f0f7e9641.png',5541,'png',1,1,1,1487147557,'127.0.0.1',0),(39,2,0,0,'duihao.png','/uploads/20170215/ab5eea0a79943b5e11373a3eedc217a0.png',5541,'png',1,1,1,1487148177,'127.0.0.1',0),(40,2,0,0,'duihao.png','/uploads/20170215/79000218a5be1b788947114c5c05ae88.png',5541,'png',1,1,1,1487148325,'127.0.0.1',0),(41,2,0,0,'duihao.png','/uploads/20170215/e2debac60065b203a6d4dc49cada0a16.png',5541,'png',1,1,1,1487148412,'127.0.0.1',0),(42,2,0,0,'groupby.jpg','/uploads/20170220/a4ca267a7d99483ccabfdfd02dc71f6a.jpg',271413,'jpg',1,1,1,1487574082,'127.0.0.1',0),(43,2,0,0,'cover_chichu_cltphp.jpg','/uploads/20170221/ad7663d3b22079723d2de6e118fe578f.jpg',14063,'jpg',1,1,1,1487645710,'127.0.0.1',0),(44,2,0,0,'qrcode_for_gh_8aacbef4e497_430.jpg','/uploads/20170222/617d421e8f0fe37c7bc28cec3493fa88.jpg',39116,'jpg',1,1,1,1487748377,'127.0.0.1',0),(45,2,0,0,'qrcode_for_gh_8aacbef4e497_430.jpg','/uploads/20170222/f4902c1dc258eb5769be8198052c0947.jpg',39116,'jpg',1,1,1,1487748396,'127.0.0.1',0),(46,2,0,0,'fudong1.png','/uploads/20170223/a722ae3c010c3ca06a93bd8517a6fca5.png',11688,'png',1,1,1,1487818730,'0.0.0.0',0),(47,2,0,0,'banner4.jpg','/uploads/20170223/b5014e279f2684c952b94a563118569f.jpg',106958,'jpg',1,1,1,1487819103,'0.0.0.0',0),(48,2,0,0,'banner3.jpg','/uploads/20170223/ef2a8f9e2e892d00059dbfaec52b07ac.jpg',180123,'jpg',1,1,1,1487819200,'0.0.0.0',0),(49,2,0,0,'05015460740291410.png','/uploads/20170223/402f4cf7fbd0f1a24b27c50b12000c34.png',6242,'png',1,1,1,1487819308,'0.0.0.0',0),(50,4,0,0,'1an - 副本.png','/uploads/20170223/69f050c48b8edf550980abb6341eb328.png',43115,'png',1,1,1,1487819827,'0.0.0.0',0),(51,2,0,0,'1an.png','/uploads/20170223/5c73870f8b16687c634e5962caad3bda.png',43115,'png',1,1,1,1487819848,'0.0.0.0',0),(52,1,0,0,'banner3.jpg','/uploads/20170223/f3f95da69d7a1a86e01b49697f856fc7.jpg',180123,'jpg',1,1,1,1487819864,'0.0.0.0',0),(53,2,0,0,'05024646980589525.png','/uploads/20170223/b8631b79380348a564046eb7f9350715.png',6872,'png',1,1,1,1487820073,'127.0.0.1',0),(54,2,0,0,'05015460740291410.png','/uploads/20170223/c526a9df8e243617fc2395b2005668c0.png',6242,'png',1,1,1,1487820138,'127.0.0.1',0),(55,2,0,0,'banner4.jpg','/uploads/20170223/6f728d80d0b9f26bfefafe49d5d99c2b.jpg',106958,'jpg',1,1,1,1487820356,'0.0.0.0',0),(56,4,1,1,'05024646980589525.png','/uploads/20170223/7d5c2a0b02176d0620f532765bd03c8a.png',6872,'png',1,1,1,1487820507,'127.0.0.1',1),(57,4,0,0,'05024646980589525.png','/uploads/20170223/049726893d42e55f62d27cd5ba717b4a.png',6872,'png',1,1,1,1487820701,'127.0.0.1',0),(58,2,35,1,'536285cb433e36070b12c71fafd37e1f.jpg','/uploads/20170223/6f594f6bb2ab40ce8efb01f9cf64b845.jpg',44236,'jpg',1,1,1,1487826561,'0.0.0.0',1);

UNLOCK TABLES;

/*Table structure for table `clt_auth_group` */

DROP TABLE IF EXISTS `clt_auth_group`;

CREATE TABLE `clt_auth_group` (
  `group_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '全新ID',
  `title` char(100) NOT NULL DEFAULT '' COMMENT '标题',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `rules` longtext COMMENT '规则',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `clt_auth_group` */

LOCK TABLES `clt_auth_group` WRITE;

insert  into `clt_auth_group`(`group_id`,`title`,`status`,`rules`,`addtime`) values (1,'超级管理员',1,'206,207,212,208,213,209,214,215,210,216,217,211,1,2,6,105,15,16,119,144,120,146,121,145,17,149,115,116,150,117,118,151,148,147,181,18,108,152,114,113,112,109,110,111,3,5,126,127,128,4,129,189,190,192,193,7,9,13,157,158,159,160,155,14,156,27,29,37,161,163,164,162,38,167,182,168,169,165,166,28,31,32,44,45,170,171,172,173,174,175,46,176,183,177,178,179,48,49,196,197,202,198,203,204,205,',1465114224),(2,'管理员',1,'1,15,16,120,146,40,41,7,8,12,139,11,136,154,137,138,135,25,140,141,142,9,13,157,158,159,160,155,14,156,27,29,37,161,163,164,162,38,167,182,168,169,165,166,35,36,39,28,31,32,33,34,44,45,170,171,172,173,174,175,46,176,183,177,178,179,48,49,',1465114224),(3,'商品管理员',1,'27,29,37,161,163,164,162,38,167,182,168,169,165,166,',1465114224);

UNLOCK TABLES;

/*Table structure for table `clt_auth_rule` */

DROP TABLE IF EXISTS `clt_auth_rule`;

CREATE TABLE `clt_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `authopen` tinyint(2) NOT NULL DEFAULT '1',
  `css` varchar(20) DEFAULT NULL COMMENT '样式',
  `condition` char(100) DEFAULT '',
  `pid` int(5) NOT NULL DEFAULT '0' COMMENT '父栏目ID',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `zt` int(1) DEFAULT NULL,
  `menustatus` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=218 DEFAULT CHARSET=utf8;

/*Data for the table `clt_auth_rule` */

LOCK TABLES `clt_auth_rule` WRITE;

insert  into `clt_auth_rule`(`id`,`name`,`title`,`type`,`status`,`authopen`,`css`,`condition`,`pid`,`sort`,`addtime`,`zt`,`menustatus`) values (1,'Sys','系统设置',1,1,0,'fa-cogs','',0,1,1446535750,1,1),(2,'Sys/sys','系统设置',1,1,0,'','',1,0,1446535789,1,1),(3,'Sys/database','数据库管理',1,1,0,'','',1,2,1446535805,1,1),(4,'Sys/restore','数据库还原',1,1,0,'','',3,10,1446535750,1,1),(5,'Sys/database','数据库管理',1,1,0,'','',3,1,1446535834,1,1),(6,'Sys/sys','站点设置',1,1,0,'','',2,0,1446535843,1,1),(7,'Category','栏目管理',1,1,0,'fa-folder','',0,3,1446535875,1,1),(9,'Category/index','栏目管理',1,1,0,'','',7,0,1446535750,1,1),(13,'Category/index','栏目列表',1,1,0,'','',9,0,1446535750,1,1),(14,'Category/add','添加栏目',1,1,0,'','',9,0,1446535750,1,1),(15,'Sys/adminList','管理员管理',1,1,0,'','',1,1,1446535750,1,1),(16,'Sys/adminList','管理员列表',1,1,0,'','',15,0,1446535750,1,1),(17,'Sys/adminGroup','用户组列表',1,1,0,'','',15,1,1446535750,1,1),(18,'Sys/adminRule','权限管理',1,1,0,'','',15,1,1446535750,1,1),(23,'Help/soft','软件下载',1,1,0,'','',22,50,1446711421,0,1),(27,'Member','会员管理',1,1,0,'fa-users','',0,4,1447231507,1,1),(28,'Plug','网站功能',1,1,0,'fa-cog','',0,5,1447231590,1,1),(29,'Member/memberList','会员列表',1,1,0,'','',27,10,1447232085,1,1),(31,'Plug/linkList','友情链接',1,1,0,'','',28,50,1447232183,0,1),(32,'Plug/linkList','链接列表',1,1,0,'','',31,50,1447639935,0,1),(36,'We/we_menu','自定义菜单',1,1,0,'','',35,50,1447842477,0,1),(44,'Plug/adList','广告管理',1,1,0,'','',28,50,1450314265,1,1),(37,'Member/memberList','会员列表',1,1,0,'','',29,50,1448413219,1,1),(38,'Member/memberGroup','会员组',1,1,0,'','',29,50,1448413248,1,1),(39,'We/we_menu','自定义菜单',1,1,0,'','',36,50,1448501584,0,1),(45,'Plug/adList','广告设置',1,1,0,'','',44,50,1450314297,1,1),(46,'Plug/adTypeList','广告位设置',1,1,0,'','',44,50,1450314324,1,1),(48,'Plug/message','留言管理',1,1,0,'','',28,50,1451267354,0,1),(49,'Plug/message','留言管理',1,1,0,'','',48,50,1451267369,0,1),(105,'Sys/runsys','操作-保存',1,1,0,'','',6,50,1461036331,1,0),(106,'Sys/runwesys','操作-保存',1,1,0,'','',10,50,1461037680,0,0),(107,'Sys/runemail','操作-保存',1,1,0,'','',19,50,1461039346,1,0),(108,'Sys/ruleAdd','操作-添加',1,1,0,'','',18,0,1461550835,1,0),(109,'Sys/ruleState','操作-状态',1,1,0,'','',18,5,1461550949,1,0),(110,'Sys/ruleTz','操作-验证',1,1,0,'','',18,6,1461551129,1,0),(111,'Sys/ruleorder','操作-排序',1,1,0,'','',18,7,1461551263,1,0),(112,'Sys/ruleDel','操作-删除',1,1,0,'','',18,4,1461551536,1,0),(113,'Sys/ruleUpdate','操作-改存',1,1,0,'','',18,3,1461551855,1,0),(114,'Sys/ruleEdit','操作-修改',1,1,0,'','',18,2,1461551913,1,0),(115,'Sys/groupInsert','操作-添存',1,1,0,'','',17,2,1461552280,1,0),(116,'Sys/groupEdit','操作-修改',1,1,0,'','',17,3,1461552326,1,0),(117,'Sys/groupDel','操作-删除',1,1,0,'','',17,30,1461552349,1,0),(118,'Sys/groupAccess','操作-权限',1,1,0,'','',17,40,1461552404,1,0),(119,'Sys/adminAdd','操作-添加',1,1,0,'','',16,0,1461553162,1,0),(120,'Sys/adminEdit','操作-修改',1,1,0,'','',16,2,1461554130,1,0),(121,'Sys/adminDel','操作-删除',1,1,0,'','',16,4,1461554152,1,0),(122,'Sys/source_runadd','操作-添加',1,1,0,'','',43,10,1461036331,1,0),(123,'Sys/source_order','操作-排序',1,1,0,'','',43,50,1461037680,1,0),(124,'Sys/source_runedit','操作-改存',1,1,0,'','',43,30,1461039346,1,0),(125,'Sys/source_del','操作-删除',1,1,0,'','',43,40,146103934,1,0),(126,'Sys/backup','操作-备份',1,1,0,'','',5,1,1461550835,1,0),(127,'Sys/optimize','操作-优化',1,1,0,'','',5,1,1461550835,1,0),(128,'Sys/repair','操作-修复',1,1,0,'','',5,1,1461550835,1,0),(129,'Sys/del','操作-删除',1,1,0,'','',4,1,1461550835,1,0),(130,'Sys/bxgs_state','操作-状态',1,1,0,'','',67,5,1461550835,1,0),(131,'Sys/bxgs_edit','操作-修改',1,1,0,'','',67,1,1461550835,1,0),(132,'Sys/bxgs_runedit','操作-改存',1,1,0,'','',67,2,1461550835,1,0),(134,'Sys/myinfo_runedit','个人资料修改',1,1,0,'','',68,1,1461550835,1,0),(155,'News/catOrder','操作-排序',1,1,0,'','',13,5,1461550835,1,0),(147,'Sys/groupInsert','操作-添存',1,1,0,'','',17,50,1461812136,0,0),(144,'Sys/adminInsert','操作-添存',1,1,0,'','',16,1,1461550835,1,0),(145,'Sys/adminState','操作-状态',1,1,0,'','',16,5,1461550835,1,0),(146,'Sys/adminUpdate','操作-改存',1,1,0,'','',16,3,1461550835,1,0),(148,'Sys/groupAdd','操作-添加',1,1,0,'','',17,50,1461812245,0,0),(149,'Sys/groupAdd','操作-添加',1,1,0,'','',17,1,1461550835,1,0),(150,'Sys/groupUpdate','操作-改存',1,1,0,'','',17,4,1461550835,1,0),(151,'Sys/groupRunaccess','操作-权存',1,1,0,'','',17,50,1461550835,1,0),(152,'Sys/ruleAdmin','操作-添存',1,1,0,'','',18,1,1461550835,1,0),(153,'Sys/bxgs_runadd','操作-添存',1,1,0,'','',66,1,1461550835,1,0),(156,'News/catInsert','操作-添存',1,1,0,'','',14,1,1461550835,1,0),(157,'Category/edit','操作-修改',1,1,0,'','',13,1,1461550835,1,0),(158,'News/catUpdate','操作-改存',1,1,0,'','',13,2,1461550835,1,0),(159,'Category/del','操作-删除',1,1,0,'','',13,3,1461550835,1,0),(160,'News/catPage','操作-单页修改',1,1,0,'','',13,4,1461550835,1,0),(161,'Member/memberState','操作-状态',1,1,0,'','',37,10,1461550835,1,0),(162,'Member/memberDel','操作-删除',1,1,0,'','',37,40,1461550835,1,0),(163,'Member/memberEdit','操作-审核',1,1,0,'','',37,20,1461550835,1,0),(164,'Member/memberUpdate','操作-保存',1,1,0,'','',37,30,1461550835,1,0),(165,'Member/groupState','操作-状态',1,1,0,'','',38,40,1461550835,1,0),(166,'Member/groupOrder','操作-排序',1,1,0,'','',38,50,1461550835,1,0),(167,'Member/groupAdd','操作-添加',1,1,0,'','',38,10,1461550835,1,0),(168,'Member/groupUpdate','操作-改存',1,1,0,'','',38,20,1461550835,1,0),(169,'Member/groupDel','操作-删除',1,1,0,'','',38,30,1461550835,1,0),(170,'Plug/adInsert','操作-添加',1,1,0,'','',45,10,1461550835,1,0),(171,'Plug/adEdit','操作-修改',1,1,0,'','',45,20,1461550835,1,0),(172,'Plug/adUpdate','操作-改存',1,1,0,'','',45,30,1461550835,1,0),(173,'Plug/adDel','操作-删除',1,1,0,'','',45,40,1461550835,1,0),(174,'Plug/adOrder','操作-排序',1,1,0,'','',45,50,1461550835,1,0),(175,'Plug/adState','操作-状态',1,1,0,'','',45,60,1461550835,1,0),(176,'Plug/adTypeInsert','操作-添加',1,1,0,'','',46,10,1461550835,1,0),(177,'Plug/adTypeUpdate','操作-改存',1,1,0,'','',46,20,1461550835,1,0),(178,'Plug/adTypeDel','操作-删除',1,1,0,'','',46,30,1461550835,1,0),(179,'Plug/adTypeOrder','操作-排序',1,1,0,'','',46,40,1461550835,1,0),(180,'Sys/source_edit','操作-修改',1,1,0,'','',43,20,1461832933,1,0),(181,'Sys/groupState','操作-状态',1,1,0,'','',17,50,1461834340,1,0),(182,'Member/groupEdit','操作-修改',1,1,0,'','',38,15,1461834780,1,0),(183,'Plug/adTypeEdit','操作-修改',1,1,0,'','',46,15,1461834988,1,0),(188,'Plug/donation','捐赠列表',1,1,0,'','',187,50,1466563673,0,1),(189,'Module','模型管理',1,1,0,'fa-globe','',0,2,1466825363,0,1),(190,'Module/index','模型列表',1,1,0,'','',189,1,1466826681,0,1),(192,'Module/index','模型列表',1,1,0,'','',190,1,1467007920,0,1),(193,'Module/add','添加模型',1,1,0,'','',190,50,1467007955,0,1),(196,'Template','模版管理',1,1,0,' fa-folder','',0,6,1481857304,0,1),(197,'Template/index','模版管理',1,1,0,'','',196,1,1481857540,0,1),(198,'Template/add','添加模版',1,1,0,'','',197,2,1481857587,0,1),(202,'Template/index','模版管理',1,1,0,'','',197,1,1481859447,0,1),(203,'Template/debris','碎片管理',1,1,0,'','',196,50,1484797759,0,1),(204,'Template/debris','碎片管理',1,1,0,'','',203,50,1484797849,0,1),(205,'Template/addDebris','添加碎片',1,1,0,'','',203,50,1484797878,0,1),(206,'Wechat','微信管理',1,1,0,'fa-weixin','',0,7,1487063570,0,1),(207,'Wechat/index','账号管理',1,1,0,'','',206,1,1487063705,0,1),(208,'Wechat/menu','菜单管理',1,1,0,'','',206,2,1487063765,0,1),(209,'Wechat/text','文本回复',1,1,0,'','',206,3,1487063834,0,1),(210,'Wechat/img','图文回复',1,1,0,'','',206,4,1487063858,0,1),(211,'Wechat/news','消息推送',1,1,0,'','',206,5,1487063934,0,0),(212,'Wechat/index','账号管理',1,1,0,'','',207,1,1487064541,0,1),(213,'Wechat/menu','菜单管理',1,1,0,'','',208,1,1487149151,0,1),(214,'Wechat/text','文本回复',1,1,0,'','',209,1,1487233984,0,1),(215,'Wechat/addText','添加文本回复',1,1,0,'','',209,2,1487234062,0,1),(216,'Wechat/img','图文回复',1,1,0,'','',210,1,1487318148,0,1),(217,'Wechat/addImg','添加图文回复',1,1,0,'','',210,2,1487318175,0,1);

UNLOCK TABLES;

/*Table structure for table `clt_category` */

DROP TABLE IF EXISTS `clt_category`;

CREATE TABLE `clt_category` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `catname` varchar(255) NOT NULL DEFAULT '',
  `catdir` varchar(30) NOT NULL DEFAULT '',
  `parentdir` varchar(50) NOT NULL DEFAULT '',
  `parentid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `moduleid` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `module` char(24) NOT NULL DEFAULT '',
  `arrparentid` varchar(100) NOT NULL DEFAULT '',
  `arrchildid` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(150) NOT NULL DEFAULT '',
  `keywords` varchar(200) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ishtml` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `image` varchar(100) NOT NULL DEFAULT '',
  `child` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `url` varchar(100) NOT NULL DEFAULT '',
  `template_list` varchar(20) NOT NULL DEFAULT '',
  `template_show` varchar(20) NOT NULL DEFAULT '',
  `pagesize` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `readgroup` varchar(100) NOT NULL DEFAULT '',
  `listtype` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lang` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parentid` (`parentid`),
  KEY `listorder` (`listorder`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

/*Data for the table `clt_category` */

LOCK TABLES `clt_category` WRITE;

insert  into `clt_category`(`id`,`catname`,`catdir`,`parentdir`,`parentid`,`moduleid`,`module`,`arrparentid`,`arrchildid`,`type`,`title`,`keywords`,`description`,`listorder`,`ishtml`,`ismenu`,`hits`,`image`,`child`,`url`,`template_list`,`template_show`,`pagesize`,`readgroup`,`listtype`,`lang`) values (1,'精彩案例','case','',0,4,'picture','0','1',0,'案例展示','案例展示','案例展示',4,0,1,0,'',0,'/index.php?m=Article&a=index&id=1','picture-list','picture-show',0,'1,2,3',0,0),(8,'关于我们','about','',0,1,'page','0','8',0,'','','',0,0,1,0,'http://pic55.nipic.com/file/20141208/19462408_171130083000_2.jpg',0,'/index.php?m=Page&a=index&id=8','','',0,'',0,0),(33,'优惠活动','activity','',0,2,'article','0','33',0,'优惠活动','优惠活动','优惠活动',2,0,1,0,'',0,'','article-list','article-show',0,'1,2,3',0,0),(24,'测试新闻子','tests','test/',23,1,'Page','0,23','24',0,'','','',0,0,1,0,'',0,'/index.php?m=Page&a=index&id=24','','',0,'',0,0),(34,'新闻中心','news','',0,2,'article','0','34,35,36',0,'新闻中心','新闻中心','新闻中心',1,0,1,0,'',1,'','','',15,'1,2,3',0,0),(35,'公司新闻','news','news/',34,2,'article','0,34','35',0,'站内新闻','站内新闻','站内新闻',1,0,1,0,'',0,'','article-list','article-show',5,'1,2,3',0,0),(36,'行业咨询','industry','news/',34,2,'article','0,34','36',0,'行业新闻','行业新闻','行业新闻',2,0,1,0,'',0,'','','',15,'1,2,3',0,0),(42,'加入我们','recruit','',0,2,'article','0','42',0,'加入我们','加入我们','加入我们',6,0,1,0,'',0,'','','',0,'',0,0),(43,'联系我们','contact','',0,1,'page','0','43',0,'联系我们','联系我们','联系我们',7,0,1,0,'',0,'','page-show','page-show',0,'',0,0),(41,'精英团队','team','',0,2,'article','0','41',0,'精英团队','精英团队','精英团队',5,0,1,0,'',0,'','article-list-team','article-show',0,'',0,0);

UNLOCK TABLES;

/*Table structure for table `clt_debris` */

DROP TABLE IF EXISTS `clt_debris`;

CREATE TABLE `clt_debris` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `title` varchar(120) DEFAULT NULL,
  `content` text,
  `addtime` int(13) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `clt_debris` */

LOCK TABLES `clt_debris` WRITE;

insert  into `clt_debris`(`id`,`title`,`content`,`addtime`) values (3,'联系我们','<p>电 话：0551-82635030</p><p>传 真：0551-82635030</p><p>手 机：18056576630（网站咨询热线）</p><p>邮 箱：348705622@qq.com</p><p>地 址：巢湖市健康东路东方新世界9号楼1210</p><p><br/></p>',1484815834);

UNLOCK TABLES;

/*Table structure for table `clt_download` */

DROP TABLE IF EXISTS `clt_download`;

CREATE TABLE `clt_download` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userid` int(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(40) NOT NULL DEFAULT '',
  `title` varchar(120) NOT NULL DEFAULT '',
  `title_style` varchar(40) NOT NULL DEFAULT '',
  `thumb` varchar(100) NOT NULL DEFAULT '',
  `keywords` varchar(120) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `content` mediumtext NOT NULL,
  `template` varchar(40) NOT NULL DEFAULT '',
  `posid` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `readgroup` varchar(100) NOT NULL DEFAULT '',
  `readpoint` smallint(5) unsigned NOT NULL,
  `listorder` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `url` varchar(60) NOT NULL DEFAULT '',
  `file` varchar(80) NOT NULL DEFAULT '',
  `ext` varchar(10) NOT NULL DEFAULT '',
  `size` varchar(10) NOT NULL DEFAULT '',
  `downs` int(10) unsigned NOT NULL DEFAULT '0',
  `files` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`id`,`status`,`listorder`),
  KEY `catid` (`id`,`catid`,`status`),
  KEY `listorder` (`id`,`catid`,`status`,`listorder`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `clt_download` */

LOCK TABLES `clt_download` WRITE;

insert  into `clt_download`(`id`,`catid`,`userid`,`username`,`title`,`title_style`,`thumb`,`keywords`,`description`,`content`,`template`,`posid`,`status`,`recommend`,`readgroup`,`readpoint`,`listorder`,`hits`,`createtime`,`updatetime`,`url`,`file`,`ext`,`size`,`downs`,`files`) values (1,40,1,'admin','测试文档','color:#555','','测试文档','测试文档','<p>测试文档</p>','',0,1,0,'',0,0,0,1482226237,1482724387,'','/public/uploads/20161226/c5b00384fbf60dd703e7e9c61e05983c.zip','rar','170kb',0,'');

UNLOCK TABLES;

/*Table structure for table `clt_field` */

DROP TABLE IF EXISTS `clt_field`;

CREATE TABLE `clt_field` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `moduleid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `field` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `tips` varchar(150) NOT NULL DEFAULT '',
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `minlength` int(10) unsigned NOT NULL DEFAULT '0',
  `maxlength` int(10) unsigned NOT NULL DEFAULT '0',
  `pattern` varchar(255) NOT NULL DEFAULT '',
  `errormsg` varchar(255) NOT NULL DEFAULT '',
  `class` varchar(20) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  `setup` mediumtext NOT NULL,
  `ispost` tinyint(1) NOT NULL DEFAULT '0',
  `unpostgroup` varchar(60) NOT NULL DEFAULT '',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=215 DEFAULT CHARSET=utf8;

/*Data for the table `clt_field` */

LOCK TABLES `clt_field` WRITE;

insert  into `clt_field`(`id`,`moduleid`,`field`,`name`,`tips`,`required`,`minlength`,`maxlength`,`pattern`,`errormsg`,`class`,`type`,`setup`,`ispost`,`unpostgroup`,`listorder`,`status`,`issystem`) values (1,1,'title','标题','',1,3,80,'','标题必填3-80个字','','title','array (\n  \'thumb\' => \'1\',\n  \'style\' => \'0\',\n  \'size\' => \'\',\n)',1,'',0,1,1),(2,1,'keywords','关键词','',0,0,0,'','','','text','array (\n  \'size\' => \'55\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n)',1,'',0,1,1),(3,1,'description','SEO简介','',0,0,0,'','','','textarea','array (\n  \'rows\' => \'4\',\n  \'cols\' => \'55\',\n  \'default\' => \'\',\n)',1,'',0,1,1),(4,1,'content','内容','',0,0,0,'','','','editor','array (\n  \'toolbar\' => \'full\',\n  \'default\' => \'\',\n  \'height\' => \'\',\n  \'showpage\' => \'1\',\n  \'enablekeylink\' => \'0\',\n  \'replacenum\' => \'\',\n  \'enablesaveimage\' => \'0\',\n  \'flashupload\' => \'1\',\n  \'alowuploadexts\' => \'*.jpg;*.jpeg;*.gif;*.doc;*.rar;*.zip;*.xls\',\n)',1,'',0,1,1),(5,2,'catid','栏目','',1,1,6,'digits','必须选择一个栏目','','catid','',1,'',0,1,1),(6,2,'title','标题','',1,1,80,'0','标题必须为1-80个字符','','title','array (\n  \'thumb\' => \'1\',\n  \'style\' => \'1\',\n  \'size\' => \'55\',\n)',1,'',0,1,1),(7,2,'keywords','关键词','',0,0,0,'0','','','text','array (\n  \'size\' => \'\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n  \'fieldtype\' => \'varchar\',\n)',1,'',0,1,1),(8,2,'description','SEO简介','',0,0,0,'','','','textarea','array (\n  \'rows\' => \'4\',\n  \'cols\' => \'55\',\n  \'default\' => \'\',\n)',1,'',0,1,1),(9,2,'content','内容','',0,0,0,'','','','editor','array (\n  \'toolbar\' => \'full\',\n  \'default\' => \'\',\n  \'height\' => \'\',\n  \'show_add_description\' => \'1\',\n  \'show_auto_thumb\' => \'1\',\n  \'showpage\' => \'1\',\n  \'enablekeylink\' => \'0\',\n  \'replacenum\' => \'\',\n  \'enablesaveimage\' => \'0\',\n  \'flashupload\' => \'1\',\n  \'alowuploadexts\' => \'\',\n)',1,'',0,1,1),(10,2,'createtime','发布时间','',1,0,0,'','','','datetime','',1,'',0,1,1),(11,2,'copyfrom','来源','',0,0,0,'','','','text','array (\n  \'size\' => \'20\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n)',1,'',0,1,1),(12,2,'fromlink','来源网址','',0,0,0,'','','','text','array (\n  \'size\' => \'20\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n)',1,'',0,1,1),(13,2,'readgroup','访问权限','',0,0,0,'','','','groupid','array (\n  \'inputtype\' => \'checkbox\',\n  \'fieldtype\' => \'varchar\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'85\',\n  \'default\' => \'\',\n)',1,'',0,1,1),(14,2,'posid','推荐位','',0,0,0,'','','','posid','',1,'',0,1,1),(15,2,'template','模板','',0,0,0,'','','','template','',1,'',0,1,1),(16,2,'status','状态','',0,0,0,'','','','radio','array (\n  \'options\' => \'发布|1\r\n定时发布|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'labelwidth\' => \'75\',\n  \'default\' => \'1\',\n)',1,'',0,1,1),(17,3,'catid','栏目','',1,1,6,'','必须选择一个栏目','','catid','',1,'',0,1,1),(18,3,'title','标题','',1,1,80,'','标题必须为1-80个字符','','title','array (\n  \'thumb\' => \'1\',\n  \'style\' => \'1\',\n  \'size\' => \'55\',\n)',1,'',0,1,1),(19,3,'keywords','关键词','',0,0,80,'','','','text','array (\n  \'size\' => \'55\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n  \'fieldtype\' => \'varchar\',\n)',1,'',0,1,1),(20,3,'description','SEO简介','',0,0,0,'','','','textarea','array (\n  \'fieldtype\' => \'mediumtext\',\n  \'rows\' => \'4\',\n  \'cols\' => \'55\',\n  \'default\' => \'\',\n)',1,'',0,1,1),(21,3,'content','内容','',0,0,0,'','','','editor','array (\n  \'toolbar\' => \'full\',\n  \'default\' => \'\',\n  \'height\' => \'\',\n  \'showpage\' => \'1\',\n  \'enablekeylink\' => \'0\',\n  \'replacenum\' => \'\',\n  \'enablesaveimage\' => \'0\',\n  \'flashupload\' => \'1\',\n  \'alowuploadexts\' => \'\',\n)',1,'',10,1,1),(22,3,'createtime','发布时间','',1,0,0,'','','','datetime','',1,'',93,1,1),(31,2,'recommend','允许评论','',0,0,1,'','','','radio','array (\n  \'options\' => \'允许评论|1\r\n不允许评论|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'\',\n  \'default\' => \'1\',\n)',1,'',0,0,0),(30,3,'xinghao','型号','',0,0,30,'','','','text','array (\n  \'size\' => \'20\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n  \'fieldtype\' => \'varchar\',\n)',1,'',0,1,1),(25,3,'readgroup','访问权限','',0,0,0,'','','','groupid','array (\n  \'inputtype\' => \'checkbox\',\n  \'fieldtype\' => \'tinyint\',\n  \'labelwidth\' => \'85\',\n  \'default\' => \'\',\n)',1,'',96,0,1),(26,3,'posid','推荐位','',0,0,0,'','','','posid','',1,'',97,1,1),(27,3,'template','模板','',0,0,0,'','','','template','',1,'',98,1,1),(28,3,'status','状态','',0,0,0,'','','','radio','array (\n  \'options\' => \'发布|1\r\n定时发布|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'75\',\n  \'default\' => \'1\',\n)',1,'',99,1,1),(29,3,'price','价格','',0,0,0,'','','','number','array (\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'2\',\n  \'default\' => \'0\',\n)',1,'',0,1,1),(34,3,'recommend','允许评论','',0,0,1,'','','','radio','array (\n  \'options\' => \'允许评论|1\r\n不允许评论|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'\',\n  \'default\' => \'\',\n)',1,'',0,0,0),(32,2,'readpoint','阅读收费','',0,0,3,'','','','number','array (\n  \'size\' => \'5\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'\',\n)',1,'',0,0,0),(33,2,'hits','点击次数','',0,0,8,'','','','number','array (\n  \'size\' => \'5\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'\',\n)',1,'',0,0,0),(35,3,'readpoint','阅读收费','',0,0,5,'','','','number','array (\n  \'size\' => \'5\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'0\',\n)',1,'',0,0,0),(36,3,'hits','点击次数','',0,0,8,'','','','number','array (\n  \'size\' => \'10\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'0\',\n)',1,'',0,0,0),(37,4,'catid','栏目','',1,1,6,'','必须选择一个栏目','','catid','',1,'',0,1,1),(38,4,'title','标题','',1,1,80,'','标题必须为1-80个字符','','title','array (\n  \'thumb\' => \'1\',\n  \'style\' => \'1\',\n  \'size\' => \'55\',\n)',1,'',0,1,1),(39,4,'keywords','关键词','',0,0,80,'','','','text','array (\n  \'size\' => \'55\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n  \'fieldtype\' => \'varchar\',\n)',1,'',0,1,1),(40,4,'description','SEO简介','',0,0,0,'','','','textarea','array (\n  \'fieldtype\' => \'mediumtext\',\n  \'rows\' => \'4\',\n  \'cols\' => \'55\',\n  \'default\' => \'\',\n)',1,'',0,1,1),(41,4,'content','内容','',0,0,0,'','','','editor','array (\n  \'toolbar\' => \'full\',\n  \'default\' => \'\',\n  \'height\' => \'\',\n  \'showpage\' => \'1\',\n  \'enablekeylink\' => \'0\',\n  \'replacenum\' => \'\',\n  \'enablesaveimage\' => \'0\',\n  \'flashupload\' => \'1\',\n  \'alowuploadexts\' => \'\',\n)',1,'',10,1,1),(42,4,'createtime','发布时间','',1,0,0,'','','','datetime','',1,'',93,1,1),(43,4,'recommend','允许评论','',0,0,1,'','','','radio','array (\n  \'options\' => \'允许评论|1\r\n不允许评论|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'\',\n  \'default\' => \'\',\n)',1,'',0,0,0),(44,4,'readpoint','阅读收费','',0,0,5,'','','','number','array (\n  \'size\' => \'5\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'0\',\n)',1,'',0,0,0),(45,4,'hits','点击次数','',0,0,8,'','','','number','array (\n  \'size\' => \'10\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'0\',\n)',1,'',0,0,0),(46,4,'readgroup','访问权限','',0,0,0,'','','','groupid','array (\n  \'inputtype\' => \'checkbox\',\n  \'fieldtype\' => \'tinyint\',\n  \'labelwidth\' => \'85\',\n  \'default\' => \'\',\n)',1,'',96,0,1),(47,4,'posid','推荐位','',0,0,0,'','','','posid','',1,'',97,1,1),(48,4,'template','模板','',0,0,0,'','','','template','',1,'',98,1,1),(49,4,'status','状态','',0,0,0,'','','','radio','array (\n  \'options\' => \'发布|1\r\n定时发布|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'75\',\n  \'default\' => \'1\',\n)',1,'',99,1,1),(50,5,'catid','栏目','',1,1,6,'','必须选择一个栏目','','catid','',1,'',0,1,1),(51,5,'title','标题','',1,1,80,'','标题必须为1-80个字符','','title','array (\n  \'thumb\' => \'1\',\n  \'style\' => \'1\',\n  \'size\' => \'55\',\n)',1,'',0,1,1),(52,5,'keywords','关键词','',0,0,80,'','','','text','array (\n  \'size\' => \'55\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n  \'fieldtype\' => \'varchar\',\n)',1,'',0,1,1),(53,5,'description','SEO简介','',0,0,0,'','','','textarea','array (\n  \'fieldtype\' => \'mediumtext\',\n  \'rows\' => \'4\',\n  \'cols\' => \'55\',\n  \'default\' => \'\',\n)',1,'',0,1,1),(54,5,'content','内容','',0,0,0,'','','','editor','array (\n  \'toolbar\' => \'full\',\n  \'default\' => \'\',\n  \'height\' => \'\',\n  \'showpage\' => \'1\',\n  \'enablekeylink\' => \'0\',\n  \'replacenum\' => \'\',\n  \'enablesaveimage\' => \'0\',\n  \'flashupload\' => \'1\',\n  \'alowuploadexts\' => \'\',\n)',1,'',10,1,1),(55,5,'createtime','发布时间','',1,0,0,'','','','datetime','',1,'',93,1,1),(56,5,'recommend','允许评论','',0,0,1,'','','','radio','array (\n  \'options\' => \'允许评论|1\r\n不允许评论|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'\',\n  \'default\' => \'\',\n)',1,'',0,0,0),(57,5,'readpoint','阅读收费','',0,0,5,'','','','number','array (\n  \'size\' => \'5\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'0\',\n)',1,'',0,0,0),(58,5,'hits','点击次数','',0,0,8,'','','','number','array (\n  \'size\' => \'10\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'0\',\n)',1,'',0,0,0),(59,5,'readgroup','访问权限','',0,0,0,'','','','groupid','array (\n  \'inputtype\' => \'checkbox\',\n  \'fieldtype\' => \'tinyint\',\n  \'labelwidth\' => \'85\',\n  \'default\' => \'\',\n)',1,'',96,0,1),(60,5,'posid','推荐位','',0,0,0,'','','','posid','',1,'',97,1,1),(61,5,'template','模板','',0,0,0,'','','','template','',1,'',98,1,1),(62,5,'status','状态','',0,0,0,'','','','radio','array (\n  \'options\' => \'发布|1\r\n定时发布|0\',\n  \'fieldtype\' => \'tinyint\',\n  \'numbertype\' => \'1\',\n  \'labelwidth\' => \'75\',\n  \'default\' => \'1\',\n)',1,'',99,1,1),(63,3,'pics','图片','',0,0,0,'','','','images','array (\n  \'size\' => \'55\',\n  \'default\' => \'\',\n  \'upload_maxnum\' => \'10\',\n  \'upload_maxsize\' => \'2\',\n  \'upload_allowext\' => \'*.jpeg;*.jpg;*.gif\',\n  \'watermark\' => \'0\',\n  \'more\' => \'1\',\n)',1,'',0,1,0),(64,4,'pics','图组','',0,0,0,'','','','images','array (\n  \'size\' => \'55\',\n  \'default\' => \'\',\n  \'upload_maxnum\' => \'20\',\n  \'upload_maxsize\' => \'2\',\n  \'upload_allowext\' => \'*.jpeg;*.jpg;*.png;*.gif\',\n  \'watermark\' => \'0\',\n  \'more\' => \'1\',\n)',1,'',0,1,0),(65,5,'file','上传文件','',0,0,0,'','','','file','array (\n  \'size\' => \'55\',\n  \'default\' => \'\',\n  \'upload_maxsize\' => \'2\',\n  \'upload_allowext\' => \'*.zip;*.rar;*.doc;*.ppt\',\n  \'more\' => \'1\',\n)',1,'',0,1,0),(66,5,'ext','文档类型','',0,0,10,'','','','text','array (\n  \'size\' => \'10\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n  \'fieldtype\' => \'varchar\',\n)',1,'',0,1,0),(67,5,'size','文档大小','',0,0,10,'','','','text','array (\n  \'size\' => \'10\',\n  \'default\' => \'\',\n  \'ispassword\' => \'0\',\n  \'fieldtype\' => \'varchar\',\n)',1,'',0,1,0),(68,5,'downs','下载次数','',0,0,0,'','','','number','array (\n  \'size\' => \'10\',\n  \'numbertype\' => \'1\',\n  \'decimaldigits\' => \'0\',\n  \'default\' => \'\',\n)',1,'',0,0,0),(213,5,'files','多文件','',0,0,0,'0','','','files','array (\n  \'default\' => \'\',\n  \'upload_maxnum\' => \'5\',\n  \'upload_maxsize\' => \'2\',\n  \'upload_allowext\' => \'zip,rar,doc,ppt\',\n  \'more\' => \'1\',\n)',0,'',0,1,0);

UNLOCK TABLES;

/*Table structure for table `clt_link` */

DROP TABLE IF EXISTS `clt_link`;

CREATE TABLE `clt_link` (
  `link_id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '链接名称',
  `url` varchar(200) NOT NULL COMMENT '链接URL',
  `type_id` tinyint(4) DEFAULT NULL COMMENT '所属栏目ID',
  `qq` varchar(20) NOT NULL COMMENT '联系QQ',
  `sort` int(5) NOT NULL DEFAULT '50' COMMENT '排序',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `open` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0禁用1启用',
  PRIMARY KEY (`link_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Data for the table `clt_link` */

LOCK TABLES `clt_link` WRITE;

insert  into `clt_link`(`link_id`,`name`,`url`,`type_id`,`qq`,`sort`,`addtime`,`open`) values (3,'CLTPHP','http://www.cltphp.com/',1,'1109305987',1,1465714838,1),(8,'ThinkPHP','http://www.thinkphp.cn/',0,'',0,1484791374,1);

UNLOCK TABLES;

/*Table structure for table `clt_member` */

DROP TABLE IF EXISTS `clt_member`;

CREATE TABLE `clt_member` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `weid` int(11) NOT NULL COMMENT '对应微信会员ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '登录用户名',
  `pwd` char(32) NOT NULL DEFAULT '' COMMENT '登录密码',
  `group_id` tinyint(2) NOT NULL COMMENT '所属会员组',
  `petname` varchar(30) NOT NULL DEFAULT '' COMMENT '昵称',
  `province` int(6) NOT NULL COMMENT '城市',
  `city` int(6) NOT NULL COMMENT '市县',
  `town` int(6) NOT NULL COMMENT '乡镇',
  `sex` tinyint(2) NOT NULL COMMENT '1=男  2=女',
  `headpic` varchar(100) NOT NULL DEFAULT '' COMMENT '会员头像路径',
  `tel` varchar(20) NOT NULL COMMENT '手机',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `open` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(11) NOT NULL COMMENT '添加时间戳',
  PRIMARY KEY (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

/*Data for the table `clt_member` */

LOCK TABLES `clt_member` WRITE;

insert  into `clt_member`(`member_id`,`weid`,`username`,`pwd`,`group_id`,`petname`,`province`,`city`,`town`,`sex`,`headpic`,`tel`,`email`,`open`,`create_time`) values (17,0,'chichu','e10adc3949ba59abbe56e057f20f883e',1,'chichu',0,0,0,1,'','18792402228','18792402229@qq.com',0,1484986105),(19,0,'chichu1','e10adc3949ba59abbe56e057f20f883e',1,'chichu',0,0,0,1,'','18792402229','18792402239@qq.com',0,1484988595);

UNLOCK TABLES;

/*Table structure for table `clt_member_group` */

DROP TABLE IF EXISTS `clt_member_group`;

CREATE TABLE `clt_member_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员组ID',
  `name` varchar(30) NOT NULL COMMENT '会员组名',
  `open` int(11) NOT NULL DEFAULT '0' COMMENT '会员组是否开放',
  `toplimit` int(11) NOT NULL DEFAULT '0' COMMENT '积分上限',
  `bomlimit` int(11) NOT NULL DEFAULT '0' COMMENT '积分下限',
  `sort` int(11) NOT NULL COMMENT '排序',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Data for the table `clt_member_group` */

LOCK TABLES `clt_member_group` WRITE;

insert  into `clt_member_group`(`group_id`,`name`,`open`,`toplimit`,`bomlimit`,`sort`) values (1,'普通会员',1,200,0,1),(3,'高级会员',1,300,100,2),(4,'银牌会员',1,600,300,3),(9,'金牌会员',1,900,600,4);

UNLOCK TABLES;

/*Table structure for table `clt_message` */

DROP TABLE IF EXISTS `clt_message`;

CREATE TABLE `clt_message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT '' COMMENT '留言标题',
  `tel` varchar(15) NOT NULL DEFAULT '' COMMENT '留言电话',
  `addtime` varchar(15) NOT NULL COMMENT '留言时间',
  `open` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1=审核 0=不审核',
  `ip` varchar(50) DEFAULT '' COMMENT '留言者IP',
  `content` longtext NOT NULL COMMENT '留言内容',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `email` varchar(50) DEFAULT NULL COMMENT '留言邮箱',
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `clt_message` */

LOCK TABLES `clt_message` WRITE;

UNLOCK TABLES;

/*Table structure for table `clt_module` */

DROP TABLE IF EXISTS `clt_module`;

CREATE TABLE `clt_module` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(200) NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `listfields` varchar(255) NOT NULL DEFAULT '',
  `setup` text NOT NULL,
  `listorder` smallint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

/*Data for the table `clt_module` */

LOCK TABLES `clt_module` WRITE;

insert  into `clt_module`(`id`,`title`,`name`,`description`,`type`,`issystem`,`listfields`,`setup`,`listorder`,`status`) values (1,'单页模型','page','单页模型',1,1,'*','',0,1),(2,'文章模型','article','新闻文章',1,1,'*','',0,1),(3,'产品模型','product','产品展示',1,1,'*','',0,1),(4,'图片模型','picture','图片展示',1,1,'*','',0,1),(5,'下载模型','download','文件下载',1,1,'*','',0,1);

UNLOCK TABLES;

/*Table structure for table `clt_order` */

DROP TABLE IF EXISTS `clt_order`;

CREATE TABLE `clt_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sn` char(22) NOT NULL DEFAULT '',
  `password` varchar(30) NOT NULL DEFAULT '',
  `module` varchar(20) NOT NULL DEFAULT '',
  `userid` int(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(40) NOT NULL DEFAULT '',
  `price` decimal(10,0) unsigned NOT NULL DEFAULT '0',
  `productlist` mediumtext NOT NULL,
  `note` mediumtext NOT NULL,
  `realname` varchar(40) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `tel` varchar(50) NOT NULL DEFAULT '',
  `mobile` varchar(18) NOT NULL DEFAULT '',
  `fax` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(80) NOT NULL DEFAULT '',
  `zipcode` varchar(10) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL DEFAULT '',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sn` (`sn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `clt_order` */

LOCK TABLES `clt_order` WRITE;

UNLOCK TABLES;

/*Table structure for table `clt_page` */

DROP TABLE IF EXISTS `clt_page`;

CREATE TABLE `clt_page` (
  `id` smallint(5) NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `thumb` varchar(100) NOT NULL DEFAULT '',
  `keywords` varchar(250) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `content` mediumtext NOT NULL,
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0',
  `template` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `clt_page` */

LOCK TABLES `clt_page` WRITE;

insert  into `clt_page`(`id`,`title`,`thumb`,`keywords`,`description`,`content`,`listorder`,`template`) values (43,'联系我们','','联系我们','联系我们','<p><iframe class=\"ueditor_baidumap\" src=\"http://localhost/thinkphp/cltphp2/public/ueditor/dialogs/map/show.html#center=116.404,39.915&zoom=10&width=530&height=340&markers=116.404,39.915&markerStyles=l,A\" frameborder=\"0\" width=\"534\" height=\"344\"></iframe></p><p>电话：******</p><p>联系人：******</p>',0,'0'),(8,'关于我们','/uploads/20161228/242e022489f552eb0f98c51567ba4f67.jpg','关于我们','关于我们','<p style=\"text-indent: 2em;\">西安大明宫实业集团，以商业地产开发经营为主业，致力于为建材、装饰材料、家居产品的流通及百货、餐饮、娱乐行业提供经营平台。聚合资源，形成规模，为房地产开发、工程建设、百姓居家生活提供服务。集团总注册资金 2.3 亿元，资产 12 亿元。直接解决就业上万人，带动周边就业 10 余万人。</p><p style=\"text-indent: 2em;\">自1993 年从 52 亩地开始创办大明宫建材市场以来，市场规模逐渐扩大至 50 余万平方米。容纳商户 3000 家，经营十万余种建材装饰材料。 1994 年修建西北地区首家民营铁路专运线。现市场已成为西北地区最大的建材装饰材料集散地和全国十大建材批发市场之一。十余年来，市场接连被评为 “ 市级文明市场 ” 、 “ 省级文明市场 ” 和 “ 全国首批规范化管理市场 ” ，并于 2001 年通过了 ISO9001 国际质量认证。集团先后荣获国家、省、市、区授予的 200 多项荣誉。</p><p><br/></p>',0,'page-show');

UNLOCK TABLES;

/*Table structure for table `clt_picture` */

DROP TABLE IF EXISTS `clt_picture`;

CREATE TABLE `clt_picture` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(40) NOT NULL DEFAULT '',
  `title` varchar(120) NOT NULL DEFAULT '',
  `title_style` varchar(40) NOT NULL DEFAULT '',
  `thumb` varchar(100) NOT NULL DEFAULT '',
  `keywords` varchar(120) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `content` mediumtext NOT NULL,
  `url` varchar(60) NOT NULL DEFAULT '',
  `template` varchar(40) NOT NULL DEFAULT '',
  `posid` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `readgroup` varchar(100) NOT NULL DEFAULT '',
  `readpoint` smallint(5) NOT NULL DEFAULT '0',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `pics` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`id`,`status`,`listorder`),
  KEY `catid` (`id`,`catid`,`status`),
  KEY `listorder` (`id`,`catid`,`status`,`listorder`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `clt_picture` */

LOCK TABLES `clt_picture` WRITE;

insert  into `clt_picture`(`id`,`catid`,`userid`,`username`,`title`,`title_style`,`thumb`,`keywords`,`description`,`content`,`url`,`template`,`posid`,`status`,`recommend`,`readgroup`,`readpoint`,`listorder`,`hits`,`createtime`,`updatetime`,`pics`) values (1,1,1,'admin','案例展示01','color:#555','/uploads/20161214/6c6265ebab1d5fa656d36cc148e0760b.jpg','案例展示01','案例展示01','<p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">安徽伟光电缆股份有限公司位于中国科教之城-合肥，公司是以电线电缆研发、制造、销售为主的高科技企业。公司注册资本1.18亿，年产值6.8亿，占地面积106000多平方米。员工265人，专业技术人员53人，拥有雄厚的技术力量，先进的生产设备、检验设备和较强的新产品开发能力。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">公司在安徽电缆行业中不断创新，企业连续多年被评为“重合同守信用先进企业”。通过吸收国内外先进的生产和管理经验，取得了长足的发展，已经建立起符合现代企业制度的经营模式。 公司是一家专业生产电线电缆的现代化企业，目前主要生产的产品有：35KV及以下聚乙烯绝缘电缆、电线和软线、电力电缆、控制电缆、橡套软电缆、交联电缆、架空电缆、矿用电缆、阻燃、耐火特种电线电缆和耐高温新型绿色环保特种电缆等多个系列的产品。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">公司在各地设立了上百个经销与技术、售后服务机构，产品畅销全国各地。公司通过了ISO9001质量管理体系认证获得国家质量监督检验总局的全国工业产品生产许可证，中国质量认证中心的中国国家强制性产品认证证书（CCC证书），是“安徽省计量保证确认单位”、“重合同守信用企业”、“AAA级资信企业”、“安徽省质量信得过产品”、“国家环保体系认证”等多项荣誉称号。 公司将继续坚持励志超越的企 业精神，坚持客户第一、质量第一，科学管理的宗旨，坚持以为客户提供优质的产品和满意的服务，为追求卓越绩效而不懈努力。</p><p><br/></p>','','picture-show',0,1,0,'',0,1,46,1481703496,1487827356,'/public/uploads/20161228/d14205195a5d7951856bfc0c2acf2134.jpg|08d9c6acbdc8035815acd3bb9acb3b49.jpg|'),(2,1,1,'admin','案例展示02','color:#555','/uploads/20161214/6c6265ebab1d5fa656d36cc148e0760b.jpg','','','<p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">安徽伟光电缆股份有限公司位于中国科教之城-合肥，公司是以电线电缆研发、制造、销售为主的高科技企业。公司注册资本1.18亿，年产值6.8亿，占地面积106000多平方米。员工265人，专业技术人员53人，拥有雄厚的技术力量，先进的生产设备、检验设备和较强的新产品开发能力。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">公司在安徽电缆行业中不断创新，企业连续多年被评为“重合同守信用先进企业”。通过吸收国内外先进的生产和管理经验，取得了长足的发展，已经建立起符合现代企业制度的经营模式。 公司是一家专业生产电线电缆的现代化企业，目前主要生产的产品有：35KV及以下聚乙烯绝缘电缆、电线和软线、电力电缆、控制电缆、橡套软电缆、交联电缆、架空电缆、矿用电缆、阻燃、耐火特种电线电缆和耐高温新型绿色环保特种电缆等多个系列的产品。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">公司在各地设立了上百个经销与技术、售后服务机构，产品畅销全国各地。公司通过了ISO9001质量管理体系认证获得国家质量监督检验总局的全国工业产品生产许可证，中国质量认证中心的中国国家强制性产品认证证书（CCC证书），是“安徽省计量保证确认单位”、“重合同守信用企业”、“AAA级资信企业”、“安徽省质量信得过产品”、“国家环保体系认证”等多项荣誉称号。 公司将继续坚持励志超越的企业精神，坚持客户第一、质量第一，科学管理的宗旨，坚持以为客户提供优质的产品和满意的服务，为追求卓越绩效而不懈努力。</p><p><br/></p>','','0',0,1,0,'',0,2,5,1482897233,1487827456,''),(3,1,1,'admin','案例展示03','color:#555','/uploads/20161214/6c6265ebab1d5fa656d36cc148e0760b.jpg','','','<p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">安徽伟光电缆股份有限公司位于中国科教之城-合肥，公司是以电线电缆研发、制造、销售为主的高科技企业。公司注册资本1.18亿，年产值6.8亿，占地面积106000多平方米。员工265人，专业技术人员53人，拥有雄厚的技术力量，先进的生产设备、检验设备和较强的新产品开发能力。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">公司在安徽电缆行业中不断创新，企业连续多年被评为“重合同守信用先进企业”。通过吸收国内外先进的生产和管理经验，取得了长足的发展，已经建立起符合现代企业制度的经营模式。 公司是一家专业生产电线电缆的现代化企业，目前主要生产的产品有：35KV及以下聚乙烯绝缘电缆、电线和软线、电力电缆、控制电缆、橡套软电缆、交联电缆、架空电缆、矿用电缆、阻燃、耐火特种电线电缆和耐高温新型绿色环保特种电缆等多个系列的产品。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">公司在各地设立了上百个经销与技术、售后服务机构，产品畅销全国各地。公司通过了ISO9001质量管理体系认证获得国家质量监督检验总局的全国工业产品生产许可证，中国质量认证中心的中国国家强制性产品认证证书（CCC证书），是“安徽省计量保证确认单位”、“重合同守信用企业”、“AAA级资信企业”、“安徽省质量信得过产品”、“国家环保体系认证”等多项荣誉称号。 公司将继续坚持励志超越的企业精神，坚持客户第一、质量第一，科学管理的宗旨，坚持以为客户提供优质的产品和满意的服务，为追求卓越绩效而不懈努力。</p><p><br/></p>','','0',0,1,0,'',0,3,7,1482897254,1487827472,''),(4,1,1,'admin','案例展示04','color:#555','/uploads/20161214/6c6265ebab1d5fa656d36cc148e0760b.jpg','','','<p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">安徽伟光电缆股份有限公司位于中国科教之城-合肥，公司是以电线电缆研发、制造、销售为主的高科技企业。公司注册资本1.18亿，年产值6.8亿，占地面积106000多平方米。员工265人，专业技术人员53人，拥有雄厚的技术力量，先进的生产设备、检验设备和较强的新产品开发能力。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">公司在安徽电缆行业中不断创新，企业连续多年被评为“重合同守信用先进企业”。通过吸收国内外先进的生产和管理经验，取得了长足的发展，已经建立起符合现代企业制度的经营模式。 公司是一家专业生产电线电缆的现代化企业，目前主要生产的产品有：35KV及以下聚乙烯绝缘电缆、电线和软线、电力电缆、控制电缆、橡套软电缆、交联电缆、架空电缆、矿用电缆、阻燃、耐火特种电线电缆和耐高温新型绿色环保特种电缆等多个系列的产品。</p><p style=\"margin-top: 0px; margin-bottom: 0px; padding: 0px 0px 5px; line-height: 26px; text-indent: 24px; color: rgb(85, 85, 85); font-size: 14px; font-family: 宋体, Verdana, sans-serif; white-space: normal;\">公司在各地设立了上百个经销与技术、售后服务机构，产品畅销全国各地。公司通过了ISO9001质量管理体系认证获得国家质量监督检验总局的全国工业产品生产许可证，中国质量认证中心的中国国家强制性产品认证证书（CCC证书），是“安徽省计量保证确认单位”、“重合同守信用企业”、“AAA级资信企业”、“安徽省质量信得过产品”、“国家环保体系认证”等多项荣誉称号。 公司将继续坚持励志超越的企业精神，坚持客户第一、质量第一，科学管理的宗旨，坚持以为客户提供优质的产品和满意的服务，为追求卓越绩效而不懈努力。</p><p><br/></p>','','0',0,1,0,'',0,4,20,1482897273,1487827507,'');

UNLOCK TABLES;

/*Table structure for table `clt_posid` */

DROP TABLE IF EXISTS `clt_posid`;

CREATE TABLE `clt_posid` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL DEFAULT '',
  `listorder` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `clt_posid` */

LOCK TABLES `clt_posid` WRITE;

insert  into `clt_posid`(`id`,`name`,`listorder`) values (1,'首页推荐',0),(2,'首页幻灯片',0),(3,'推荐产品',0),(4,'促销产品',0);

UNLOCK TABLES;

/*Table structure for table `clt_product` */

DROP TABLE IF EXISTS `clt_product`;

CREATE TABLE `clt_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(40) NOT NULL DEFAULT '',
  `title` varchar(120) NOT NULL DEFAULT '',
  `title_style` varchar(40) NOT NULL DEFAULT '',
  `keywords` varchar(80) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `content` mediumtext NOT NULL,
  `template` varchar(40) NOT NULL DEFAULT '',
  `thumb` varchar(100) NOT NULL DEFAULT '',
  `posid` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `readgroup` varchar(100) NOT NULL DEFAULT '',
  `readpoint` smallint(5) NOT NULL DEFAULT '0',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `url` varchar(60) NOT NULL DEFAULT '',
  `xinghao` varchar(30) NOT NULL DEFAULT '',
  `pics` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`id`,`status`,`listorder`),
  KEY `catid` (`id`,`catid`,`status`),
  KEY `listorder` (`id`,`catid`,`status`,`listorder`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `clt_product` */

LOCK TABLES `clt_product` WRITE;

insert  into `clt_product`(`id`,`catid`,`userid`,`username`,`title`,`title_style`,`keywords`,`description`,`content`,`template`,`thumb`,`posid`,`status`,`recommend`,`readgroup`,`readpoint`,`listorder`,`hits`,`createtime`,`updatetime`,`price`,`url`,`xinghao`,`pics`) values (1,38,1,'admin','cltphp产品','color:#555','cltphp产品','cltphp产品','','','',0,1,0,'',0,0,0,1481513016,1482724264,'15.00','','cltphp产品','');

UNLOCK TABLES;

/*Table structure for table `clt_region` */

DROP TABLE IF EXISTS `clt_region`;

CREATE TABLE `clt_region` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(120) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3726 DEFAULT CHARSET=utf8;

/*Data for the table `clt_region` */

LOCK TABLES `clt_region` WRITE;

insert  into `clt_region`(`id`,`pid`,`name`,`type`) values (1,0,'中国',0),(2,1,'北京',1),(3,1,'安徽',1),(4,1,'福建',1),(5,1,'甘肃',1),(6,1,'广东',1),(7,1,'广西',1),(8,1,'贵州',1),(9,1,'海南',1),(10,1,'河北',1),(11,1,'河南',1),(12,1,'黑龙江',1),(13,1,'湖北',1),(14,1,'湖南',1),(15,1,'吉林',1),(16,1,'江苏',1),(17,1,'江西',1),(18,1,'辽宁',1),(19,1,'内蒙古',1),(20,1,'宁夏',1),(21,1,'青海',1),(22,1,'山东',1),(23,1,'山西',1),(24,1,'陕西',1),(25,1,'上海',1),(26,1,'四川',1),(27,1,'天津',1),(28,1,'西藏',1),(29,1,'新疆',1),(30,1,'云南',1),(31,1,'浙江',1),(32,1,'重庆',1),(33,1,'香港',1),(34,1,'澳门',1),(35,1,'台湾',1),(36,3,'安庆',2),(37,3,'蚌埠',2),(38,3,'巢湖',2),(39,3,'池州',2),(40,3,'滁州',2),(41,3,'阜阳',2),(42,3,'淮北',2),(43,3,'淮南',2),(44,3,'黄山',2),(45,3,'六安',2),(46,3,'马鞍山',2),(47,3,'宿州',2),(48,3,'铜陵',2),(49,3,'芜湖',2),(50,3,'宣城',2),(51,3,'亳州',2),(52,2,'北京',2),(53,4,'福州',2),(54,4,'龙岩',2),(55,4,'南平',2),(56,4,'宁德',2),(57,4,'莆田',2),(58,4,'泉州',2),(59,4,'三明',2),(60,4,'厦门',2),(61,4,'漳州',2),(62,5,'兰州',2),(63,5,'白银',2),(64,5,'定西',2),(65,5,'甘南',2),(66,5,'嘉峪关',2),(67,5,'金昌',2),(68,5,'酒泉',2),(69,5,'临夏',2),(70,5,'陇南',2),(71,5,'平凉',2),(72,5,'庆阳',2),(73,5,'天水',2),(74,5,'武威',2),(75,5,'张掖',2),(76,6,'广州',2),(77,6,'深圳',2),(78,6,'潮州',2),(79,6,'东莞',2),(80,6,'佛山',2),(81,6,'河源',2),(82,6,'惠州',2),(83,6,'江门',2),(84,6,'揭阳',2),(85,6,'茂名',2),(86,6,'梅州',2),(87,6,'清远',2),(88,6,'汕头',2),(89,6,'汕尾',2),(90,6,'韶关',2),(91,6,'阳江',2),(92,6,'云浮',2),(93,6,'湛江',2),(94,6,'肇庆',2),(95,6,'中山',2),(96,6,'珠海',2),(97,7,'南宁',2),(98,7,'桂林',2),(99,7,'百色',2),(100,7,'北海',2),(101,7,'崇左',2),(102,7,'防城港',2),(103,7,'贵港',2),(104,7,'河池',2),(105,7,'贺州',2),(106,7,'来宾',2),(107,7,'柳州',2),(108,7,'钦州',2),(109,7,'梧州',2),(110,7,'玉林',2),(111,8,'贵阳',2),(112,8,'安顺',2),(113,8,'毕节',2),(114,8,'六盘水',2),(115,8,'黔东南',2),(116,8,'黔南',2),(117,8,'黔西南',2),(118,8,'铜仁',2),(119,8,'遵义',2),(120,9,'海口',2),(121,9,'三亚',2),(122,9,'白沙',2),(123,9,'保亭',2),(124,9,'昌江',2),(125,9,'澄迈县',2),(126,9,'定安县',2),(127,9,'东方',2),(128,9,'乐东',2),(129,9,'临高县',2),(130,9,'陵水',2),(131,9,'琼海',2),(132,9,'琼中',2),(133,9,'屯昌县',2),(134,9,'万宁',2),(135,9,'文昌',2),(136,9,'五指山',2),(137,9,'儋州',2),(138,10,'石家庄',2),(139,10,'保定',2),(140,10,'沧州',2),(141,10,'承德',2),(142,10,'邯郸',2),(143,10,'衡水',2),(144,10,'廊坊',2),(145,10,'秦皇岛',2),(146,10,'唐山',2),(147,10,'邢台',2),(148,10,'张家口',2),(149,11,'郑州',2),(150,11,'洛阳',2),(151,11,'开封',2),(152,11,'安阳',2),(153,11,'鹤壁',2),(154,11,'济源',2),(155,11,'焦作',2),(156,11,'南阳',2),(157,11,'平顶山',2),(158,11,'三门峡',2),(159,11,'商丘',2),(160,11,'新乡',2),(161,11,'信阳',2),(162,11,'许昌',2),(163,11,'周口',2),(164,11,'驻马店',2),(165,11,'漯河',2),(166,11,'濮阳',2),(167,12,'哈尔滨',2),(168,12,'大庆',2),(169,12,'大兴安岭',2),(170,12,'鹤岗',2),(171,12,'黑河',2),(172,12,'鸡西',2),(173,12,'佳木斯',2),(174,12,'牡丹江',2),(175,12,'七台河',2),(176,12,'齐齐哈尔',2),(177,12,'双鸭山',2),(178,12,'绥化',2),(179,12,'伊春',2),(180,13,'武汉',2),(181,13,'仙桃',2),(182,13,'鄂州',2),(183,13,'黄冈',2),(184,13,'黄石',2),(185,13,'荆门',2),(186,13,'荆州',2),(187,13,'潜江',2),(188,13,'神农架林区',2),(189,13,'十堰',2),(190,13,'随州',2),(191,13,'天门',2),(192,13,'咸宁',2),(193,13,'襄樊',2),(194,13,'孝感',2),(195,13,'宜昌',2),(196,13,'恩施',2),(197,14,'长沙',2),(198,14,'张家界',2),(199,14,'常德',2),(200,14,'郴州',2),(201,14,'衡阳',2),(202,14,'怀化',2),(203,14,'娄底',2),(204,14,'邵阳',2),(205,14,'湘潭',2),(206,14,'湘西',2),(207,14,'益阳',2),(208,14,'永州',2),(209,14,'岳阳',2),(210,14,'株洲',2),(211,15,'长春',2),(212,15,'吉林',2),(213,15,'白城',2),(214,15,'白山',2),(215,15,'辽源',2),(216,15,'四平',2),(217,15,'松原',2),(218,15,'通化',2),(219,15,'延边',2),(220,16,'南京',2),(221,16,'苏州',2),(222,16,'无锡',2),(223,16,'常州',2),(224,16,'淮安',2),(225,16,'连云港',2),(226,16,'南通',2),(227,16,'宿迁',2),(228,16,'泰州',2),(229,16,'徐州',2),(230,16,'盐城',2),(231,16,'扬州',2),(232,16,'镇江',2),(233,17,'南昌',2),(234,17,'抚州',2),(235,17,'赣州',2),(236,17,'吉安',2),(237,17,'景德镇',2),(238,17,'九江',2),(239,17,'萍乡',2),(240,17,'上饶',2),(241,17,'新余',2),(242,17,'宜春',2),(243,17,'鹰潭',2),(244,18,'沈阳',2),(245,18,'大连',2),(246,18,'鞍山',2),(247,18,'本溪',2),(248,18,'朝阳',2),(249,18,'丹东',2),(250,18,'抚顺',2),(251,18,'阜新',2),(252,18,'葫芦岛',2),(253,18,'锦州',2),(254,18,'辽阳',2),(255,18,'盘锦',2),(256,18,'铁岭',2),(257,18,'营口',2),(258,19,'呼和浩特',2),(259,19,'阿拉善盟',2),(260,19,'巴彦淖尔盟',2),(261,19,'包头',2),(262,19,'赤峰',2),(263,19,'鄂尔多斯',2),(264,19,'呼伦贝尔',2),(265,19,'通辽',2),(266,19,'乌海',2),(267,19,'乌兰察布市',2),(268,19,'锡林郭勒盟',2),(269,19,'兴安盟',2),(270,20,'银川',2),(271,20,'固原',2),(272,20,'石嘴山',2),(273,20,'吴忠',2),(274,20,'中卫',2),(275,21,'西宁',2),(276,21,'果洛',2),(277,21,'海北',2),(278,21,'海东',2),(279,21,'海南',2),(280,21,'海西',2),(281,21,'黄南',2),(282,21,'玉树',2),(283,22,'济南',2),(284,22,'青岛',2),(285,22,'滨州',2),(286,22,'德州',2),(287,22,'东营',2),(288,22,'菏泽',2),(289,22,'济宁',2),(290,22,'莱芜',2),(291,22,'聊城',2),(292,22,'临沂',2),(293,22,'日照',2),(294,22,'泰安',2),(295,22,'威海',2),(296,22,'潍坊',2),(297,22,'烟台',2),(298,22,'枣庄',2),(299,22,'淄博',2),(300,23,'太原',2),(301,23,'长治',2),(302,23,'大同',2),(303,23,'晋城',2),(304,23,'晋中',2),(305,23,'临汾',2),(306,23,'吕梁',2),(307,23,'朔州',2),(308,23,'忻州',2),(309,23,'阳泉',2),(310,23,'运城',2),(311,24,'西安',2),(312,24,'安康',2),(313,24,'宝鸡',2),(314,24,'汉中',2),(315,24,'商洛',2),(316,24,'铜川',2),(317,24,'渭南',2),(318,24,'咸阳',2),(319,24,'延安',2),(320,24,'榆林',2),(321,25,'上海',2),(322,26,'成都',2),(323,26,'绵阳',2),(324,26,'阿坝',2),(325,26,'巴中',2),(326,26,'达州',2),(327,26,'德阳',2),(328,26,'甘孜',2),(329,26,'广安',2),(330,26,'广元',2),(331,26,'乐山',2),(332,26,'凉山',2),(333,26,'眉山',2),(334,26,'南充',2),(335,26,'内江',2),(336,26,'攀枝花',2),(337,26,'遂宁',2),(338,26,'雅安',2),(339,26,'宜宾',2),(340,26,'资阳',2),(341,26,'自贡',2),(342,26,'泸州',2),(343,27,'天津',2),(344,28,'拉萨',2),(345,28,'阿里',2),(346,28,'昌都',2),(347,28,'林芝',2),(348,28,'那曲',2),(349,28,'日喀则',2),(350,28,'山南',2),(351,29,'乌鲁木齐',2),(352,29,'阿克苏',2),(353,29,'阿拉尔',2),(354,29,'巴音郭楞',2),(355,29,'博尔塔拉',2),(356,29,'昌吉',2),(357,29,'哈密',2),(358,29,'和田',2),(359,29,'喀什',2),(360,29,'克拉玛依',2),(361,29,'克孜勒苏',2),(362,29,'石河子',2),(363,29,'图木舒克',2),(364,29,'吐鲁番',2),(365,29,'五家渠',2),(366,29,'伊犁',2),(367,30,'昆明',2),(368,30,'怒江',2),(369,30,'普洱',2),(370,30,'丽江',2),(371,30,'保山',2),(372,30,'楚雄',2),(373,30,'大理',2),(374,30,'德宏',2),(375,30,'迪庆',2),(376,30,'红河',2),(377,30,'临沧',2),(378,30,'曲靖',2),(379,30,'文山',2),(380,30,'西双版纳',2),(381,30,'玉溪',2),(382,30,'昭通',2),(383,31,'杭州',2),(384,31,'湖州',2),(385,31,'嘉兴',2),(386,31,'金华',2),(387,31,'丽水',2),(388,31,'宁波',2),(389,31,'绍兴',2),(390,31,'台州',2),(391,31,'温州',2),(392,31,'舟山',2),(393,31,'衢州',2),(394,32,'重庆',2),(395,33,'香港',2),(396,34,'澳门',2),(397,35,'台湾',2),(398,36,'迎江区',3),(399,36,'大观区',3),(400,36,'宜秀区',3),(401,36,'桐城市',3),(402,36,'怀宁县',3),(403,36,'枞阳县',3),(404,36,'潜山县',3),(405,36,'太湖县',3),(406,36,'宿松县',3),(407,36,'望江县',3),(408,36,'岳西县',3),(409,37,'中市区',3),(410,37,'东市区',3),(411,37,'西市区',3),(412,37,'郊区',3),(413,37,'怀远县',3),(414,37,'五河县',3),(415,37,'固镇县',3),(416,38,'居巢区',3),(417,38,'庐江县',3),(418,38,'无为县',3),(419,38,'含山县',3),(420,38,'和县',3),(421,39,'贵池区',3),(422,39,'东至县',3),(423,39,'石台县',3),(424,39,'青阳县',3),(425,40,'琅琊区',3),(426,40,'南谯区',3),(427,40,'天长市',3),(428,40,'明光市',3),(429,40,'来安县',3),(430,40,'全椒县',3),(431,40,'定远县',3),(432,40,'凤阳县',3),(433,41,'蚌山区',3),(434,41,'龙子湖区',3),(435,41,'禹会区',3),(436,41,'淮上区',3),(437,41,'颍州区',3),(438,41,'颍东区',3),(439,41,'颍泉区',3),(440,41,'界首市',3),(441,41,'临泉县',3),(442,41,'太和县',3),(443,41,'阜南县',3),(444,41,'颖上县',3),(445,42,'相山区',3),(446,42,'杜集区',3),(447,42,'烈山区',3),(448,42,'濉溪县',3),(449,43,'田家庵区',3),(450,43,'大通区',3),(451,43,'谢家集区',3),(452,43,'八公山区',3),(453,43,'潘集区',3),(454,43,'凤台县',3),(455,44,'屯溪区',3),(456,44,'黄山区',3),(457,44,'徽州区',3),(458,44,'歙县',3),(459,44,'休宁县',3),(460,44,'黟县',3),(461,44,'祁门县',3),(462,45,'金安区',3),(463,45,'裕安区',3),(464,45,'寿县',3),(465,45,'霍邱县',3),(466,45,'舒城县',3),(467,45,'金寨县',3),(468,45,'霍山县',3),(469,46,'雨山区',3),(470,46,'花山区',3),(471,46,'金家庄区',3),(472,46,'当涂县',3),(473,47,'埇桥区',3),(474,47,'砀山县',3),(475,47,'萧县',3),(476,47,'灵璧县',3),(477,47,'泗县',3),(478,48,'铜官山区',3),(479,48,'狮子山区',3),(480,48,'郊区',3),(481,48,'铜陵县',3),(482,49,'镜湖区',3),(483,49,'弋江区',3),(484,49,'鸠江区',3),(485,49,'三山区',3),(486,49,'芜湖县',3),(487,49,'繁昌县',3),(488,49,'南陵县',3),(489,50,'宣州区',3),(490,50,'宁国市',3),(491,50,'郎溪县',3),(492,50,'广德县',3),(493,50,'泾县',3),(494,50,'绩溪县',3),(495,50,'旌德县',3),(496,51,'涡阳县',3),(497,51,'蒙城县',3),(498,51,'利辛县',3),(499,51,'谯城区',3),(500,52,'东城区',3),(501,52,'西城区',3),(502,52,'海淀区',3),(503,52,'朝阳区',3),(504,52,'崇文区',3),(505,52,'宣武区',3),(506,52,'丰台区',3),(507,52,'石景山区',3),(508,52,'房山区',3),(509,52,'门头沟区',3),(510,52,'通州区',3),(511,52,'顺义区',3),(512,52,'昌平区',3),(513,52,'怀柔区',3),(514,52,'平谷区',3),(515,52,'大兴区',3),(516,52,'密云县',3),(517,52,'延庆县',3),(518,53,'鼓楼区',3),(519,53,'台江区',3),(520,53,'仓山区',3),(521,53,'马尾区',3),(522,53,'晋安区',3),(523,53,'福清市',3),(524,53,'长乐市',3),(525,53,'闽侯县',3),(526,53,'连江县',3),(527,53,'罗源县',3),(528,53,'闽清县',3),(529,53,'永泰县',3),(530,53,'平潭县',3),(531,54,'新罗区',3),(532,54,'漳平市',3),(533,54,'长汀县',3),(534,54,'永定县',3),(535,54,'上杭县',3),(536,54,'武平县',3),(537,54,'连城县',3),(538,55,'延平区',3),(539,55,'邵武市',3),(540,55,'武夷山市',3),(541,55,'建瓯市',3),(542,55,'建阳市',3),(543,55,'顺昌县',3),(544,55,'浦城县',3),(545,55,'光泽县',3),(546,55,'松溪县',3),(547,55,'政和县',3),(548,56,'蕉城区',3),(549,56,'福安市',3),(550,56,'福鼎市',3),(551,56,'霞浦县',3),(552,56,'古田县',3),(553,56,'屏南县',3),(554,56,'寿宁县',3),(555,56,'周宁县',3),(556,56,'柘荣县',3),(557,57,'城厢区',3),(558,57,'涵江区',3),(559,57,'荔城区',3),(560,57,'秀屿区',3),(561,57,'仙游县',3),(562,58,'鲤城区',3),(563,58,'丰泽区',3),(564,58,'洛江区',3),(565,58,'清濛开发区',3),(566,58,'泉港区',3),(567,58,'石狮市',3),(568,58,'晋江市',3),(569,58,'南安市',3),(570,58,'惠安县',3),(571,58,'安溪县',3),(572,58,'永春县',3),(573,58,'德化县',3),(574,58,'金门县',3),(575,59,'梅列区',3),(576,59,'三元区',3),(577,59,'永安市',3),(578,59,'明溪县',3),(579,59,'清流县',3),(580,59,'宁化县',3),(581,59,'大田县',3),(582,59,'尤溪县',3),(583,59,'沙县',3),(584,59,'将乐县',3),(585,59,'泰宁县',3),(586,59,'建宁县',3),(587,60,'思明区',3),(588,60,'海沧区',3),(589,60,'湖里区',3),(590,60,'集美区',3),(591,60,'同安区',3),(592,60,'翔安区',3),(593,61,'芗城区',3),(594,61,'龙文区',3),(595,61,'龙海市',3),(596,61,'云霄县',3),(597,61,'漳浦县',3),(598,61,'诏安县',3),(599,61,'长泰县',3),(600,61,'东山县',3),(601,61,'南靖县',3),(602,61,'平和县',3),(603,61,'华安县',3),(604,62,'皋兰县',3),(605,62,'城关区',3),(606,62,'七里河区',3),(607,62,'西固区',3),(608,62,'安宁区',3),(609,62,'红古区',3),(610,62,'永登县',3),(611,62,'榆中县',3),(612,63,'白银区',3),(613,63,'平川区',3),(614,63,'会宁县',3),(615,63,'景泰县',3),(616,63,'靖远县',3),(617,64,'临洮县',3),(618,64,'陇西县',3),(619,64,'通渭县',3),(620,64,'渭源县',3),(621,64,'漳县',3),(622,64,'岷县',3),(623,64,'安定区',3),(624,64,'安定区',3),(625,65,'合作市',3),(626,65,'临潭县',3),(627,65,'卓尼县',3),(628,65,'舟曲县',3),(629,65,'迭部县',3),(630,65,'玛曲县',3),(631,65,'碌曲县',3),(632,65,'夏河县',3),(633,66,'嘉峪关市',3),(634,67,'金川区',3),(635,67,'永昌县',3),(636,68,'肃州区',3),(637,68,'玉门市',3),(638,68,'敦煌市',3),(639,68,'金塔县',3),(640,68,'瓜州县',3),(641,68,'肃北',3),(642,68,'阿克塞',3),(643,69,'临夏市',3),(644,69,'临夏县',3),(645,69,'康乐县',3),(646,69,'永靖县',3),(647,69,'广河县',3),(648,69,'和政县',3),(649,69,'东乡族自治县',3),(650,69,'积石山',3),(651,70,'成县',3),(652,70,'徽县',3),(653,70,'康县',3),(654,70,'礼县',3),(655,70,'两当县',3),(656,70,'文县',3),(657,70,'西和县',3),(658,70,'宕昌县',3),(659,70,'武都区',3),(660,71,'崇信县',3),(661,71,'华亭县',3),(662,71,'静宁县',3),(663,71,'灵台县',3),(664,71,'崆峒区',3),(665,71,'庄浪县',3),(666,71,'泾川县',3),(667,72,'合水县',3),(668,72,'华池县',3),(669,72,'环县',3),(670,72,'宁县',3),(671,72,'庆城县',3),(672,72,'西峰区',3),(673,72,'镇原县',3),(674,72,'正宁县',3),(675,73,'甘谷县',3),(676,73,'秦安县',3),(677,73,'清水县',3),(678,73,'秦州区',3),(679,73,'麦积区',3),(680,73,'武山县',3),(681,73,'张家川',3),(682,74,'古浪县',3),(683,74,'民勤县',3),(684,74,'天祝',3),(685,74,'凉州区',3),(686,75,'高台县',3),(687,75,'临泽县',3),(688,75,'民乐县',3),(689,75,'山丹县',3),(690,75,'肃南',3),(691,75,'甘州区',3),(692,76,'从化市',3),(693,76,'天河区',3),(694,76,'东山区',3),(695,76,'白云区',3),(696,76,'海珠区',3),(697,76,'荔湾区',3),(698,76,'越秀区',3),(699,76,'黄埔区',3),(700,76,'番禺区',3),(701,76,'花都区',3),(702,76,'增城区',3),(703,76,'从化区',3),(704,76,'市郊',3),(705,77,'福田区',3),(706,77,'罗湖区',3),(707,77,'南山区',3),(708,77,'宝安区',3),(709,77,'龙岗区',3),(710,77,'盐田区',3),(711,78,'湘桥区',3),(712,78,'潮安县',3),(713,78,'饶平县',3),(714,79,'南城区',3),(715,79,'东城区',3),(716,79,'万江区',3),(717,79,'莞城区',3),(718,79,'石龙镇',3),(719,79,'虎门镇',3),(720,79,'麻涌镇',3),(721,79,'道滘镇',3),(722,79,'石碣镇',3),(723,79,'沙田镇',3),(724,79,'望牛墩镇',3),(725,79,'洪梅镇',3),(726,79,'茶山镇',3),(727,79,'寮步镇',3),(728,79,'大岭山镇',3),(729,79,'大朗镇',3),(730,79,'黄江镇',3),(731,79,'樟木头',3),(732,79,'凤岗镇',3),(733,79,'塘厦镇',3),(734,79,'谢岗镇',3),(735,79,'厚街镇',3),(736,79,'清溪镇',3),(737,79,'常平镇',3),(738,79,'桥头镇',3),(739,79,'横沥镇',3),(740,79,'东坑镇',3),(741,79,'企石镇',3),(742,79,'石排镇',3),(743,79,'长安镇',3),(744,79,'中堂镇',3),(745,79,'高埗镇',3),(746,80,'禅城区',3),(747,80,'南海区',3),(748,80,'顺德区',3),(749,80,'三水区',3),(750,80,'高明区',3),(751,81,'东源县',3),(752,81,'和平县',3),(753,81,'源城区',3),(754,81,'连平县',3),(755,81,'龙川县',3),(756,81,'紫金县',3),(757,82,'惠阳区',3),(758,82,'惠城区',3),(759,82,'大亚湾',3),(760,82,'博罗县',3),(761,82,'惠东县',3),(762,82,'龙门县',3),(763,83,'江海区',3),(764,83,'蓬江区',3),(765,83,'新会区',3),(766,83,'台山市',3),(767,83,'开平市',3),(768,83,'鹤山市',3),(769,83,'恩平市',3),(770,84,'榕城区',3),(771,84,'普宁市',3),(772,84,'揭东县',3),(773,84,'揭西县',3),(774,84,'惠来县',3),(775,85,'茂南区',3),(776,85,'茂港区',3),(777,85,'高州市',3),(778,85,'化州市',3),(779,85,'信宜市',3),(780,85,'电白县',3),(781,86,'梅县',3),(782,86,'梅江区',3),(783,86,'兴宁市',3),(784,86,'大埔县',3),(785,86,'丰顺县',3),(786,86,'五华县',3),(787,86,'平远县',3),(788,86,'蕉岭县',3),(789,87,'清城区',3),(790,87,'英德市',3),(791,87,'连州市',3),(792,87,'佛冈县',3),(793,87,'阳山县',3),(794,87,'清新县',3),(795,87,'连山',3),(796,87,'连南',3),(797,88,'南澳县',3),(798,88,'潮阳区',3),(799,88,'澄海区',3),(800,88,'龙湖区',3),(801,88,'金平区',3),(802,88,'濠江区',3),(803,88,'潮南区',3),(804,89,'城区',3),(805,89,'陆丰市',3),(806,89,'海丰县',3),(807,89,'陆河县',3),(808,90,'曲江县',3),(809,90,'浈江区',3),(810,90,'武江区',3),(811,90,'曲江区',3),(812,90,'乐昌市',3),(813,90,'南雄市',3),(814,90,'始兴县',3),(815,90,'仁化县',3),(816,90,'翁源县',3),(817,90,'新丰县',3),(818,90,'乳源',3),(819,91,'江城区',3),(820,91,'阳春市',3),(821,91,'阳西县',3),(822,91,'阳东县',3),(823,92,'云城区',3),(824,92,'罗定市',3),(825,92,'新兴县',3),(826,92,'郁南县',3),(827,92,'云安县',3),(828,93,'赤坎区',3),(829,93,'霞山区',3),(830,93,'坡头区',3),(831,93,'麻章区',3),(832,93,'廉江市',3),(833,93,'雷州市',3),(834,93,'吴川市',3),(835,93,'遂溪县',3),(836,93,'徐闻县',3),(837,94,'肇庆市',3),(838,94,'高要市',3),(839,94,'四会市',3),(840,94,'广宁县',3),(841,94,'怀集县',3),(842,94,'封开县',3),(843,94,'德庆县',3),(844,95,'石岐街道',3),(845,95,'东区街道',3),(846,95,'西区街道',3),(847,95,'环城街道',3),(848,95,'中山港街道',3),(849,95,'五桂山街道',3),(850,96,'香洲区',3),(851,96,'斗门区',3),(852,96,'金湾区',3),(853,97,'邕宁区',3),(854,97,'青秀区',3),(855,97,'兴宁区',3),(856,97,'良庆区',3),(857,97,'西乡塘区',3),(858,97,'江南区',3),(859,97,'武鸣县',3),(860,97,'隆安县',3),(861,97,'马山县',3),(862,97,'上林县',3),(863,97,'宾阳县',3),(864,97,'横县',3),(865,98,'秀峰区',3),(866,98,'叠彩区',3),(867,98,'象山区',3),(868,98,'七星区',3),(869,98,'雁山区',3),(870,98,'阳朔县',3),(871,98,'临桂县',3),(872,98,'灵川县',3),(873,98,'全州县',3),(874,98,'平乐县',3),(875,98,'兴安县',3),(876,98,'灌阳县',3),(877,98,'荔浦县',3),(878,98,'资源县',3),(879,98,'永福县',3),(880,98,'龙胜',3),(881,98,'恭城',3),(882,99,'右江区',3),(883,99,'凌云县',3),(884,99,'平果县',3),(885,99,'西林县',3),(886,99,'乐业县',3),(887,99,'德保县',3),(888,99,'田林县',3),(889,99,'田阳县',3),(890,99,'靖西县',3),(891,99,'田东县',3),(892,99,'那坡县',3),(893,99,'隆林',3),(894,100,'海城区',3),(895,100,'银海区',3),(896,100,'铁山港区',3),(897,100,'合浦县',3),(898,101,'江州区',3),(899,101,'凭祥市',3),(900,101,'宁明县',3),(901,101,'扶绥县',3),(902,101,'龙州县',3),(903,101,'大新县',3),(904,101,'天等县',3),(905,102,'港口区',3),(906,102,'防城区',3),(907,102,'东兴市',3),(908,102,'上思县',3),(909,103,'港北区',3),(910,103,'港南区',3),(911,103,'覃塘区',3),(912,103,'桂平市',3),(913,103,'平南县',3),(914,104,'金城江区',3),(915,104,'宜州市',3),(916,104,'天峨县',3),(917,104,'凤山县',3),(918,104,'南丹县',3),(919,104,'东兰县',3),(920,104,'都安',3),(921,104,'罗城',3),(922,104,'巴马',3),(923,104,'环江',3),(924,104,'大化',3),(925,105,'八步区',3),(926,105,'钟山县',3),(927,105,'昭平县',3),(928,105,'富川',3),(929,106,'兴宾区',3),(930,106,'合山市',3),(931,106,'象州县',3),(932,106,'武宣县',3),(933,106,'忻城县',3),(934,106,'金秀',3),(935,107,'城中区',3),(936,107,'鱼峰区',3),(937,107,'柳北区',3),(938,107,'柳南区',3),(939,107,'柳江县',3),(940,107,'柳城县',3),(941,107,'鹿寨县',3),(942,107,'融安县',3),(943,107,'融水',3),(944,107,'三江',3),(945,108,'钦南区',3),(946,108,'钦北区',3),(947,108,'灵山县',3),(948,108,'浦北县',3),(949,109,'万秀区',3),(950,109,'蝶山区',3),(951,109,'长洲区',3),(952,109,'岑溪市',3),(953,109,'苍梧县',3),(954,109,'藤县',3),(955,109,'蒙山县',3),(956,110,'玉州区',3),(957,110,'北流市',3),(958,110,'容县',3),(959,110,'陆川县',3),(960,110,'博白县',3),(961,110,'兴业县',3),(962,111,'南明区',3),(963,111,'云岩区',3),(964,111,'花溪区',3),(965,111,'乌当区',3),(966,111,'白云区',3),(967,111,'小河区',3),(968,111,'金阳新区',3),(969,111,'新天园区',3),(970,111,'清镇市',3),(971,111,'开阳县',3),(972,111,'修文县',3),(973,111,'息烽县',3),(974,112,'西秀区',3),(975,112,'关岭',3),(976,112,'镇宁',3),(977,112,'紫云',3),(978,112,'平坝县',3),(979,112,'普定县',3),(980,113,'毕节市',3),(981,113,'大方县',3),(982,113,'黔西县',3),(983,113,'金沙县',3),(984,113,'织金县',3),(985,113,'纳雍县',3),(986,113,'赫章县',3),(987,113,'威宁',3),(988,114,'钟山区',3),(989,114,'六枝特区',3),(990,114,'水城县',3),(991,114,'盘县',3),(992,115,'凯里市',3),(993,115,'黄平县',3),(994,115,'施秉县',3),(995,115,'三穗县',3),(996,115,'镇远县',3),(997,115,'岑巩县',3),(998,115,'天柱县',3),(999,115,'锦屏县',3),(1000,115,'剑河县',3),(1001,115,'台江县',3),(1002,115,'黎平县',3),(1003,115,'榕江县',3),(1004,115,'从江县',3),(1005,115,'雷山县',3),(1006,115,'麻江县',3),(1007,115,'丹寨县',3),(1008,116,'都匀市',3),(1009,116,'福泉市',3),(1010,116,'荔波县',3),(1011,116,'贵定县',3),(1012,116,'瓮安县',3),(1013,116,'独山县',3),(1014,116,'平塘县',3),(1015,116,'罗甸县',3),(1016,116,'长顺县',3),(1017,116,'龙里县',3),(1018,116,'惠水县',3),(1019,116,'三都',3),(1020,117,'兴义市',3),(1021,117,'兴仁县',3),(1022,117,'普安县',3),(1023,117,'晴隆县',3),(1024,117,'贞丰县',3),(1025,117,'望谟县',3),(1026,117,'册亨县',3),(1027,117,'安龙县',3),(1028,118,'铜仁市',3),(1029,118,'江口县',3),(1030,118,'石阡县',3),(1031,118,'思南县',3),(1032,118,'德江县',3),(1033,118,'玉屏',3),(1034,118,'印江',3),(1035,118,'沿河',3),(1036,118,'松桃',3),(1037,118,'万山特区',3),(1038,119,'红花岗区',3),(1039,119,'务川县',3),(1040,119,'道真县',3),(1041,119,'汇川区',3),(1042,119,'赤水市',3),(1043,119,'仁怀市',3),(1044,119,'遵义县',3),(1045,119,'桐梓县',3),(1046,119,'绥阳县',3),(1047,119,'正安县',3),(1048,119,'凤冈县',3),(1049,119,'湄潭县',3),(1050,119,'余庆县',3),(1051,119,'习水县',3),(1052,119,'道真',3),(1053,119,'务川',3),(1054,120,'秀英区',3),(1055,120,'龙华区',3),(1056,120,'琼山区',3),(1057,120,'美兰区',3),(1058,137,'市区',3),(1059,137,'洋浦开发区',3),(1060,137,'那大镇',3),(1061,137,'王五镇',3),(1062,137,'雅星镇',3),(1063,137,'大成镇',3),(1064,137,'中和镇',3),(1065,137,'峨蔓镇',3),(1066,137,'南丰镇',3),(1067,137,'白马井镇',3),(1068,137,'兰洋镇',3),(1069,137,'和庆镇',3),(1070,137,'海头镇',3),(1071,137,'排浦镇',3),(1072,137,'东成镇',3),(1073,137,'光村镇',3),(1074,137,'木棠镇',3),(1075,137,'新州镇',3),(1076,137,'三都镇',3),(1077,137,'其他',3),(1078,138,'长安区',3),(1079,138,'桥东区',3),(1080,138,'桥西区',3),(1081,138,'新华区',3),(1082,138,'裕华区',3),(1083,138,'井陉矿区',3),(1084,138,'高新区',3),(1085,138,'辛集市',3),(1086,138,'藁城市',3),(1087,138,'晋州市',3),(1088,138,'新乐市',3),(1089,138,'鹿泉市',3),(1090,138,'井陉县',3),(1091,138,'正定县',3),(1092,138,'栾城县',3),(1093,138,'行唐县',3),(1094,138,'灵寿县',3),(1095,138,'高邑县',3),(1096,138,'深泽县',3),(1097,138,'赞皇县',3),(1098,138,'无极县',3),(1099,138,'平山县',3),(1100,138,'元氏县',3),(1101,138,'赵县',3),(1102,139,'新市区',3),(1103,139,'南市区',3),(1104,139,'北市区',3),(1105,139,'涿州市',3),(1106,139,'定州市',3),(1107,139,'安国市',3),(1108,139,'高碑店市',3),(1109,139,'满城县',3),(1110,139,'清苑县',3),(1111,139,'涞水县',3),(1112,139,'阜平县',3),(1113,139,'徐水县',3),(1114,139,'定兴县',3),(1115,139,'唐县',3),(1116,139,'高阳县',3),(1117,139,'容城县',3),(1118,139,'涞源县',3),(1119,139,'望都县',3),(1120,139,'安新县',3),(1121,139,'易县',3),(1122,139,'曲阳县',3),(1123,139,'蠡县',3),(1124,139,'顺平县',3),(1125,139,'博野县',3),(1126,139,'雄县',3),(1127,140,'运河区',3),(1128,140,'新华区',3),(1129,140,'泊头市',3),(1130,140,'任丘市',3),(1131,140,'黄骅市',3),(1132,140,'河间市',3),(1133,140,'沧县',3),(1134,140,'青县',3),(1135,140,'东光县',3),(1136,140,'海兴县',3),(1137,140,'盐山县',3),(1138,140,'肃宁县',3),(1139,140,'南皮县',3),(1140,140,'吴桥县',3),(1141,140,'献县',3),(1142,140,'孟村',3),(1143,141,'双桥区',3),(1144,141,'双滦区',3),(1145,141,'鹰手营子矿区',3),(1146,141,'承德县',3),(1147,141,'兴隆县',3),(1148,141,'平泉县',3),(1149,141,'滦平县',3),(1150,141,'隆化县',3),(1151,141,'丰宁',3),(1152,141,'宽城',3),(1153,141,'围场',3),(1154,142,'从台区',3),(1155,142,'复兴区',3),(1156,142,'邯山区',3),(1157,142,'峰峰矿区',3),(1158,142,'武安市',3),(1159,142,'邯郸县',3),(1160,142,'临漳县',3),(1161,142,'成安县',3),(1162,142,'大名县',3),(1163,142,'涉县',3),(1164,142,'磁县',3),(1165,142,'肥乡县',3),(1166,142,'永年县',3),(1167,142,'邱县',3),(1168,142,'鸡泽县',3),(1169,142,'广平县',3),(1170,142,'馆陶县',3),(1171,142,'魏县',3),(1172,142,'曲周县',3),(1173,143,'桃城区',3),(1174,143,'冀州市',3),(1175,143,'深州市',3),(1176,143,'枣强县',3),(1177,143,'武邑县',3),(1178,143,'武强县',3),(1179,143,'饶阳县',3),(1180,143,'安平县',3),(1181,143,'故城县',3),(1182,143,'景县',3),(1183,143,'阜城县',3),(1184,144,'安次区',3),(1185,144,'广阳区',3),(1186,144,'霸州市',3),(1187,144,'三河市',3),(1188,144,'固安县',3),(1189,144,'永清县',3),(1190,144,'香河县',3),(1191,144,'大城县',3),(1192,144,'文安县',3),(1193,144,'大厂',3),(1194,145,'海港区',3),(1195,145,'山海关区',3),(1196,145,'北戴河区',3),(1197,145,'昌黎县',3),(1198,145,'抚宁县',3),(1199,145,'卢龙县',3),(1200,145,'青龙',3),(1201,146,'路北区',3),(1202,146,'路南区',3),(1203,146,'古冶区',3),(1204,146,'开平区',3),(1205,146,'丰南区',3),(1206,146,'丰润区',3),(1207,146,'遵化市',3),(1208,146,'迁安市',3),(1209,146,'滦县',3),(1210,146,'滦南县',3),(1211,146,'乐亭县',3),(1212,146,'迁西县',3),(1213,146,'玉田县',3),(1214,146,'唐海县',3),(1215,147,'桥东区',3),(1216,147,'桥西区',3),(1217,147,'南宫市',3),(1218,147,'沙河市',3),(1219,147,'邢台县',3),(1220,147,'临城县',3),(1221,147,'内丘县',3),(1222,147,'柏乡县',3),(1223,147,'隆尧县',3),(1224,147,'任县',3),(1225,147,'南和县',3),(1226,147,'宁晋县',3),(1227,147,'巨鹿县',3),(1228,147,'新河县',3),(1229,147,'广宗县',3),(1230,147,'平乡县',3),(1231,147,'威县',3),(1232,147,'清河县',3),(1233,147,'临西县',3),(1234,148,'桥西区',3),(1235,148,'桥东区',3),(1236,148,'宣化区',3),(1237,148,'下花园区',3),(1238,148,'宣化县',3),(1239,148,'张北县',3),(1240,148,'康保县',3),(1241,148,'沽源县',3),(1242,148,'尚义县',3),(1243,148,'蔚县',3),(1244,148,'阳原县',3),(1245,148,'怀安县',3),(1246,148,'万全县',3),(1247,148,'怀来县',3),(1248,148,'涿鹿县',3),(1249,148,'赤城县',3),(1250,148,'崇礼县',3),(1251,149,'金水区',3),(1252,149,'邙山区',3),(1253,149,'二七区',3),(1254,149,'管城区',3),(1255,149,'中原区',3),(1256,149,'上街区',3),(1257,149,'惠济区',3),(1258,149,'郑东新区',3),(1259,149,'经济技术开发区',3),(1260,149,'高新开发区',3),(1261,149,'出口加工区',3),(1262,149,'巩义市',3),(1263,149,'荥阳市',3),(1264,149,'新密市',3),(1265,149,'新郑市',3),(1266,149,'登封市',3),(1267,149,'中牟县',3),(1268,150,'西工区',3),(1269,150,'老城区',3),(1270,150,'涧西区',3),(1271,150,'瀍河回族区',3),(1272,150,'洛龙区',3),(1273,150,'吉利区',3),(1274,150,'偃师市',3),(1275,150,'孟津县',3),(1276,150,'新安县',3),(1277,150,'栾川县',3),(1278,150,'嵩县',3),(1279,150,'汝阳县',3),(1280,150,'宜阳县',3),(1281,150,'洛宁县',3),(1282,150,'伊川县',3),(1283,151,'鼓楼区',3),(1284,151,'龙亭区',3),(1285,151,'顺河回族区',3),(1286,151,'金明区',3),(1287,151,'禹王台区',3),(1288,151,'杞县',3),(1289,151,'通许县',3),(1290,151,'尉氏县',3),(1291,151,'开封县',3),(1292,151,'兰考县',3),(1293,152,'北关区',3),(1294,152,'文峰区',3),(1295,152,'殷都区',3),(1296,152,'龙安区',3),(1297,152,'林州市',3),(1298,152,'安阳县',3),(1299,152,'汤阴县',3),(1300,152,'滑县',3),(1301,152,'内黄县',3),(1302,153,'淇滨区',3),(1303,153,'山城区',3),(1304,153,'鹤山区',3),(1305,153,'浚县',3),(1306,153,'淇县',3),(1307,154,'济源市',3),(1308,155,'解放区',3),(1309,155,'中站区',3),(1310,155,'马村区',3),(1311,155,'山阳区',3),(1312,155,'沁阳市',3),(1313,155,'孟州市',3),(1314,155,'修武县',3),(1315,155,'博爱县',3),(1316,155,'武陟县',3),(1317,155,'温县',3),(1318,156,'卧龙区',3),(1319,156,'宛城区',3),(1320,156,'邓州市',3),(1321,156,'南召县',3),(1322,156,'方城县',3),(1323,156,'西峡县',3),(1324,156,'镇平县',3),(1325,156,'内乡县',3),(1326,156,'淅川县',3),(1327,156,'社旗县',3),(1328,156,'唐河县',3),(1329,156,'新野县',3),(1330,156,'桐柏县',3),(1331,157,'新华区',3),(1332,157,'卫东区',3),(1333,157,'湛河区',3),(1334,157,'石龙区',3),(1335,157,'舞钢市',3),(1336,157,'汝州市',3),(1337,157,'宝丰县',3),(1338,157,'叶县',3),(1339,157,'鲁山县',3),(1340,157,'郏县',3),(1341,158,'湖滨区',3),(1342,158,'义马市',3),(1343,158,'灵宝市',3),(1344,158,'渑池县',3),(1345,158,'陕县',3),(1346,158,'卢氏县',3),(1347,159,'梁园区',3),(1348,159,'睢阳区',3),(1349,159,'永城市',3),(1350,159,'民权县',3),(1351,159,'睢县',3),(1352,159,'宁陵县',3),(1353,159,'虞城县',3),(1354,159,'柘城县',3),(1355,159,'夏邑县',3),(1356,160,'卫滨区',3),(1357,160,'红旗区',3),(1358,160,'凤泉区',3),(1359,160,'牧野区',3),(1360,160,'卫辉市',3),(1361,160,'辉县市',3),(1362,160,'新乡县',3),(1363,160,'获嘉县',3),(1364,160,'原阳县',3),(1365,160,'延津县',3),(1366,160,'封丘县',3),(1367,160,'长垣县',3),(1368,161,'浉河区',3),(1369,161,'平桥区',3),(1370,161,'罗山县',3),(1371,161,'光山县',3),(1372,161,'新县',3),(1373,161,'商城县',3),(1374,161,'固始县',3),(1375,161,'潢川县',3),(1376,161,'淮滨县',3),(1377,161,'息县',3),(1378,162,'魏都区',3),(1379,162,'禹州市',3),(1380,162,'长葛市',3),(1381,162,'许昌县',3),(1382,162,'鄢陵县',3),(1383,162,'襄城县',3),(1384,163,'川汇区',3),(1385,163,'项城市',3),(1386,163,'扶沟县',3),(1387,163,'西华县',3),(1388,163,'商水县',3),(1389,163,'沈丘县',3),(1390,163,'郸城县',3),(1391,163,'淮阳县',3),(1392,163,'太康县',3),(1393,163,'鹿邑县',3),(1394,164,'驿城区',3),(1395,164,'西平县',3),(1396,164,'上蔡县',3),(1397,164,'平舆县',3),(1398,164,'正阳县',3),(1399,164,'确山县',3),(1400,164,'泌阳县',3),(1401,164,'汝南县',3),(1402,164,'遂平县',3),(1403,164,'新蔡县',3),(1404,165,'郾城区',3),(1405,165,'源汇区',3),(1406,165,'召陵区',3),(1407,165,'舞阳县',3),(1408,165,'临颍县',3),(1409,166,'华龙区',3),(1410,166,'清丰县',3),(1411,166,'南乐县',3),(1412,166,'范县',3),(1413,166,'台前县',3),(1414,166,'濮阳县',3),(1415,167,'道里区',3),(1416,167,'南岗区',3),(1417,167,'动力区',3),(1418,167,'平房区',3),(1419,167,'香坊区',3),(1420,167,'太平区',3),(1421,167,'道外区',3),(1422,167,'阿城区',3),(1423,167,'呼兰区',3),(1424,167,'松北区',3),(1425,167,'尚志市',3),(1426,167,'双城市',3),(1427,167,'五常市',3),(1428,167,'方正县',3),(1429,167,'宾县',3),(1430,167,'依兰县',3),(1431,167,'巴彦县',3),(1432,167,'通河县',3),(1433,167,'木兰县',3),(1434,167,'延寿县',3),(1435,168,'萨尔图区',3),(1436,168,'红岗区',3),(1437,168,'龙凤区',3),(1438,168,'让胡路区',3),(1439,168,'大同区',3),(1440,168,'肇州县',3),(1441,168,'肇源县',3),(1442,168,'林甸县',3),(1443,168,'杜尔伯特',3),(1444,169,'呼玛县',3),(1445,169,'漠河县',3),(1446,169,'塔河县',3),(1447,170,'兴山区',3),(1448,170,'工农区',3),(1449,170,'南山区',3),(1450,170,'兴安区',3),(1451,170,'向阳区',3),(1452,170,'东山区',3),(1453,170,'萝北县',3),(1454,170,'绥滨县',3),(1455,171,'爱辉区',3),(1456,171,'五大连池市',3),(1457,171,'北安市',3),(1458,171,'嫩江县',3),(1459,171,'逊克县',3),(1460,171,'孙吴县',3),(1461,172,'鸡冠区',3),(1462,172,'恒山区',3),(1463,172,'城子河区',3),(1464,172,'滴道区',3),(1465,172,'梨树区',3),(1466,172,'虎林市',3),(1467,172,'密山市',3),(1468,172,'鸡东县',3),(1469,173,'前进区',3),(1470,173,'郊区',3),(1471,173,'向阳区',3),(1472,173,'东风区',3),(1473,173,'同江市',3),(1474,173,'富锦市',3),(1475,173,'桦南县',3),(1476,173,'桦川县',3),(1477,173,'汤原县',3),(1478,173,'抚远县',3),(1479,174,'爱民区',3),(1480,174,'东安区',3),(1481,174,'阳明区',3),(1482,174,'西安区',3),(1483,174,'绥芬河市',3),(1484,174,'海林市',3),(1485,174,'宁安市',3),(1486,174,'穆棱市',3),(1487,174,'东宁县',3),(1488,174,'林口县',3),(1489,175,'桃山区',3),(1490,175,'新兴区',3),(1491,175,'茄子河区',3),(1492,175,'勃利县',3),(1493,176,'龙沙区',3),(1494,176,'昂昂溪区',3),(1495,176,'铁峰区',3),(1496,176,'建华区',3),(1497,176,'富拉尔基区',3),(1498,176,'碾子山区',3),(1499,176,'梅里斯达斡尔区',3),(1500,176,'讷河市',3),(1501,176,'龙江县',3),(1502,176,'依安县',3),(1503,176,'泰来县',3),(1504,176,'甘南县',3),(1505,176,'富裕县',3),(1506,176,'克山县',3),(1507,176,'克东县',3),(1508,176,'拜泉县',3),(1509,177,'尖山区',3),(1510,177,'岭东区',3),(1511,177,'四方台区',3),(1512,177,'宝山区',3),(1513,177,'集贤县',3),(1514,177,'友谊县',3),(1515,177,'宝清县',3),(1516,177,'饶河县',3),(1517,178,'北林区',3),(1518,178,'安达市',3),(1519,178,'肇东市',3),(1520,178,'海伦市',3),(1521,178,'望奎县',3),(1522,178,'兰西县',3),(1523,178,'青冈县',3),(1524,178,'庆安县',3),(1525,178,'明水县',3),(1526,178,'绥棱县',3),(1527,179,'伊春区',3),(1528,179,'带岭区',3),(1529,179,'南岔区',3),(1530,179,'金山屯区',3),(1531,179,'西林区',3),(1532,179,'美溪区',3),(1533,179,'乌马河区',3),(1534,179,'翠峦区',3),(1535,179,'友好区',3),(1536,179,'上甘岭区',3),(1537,179,'五营区',3),(1538,179,'红星区',3),(1539,179,'新青区',3),(1540,179,'汤旺河区',3),(1541,179,'乌伊岭区',3),(1542,179,'铁力市',3),(1543,179,'嘉荫县',3),(1544,180,'江岸区',3),(1545,180,'武昌区',3),(1546,180,'江汉区',3),(1547,180,'硚口区',3),(1548,180,'汉阳区',3),(1549,180,'青山区',3),(1550,180,'洪山区',3),(1551,180,'东西湖区',3),(1552,180,'汉南区',3),(1553,180,'蔡甸区',3),(1554,180,'江夏区',3),(1555,180,'黄陂区',3),(1556,180,'新洲区',3),(1557,180,'经济开发区',3),(1558,181,'仙桃市',3),(1559,182,'鄂城区',3),(1560,182,'华容区',3),(1561,182,'梁子湖区',3),(1562,183,'黄州区',3),(1563,183,'麻城市',3),(1564,183,'武穴市',3),(1565,183,'团风县',3),(1566,183,'红安县',3),(1567,183,'罗田县',3),(1568,183,'英山县',3),(1569,183,'浠水县',3),(1570,183,'蕲春县',3),(1571,183,'黄梅县',3),(1572,184,'黄石港区',3),(1573,184,'西塞山区',3),(1574,184,'下陆区',3),(1575,184,'铁山区',3),(1576,184,'大冶市',3),(1577,184,'阳新县',3),(1578,185,'东宝区',3),(1579,185,'掇刀区',3),(1580,185,'钟祥市',3),(1581,185,'京山县',3),(1582,185,'沙洋县',3),(1583,186,'沙市区',3),(1584,186,'荆州区',3),(1585,186,'石首市',3),(1586,186,'洪湖市',3),(1587,186,'松滋市',3),(1588,186,'公安县',3),(1589,186,'监利县',3),(1590,186,'江陵县',3),(1591,187,'潜江市',3),(1592,188,'神农架林区',3),(1593,189,'张湾区',3),(1594,189,'茅箭区',3),(1595,189,'丹江口市',3),(1596,189,'郧县',3),(1597,189,'郧西县',3),(1598,189,'竹山县',3),(1599,189,'竹溪县',3),(1600,189,'房县',3),(1601,190,'曾都区',3),(1602,190,'广水市',3),(1603,191,'天门市',3),(1604,192,'咸安区',3),(1605,192,'赤壁市',3),(1606,192,'嘉鱼县',3),(1607,192,'通城县',3),(1608,192,'崇阳县',3),(1609,192,'通山县',3),(1610,193,'襄城区',3),(1611,193,'樊城区',3),(1612,193,'襄阳区',3),(1613,193,'老河口市',3),(1614,193,'枣阳市',3),(1615,193,'宜城市',3),(1616,193,'南漳县',3),(1617,193,'谷城县',3),(1618,193,'保康县',3),(1619,194,'孝南区',3),(1620,194,'应城市',3),(1621,194,'安陆市',3),(1622,194,'汉川市',3),(1623,194,'孝昌县',3),(1624,194,'大悟县',3),(1625,194,'云梦县',3),(1626,195,'长阳',3),(1627,195,'五峰',3),(1628,195,'西陵区',3),(1629,195,'伍家岗区',3),(1630,195,'点军区',3),(1631,195,'猇亭区',3),(1632,195,'夷陵区',3),(1633,195,'宜都市',3),(1634,195,'当阳市',3),(1635,195,'枝江市',3),(1636,195,'远安县',3),(1637,195,'兴山县',3),(1638,195,'秭归县',3),(1639,196,'恩施市',3),(1640,196,'利川市',3),(1641,196,'建始县',3),(1642,196,'巴东县',3),(1643,196,'宣恩县',3),(1644,196,'咸丰县',3),(1645,196,'来凤县',3),(1646,196,'鹤峰县',3),(1647,197,'岳麓区',3),(1648,197,'芙蓉区',3),(1649,197,'天心区',3),(1650,197,'开福区',3),(1651,197,'雨花区',3),(1652,197,'开发区',3),(1653,197,'浏阳市',3),(1654,197,'长沙县',3),(1655,197,'望城县',3),(1656,197,'宁乡县',3),(1657,198,'永定区',3),(1658,198,'武陵源区',3),(1659,198,'慈利县',3),(1660,198,'桑植县',3),(1661,199,'武陵区',3),(1662,199,'鼎城区',3),(1663,199,'津市市',3),(1664,199,'安乡县',3),(1665,199,'汉寿县',3),(1666,199,'澧县',3),(1667,199,'临澧县',3),(1668,199,'桃源县',3),(1669,199,'石门县',3),(1670,200,'北湖区',3),(1671,200,'苏仙区',3),(1672,200,'资兴市',3),(1673,200,'桂阳县',3),(1674,200,'宜章县',3),(1675,200,'永兴县',3),(1676,200,'嘉禾县',3),(1677,200,'临武县',3),(1678,200,'汝城县',3),(1679,200,'桂东县',3),(1680,200,'安仁县',3),(1681,201,'雁峰区',3),(1682,201,'珠晖区',3),(1683,201,'石鼓区',3),(1684,201,'蒸湘区',3),(1685,201,'南岳区',3),(1686,201,'耒阳市',3),(1687,201,'常宁市',3),(1688,201,'衡阳县',3),(1689,201,'衡南县',3),(1690,201,'衡山县',3),(1691,201,'衡东县',3),(1692,201,'祁东县',3),(1693,202,'鹤城区',3),(1694,202,'靖州',3),(1695,202,'麻阳',3),(1696,202,'通道',3),(1697,202,'新晃',3),(1698,202,'芷江',3),(1699,202,'沅陵县',3),(1700,202,'辰溪县',3),(1701,202,'溆浦县',3),(1702,202,'中方县',3),(1703,202,'会同县',3),(1704,202,'洪江市',3),(1705,203,'娄星区',3),(1706,203,'冷水江市',3),(1707,203,'涟源市',3),(1708,203,'双峰县',3),(1709,203,'新化县',3),(1710,204,'城步',3),(1711,204,'双清区',3),(1712,204,'大祥区',3),(1713,204,'北塔区',3),(1714,204,'武冈市',3),(1715,204,'邵东县',3),(1716,204,'新邵县',3),(1717,204,'邵阳县',3),(1718,204,'隆回县',3),(1719,204,'洞口县',3),(1720,204,'绥宁县',3),(1721,204,'新宁县',3),(1722,205,'岳塘区',3),(1723,205,'雨湖区',3),(1724,205,'湘乡市',3),(1725,205,'韶山市',3),(1726,205,'湘潭县',3),(1727,206,'吉首市',3),(1728,206,'泸溪县',3),(1729,206,'凤凰县',3),(1730,206,'花垣县',3),(1731,206,'保靖县',3),(1732,206,'古丈县',3),(1733,206,'永顺县',3),(1734,206,'龙山县',3),(1735,207,'赫山区',3),(1736,207,'资阳区',3),(1737,207,'沅江市',3),(1738,207,'南县',3),(1739,207,'桃江县',3),(1740,207,'安化县',3),(1741,208,'江华',3),(1742,208,'冷水滩区',3),(1743,208,'零陵区',3),(1744,208,'祁阳县',3),(1745,208,'东安县',3),(1746,208,'双牌县',3),(1747,208,'道县',3),(1748,208,'江永县',3),(1749,208,'宁远县',3),(1750,208,'蓝山县',3),(1751,208,'新田县',3),(1752,209,'岳阳楼区',3),(1753,209,'君山区',3),(1754,209,'云溪区',3),(1755,209,'汨罗市',3),(1756,209,'临湘市',3),(1757,209,'岳阳县',3),(1758,209,'华容县',3),(1759,209,'湘阴县',3),(1760,209,'平江县',3),(1761,210,'天元区',3),(1762,210,'荷塘区',3),(1763,210,'芦淞区',3),(1764,210,'石峰区',3),(1765,210,'醴陵市',3),(1766,210,'株洲县',3),(1767,210,'攸县',3),(1768,210,'茶陵县',3),(1769,210,'炎陵县',3),(1770,211,'朝阳区',3),(1771,211,'宽城区',3),(1772,211,'二道区',3),(1773,211,'南关区',3),(1774,211,'绿园区',3),(1775,211,'双阳区',3),(1776,211,'净月潭开发区',3),(1777,211,'高新技术开发区',3),(1778,211,'经济技术开发区',3),(1779,211,'汽车产业开发区',3),(1780,211,'德惠市',3),(1781,211,'九台市',3),(1782,211,'榆树市',3),(1783,211,'农安县',3),(1784,212,'船营区',3),(1785,212,'昌邑区',3),(1786,212,'龙潭区',3),(1787,212,'丰满区',3),(1788,212,'蛟河市',3),(1789,212,'桦甸市',3),(1790,212,'舒兰市',3),(1791,212,'磐石市',3),(1792,212,'永吉县',3),(1793,213,'洮北区',3),(1794,213,'洮南市',3),(1795,213,'大安市',3),(1796,213,'镇赉县',3),(1797,213,'通榆县',3),(1798,214,'江源区',3),(1799,214,'八道江区',3),(1800,214,'长白',3),(1801,214,'临江市',3),(1802,214,'抚松县',3),(1803,214,'靖宇县',3),(1804,215,'龙山区',3),(1805,215,'西安区',3),(1806,215,'东丰县',3),(1807,215,'东辽县',3),(1808,216,'铁西区',3),(1809,216,'铁东区',3),(1810,216,'伊通',3),(1811,216,'公主岭市',3),(1812,216,'双辽市',3),(1813,216,'梨树县',3),(1814,217,'前郭尔罗斯',3),(1815,217,'宁江区',3),(1816,217,'长岭县',3),(1817,217,'乾安县',3),(1818,217,'扶余县',3),(1819,218,'东昌区',3),(1820,218,'二道江区',3),(1821,218,'梅河口市',3),(1822,218,'集安市',3),(1823,218,'通化县',3),(1824,218,'辉南县',3),(1825,218,'柳河县',3),(1826,219,'延吉市',3),(1827,219,'图们市',3),(1828,219,'敦化市',3),(1829,219,'珲春市',3),(1830,219,'龙井市',3),(1831,219,'和龙市',3),(1832,219,'安图县',3),(1833,219,'汪清县',3),(1834,220,'玄武区',3),(1835,220,'鼓楼区',3),(1836,220,'白下区',3),(1837,220,'建邺区',3),(1838,220,'秦淮区',3),(1839,220,'雨花台区',3),(1840,220,'下关区',3),(1841,220,'栖霞区',3),(1842,220,'浦口区',3),(1843,220,'江宁区',3),(1844,220,'六合区',3),(1845,220,'溧水县',3),(1846,220,'高淳县',3),(1847,221,'沧浪区',3),(1848,221,'金阊区',3),(1849,221,'平江区',3),(1850,221,'虎丘区',3),(1851,221,'吴中区',3),(1852,221,'相城区',3),(1853,221,'园区',3),(1854,221,'新区',3),(1855,221,'常熟市',3),(1856,221,'张家港市',3),(1857,221,'玉山镇',3),(1858,221,'巴城镇',3),(1859,221,'周市镇',3),(1860,221,'陆家镇',3),(1861,221,'花桥镇',3),(1862,221,'淀山湖镇',3),(1863,221,'张浦镇',3),(1864,221,'周庄镇',3),(1865,221,'千灯镇',3),(1866,221,'锦溪镇',3),(1867,221,'开发区',3),(1868,221,'吴江市',3),(1869,221,'太仓市',3),(1870,222,'崇安区',3),(1871,222,'北塘区',3),(1872,222,'南长区',3),(1873,222,'锡山区',3),(1874,222,'惠山区',3),(1875,222,'滨湖区',3),(1876,222,'新区',3),(1877,222,'江阴市',3),(1878,222,'宜兴市',3),(1879,223,'天宁区',3),(1880,223,'钟楼区',3),(1881,223,'戚墅堰区',3),(1882,223,'郊区',3),(1883,223,'新北区',3),(1884,223,'武进区',3),(1885,223,'溧阳市',3),(1886,223,'金坛市',3),(1887,224,'清河区',3),(1888,224,'清浦区',3),(1889,224,'楚州区',3),(1890,224,'淮阴区',3),(1891,224,'涟水县',3),(1892,224,'洪泽县',3),(1893,224,'盱眙县',3),(1894,224,'金湖县',3),(1895,225,'新浦区',3),(1896,225,'连云区',3),(1897,225,'海州区',3),(1898,225,'赣榆县',3),(1899,225,'东海县',3),(1900,225,'灌云县',3),(1901,225,'灌南县',3),(1902,226,'崇川区',3),(1903,226,'港闸区',3),(1904,226,'经济开发区',3),(1905,226,'启东市',3),(1906,226,'如皋市',3),(1907,226,'通州市',3),(1908,226,'海门市',3),(1909,226,'海安县',3),(1910,226,'如东县',3),(1911,227,'宿城区',3),(1912,227,'宿豫区',3),(1913,227,'宿豫县',3),(1914,227,'沭阳县',3),(1915,227,'泗阳县',3),(1916,227,'泗洪县',3),(1917,228,'海陵区',3),(1918,228,'高港区',3),(1919,228,'兴化市',3),(1920,228,'靖江市',3),(1921,228,'泰兴市',3),(1922,228,'姜堰市',3),(1923,229,'云龙区',3),(1924,229,'鼓楼区',3),(1925,229,'九里区',3),(1926,229,'贾汪区',3),(1927,229,'泉山区',3),(1928,229,'新沂市',3),(1929,229,'邳州市',3),(1930,229,'丰县',3),(1931,229,'沛县',3),(1932,229,'铜山县',3),(1933,229,'睢宁县',3),(1934,230,'城区',3),(1935,230,'亭湖区',3),(1936,230,'盐都区',3),(1937,230,'盐都县',3),(1938,230,'东台市',3),(1939,230,'大丰市',3),(1940,230,'响水县',3),(1941,230,'滨海县',3),(1942,230,'阜宁县',3),(1943,230,'射阳县',3),(1944,230,'建湖县',3),(1945,231,'广陵区',3),(1946,231,'维扬区',3),(1947,231,'邗江区',3),(1948,231,'仪征市',3),(1949,231,'高邮市',3),(1950,231,'江都市',3),(1951,231,'宝应县',3),(1952,232,'京口区',3),(1953,232,'润州区',3),(1954,232,'丹徒区',3),(1955,232,'丹阳市',3),(1956,232,'扬中市',3),(1957,232,'句容市',3),(1958,233,'东湖区',3),(1959,233,'西湖区',3),(1960,233,'青云谱区',3),(1961,233,'湾里区',3),(1962,233,'青山湖区',3),(1963,233,'红谷滩新区',3),(1964,233,'昌北区',3),(1965,233,'高新区',3),(1966,233,'南昌县',3),(1967,233,'新建县',3),(1968,233,'安义县',3),(1969,233,'进贤县',3),(1970,234,'临川区',3),(1971,234,'南城县',3),(1972,234,'黎川县',3),(1973,234,'南丰县',3),(1974,234,'崇仁县',3),(1975,234,'乐安县',3),(1976,234,'宜黄县',3),(1977,234,'金溪县',3),(1978,234,'资溪县',3),(1979,234,'东乡县',3),(1980,234,'广昌县',3),(1981,235,'章贡区',3),(1982,235,'于都县',3),(1983,235,'瑞金市',3),(1984,235,'南康市',3),(1985,235,'赣县',3),(1986,235,'信丰县',3),(1987,235,'大余县',3),(1988,235,'上犹县',3),(1989,235,'崇义县',3),(1990,235,'安远县',3),(1991,235,'龙南县',3),(1992,235,'定南县',3),(1993,235,'全南县',3),(1994,235,'宁都县',3),(1995,235,'兴国县',3),(1996,235,'会昌县',3),(1997,235,'寻乌县',3),(1998,235,'石城县',3),(1999,236,'安福县',3),(2000,236,'吉州区',3),(2001,236,'青原区',3),(2002,236,'井冈山市',3),(2003,236,'吉安县',3),(2004,236,'吉水县',3),(2005,236,'峡江县',3),(2006,236,'新干县',3),(2007,236,'永丰县',3),(2008,236,'泰和县',3),(2009,236,'遂川县',3),(2010,236,'万安县',3),(2011,236,'永新县',3),(2012,237,'珠山区',3),(2013,237,'昌江区',3),(2014,237,'乐平市',3),(2015,237,'浮梁县',3),(2016,238,'浔阳区',3),(2017,238,'庐山区',3),(2018,238,'瑞昌市',3),(2019,238,'九江县',3),(2020,238,'武宁县',3),(2021,238,'修水县',3),(2022,238,'永修县',3),(2023,238,'德安县',3),(2024,238,'星子县',3),(2025,238,'都昌县',3),(2026,238,'湖口县',3),(2027,238,'彭泽县',3),(2028,239,'安源区',3),(2029,239,'湘东区',3),(2030,239,'莲花县',3),(2031,239,'芦溪县',3),(2032,239,'上栗县',3),(2033,240,'信州区',3),(2034,240,'德兴市',3),(2035,240,'上饶县',3),(2036,240,'广丰县',3),(2037,240,'玉山县',3),(2038,240,'铅山县',3),(2039,240,'横峰县',3),(2040,240,'弋阳县',3),(2041,240,'余干县',3),(2042,240,'波阳县',3),(2043,240,'万年县',3),(2044,240,'婺源县',3),(2045,241,'渝水区',3),(2046,241,'分宜县',3),(2047,242,'袁州区',3),(2048,242,'丰城市',3),(2049,242,'樟树市',3),(2050,242,'高安市',3),(2051,242,'奉新县',3),(2052,242,'万载县',3),(2053,242,'上高县',3),(2054,242,'宜丰县',3),(2055,242,'靖安县',3),(2056,242,'铜鼓县',3),(2057,243,'月湖区',3),(2058,243,'贵溪市',3),(2059,243,'余江县',3),(2060,244,'沈河区',3),(2061,244,'皇姑区',3),(2062,244,'和平区',3),(2063,244,'大东区',3),(2064,244,'铁西区',3),(2065,244,'苏家屯区',3),(2066,244,'东陵区',3),(2067,244,'沈北新区',3),(2068,244,'于洪区',3),(2069,244,'浑南新区',3),(2070,244,'新民市',3),(2071,244,'辽中县',3),(2072,244,'康平县',3),(2073,244,'法库县',3),(2074,245,'西岗区',3),(2075,245,'中山区',3),(2076,245,'沙河口区',3),(2077,245,'甘井子区',3),(2078,245,'旅顺口区',3),(2079,245,'金州区',3),(2080,245,'开发区',3),(2081,245,'瓦房店市',3),(2082,245,'普兰店市',3),(2083,245,'庄河市',3),(2084,245,'长海县',3),(2085,246,'铁东区',3),(2086,246,'铁西区',3),(2087,246,'立山区',3),(2088,246,'千山区',3),(2089,246,'岫岩',3),(2090,246,'海城市',3),(2091,246,'台安县',3),(2092,247,'本溪',3),(2093,247,'平山区',3),(2094,247,'明山区',3),(2095,247,'溪湖区',3),(2096,247,'南芬区',3),(2097,247,'桓仁',3),(2098,248,'双塔区',3),(2099,248,'龙城区',3),(2100,248,'喀喇沁左翼蒙古族自治县',3),(2101,248,'北票市',3),(2102,248,'凌源市',3),(2103,248,'朝阳县',3),(2104,248,'建平县',3),(2105,249,'振兴区',3),(2106,249,'元宝区',3),(2107,249,'振安区',3),(2108,249,'宽甸',3),(2109,249,'东港市',3),(2110,249,'凤城市',3),(2111,250,'顺城区',3),(2112,250,'新抚区',3),(2113,250,'东洲区',3),(2114,250,'望花区',3),(2115,250,'清原',3),(2116,250,'新宾',3),(2117,250,'抚顺县',3),(2118,251,'阜新',3),(2119,251,'海州区',3),(2120,251,'新邱区',3),(2121,251,'太平区',3),(2122,251,'清河门区',3),(2123,251,'细河区',3),(2124,251,'彰武县',3),(2125,252,'龙港区',3),(2126,252,'南票区',3),(2127,252,'连山区',3),(2128,252,'兴城市',3),(2129,252,'绥中县',3),(2130,252,'建昌县',3),(2131,253,'太和区',3),(2132,253,'古塔区',3),(2133,253,'凌河区',3),(2134,253,'凌海市',3),(2135,253,'北镇市',3),(2136,253,'黑山县',3),(2137,253,'义县',3),(2138,254,'白塔区',3),(2139,254,'文圣区',3),(2140,254,'宏伟区',3),(2141,254,'太子河区',3),(2142,254,'弓长岭区',3),(2143,254,'灯塔市',3),(2144,254,'辽阳县',3),(2145,255,'双台子区',3),(2146,255,'兴隆台区',3),(2147,255,'大洼县',3),(2148,255,'盘山县',3),(2149,256,'银州区',3),(2150,256,'清河区',3),(2151,256,'调兵山市',3),(2152,256,'开原市',3),(2153,256,'铁岭县',3),(2154,256,'西丰县',3),(2155,256,'昌图县',3),(2156,257,'站前区',3),(2157,257,'西市区',3),(2158,257,'鲅鱼圈区',3),(2159,257,'老边区',3),(2160,257,'盖州市',3),(2161,257,'大石桥市',3),(2162,258,'回民区',3),(2163,258,'玉泉区',3),(2164,258,'新城区',3),(2165,258,'赛罕区',3),(2166,258,'清水河县',3),(2167,258,'土默特左旗',3),(2168,258,'托克托县',3),(2169,258,'和林格尔县',3),(2170,258,'武川县',3),(2171,259,'阿拉善左旗',3),(2172,259,'阿拉善右旗',3),(2173,259,'额济纳旗',3),(2174,260,'临河区',3),(2175,260,'五原县',3),(2176,260,'磴口县',3),(2177,260,'乌拉特前旗',3),(2178,260,'乌拉特中旗',3),(2179,260,'乌拉特后旗',3),(2180,260,'杭锦后旗',3),(2181,261,'昆都仑区',3),(2182,261,'青山区',3),(2183,261,'东河区',3),(2184,261,'九原区',3),(2185,261,'石拐区',3),(2186,261,'白云矿区',3),(2187,261,'土默特右旗',3),(2188,261,'固阳县',3),(2189,261,'达尔罕茂明安联合旗',3),(2190,262,'红山区',3),(2191,262,'元宝山区',3),(2192,262,'松山区',3),(2193,262,'阿鲁科尔沁旗',3),(2194,262,'巴林左旗',3),(2195,262,'巴林右旗',3),(2196,262,'林西县',3),(2197,262,'克什克腾旗',3),(2198,262,'翁牛特旗',3),(2199,262,'喀喇沁旗',3),(2200,262,'宁城县',3),(2201,262,'敖汉旗',3),(2202,263,'东胜区',3),(2203,263,'达拉特旗',3),(2204,263,'准格尔旗',3),(2205,263,'鄂托克前旗',3),(2206,263,'鄂托克旗',3),(2207,263,'杭锦旗',3),(2208,263,'乌审旗',3),(2209,263,'伊金霍洛旗',3),(2210,264,'海拉尔区',3),(2211,264,'莫力达瓦',3),(2212,264,'满洲里市',3),(2213,264,'牙克石市',3),(2214,264,'扎兰屯市',3),(2215,264,'额尔古纳市',3),(2216,264,'根河市',3),(2217,264,'阿荣旗',3),(2218,264,'鄂伦春自治旗',3),(2219,264,'鄂温克族自治旗',3),(2220,264,'陈巴尔虎旗',3),(2221,264,'新巴尔虎左旗',3),(2222,264,'新巴尔虎右旗',3),(2223,265,'科尔沁区',3),(2224,265,'霍林郭勒市',3),(2225,265,'科尔沁左翼中旗',3),(2226,265,'科尔沁左翼后旗',3),(2227,265,'开鲁县',3),(2228,265,'库伦旗',3),(2229,265,'奈曼旗',3),(2230,265,'扎鲁特旗',3),(2231,266,'海勃湾区',3),(2232,266,'乌达区',3),(2233,266,'海南区',3),(2234,267,'化德县',3),(2235,267,'集宁区',3),(2236,267,'丰镇市',3),(2237,267,'卓资县',3),(2238,267,'商都县',3),(2239,267,'兴和县',3),(2240,267,'凉城县',3),(2241,267,'察哈尔右翼前旗',3),(2242,267,'察哈尔右翼中旗',3),(2243,267,'察哈尔右翼后旗',3),(2244,267,'四子王旗',3),(2245,268,'二连浩特市',3),(2246,268,'锡林浩特市',3),(2247,268,'阿巴嘎旗',3),(2248,268,'苏尼特左旗',3),(2249,268,'苏尼特右旗',3),(2250,268,'东乌珠穆沁旗',3),(2251,268,'西乌珠穆沁旗',3),(2252,268,'太仆寺旗',3),(2253,268,'镶黄旗',3),(2254,268,'正镶白旗',3),(2255,268,'正蓝旗',3),(2256,268,'多伦县',3),(2257,269,'乌兰浩特市',3),(2258,269,'阿尔山市',3),(2259,269,'科尔沁右翼前旗',3),(2260,269,'科尔沁右翼中旗',3),(2261,269,'扎赉特旗',3),(2262,269,'突泉县',3),(2263,270,'西夏区',3),(2264,270,'金凤区',3),(2265,270,'兴庆区',3),(2266,270,'灵武市',3),(2267,270,'永宁县',3),(2268,270,'贺兰县',3),(2269,271,'原州区',3),(2270,271,'海原县',3),(2271,271,'西吉县',3),(2272,271,'隆德县',3),(2273,271,'泾源县',3),(2274,271,'彭阳县',3),(2275,272,'惠农县',3),(2276,272,'大武口区',3),(2277,272,'惠农区',3),(2278,272,'陶乐县',3),(2279,272,'平罗县',3),(2280,273,'利通区',3),(2281,273,'中卫县',3),(2282,273,'青铜峡市',3),(2283,273,'中宁县',3),(2284,273,'盐池县',3),(2285,273,'同心县',3),(2286,274,'沙坡头区',3),(2287,274,'海原县',3),(2288,274,'中宁县',3),(2289,275,'城中区',3),(2290,275,'城东区',3),(2291,275,'城西区',3),(2292,275,'城北区',3),(2293,275,'湟中县',3),(2294,275,'湟源县',3),(2295,275,'大通',3),(2296,276,'玛沁县',3),(2297,276,'班玛县',3),(2298,276,'甘德县',3),(2299,276,'达日县',3),(2300,276,'久治县',3),(2301,276,'玛多县',3),(2302,277,'海晏县',3),(2303,277,'祁连县',3),(2304,277,'刚察县',3),(2305,277,'门源',3),(2306,278,'平安县',3),(2307,278,'乐都县',3),(2308,278,'民和',3),(2309,278,'互助',3),(2310,278,'化隆',3),(2311,278,'循化',3),(2312,279,'共和县',3),(2313,279,'同德县',3),(2314,279,'贵德县',3),(2315,279,'兴海县',3),(2316,279,'贵南县',3),(2317,280,'德令哈市',3),(2318,280,'格尔木市',3),(2319,280,'乌兰县',3),(2320,280,'都兰县',3),(2321,280,'天峻县',3),(2322,281,'同仁县',3),(2323,281,'尖扎县',3),(2324,281,'泽库县',3),(2325,281,'河南蒙古族自治县',3),(2326,282,'玉树县',3),(2327,282,'杂多县',3),(2328,282,'称多县',3),(2329,282,'治多县',3),(2330,282,'囊谦县',3),(2331,282,'曲麻莱县',3),(2332,283,'市中区',3),(2333,283,'历下区',3),(2334,283,'天桥区',3),(2335,283,'槐荫区',3),(2336,283,'历城区',3),(2337,283,'长清区',3),(2338,283,'章丘市',3),(2339,283,'平阴县',3),(2340,283,'济阳县',3),(2341,283,'商河县',3),(2342,284,'市南区',3),(2343,284,'市北区',3),(2344,284,'城阳区',3),(2345,284,'四方区',3),(2346,284,'李沧区',3),(2347,284,'黄岛区',3),(2348,284,'崂山区',3),(2349,284,'胶州市',3),(2350,284,'即墨市',3),(2351,284,'平度市',3),(2352,284,'胶南市',3),(2353,284,'莱西市',3),(2354,285,'滨城区',3),(2355,285,'惠民县',3),(2356,285,'阳信县',3),(2357,285,'无棣县',3),(2358,285,'沾化县',3),(2359,285,'博兴县',3),(2360,285,'邹平县',3),(2361,286,'德城区',3),(2362,286,'陵县',3),(2363,286,'乐陵市',3),(2364,286,'禹城市',3),(2365,286,'宁津县',3),(2366,286,'庆云县',3),(2367,286,'临邑县',3),(2368,286,'齐河县',3),(2369,286,'平原县',3),(2370,286,'夏津县',3),(2371,286,'武城县',3),(2372,287,'东营区',3),(2373,287,'河口区',3),(2374,287,'垦利县',3),(2375,287,'利津县',3),(2376,287,'广饶县',3),(2377,288,'牡丹区',3),(2378,288,'曹县',3),(2379,288,'单县',3),(2380,288,'成武县',3),(2381,288,'巨野县',3),(2382,288,'郓城县',3),(2383,288,'鄄城县',3),(2384,288,'定陶县',3),(2385,288,'东明县',3),(2386,289,'市中区',3),(2387,289,'任城区',3),(2388,289,'曲阜市',3),(2389,289,'兖州市',3),(2390,289,'邹城市',3),(2391,289,'微山县',3),(2392,289,'鱼台县',3),(2393,289,'金乡县',3),(2394,289,'嘉祥县',3),(2395,289,'汶上县',3),(2396,289,'泗水县',3),(2397,289,'梁山县',3),(2398,290,'莱城区',3),(2399,290,'钢城区',3),(2400,291,'东昌府区',3),(2401,291,'临清市',3),(2402,291,'阳谷县',3),(2403,291,'莘县',3),(2404,291,'茌平县',3),(2405,291,'东阿县',3),(2406,291,'冠县',3),(2407,291,'高唐县',3),(2408,292,'兰山区',3),(2409,292,'罗庄区',3),(2410,292,'河东区',3),(2411,292,'沂南县',3),(2412,292,'郯城县',3),(2413,292,'沂水县',3),(2414,292,'苍山县',3),(2415,292,'费县',3),(2416,292,'平邑县',3),(2417,292,'莒南县',3),(2418,292,'蒙阴县',3),(2419,292,'临沭县',3),(2420,293,'东港区',3),(2421,293,'岚山区',3),(2422,293,'五莲县',3),(2423,293,'莒县',3),(2424,294,'泰山区',3),(2425,294,'岱岳区',3),(2426,294,'新泰市',3),(2427,294,'肥城市',3),(2428,294,'宁阳县',3),(2429,294,'东平县',3),(2430,295,'荣成市',3),(2431,295,'乳山市',3),(2432,295,'环翠区',3),(2433,295,'文登市',3),(2434,296,'潍城区',3),(2435,296,'寒亭区',3),(2436,296,'坊子区',3),(2437,296,'奎文区',3),(2438,296,'青州市',3),(2439,296,'诸城市',3),(2440,296,'寿光市',3),(2441,296,'安丘市',3),(2442,296,'高密市',3),(2443,296,'昌邑市',3),(2444,296,'临朐县',3),(2445,296,'昌乐县',3),(2446,297,'芝罘区',3),(2447,297,'福山区',3),(2448,297,'牟平区',3),(2449,297,'莱山区',3),(2450,297,'开发区',3),(2451,297,'龙口市',3),(2452,297,'莱阳市',3),(2453,297,'莱州市',3),(2454,297,'蓬莱市',3),(2455,297,'招远市',3),(2456,297,'栖霞市',3),(2457,297,'海阳市',3),(2458,297,'长岛县',3),(2459,298,'市中区',3),(2460,298,'山亭区',3),(2461,298,'峄城区',3),(2462,298,'台儿庄区',3),(2463,298,'薛城区',3),(2464,298,'滕州市',3),(2465,299,'张店区',3),(2466,299,'临淄区',3),(2467,299,'淄川区',3),(2468,299,'博山区',3),(2469,299,'周村区',3),(2470,299,'桓台县',3),(2471,299,'高青县',3),(2472,299,'沂源县',3),(2473,300,'杏花岭区',3),(2474,300,'小店区',3),(2475,300,'迎泽区',3),(2476,300,'尖草坪区',3),(2477,300,'万柏林区',3),(2478,300,'晋源区',3),(2479,300,'高新开发区',3),(2480,300,'民营经济开发区',3),(2481,300,'经济技术开发区',3),(2482,300,'清徐县',3),(2483,300,'阳曲县',3),(2484,300,'娄烦县',3),(2485,300,'古交市',3),(2486,301,'城区',3),(2487,301,'郊区',3),(2488,301,'沁县',3),(2489,301,'潞城市',3),(2490,301,'长治县',3),(2491,301,'襄垣县',3),(2492,301,'屯留县',3),(2493,301,'平顺县',3),(2494,301,'黎城县',3),(2495,301,'壶关县',3),(2496,301,'长子县',3),(2497,301,'武乡县',3),(2498,301,'沁源县',3),(2499,302,'城区',3),(2500,302,'矿区',3),(2501,302,'南郊区',3),(2502,302,'新荣区',3),(2503,302,'阳高县',3),(2504,302,'天镇县',3),(2505,302,'广灵县',3),(2506,302,'灵丘县',3),(2507,302,'浑源县',3),(2508,302,'左云县',3),(2509,302,'大同县',3),(2510,303,'城区',3),(2511,303,'高平市',3),(2512,303,'沁水县',3),(2513,303,'阳城县',3),(2514,303,'陵川县',3),(2515,303,'泽州县',3),(2516,304,'榆次区',3),(2517,304,'介休市',3),(2518,304,'榆社县',3),(2519,304,'左权县',3),(2520,304,'和顺县',3),(2521,304,'昔阳县',3),(2522,304,'寿阳县',3),(2523,304,'太谷县',3),(2524,304,'祁县',3),(2525,304,'平遥县',3),(2526,304,'灵石县',3),(2527,305,'尧都区',3),(2528,305,'侯马市',3),(2529,305,'霍州市',3),(2530,305,'曲沃县',3),(2531,305,'翼城县',3),(2532,305,'襄汾县',3),(2533,305,'洪洞县',3),(2534,305,'吉县',3),(2535,305,'安泽县',3),(2536,305,'浮山县',3),(2537,305,'古县',3),(2538,305,'乡宁县',3),(2539,305,'大宁县',3),(2540,305,'隰县',3),(2541,305,'永和县',3),(2542,305,'蒲县',3),(2543,305,'汾西县',3),(2544,306,'离石市',3),(2545,306,'离石区',3),(2546,306,'孝义市',3),(2547,306,'汾阳市',3),(2548,306,'文水县',3),(2549,306,'交城县',3),(2550,306,'兴县',3),(2551,306,'临县',3),(2552,306,'柳林县',3),(2553,306,'石楼县',3),(2554,306,'岚县',3),(2555,306,'方山县',3),(2556,306,'中阳县',3),(2557,306,'交口县',3),(2558,307,'朔城区',3),(2559,307,'平鲁区',3),(2560,307,'山阴县',3),(2561,307,'应县',3),(2562,307,'右玉县',3),(2563,307,'怀仁县',3),(2564,308,'忻府区',3),(2565,308,'原平市',3),(2566,308,'定襄县',3),(2567,308,'五台县',3),(2568,308,'代县',3),(2569,308,'繁峙县',3),(2570,308,'宁武县',3),(2571,308,'静乐县',3),(2572,308,'神池县',3),(2573,308,'五寨县',3),(2574,308,'岢岚县',3),(2575,308,'河曲县',3),(2576,308,'保德县',3),(2577,308,'偏关县',3),(2578,309,'城区',3),(2579,309,'矿区',3),(2580,309,'郊区',3),(2581,309,'平定县',3),(2582,309,'盂县',3),(2583,310,'盐湖区',3),(2584,310,'永济市',3),(2585,310,'河津市',3),(2586,310,'临猗县',3),(2587,310,'万荣县',3),(2588,310,'闻喜县',3),(2589,310,'稷山县',3),(2590,310,'新绛县',3),(2591,310,'绛县',3),(2592,310,'垣曲县',3),(2593,310,'夏县',3),(2594,310,'平陆县',3),(2595,310,'芮城县',3),(2596,311,'莲湖区',3),(2597,311,'新城区',3),(2598,311,'碑林区',3),(2599,311,'雁塔区',3),(2600,311,'灞桥区',3),(2601,311,'未央区',3),(2602,311,'阎良区',3),(2603,311,'临潼区',3),(2604,311,'长安区',3),(2605,311,'蓝田县',3),(2606,311,'周至县',3),(2607,311,'户县',3),(2608,311,'高陵县',3),(2609,312,'汉滨区',3),(2610,312,'汉阴县',3),(2611,312,'石泉县',3),(2612,312,'宁陕县',3),(2613,312,'紫阳县',3),(2614,312,'岚皋县',3),(2615,312,'平利县',3),(2616,312,'镇坪县',3),(2617,312,'旬阳县',3),(2618,312,'白河县',3),(2619,313,'陈仓区',3),(2620,313,'渭滨区',3),(2621,313,'金台区',3),(2622,313,'凤翔县',3),(2623,313,'岐山县',3),(2624,313,'扶风县',3),(2625,313,'眉县',3),(2626,313,'陇县',3),(2627,313,'千阳县',3),(2628,313,'麟游县',3),(2629,313,'凤县',3),(2630,313,'太白县',3),(2631,314,'汉台区',3),(2632,314,'南郑县',3),(2633,314,'城固县',3),(2634,314,'洋县',3),(2635,314,'西乡县',3),(2636,314,'勉县',3),(2637,314,'宁强县',3),(2638,314,'略阳县',3),(2639,314,'镇巴县',3),(2640,314,'留坝县',3),(2641,314,'佛坪县',3),(2642,315,'商州区',3),(2643,315,'洛南县',3),(2644,315,'丹凤县',3),(2645,315,'商南县',3),(2646,315,'山阳县',3),(2647,315,'镇安县',3),(2648,315,'柞水县',3),(2649,316,'耀州区',3),(2650,316,'王益区',3),(2651,316,'印台区',3),(2652,316,'宜君县',3),(2653,317,'临渭区',3),(2654,317,'韩城市',3),(2655,317,'华阴市',3),(2656,317,'华县',3),(2657,317,'潼关县',3),(2658,317,'大荔县',3),(2659,317,'合阳县',3),(2660,317,'澄城县',3),(2661,317,'蒲城县',3),(2662,317,'白水县',3),(2663,317,'富平县',3),(2664,318,'秦都区',3),(2665,318,'渭城区',3),(2666,318,'杨陵区',3),(2667,318,'兴平市',3),(2668,318,'三原县',3),(2669,318,'泾阳县',3),(2670,318,'乾县',3),(2671,318,'礼泉县',3),(2672,318,'永寿县',3),(2673,318,'彬县',3),(2674,318,'长武县',3),(2675,318,'旬邑县',3),(2676,318,'淳化县',3),(2677,318,'武功县',3),(2678,319,'吴起县',3),(2679,319,'宝塔区',3),(2680,319,'延长县',3),(2681,319,'延川县',3),(2682,319,'子长县',3),(2683,319,'安塞县',3),(2684,319,'志丹县',3),(2685,319,'甘泉县',3),(2686,319,'富县',3),(2687,319,'洛川县',3),(2688,319,'宜川县',3),(2689,319,'黄龙县',3),(2690,319,'黄陵县',3),(2691,320,'榆阳区',3),(2692,320,'神木县',3),(2693,320,'府谷县',3),(2694,320,'横山县',3),(2695,320,'靖边县',3),(2696,320,'定边县',3),(2697,320,'绥德县',3),(2698,320,'米脂县',3),(2699,320,'佳县',3),(2700,320,'吴堡县',3),(2701,320,'清涧县',3),(2702,320,'子洲县',3),(2703,321,'长宁区',3),(2704,321,'闸北区',3),(2705,321,'闵行区',3),(2706,321,'徐汇区',3),(2707,321,'浦东新区',3),(2708,321,'杨浦区',3),(2709,321,'普陀区',3),(2710,321,'静安区',3),(2711,321,'卢湾区',3),(2712,321,'虹口区',3),(2713,321,'黄浦区',3),(2714,321,'南汇区',3),(2715,321,'松江区',3),(2716,321,'嘉定区',3),(2717,321,'宝山区',3),(2718,321,'青浦区',3),(2719,321,'金山区',3),(2720,321,'奉贤区',3),(2721,321,'崇明县',3),(2722,322,'青羊区',3),(2723,322,'锦江区',3),(2724,322,'金牛区',3),(2725,322,'武侯区',3),(2726,322,'成华区',3),(2727,322,'龙泉驿区',3),(2728,322,'青白江区',3),(2729,322,'新都区',3),(2730,322,'温江区',3),(2731,322,'高新区',3),(2732,322,'高新西区',3),(2733,322,'都江堰市',3),(2734,322,'彭州市',3),(2735,322,'邛崃市',3),(2736,322,'崇州市',3),(2737,322,'金堂县',3),(2738,322,'双流县',3),(2739,322,'郫县',3),(2740,322,'大邑县',3),(2741,322,'蒲江县',3),(2742,322,'新津县',3),(2743,322,'都江堰市',3),(2744,322,'彭州市',3),(2745,322,'邛崃市',3),(2746,322,'崇州市',3),(2747,322,'金堂县',3),(2748,322,'双流县',3),(2749,322,'郫县',3),(2750,322,'大邑县',3),(2751,322,'蒲江县',3),(2752,322,'新津县',3),(2753,323,'涪城区',3),(2754,323,'游仙区',3),(2755,323,'江油市',3),(2756,323,'盐亭县',3),(2757,323,'三台县',3),(2758,323,'平武县',3),(2759,323,'安县',3),(2760,323,'梓潼县',3),(2761,323,'北川县',3),(2762,324,'马尔康县',3),(2763,324,'汶川县',3),(2764,324,'理县',3),(2765,324,'茂县',3),(2766,324,'松潘县',3),(2767,324,'九寨沟县',3),(2768,324,'金川县',3),(2769,324,'小金县',3),(2770,324,'黑水县',3),(2771,324,'壤塘县',3),(2772,324,'阿坝县',3),(2773,324,'若尔盖县',3),(2774,324,'红原县',3),(2775,325,'巴州区',3),(2776,325,'通江县',3),(2777,325,'南江县',3),(2778,325,'平昌县',3),(2779,326,'通川区',3),(2780,326,'万源市',3),(2781,326,'达县',3),(2782,326,'宣汉县',3),(2783,326,'开江县',3),(2784,326,'大竹县',3),(2785,326,'渠县',3),(2786,327,'旌阳区',3),(2787,327,'广汉市',3),(2788,327,'什邡市',3),(2789,327,'绵竹市',3),(2790,327,'罗江县',3),(2791,327,'中江县',3),(2792,328,'康定县',3),(2793,328,'丹巴县',3),(2794,328,'泸定县',3),(2795,328,'炉霍县',3),(2796,328,'九龙县',3),(2797,328,'甘孜县',3),(2798,328,'雅江县',3),(2799,328,'新龙县',3),(2800,328,'道孚县',3),(2801,328,'白玉县',3),(2802,328,'理塘县',3),(2803,328,'德格县',3),(2804,328,'乡城县',3),(2805,328,'石渠县',3),(2806,328,'稻城县',3),(2807,328,'色达县',3),(2808,328,'巴塘县',3),(2809,328,'得荣县',3),(2810,329,'广安区',3),(2811,329,'华蓥市',3),(2812,329,'岳池县',3),(2813,329,'武胜县',3),(2814,329,'邻水县',3),(2815,330,'利州区',3),(2816,330,'元坝区',3),(2817,330,'朝天区',3),(2818,330,'旺苍县',3),(2819,330,'青川县',3),(2820,330,'剑阁县',3),(2821,330,'苍溪县',3),(2822,331,'峨眉山市',3),(2823,331,'乐山市',3),(2824,331,'犍为县',3),(2825,331,'井研县',3),(2826,331,'夹江县',3),(2827,331,'沐川县',3),(2828,331,'峨边',3),(2829,331,'马边',3),(2830,332,'西昌市',3),(2831,332,'盐源县',3),(2832,332,'德昌县',3),(2833,332,'会理县',3),(2834,332,'会东县',3),(2835,332,'宁南县',3),(2836,332,'普格县',3),(2837,332,'布拖县',3),(2838,332,'金阳县',3),(2839,332,'昭觉县',3),(2840,332,'喜德县',3),(2841,332,'冕宁县',3),(2842,332,'越西县',3),(2843,332,'甘洛县',3),(2844,332,'美姑县',3),(2845,332,'雷波县',3),(2846,332,'木里',3),(2847,333,'东坡区',3),(2848,333,'仁寿县',3),(2849,333,'彭山县',3),(2850,333,'洪雅县',3),(2851,333,'丹棱县',3),(2852,333,'青神县',3),(2853,334,'阆中市',3),(2854,334,'南部县',3),(2855,334,'营山县',3),(2856,334,'蓬安县',3),(2857,334,'仪陇县',3),(2858,334,'顺庆区',3),(2859,334,'高坪区',3),(2860,334,'嘉陵区',3),(2861,334,'西充县',3),(2862,335,'市中区',3),(2863,335,'东兴区',3),(2864,335,'威远县',3),(2865,335,'资中县',3),(2866,335,'隆昌县',3),(2867,336,'东  区',3),(2868,336,'西  区',3),(2869,336,'仁和区',3),(2870,336,'米易县',3),(2871,336,'盐边县',3),(2872,337,'船山区',3),(2873,337,'安居区',3),(2874,337,'蓬溪县',3),(2875,337,'射洪县',3),(2876,337,'大英县',3),(2877,338,'雨城区',3),(2878,338,'名山县',3),(2879,338,'荥经县',3),(2880,338,'汉源县',3),(2881,338,'石棉县',3),(2882,338,'天全县',3),(2883,338,'芦山县',3),(2884,338,'宝兴县',3),(2885,339,'翠屏区',3),(2886,339,'宜宾县',3),(2887,339,'南溪县',3),(2888,339,'江安县',3),(2889,339,'长宁县',3),(2890,339,'高县',3),(2891,339,'珙县',3),(2892,339,'筠连县',3),(2893,339,'兴文县',3),(2894,339,'屏山县',3),(2895,340,'雁江区',3),(2896,340,'简阳市',3),(2897,340,'安岳县',3),(2898,340,'乐至县',3),(2899,341,'大安区',3),(2900,341,'自流井区',3),(2901,341,'贡井区',3),(2902,341,'沿滩区',3),(2903,341,'荣县',3),(2904,341,'富顺县',3),(2905,342,'江阳区',3),(2906,342,'纳溪区',3),(2907,342,'龙马潭区',3),(2908,342,'泸县',3),(2909,342,'合江县',3),(2910,342,'叙永县',3),(2911,342,'古蔺县',3),(2912,343,'和平区',3),(2913,343,'河西区',3),(2914,343,'南开区',3),(2915,343,'河北区',3),(2916,343,'河东区',3),(2917,343,'红桥区',3),(2918,343,'东丽区',3),(2919,343,'津南区',3),(2920,343,'西青区',3),(2921,343,'北辰区',3),(2922,343,'塘沽区',3),(2923,343,'汉沽区',3),(2924,343,'大港区',3),(2925,343,'武清区',3),(2926,343,'宝坻区',3),(2927,343,'经济开发区',3),(2928,343,'宁河县',3),(2929,343,'静海县',3),(2930,343,'蓟县',3),(2931,344,'城关区',3),(2932,344,'林周县',3),(2933,344,'当雄县',3),(2934,344,'尼木县',3),(2935,344,'曲水县',3),(2936,344,'堆龙德庆县',3),(2937,344,'达孜县',3),(2938,344,'墨竹工卡县',3),(2939,345,'噶尔县',3),(2940,345,'普兰县',3),(2941,345,'札达县',3),(2942,345,'日土县',3),(2943,345,'革吉县',3),(2944,345,'改则县',3),(2945,345,'措勤县',3),(2946,346,'昌都县',3),(2947,346,'江达县',3),(2948,346,'贡觉县',3),(2949,346,'类乌齐县',3),(2950,346,'丁青县',3),(2951,346,'察雅县',3),(2952,346,'八宿县',3),(2953,346,'左贡县',3),(2954,346,'芒康县',3),(2955,346,'洛隆县',3),(2956,346,'边坝县',3),(2957,347,'林芝县',3),(2958,347,'工布江达县',3),(2959,347,'米林县',3),(2960,347,'墨脱县',3),(2961,347,'波密县',3),(2962,347,'察隅县',3),(2963,347,'朗县',3),(2964,348,'那曲县',3),(2965,348,'嘉黎县',3),(2966,348,'比如县',3),(2967,348,'聂荣县',3),(2968,348,'安多县',3),(2969,348,'申扎县',3),(2970,348,'索县',3),(2971,348,'班戈县',3),(2972,348,'巴青县',3),(2973,348,'尼玛县',3),(2974,349,'日喀则市',3),(2975,349,'南木林县',3),(2976,349,'江孜县',3),(2977,349,'定日县',3),(2978,349,'萨迦县',3),(2979,349,'拉孜县',3),(2980,349,'昂仁县',3),(2981,349,'谢通门县',3),(2982,349,'白朗县',3),(2983,349,'仁布县',3),(2984,349,'康马县',3),(2985,349,'定结县',3),(2986,349,'仲巴县',3),(2987,349,'亚东县',3),(2988,349,'吉隆县',3),(2989,349,'聂拉木县',3),(2990,349,'萨嘎县',3),(2991,349,'岗巴县',3),(2992,350,'乃东县',3),(2993,350,'扎囊县',3),(2994,350,'贡嘎县',3),(2995,350,'桑日县',3),(2996,350,'琼结县',3),(2997,350,'曲松县',3),(2998,350,'措美县',3),(2999,350,'洛扎县',3),(3000,350,'加查县',3),(3001,350,'隆子县',3),(3002,350,'错那县',3),(3003,350,'浪卡子县',3),(3004,351,'天山区',3),(3005,351,'沙依巴克区',3),(3006,351,'新市区',3),(3007,351,'水磨沟区',3),(3008,351,'头屯河区',3),(3009,351,'达坂城区',3),(3010,351,'米东区',3),(3011,351,'乌鲁木齐县',3),(3012,352,'阿克苏市',3),(3013,352,'温宿县',3),(3014,352,'库车县',3),(3015,352,'沙雅县',3),(3016,352,'新和县',3),(3017,352,'拜城县',3),(3018,352,'乌什县',3),(3019,352,'阿瓦提县',3),(3020,352,'柯坪县',3),(3021,353,'阿拉尔市',3),(3022,354,'库尔勒市',3),(3023,354,'轮台县',3),(3024,354,'尉犁县',3),(3025,354,'若羌县',3),(3026,354,'且末县',3),(3027,354,'焉耆',3),(3028,354,'和静县',3),(3029,354,'和硕县',3),(3030,354,'博湖县',3),(3031,355,'博乐市',3),(3032,355,'精河县',3),(3033,355,'温泉县',3),(3034,356,'呼图壁县',3),(3035,356,'米泉市',3),(3036,356,'昌吉市',3),(3037,356,'阜康市',3),(3038,356,'玛纳斯县',3),(3039,356,'奇台县',3),(3040,356,'吉木萨尔县',3),(3041,356,'木垒',3),(3042,357,'哈密市',3),(3043,357,'伊吾县',3),(3044,357,'巴里坤',3),(3045,358,'和田市',3),(3046,358,'和田县',3),(3047,358,'墨玉县',3),(3048,358,'皮山县',3),(3049,358,'洛浦县',3),(3050,358,'策勒县',3),(3051,358,'于田县',3),(3052,358,'民丰县',3),(3053,359,'喀什市',3),(3054,359,'疏附县',3),(3055,359,'疏勒县',3),(3056,359,'英吉沙县',3),(3057,359,'泽普县',3),(3058,359,'莎车县',3),(3059,359,'叶城县',3),(3060,359,'麦盖提县',3),(3061,359,'岳普湖县',3),(3062,359,'伽师县',3),(3063,359,'巴楚县',3),(3064,359,'塔什库尔干',3),(3065,360,'克拉玛依市',3),(3066,361,'阿图什市',3),(3067,361,'阿克陶县',3),(3068,361,'阿合奇县',3),(3069,361,'乌恰县',3),(3070,362,'石河子市',3),(3071,363,'图木舒克市',3),(3072,364,'吐鲁番市',3),(3073,364,'鄯善县',3),(3074,364,'托克逊县',3),(3075,365,'五家渠市',3),(3076,366,'阿勒泰市',3),(3077,366,'布克赛尔',3),(3078,366,'伊宁市',3),(3079,366,'布尔津县',3),(3080,366,'奎屯市',3),(3081,366,'乌苏市',3),(3082,366,'额敏县',3),(3083,366,'富蕴县',3),(3084,366,'伊宁县',3),(3085,366,'福海县',3),(3086,366,'霍城县',3),(3087,366,'沙湾县',3),(3088,366,'巩留县',3),(3089,366,'哈巴河县',3),(3090,366,'托里县',3),(3091,366,'青河县',3),(3092,366,'新源县',3),(3093,366,'裕民县',3),(3094,366,'和布克赛尔',3),(3095,366,'吉木乃县',3),(3096,366,'昭苏县',3),(3097,366,'特克斯县',3),(3098,366,'尼勒克县',3),(3099,366,'察布查尔',3),(3100,367,'盘龙区',3),(3101,367,'五华区',3),(3102,367,'官渡区',3),(3103,367,'西山区',3),(3104,367,'东川区',3),(3105,367,'安宁市',3),(3106,367,'呈贡县',3),(3107,367,'晋宁县',3),(3108,367,'富民县',3),(3109,367,'宜良县',3),(3110,367,'嵩明县',3),(3111,367,'石林县',3),(3112,367,'禄劝',3),(3113,367,'寻甸',3),(3114,368,'兰坪',3),(3115,368,'泸水县',3),(3116,368,'福贡县',3),(3117,368,'贡山',3),(3118,369,'宁洱',3),(3119,369,'思茅区',3),(3120,369,'墨江',3),(3121,369,'景东',3),(3122,369,'景谷',3),(3123,369,'镇沅',3),(3124,369,'江城',3),(3125,369,'孟连',3),(3126,369,'澜沧',3),(3127,369,'西盟',3),(3128,370,'古城区',3),(3129,370,'宁蒗',3),(3130,370,'玉龙',3),(3131,370,'永胜县',3),(3132,370,'华坪县',3),(3133,371,'隆阳区',3),(3134,371,'施甸县',3),(3135,371,'腾冲县',3),(3136,371,'龙陵县',3),(3137,371,'昌宁县',3),(3138,372,'楚雄市',3),(3139,372,'双柏县',3),(3140,372,'牟定县',3),(3141,372,'南华县',3),(3142,372,'姚安县',3),(3143,372,'大姚县',3),(3144,372,'永仁县',3),(3145,372,'元谋县',3),(3146,372,'武定县',3),(3147,372,'禄丰县',3),(3148,373,'大理市',3),(3149,373,'祥云县',3),(3150,373,'宾川县',3),(3151,373,'弥渡县',3),(3152,373,'永平县',3),(3153,373,'云龙县',3),(3154,373,'洱源县',3),(3155,373,'剑川县',3),(3156,373,'鹤庆县',3),(3157,373,'漾濞',3),(3158,373,'南涧',3),(3159,373,'巍山',3),(3160,374,'潞西市',3),(3161,374,'瑞丽市',3),(3162,374,'梁河县',3),(3163,374,'盈江县',3),(3164,374,'陇川县',3),(3165,375,'香格里拉县',3),(3166,375,'德钦县',3),(3167,375,'维西',3),(3168,376,'泸西县',3),(3169,376,'蒙自县',3),(3170,376,'个旧市',3),(3171,376,'开远市',3),(3172,376,'绿春县',3),(3173,376,'建水县',3),(3174,376,'石屏县',3),(3175,376,'弥勒县',3),(3176,376,'元阳县',3),(3177,376,'红河县',3),(3178,376,'金平',3),(3179,376,'河口',3),(3180,376,'屏边',3),(3181,377,'临翔区',3),(3182,377,'凤庆县',3),(3183,377,'云县',3),(3184,377,'永德县',3),(3185,377,'镇康县',3),(3186,377,'双江',3),(3187,377,'耿马',3),(3188,377,'沧源',3),(3189,378,'麒麟区',3),(3190,378,'宣威市',3),(3191,378,'马龙县',3),(3192,378,'陆良县',3),(3193,378,'师宗县',3),(3194,378,'罗平县',3),(3195,378,'富源县',3),(3196,378,'会泽县',3),(3197,378,'沾益县',3),(3198,379,'文山县',3),(3199,379,'砚山县',3),(3200,379,'西畴县',3),(3201,379,'麻栗坡县',3),(3202,379,'马关县',3),(3203,379,'丘北县',3),(3204,379,'广南县',3),(3205,379,'富宁县',3),(3206,380,'景洪市',3),(3207,380,'勐海县',3),(3208,380,'勐腊县',3),(3209,381,'红塔区',3),(3210,381,'江川县',3),(3211,381,'澄江县',3),(3212,381,'通海县',3),(3213,381,'华宁县',3),(3214,381,'易门县',3),(3215,381,'峨山',3),(3216,381,'新平',3),(3217,381,'元江',3),(3218,382,'昭阳区',3),(3219,382,'鲁甸县',3),(3220,382,'巧家县',3),(3221,382,'盐津县',3),(3222,382,'大关县',3),(3223,382,'永善县',3),(3224,382,'绥江县',3),(3225,382,'镇雄县',3),(3226,382,'彝良县',3),(3227,382,'威信县',3),(3228,382,'水富县',3),(3229,383,'西湖区',3),(3230,383,'上城区',3),(3231,383,'下城区',3),(3232,383,'拱墅区',3),(3233,383,'滨江区',3),(3234,383,'江干区',3),(3235,383,'萧山区',3),(3236,383,'余杭区',3),(3237,383,'市郊',3),(3238,383,'建德市',3),(3239,383,'富阳市',3),(3240,383,'临安市',3),(3241,383,'桐庐县',3),(3242,383,'淳安县',3),(3243,384,'吴兴区',3),(3244,384,'南浔区',3),(3245,384,'德清县',3),(3246,384,'长兴县',3),(3247,384,'安吉县',3),(3248,385,'南湖区',3),(3249,385,'秀洲区',3),(3250,385,'海宁市',3),(3251,385,'嘉善县',3),(3252,385,'平湖市',3),(3253,385,'桐乡市',3),(3254,385,'海盐县',3),(3255,386,'婺城区',3),(3256,386,'金东区',3),(3257,386,'兰溪市',3),(3258,386,'市区',3),(3259,386,'佛堂镇',3),(3260,386,'上溪镇',3),(3261,386,'义亭镇',3),(3262,386,'大陈镇',3),(3263,386,'苏溪镇',3),(3264,386,'赤岸镇',3),(3265,386,'东阳市',3),(3266,386,'永康市',3),(3267,386,'武义县',3),(3268,386,'浦江县',3),(3269,386,'磐安县',3),(3270,387,'莲都区',3),(3271,387,'龙泉市',3),(3272,387,'青田县',3),(3273,387,'缙云县',3),(3274,387,'遂昌县',3),(3275,387,'松阳县',3),(3276,387,'云和县',3),(3277,387,'庆元县',3),(3278,387,'景宁',3),(3279,388,'海曙区',3),(3280,388,'江东区',3),(3281,388,'江北区',3),(3282,388,'镇海区',3),(3283,388,'北仑区',3),(3284,388,'鄞州区',3),(3285,388,'余姚市',3),(3286,388,'慈溪市',3),(3287,388,'奉化市',3),(3288,388,'象山县',3),(3289,388,'宁海县',3),(3290,389,'越城区',3),(3291,389,'上虞市',3),(3292,389,'嵊州市',3),(3293,389,'绍兴县',3),(3294,389,'新昌县',3),(3295,389,'诸暨市',3),(3296,390,'椒江区',3),(3297,390,'黄岩区',3),(3298,390,'路桥区',3),(3299,390,'温岭市',3),(3300,390,'临海市',3),(3301,390,'玉环县',3),(3302,390,'三门县',3),(3303,390,'天台县',3),(3304,390,'仙居县',3),(3305,391,'鹿城区',3),(3306,391,'龙湾区',3),(3307,391,'瓯海区',3),(3308,391,'瑞安市',3),(3309,391,'乐清市',3),(3310,391,'洞头县',3),(3311,391,'永嘉县',3),(3312,391,'平阳县',3),(3313,391,'苍南县',3),(3314,391,'文成县',3),(3315,391,'泰顺县',3),(3316,392,'定海区',3),(3317,392,'普陀区',3),(3318,392,'岱山县',3),(3319,392,'嵊泗县',3),(3320,393,'衢州市',3),(3321,393,'江山市',3),(3322,393,'常山县',3),(3323,393,'开化县',3),(3324,393,'龙游县',3),(3325,394,'合川区',3),(3326,394,'江津区',3),(3327,394,'南川区',3),(3328,394,'永川区',3),(3329,394,'南岸区',3),(3330,394,'渝北区',3),(3331,394,'万盛区',3),(3332,394,'大渡口区',3),(3333,394,'万州区',3),(3334,394,'北碚区',3),(3335,394,'沙坪坝区',3),(3336,394,'巴南区',3),(3337,394,'涪陵区',3),(3338,394,'江北区',3),(3339,394,'九龙坡区',3),(3340,394,'渝中区',3),(3341,394,'黔江开发区',3),(3342,394,'长寿区',3),(3343,394,'双桥区',3),(3344,394,'綦江县',3),(3345,394,'潼南县',3),(3346,394,'铜梁县',3),(3347,394,'大足县',3),(3348,394,'荣昌县',3),(3349,394,'璧山县',3),(3350,394,'垫江县',3),(3351,394,'武隆县',3),(3352,394,'丰都县',3),(3353,394,'城口县',3),(3354,394,'梁平县',3),(3355,394,'开县',3),(3356,394,'巫溪县',3),(3357,394,'巫山县',3),(3358,394,'奉节县',3),(3359,394,'云阳县',3),(3360,394,'忠县',3),(3361,394,'石柱',3),(3362,394,'彭水',3),(3363,394,'酉阳',3),(3364,394,'秀山',3),(3365,395,'沙田区',3),(3366,395,'东区',3),(3367,395,'观塘区',3),(3368,395,'黄大仙区',3),(3369,395,'九龙城区',3),(3370,395,'屯门区',3),(3371,395,'葵青区',3),(3372,395,'元朗区',3),(3373,395,'深水埗区',3),(3374,395,'西贡区',3),(3375,395,'大埔区',3),(3376,395,'湾仔区',3),(3377,395,'油尖旺区',3),(3378,395,'北区',3),(3379,395,'南区',3),(3380,395,'荃湾区',3),(3381,395,'中西区',3),(3382,395,'离岛区',3),(3383,396,'澳门',3),(3384,397,'台北',3),(3385,397,'高雄',3),(3386,397,'基隆',3),(3387,397,'台中',3),(3388,397,'台南',3),(3389,397,'新竹',3),(3390,397,'嘉义',3),(3391,397,'宜兰县',3),(3392,397,'桃园县',3),(3393,397,'苗栗县',3),(3394,397,'彰化县',3),(3395,397,'南投县',3),(3396,397,'云林县',3),(3397,397,'屏东县',3),(3398,397,'台东县',3),(3399,397,'花莲县',3),(3400,397,'澎湖县',3),(3401,3,'合肥',2),(3402,3401,'庐阳区',3),(3403,3401,'瑶海区',3),(3404,3401,'蜀山区',3),(3405,3401,'包河区',3),(3406,3401,'长丰县',3),(3407,3401,'肥东县',3),(3408,3401,'肥西县',3),(3409,0,'国外',0);

UNLOCK TABLES;

/*Table structure for table `clt_role` */

DROP TABLE IF EXISTS `clt_role`;

CREATE TABLE `clt_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `clt_role` */

LOCK TABLES `clt_role` WRITE;

insert  into `clt_role`(`id`,`name`,`status`,`remark`,`pid`,`listorder`) values (1,'超级管理员',1,'超级管理',0,0),(2,'普通管理员',1,'普通管理员',0,0),(3,'注册用户',1,'注册用户',0,0),(4,'游客',1,'游客',0,0);

UNLOCK TABLES;

/*Table structure for table `clt_role_user` */

DROP TABLE IF EXISTS `clt_role_user`;

CREATE TABLE `clt_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT '0',
  `user_id` char(32) DEFAULT '0',
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `clt_role_user` */

LOCK TABLES `clt_role_user` WRITE;

UNLOCK TABLES;

/*Table structure for table `clt_sys` */

DROP TABLE IF EXISTS `clt_sys`;

CREATE TABLE `clt_sys` (
  `sys_id` int(36) unsigned NOT NULL,
  `sys_name` char(36) NOT NULL DEFAULT '',
  `sys_url` varchar(36) NOT NULL DEFAULT '',
  `sys_title` varchar(200) NOT NULL,
  `sys_key` varchar(200) NOT NULL,
  `sys_des` varchar(200) NOT NULL,
  `email_open` tinyint(2) NOT NULL COMMENT '邮箱发送是否开启',
  `email_name` varchar(50) NOT NULL COMMENT '发送邮箱账号',
  `email_pwd` varchar(50) NOT NULL COMMENT '发送邮箱密码',
  `email_smtpname` varchar(50) NOT NULL COMMENT 'smtp服务器账号',
  `email_emname` varchar(30) NOT NULL COMMENT '邮件名',
  `email_rename` varchar(30) NOT NULL COMMENT '发件人姓名',
  `wesys_name` varchar(30) NOT NULL COMMENT '微信名称',
  `wesys_number` varchar(30) NOT NULL COMMENT '微信号',
  `wesys_id` varchar(20) NOT NULL COMMENT '微信原始ID',
  `wesys_type` tinyint(2) NOT NULL COMMENT '1=订阅号 2=服务号',
  `wesys_appid` varchar(30) NOT NULL COMMENT '微信appid',
  `wesys_appsecret` varchar(50) NOT NULL COMMENT '微信AppSecret',
  `wesys_token` varchar(50) NOT NULL COMMENT '微信token',
  `bah` varchar(50) DEFAULT NULL COMMENT '备案号',
  `copyright` varchar(30) DEFAULT NULL COMMENT 'copyright',
  `ads` varchar(120) DEFAULT NULL COMMENT '公司地址',
  `tel` varchar(15) DEFAULT NULL COMMENT '公司电话',
  `email` varchar(50) DEFAULT NULL COMMENT '公司邮箱',
  PRIMARY KEY (`sys_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `clt_sys` */

LOCK TABLES `clt_sys` WRITE;

insert  into `clt_sys`(`sys_id`,`sys_name`,`sys_url`,`sys_title`,`sys_key`,`sys_des`,`email_open`,`email_name`,`email_pwd`,`email_smtpname`,`email_emname`,`email_rename`,`wesys_name`,`wesys_number`,`wesys_id`,`wesys_type`,`wesys_appid`,`wesys_appsecret`,`wesys_token`,`bah`,`copyright`,`ads`,`tel`,`email`) values (1,'CLTPHP','http://www.cltphp.com/','CLTPHP后台管理系统','CLTPHP,CLTPHP后台管理系统，thinkphp,thinkphp后台管理系统','CLTPHP后台管理系统，微信公众平台、APP移动应用设计、HTML5网站API定制开发。大型企业网站、个人博客论坛、手机网站定制开发。更高效、更快捷的进行定制开发。',1,'876902658@qq.com','maggie198586','smtp.qq.com','876902658','网站管理员','chichu','chichu12345','12231231231231111',1,'12312312312','123123123123123','weixin','陕ICP备15008093号','2015-2020','南京市白下区虎踞南路40-8号1-4楼','025-86530015','1109305987@qq.com');

UNLOCK TABLES;

/*Table structure for table `clt_wx_img` */

DROP TABLE IF EXISTS `clt_wx_img`;

CREATE TABLE `clt_wx_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `keyword` char(255) NOT NULL COMMENT '关键词',
  `desc` text NOT NULL COMMENT '简介',
  `pic` char(255) NOT NULL COMMENT '封面图片',
  `url` char(255) NOT NULL COMMENT '图文外链地址',
  `createtime` varchar(13) NOT NULL COMMENT '创建时间',
  `uptatetime` varchar(13) NOT NULL COMMENT '更新时间',
  `title` varchar(60) NOT NULL COMMENT '标题',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='微信图文';

/*Data for the table `clt_wx_img` */

LOCK TABLES `clt_wx_img` WRITE;

insert  into `clt_wx_img`(`id`,`keyword`,`desc`,`pic`,`url`,`createtime`,`uptatetime`,`title`) values (1,'iphone','漂亮的手机1千块钱抢购了','/uploads/20161212/9000ba8250cccd2be494d4241c56afe2.jpg','http://www.cltphp.com/','1487574144','','iphone 1千块限时抢购1'),(3,'手册','手册','/uploads/20170223/7d5c2a0b02176d0620f532765bd03c8a.png','','1487645725','','手册');

UNLOCK TABLES;

/*Table structure for table `clt_wx_keyword` */

DROP TABLE IF EXISTS `clt_wx_keyword`;

CREATE TABLE `clt_wx_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `keyword` char(255) NOT NULL COMMENT '关键词',
  `pid` int(11) NOT NULL COMMENT '对应表ID',
  `type` varchar(30) DEFAULT 'TEXT' COMMENT '关键词操作类型',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Data for the table `clt_wx_keyword` */

LOCK TABLES `clt_wx_keyword` WRITE;

insert  into `clt_wx_keyword`(`id`,`keyword`,`pid`,`type`) values (5,'早上好！',5,'TEXT'),(4,'你好',4,'TEXT'),(6,'iphone',1,'IMG'),(8,'手册',3,'IMG');

UNLOCK TABLES;

/*Table structure for table `clt_wx_menu` */

DROP TABLE IF EXISTS `clt_wx_menu`;

CREATE TABLE `clt_wx_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `open` tinyint(1) DEFAULT '1' COMMENT '状态',
  `pid` int(11) DEFAULT '0' COMMENT '上级菜单',
  `name` varchar(50) NOT NULL COMMENT '名称',
  `listorder` int(5) DEFAULT '0' COMMENT '排序',
  `type` varchar(20) DEFAULT '' COMMENT 'view/click',
  `value` varchar(255) DEFAULT NULL COMMENT 'value',
  `token` varchar(50) NOT NULL COMMENT 'token',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

/*Data for the table `clt_wx_menu` */

LOCK TABLES `clt_wx_menu` WRITE;

insert  into `clt_wx_menu`(`id`,`open`,`pid`,`name`,`listorder`,`type`,`value`,`token`) values (1,1,0,'官网',1,'view','http://www.cltphp.com','eesops1462769263'),(2,1,0,'文档',2,'click','文档','eesops1462769263'),(3,1,2,'操作文档',1,'view','http://www.kancloud.cn/chichu/cltphp','eesops1462769263'),(33,1,2,'开发文档',2,'view','http://www.kancloud.cn/chichu/cltphp','');

UNLOCK TABLES;

/*Table structure for table `clt_wx_text` */

DROP TABLE IF EXISTS `clt_wx_text`;

CREATE TABLE `clt_wx_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `uname` varchar(90) NOT NULL COMMENT '用户名',
  `keyword` char(255) NOT NULL COMMENT '关键词',
  `precisions` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'precisions',
  `text` text NOT NULL COMMENT 'text',
  `createtime` varchar(13) NOT NULL COMMENT '创建时间',
  `updatetime` varchar(13) NOT NULL COMMENT '更新时间',
  `click` int(11) NOT NULL COMMENT '点击',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='文本回复表';

/*Data for the table `clt_wx_text` */

LOCK TABLES `clt_wx_text` WRITE;

insert  into `clt_wx_text`(`id`,`uid`,`uname`,`keyword`,`precisions`,`text`,`createtime`,`updatetime`,`click`) values (5,0,'','早上好！',0,'早上好！','1487238755','1487317308',0),(4,0,'','你好',0,'你好','1487238744','',0);

UNLOCK TABLES;

/*Table structure for table `clt_wx_user` */

DROP TABLE IF EXISTS `clt_wx_user`;

CREATE TABLE `clt_wx_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `uid` int(11) NOT NULL COMMENT 'uid',
  `wxname` varchar(60) NOT NULL COMMENT '公众号名称',
  `aeskey` varchar(256) NOT NULL DEFAULT '' COMMENT 'aeskey',
  `encode` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'encode',
  `appid` varchar(50) NOT NULL DEFAULT '' COMMENT 'appid',
  `appsecret` varchar(50) NOT NULL DEFAULT '' COMMENT 'appsecret',
  `wxid` varchar(64) NOT NULL COMMENT '公众号原始ID',
  `weixin` char(64) NOT NULL COMMENT '微信号',
  `headerpic` char(255) NOT NULL COMMENT '头像地址',
  `token` char(255) NOT NULL COMMENT 'token',
  `w_token` varchar(150) NOT NULL DEFAULT '' COMMENT '微信对接token',
  `create_time` int(11) NOT NULL COMMENT 'create_time',
  `updatetime` int(11) NOT NULL COMMENT 'updatetime',
  `tplcontentid` varchar(2) NOT NULL COMMENT '内容模版ID',
  `share_ticket` varchar(150) NOT NULL COMMENT '分享ticket',
  `share_dated` char(15) NOT NULL COMMENT 'share_dated',
  `authorizer_access_token` varchar(200) NOT NULL COMMENT 'authorizer_access_token',
  `authorizer_refresh_token` varchar(200) NOT NULL COMMENT 'authorizer_refresh_token',
  `authorizer_expires` char(10) NOT NULL COMMENT 'authorizer_expires',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型',
  `web_access_token` varchar(200) NOT NULL COMMENT ' 网页授权token',
  `web_refresh_token` varchar(200) NOT NULL COMMENT 'web_refresh_token',
  `web_expires` int(11) NOT NULL COMMENT '过期时间',
  `qr` varchar(200) NOT NULL COMMENT 'qr',
  `menu_config` text COMMENT '菜单',
  `wait_access` tinyint(1) DEFAULT '0' COMMENT '微信接入状态,0待接入1已接入',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uid_2` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='微信公共帐号';

/*Data for the table `clt_wx_user` */

LOCK TABLES `clt_wx_user` WRITE;

insert  into `clt_wx_user`(`id`,`uid`,`wxname`,`aeskey`,`encode`,`appid`,`appsecret`,`wxid`,`weixin`,`headerpic`,`token`,`w_token`,`create_time`,`updatetime`,`tplcontentid`,`share_ticket`,`share_dated`,`authorizer_access_token`,`authorizer_refresh_token`,`authorizer_expires`,`type`,`web_access_token`,`web_refresh_token`,`web_expires`,`qr`,`menu_config`,`wait_access`) values (1,0,'CLTPHP','',0,'fad79adf090l3948','aslfkafopiwqeupfjcmsdvifguyuosad','gh_b6f39fuakdu897fk','cltphp','/uploads/20170222/f4902c1dc258eb5769be8198052c0947.jpg','eesops1462769263','cltphp',0,0,'','','','','','',1,'','',1487583944,'/uploads/20170222/617d421e8f0fe37c7bc28cec3493fa88.jpg','0',1);

UNLOCK TABLES;

/*Table structure for table `tp_wx_msg` */

DROP TABLE IF EXISTS `tp_wx_msg`;

CREATE TABLE `tp_wx_msg` (
  `msgid` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL COMMENT '系统用户ID',
  `titile` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `sendtime` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间',
  `issend` tinyint(1) DEFAULT '0' COMMENT '0未发送1成功2失败',
  `sendtype` tinyint(1) DEFAULT '1' COMMENT '0单人1所有',
  PRIMARY KEY (`msgid`),
  KEY `uid` (`admin_id`),
  KEY `createymd` (`sendtime`),
  KEY `fake_id` (`titile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `tp_wx_msg` */

LOCK TABLES `tp_wx_msg` WRITE;

UNLOCK TABLES;

/*Table structure for table `tp_wx_news` */

DROP TABLE IF EXISTS `tp_wx_news`;

CREATE TABLE `tp_wx_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `keyword` char(255) NOT NULL COMMENT 'keyword',
  `createtime` varchar(13) NOT NULL COMMENT '创建时间',
  `uptatetime` varchar(13) NOT NULL COMMENT '更新时间',
  `token` char(30) NOT NULL COMMENT 'token',
  `img_id` varchar(50) DEFAULT NULL COMMENT '图文组合id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='微信图文';

/*Data for the table `tp_wx_news` */

LOCK TABLES `tp_wx_news` WRITE;

UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

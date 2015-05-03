CREATE TABLE IF NOT EXISTS `#__rstbox` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `testmode` tinyint(1) NOT NULL DEFAULT '0',
  `boxtype` varchar(30) CHARACTER SET utf8 NOT NULL,
  `settings` text CHARACTER SET utf8 NOT NULL,
  `customhtml` text CHARACTER SET utf8,
  `position` varchar(30) CHARACTER SET utf8 NOT NULL,
  `triggermethod` varchar(50) CHARACTER SET utf8 NOT NULL,
  `animation` varchar(30) CHARACTER SET utf8 NOT NULL,
  `cookie` mediumint(9) NOT NULL,
  `params` text CHARACTER SET utf8 NOT NULL,
  `accesslevel` smallint(6) DEFAULT NULL,
  `published` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `#__rstbox_menu` (
  `boxid` int(11) NOT NULL,
  `menuid` int(11) NOT NULL,
  UNIQUE KEY `boxid` (`boxid`,`menuid`),
  KEY `menuid` (`menuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `#__k2_k2locator` (
  `itemid` int(11) NOT NULL AUTO_INCREMENT,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `privacy` int(11) NOT NULL,
  PRIMARY KEY (`itemid`),
  KEY `lat` (`lat`),
  KEY `lng` (`lng`),
  KEY `privacy` (`privacy`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

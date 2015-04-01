CREATE TABLE IF NOT EXISTS `#__hellomaps_config` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `params` longtext NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
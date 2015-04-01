DROP TABLE IF EXISTS `#__je_jek2submit`;
CREATE TABLE IF NOT EXISTS `#__je_jek2submit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `notify_email` varchar(250) NOT NULL,
  `message` longtext NOT NULL,
  `notify` tinyint(4) NOT NULL,
  `notify_message` longtext NOT NULL,
  `captcha` tinyint(4) NOT NULL,
  `itemid` tinyint(4) NOT NULL,
  `term` tinyint(4) NOT NULL,
  `category` tinyint(4) NOT NULL,
  `cat_id` varchar(200) NOT NULL,
  `allow_reguser` tinyint(4) NOT NULL,
  `auto_publish` tinyint(4) NOT NULL,
  `pageurl` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `publish` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);


INSERT INTO `#__je_jek2submit` (`id`, `title`, `enabled`, `notify_email`, `message`, `notify`, `notify_message`, `captcha`, `itemid`, `term`, `category`, `cat_id`, `allow_reguser`, `auto_publish`, `pageurl`, `name`, `email`, `publish`) VALUES
(1, 'My Story', 1, 'email2hardik@gmail.com', '<table style="border-collapse:collapse;" border="1" cellspacing="5" cellpadding="5">\r\n<tbody>\r\n<tr>\r\n<td>Created</td>\r\n<td>{created_by}</td>\r\n</tr>\r\n<tr>\r\n<td>Address</td>\r\n<td>{REMOTE_ADDR}</td>\r\n</tr>\r\n<tr>\r\n<td>Email</td>\r\n<td>{email}</td>\r\n</tr>\r\n<tr>\r\n<td>Intero_text</td>\r\n<td>{introtext}</td>\r\n</tr>\r\n<tr>\r\n<td>Fulltext</td>\r\n<td>{fulltext}</td>\r\n</tr>\r\n</tbody>\r\n</table>', 1, '<p>Hi {User},</p>\r\n<p> </p>\r\n<p>Thanks for submiting the article your article will be published soon. Wait for that.</p>\r\n<p> </p>\r\n<p>Regards,</p>\r\n<p>Joomla extensions team</p>\r\n<p>(www.joomlaexetnsions.co.in)</p>', 0, 0, 1, 1, '0', 0, 1, 0, '1', '1', 1);

DROP TABLE IF EXISTS `#__je_k2itemlist`;
CREATE TABLE IF NOT EXISTS `#__je_k2itemlist` (
  `id` int(11) NOT NULL auto_increment,
  `itemid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `published` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

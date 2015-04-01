DROP TABLE IF EXISTS `#__jemulti_jek2submit`;
CREATE TABLE IF NOT EXISTS `#__jemulti_jek2submit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` longtext NOT NULL,
  `notify_message` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


INSERT INTO `#__jemulti_jek2submit` (`id`, `message`, `notify_message`) VALUES
(1, '<table style="border-collapse: collapse;" width="385" border="1" cellspacing="5" cellpadding="5">\r\n<tbody>\r\n<tr>\r\n<td>Created</td>\r\n<td>{created_by}</td>\r\n</tr>\r\n<tr>\r\n<td>Address</td>\r\n<td>{REMOTE_ADDR}</td>\r\n</tr>\r\n<tr>\r\n<td>Email</td>\r\n<td>{email}</td>\r\n</tr>\r\n<tr>\r\n<td>Intero_text</td>\r\n<td>{introtext}</td>\r\n</tr>\r\n<tr>\r\n<td>Fulltext</td>\r\n<td>{fulltext}</td>\r\n</tr>\r\n</tbody>\r\n</table>', '<p>Hi {User},</p>\r\n<p>hello</p>\r\n<p>Thanks for submiting the article your article will be published soon. Wait for that.</p>\r\n<p> </p>\r\n<p>Regards,</p>\r\n<p>Joomla extensions team</p>\r\n<p>(www.joomlaexetnsions.co.in)</p>');


DROP TABLE IF EXISTS `#__jemulti_k2itemlist`;
CREATE TABLE IF NOT EXISTS `#__jemulti_k2itemlist` (
  `id` int(11) NOT NULL auto_increment,
  `itemid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `published` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

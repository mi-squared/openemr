CREATE TABLE IF NOT EXISTS `form_prior_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `prior_auth_number` varchar(35) DEFAULT NULL,
  `not_req` varchar(3) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `desc` varchar(120) NOT NULL,
  `auth_for` int(5) DEFAULT NULL,
  `auth_from` date DEFAULT NULL,
  `auth_to` date DEFAULT NULL,
  `units` varchar(11) DEFAULT NULL,
  `auth_length` int(11) DEFAULT NULL,
  `dollar` int(10) DEFAULT NULL,
  `auth_contact` varchar(20) NOT NULL,
  `auth_phone` varchar(20) NOT NULL,
  `code1` int(11) DEFAULT NULL,
  `code2` int(10) DEFAULT NULL,
  `code3` int(10) DEFAULT NULL,
  `code4` int(10) DEFAULT NULL,
  `code5` int(10) DEFAULT NULL,
  `code6` int(10) DEFAULT NULL,
  `code7` int(10) DEFAULT NULL,
  `used` int(10) DEFAULT NULL,
  `archived` int(2) DEFAULT NULL,
  `override` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1696 ;
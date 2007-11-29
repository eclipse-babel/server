-- MySQL dump 10.10
--
-- Host: localhost    Database: babelstg
-- ------------------------------------------------------
-- Server version	5.0.18

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE `event_log` (
  `event_id` int(10) unsigned NOT NULL auto_increment,
  `table_name` varchar(100) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `key_value` varchar(100) default NULL,
  `action` varchar(100) NOT NULL,
  `userid` mediumint(9) NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY  (`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `file_id` int(10) unsigned NOT NULL auto_increment,
  `project_id` varchar(100) NOT NULL,
  `name` text NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`file_id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `language_id` smallint(5) unsigned NOT NULL auto_increment,
  `iso_code` char(2) NOT NULL,
  `locale` varchar(12) default NULL,
  `name` varchar(50) NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`language_id`),
  KEY `iso_code` (`iso_code`),
  KEY `locale` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
  `userid` mediumint(9) NOT NULL auto_increment,
  `login_name` varchar(255) NOT NULL default '',
  `cryptpassword` varchar(128) default NULL,
  `realname` varchar(255) NOT NULL default '',
  `disabledtext` mediumtext NOT NULL,
  `mybugslink` tinyint(4) NOT NULL default '1',
  `extern_id` varchar(64) default NULL,
  `disable_mail` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`userid`),
  UNIQUE KEY `profiles_login_name_idx` (`login_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `project_id` varchar(100) NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE `ratings` (
  `translation_id` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL,
  PRIMARY KEY  (`translation_id`,`userid`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`translation_id`) REFERENCES `translations` (`translation_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `schema_info`
--

DROP TABLE IF EXISTS `schema_info`;
CREATE TABLE `schema_info` (
  `version` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `gid` char(32) default NULL,
  `subnet` char(15) default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `strings`
--

DROP TABLE IF EXISTS `strings`;
CREATE TABLE `strings` (
  `string_id` int(10) unsigned NOT NULL auto_increment,
  `file_id` int(10) unsigned NOT NULL,
  `name` text NOT NULL,
  `value` text NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  `is_active` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`string_id`),
  KEY `file_id` (`file_id`),
  KEY `userid` (`userid`),
  CONSTRAINT `strings_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`) ON UPDATE CASCADE,
  CONSTRAINT `strings_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `translation_archives`
--

DROP TABLE IF EXISTS `translation_archives`;
CREATE TABLE `translation_archives` (
  `translation_id` int(10) unsigned NOT NULL,
  `string_id` int(10) unsigned NOT NULL,
  `language_id` smallint(5) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL default '1',
  `value` text NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  `userid` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY  (`string_id`,`language_id`,`version`),
  KEY `language_id` (`language_id`),
  KEY `userid` (`userid`),
  CONSTRAINT `translation_archives_ibfk_1` FOREIGN KEY (`string_id`) REFERENCES `strings` (`string_id`) ON UPDATE CASCADE,
  CONSTRAINT `translation_archives_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`language_id`) ON UPDATE CASCADE,
  CONSTRAINT `translation_archives_ibfk_3` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `translation_id` int(10) unsigned NOT NULL auto_increment,
  `string_id` int(10) unsigned NOT NULL,
  `language_id` smallint(5) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL default '1',
  `value` text NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  `userid` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY  (`string_id`,`language_id`,`version`),
  KEY `translation_id` (`translation_id`),
  KEY `language_id` (`language_id`),
  KEY `userid` (`userid`),
  CONSTRAINT `translations_ibfk_1` FOREIGN KEY (`string_id`) REFERENCES `strings` (`string_id`) ON UPDATE CASCADE,
  CONSTRAINT `translations_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`language_id`) ON UPDATE CASCADE,
  CONSTRAINT `translations_ibfk_3` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!50003 SET @OLD_SQL_MODE=@@SQL_MODE*/;
DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE TRIGGER `ins_version` BEFORE INSERT ON `translations` FOR EACH ROW SET NEW.version = 
    (SELECT IFNULL(MAX(version),0)+1 FROM translations 
        WHERE translation_id = NEW.translation_id and language_id = NEW.language_id) */;;

DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userid` int(10) unsigned NOT NULL,
  `username` varchar(256) NOT NULL default '',
  `first_name` varchar(256) NOT NULL default '',
  `last_name` varchar(256) NOT NULL default '',
  `email` varchar(256) NOT NULL default '',
  `primary_language_id` int(11) NOT NULL default '0',
  `hours_per_week` int(11) NOT NULL default '0',
  `password_hash` varchar(256) NOT NULL default '',
  `updated_on` date NOT NULL,
  `updated_at` time NOT NULL,
  `created_on` date NOT NULL,
  `created_at` time NOT NULL,
  PRIMARY KEY  (`userid`),
  KEY `primary_language_id` (`primary_language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into languages values(null, "en", null, "English", 1);


insert into projects values("eclipse", 1);



/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


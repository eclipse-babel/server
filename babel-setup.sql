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

DROP TABLE IF EXISTS event_log;
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
  `version` varchar(64) NOT NULL,
  `name` text NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`file_id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`project_id`,`version`) REFERENCES `project_versions` (`project_id`,`version`) ON UPDATE CASCADE
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
-- Table structure for table `map_files`
--

DROP TABLE IF EXISTS `map_files`;
CREATE TABLE `map_files` (
  `project_id` varchar(100) NOT NULL,
  `version` varchar(64) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`project_id`, `version`, `filename`),
  CONSTRAINT `map_files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON UPDATE CASCADE ON DELETE CASCADE
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

DROP TABLE IF EXISTS `project_versions`;
CREATE TABLE `project_versions` (
  `project_id` varchar(100) NOT NULL,
  `version` varchar(64) NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`project_id`, `version`),
  CONSTRAINT `project_versions_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON UPDATE CASCADE ON DELETE CASCADE
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


DELIMITER ;;
CREATE TRIGGER `upd_string` AFTER UPDATE ON `strings` FOR EACH ROW 
  BEGIN
    IF(NEW.value <> OLD.value) THEN
      UPDATE translations SET possibly_incorrect = 1 WHERE string_id = NEW.string_id; 
    END IF;
  END;
;;
DELIMITER ;



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
  `possibly_incorrect` tinyint unsigned NOT NULL default '0',
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
        WHERE string_id = NEW.string_id and language_id = NEW.language_id) */;;

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
  `is_committer` tinyint unsigned not null default 0,
  `updated_on` date NOT NULL,
  `updated_at` time NOT NULL,
  `created_on` date NOT NULL,
  `created_at` time NOT NULL,
  PRIMARY KEY  (`userid`),
  KEY `primary_language_id` (`primary_language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;






/*
Babel data
User: babel@eclipse.org
pass: password
*/

insert into profiles set login_name = "test", cryptpassword = "", realname = "tester", disabledtext = false, mybugslink = 1, extern_id = 1, disable_mail = false;
insert into users set userid = 1, username = "babel@eclipse.org",first_name="babel",last_name="fish",email="babel@eclipse.org",primary_language_id = "",password_hash = "HSD9a.ShTTdvo", is_committer = true;
insert into projects set project_id = 'eclipse', is_active = 1 ;
INSERT INTO `languages` VALUES (1,'en',NULL,'English',1),
(2,'fr',NULL,'French',1),
(3,'ja',NULL,'Japanese',1),
(4,'de',NULL,'German',1),
(5,'es',NULL,'Spanish',1),
(7,'it',NULL,'Italian',1),
(8,'ja',NULL,'Japanese',1),
(9,'ko',NULL,'Korean',1),
(10,'pt','Brazil','Portuguese',1),
(11,'zh','Simplified','Chinese',1),
(12,'zh','Traditional','Chinese',1),
(13,'cs',NULL,'Czech',1),
(14,'hu',NULL,'Hungarian',1),
(15,'pl',NULL,'Polish',1),
(16,'ru',NULL,'Russian',1),
(17,'da',NULL,'Russian',1),
(18,'nl',NULL,'Dutch',1),
(19,'fi',NULL,'Finnish',1),
(20,'el',NULL,'Greek',1),
(21,'no',NULL,'Norwegian',1),
(22,'pt',NULL,'Portuguese',1),
(23,'sv',NULL,'Swedish',1),
(24,'tr',NULL,'Turkish',1),
(25,'ar',NULL,'Arabic',1),
(26,'he',NULL,'Hebrew',1);
insert into project_versions set project_id = "eclipse", version = "3.4", is_active = 1;


/* MAP INPUTS */
insert into map_files values ("eclipse", "3.4", "ant.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/ant.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "base.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/base.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "compare.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/compare.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "core-hpux.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core-hpux.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "core-macosx.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core-macosx.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "core-qnx.map","http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core-qnx.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "core-variables.map","http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core-variables.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "core.map","http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/core.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "doc.map","http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/doc.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "equinox-incubator.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/equinox-incubator.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "feature.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/feature.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "jdtapt.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/jdtapt.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "jdtcore.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/jdtcore.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "jdtdebug.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/jdtdebug.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "jdtui.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/jdtui.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "orbit.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/orbit.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "pde.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/pde.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "rcp.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/rcp.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "releng.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/releng.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "swt.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/swt.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "team.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/team.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "testframework.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/testframework.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "text.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/text.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "ui.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/ui.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "update.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/update.map?view=co", 1);
insert into map_files values ("eclipse", "3.4", "userassist.map", "http://dev.eclipse.org/viewcvs/index.cgi/org.eclipse.releng/maps/userassist.map?view=co", 1);
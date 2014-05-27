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

DROP TABLE IF EXISTS `file_progress`;
CREATE TABLE `file_progress` (
  `file_id` int(10) unsigned NOT NULL,
  `language_id` smallint(5) unsigned NOT NULL,
  `pct_complete` float NOT NULL,
  PRIMARY KEY  (`file_id`, `language_id`),
  CONSTRAINT `file_progress_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `file_progress_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`language_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `file_id` int(10) unsigned NOT NULL auto_increment,
  `project_id` varchar(100) NOT NULL,
  `plugin_id` varchar(100) NOT NULL,
  `version` varchar(64) NOT NULL,
  `name` text NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`file_id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`project_id`,`version`) REFERENCES `project_versions` (`project_id`,`version`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET @OLD_SQL_MODE=@@SQL_MODE;
DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
DROP TRIGGER IF EXISTS `upd_is_active`;;

/* This trigger will recursively update the is_active status of the strings of a file */   
CREATE TRIGGER `upd_is_active` AFTER UPDATE ON `files` FOR EACH ROW BEGIN
  UPDATE strings set is_active = NEW.is_active WHERE strings.file_id = NEW.file_id;
  
  /* update project_progress table to indicate this proj/lang/vers stats are stale */
  /* don't know if record is there or not, so do both an insert and an update */
  /* This portion of the trigger is similar to what is in the translations table */
  SET @PROJECT=(SELECT project_id FROM files WHERE file_id = NEW.file_id);
  SET @VERSION=(SELECT version FROM files WHERE file_id = NEW.file_id);
     
  UPDATE IGNORE project_progress SET is_stale = 1 where project_id = @PROJECT
   AND version = @VERSION; 
END;
;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `language_id` smallint(5) unsigned NOT NULL auto_increment,
  `iso_code` varchar(6) NOT NULL,
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
  `is_map_file` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`project_id`, `version`, `filename`),
  CONSTRAINT `map_files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `project_source_locations`
--

DROP TABLE IF EXISTS `project_source_locations`;
CREATE TABLE `project_source_locations` (
  `project_id` varchar(100) NOT NULL,
  `version` varchar(64) NOT NULL,
  `location` varchar(255) NOT NULL,
  PRIMARY KEY  (`project_id`, `version`, `location`),
  CONSTRAINT `project_source_locations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `plugin_exclude_patterns`
--

DROP TABLE IF EXISTS `plugin_exclude_patterns`;
CREATE TABLE `plugin_exclude_patterns` (
  `project_id` varchar(100) NOT NULL,
  `version` varchar(64) NOT NULL,
  `pattern` varchar(128) NOT NULL,
  PRIMARY KEY  (`project_id`, `version`, `pattern`),
  CONSTRAINT `plugin_exclude_patterns_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON UPDATE CASCADE ON DELETE CASCADE
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
-- Table structure for table `project_progress`
--

DROP TABLE IF EXISTS `project_progress`;
CREATE TABLE `project_progress` (
  `project_id` varchar(100) NOT NULL,
  `version` varchar(64) NOT NULL,
  `language_id` smallint(5) unsigned NOT NULL,
  `pct_complete` float NOT NULL,
  `is_stale` tinyint unsigned not null default 0,
  PRIMARY KEY  (`project_id`, `version`, `language_id`),
  CONSTRAINT `project_progress_ibfk_1` FOREIGN KEY (`project_id`, `version`) REFERENCES `project_versions` (`project_id`, `version`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `project_progress_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`language_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `project_versions`
--

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
-- Table structure for table `release_trains`
--

DROP TABLE IF EXISTS `release_trains`;
CREATE TABLE `release_trains` (
    `train_id` varchar(30) NOT NULL,
    `train_version` varchar(64) NOT NULL,
    `is_active` tinyint(3) unsigned NOT NULL default '1',
    PRIMARY KEY (`train_id`, `train_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `release_train_projects`
--

DROP TABLE IF EXISTS `release_train_projects`;
CREATE TABLE `release_train_projects` (
    `train_id` varchar(30) NOT NULL,
    `project_id` varchar(100) NOT NULL,
    `version` varchar(64) NOT NULL,
    PRIMARY KEY (`train_id`, `project_id`, `version`),
    CONSTRAINT `release_train_progress_ibfk_1` FOREIGN KEY (`project_id`, `version`) REFERENCES `project_versions` (`project_id`, `version`) ON UPDATE CASCADE ON DELETE CASCADE
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

CREATE TABLE `strings` (
  `string_id` int(10) unsigned NOT NULL auto_increment,
  `file_id` int(10) unsigned NOT NULL,
  `name` text NOT NULL,
  `value` text NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  `is_active` tinyint(1) unsigned default NULL,
  `non_translatable` tinyint(4) default '0',
  PRIMARY KEY  (`string_id`),
  KEY `file_id` (`file_id`),
  KEY `userid` (`userid`),
  CONSTRAINT `strings_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`) ON UPDATE CASCADE,
  CONSTRAINT `strings_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX idx_value ON strings(value(40));

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
-- Table structure for table `sys_values`
--
DROP TABLE IF EXISTS `sys_values`;
CREATE TABLE `sys_values` (
  `itemid` char(6) NOT NULL,
  `value` text,
  PRIMARY KEY  (`itemid`)
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
  `possibly_incorrect` tinyint unsigned NOT NULL default '0',
  `is_active` tinyint(3) unsigned NOT NULL default '1',
  `userid` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY  (`string_id`,`language_id`,`version`),
  KEY `translation_id` (`translation_id`),
  KEY `language_id` (`language_id`),
  KEY `userid` (`userid`),
  KEY `created_on` (`created_on`),
  CONSTRAINT `translations_ibfk_1` FOREIGN KEY (`string_id`) REFERENCES `strings` (`string_id`) ON UPDATE CASCADE,
  CONSTRAINT `translations_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`language_id`) ON UPDATE CASCADE,
  CONSTRAINT `translations_ibfk_3` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET @OLD_SQL_MODE=@@SQL_MODE;
DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
DROP TRIGGER IF EXISTS `ins_version`;;

/* This trigger sets the version to max(version) + 1.  It also updates the file_progress table  */
/* We check IF(NEW.version > 1) to determine if this is a NEW translation or one that replaces an existing one */
/* (COUNT(t.string_id) + 1) because it's a BEFORE INSERT trigger, the translated str is not in the DB yet  */
/* and without the +1 the percent would always be "one behind"  */   
CREATE TRIGGER `ins_version` BEFORE INSERT ON `translations` FOR EACH ROW BEGIN
  SET NEW.version = 
    (SELECT IFNULL(MAX(version),0)+1 FROM translations 
        WHERE string_id = NEW.string_id and language_id = NEW.language_id);
  
  /* Define a few fields we need here */
  SET @FILE_ID=(SELECT file_id FROM strings WHERE string_id = NEW.string_id);  
  SET @PROJECT=(SELECT project_id FROM files WHERE file_id = @FILE_ID);
  SET @VERSION=(SELECT version FROM files WHERE file_id = @FILE_ID);
        
  
  /* update this file's progress  */
  DELETE FROM file_progress where file_id = @FILE_ID
   AND language_id = NEW.language_id;
 
  /* See notes above for the hairy select  */
  INSERT INTO file_progress SET file_id = @FILE_ID,
   language_id = NEW.language_id,
   pct_complete = (
     SELECT IF(NEW.version > 1, 
         IF(COUNT(s.string_id) > 0, (COUNT(t.string_id))/COUNT(s.string_id)*100,0),
         IF(COUNT(s.string_id) > 0, (COUNT(t.string_id) + 1)/COUNT(s.string_id)*100,0)
         ) AS translate_percent
       FROM files AS f
         LEFT JOIN strings AS s ON (s.file_id = f.file_id AND s.value <> "" and s.is_active)
         LEFT JOIN translations AS t ON (s.string_id = t.string_id 
           AND t.language_id = NEW.language_id AND t.is_active = 1)
       WHERE f.file_id = @FILE_ID
    );


  /* update project_progress table to indicate this proj/lang/vers stats are stale */
  /* don't know if record is there or not, so do both an insert and an update */
  /* This portion of the trigger is similar to what is in the files table */
  UPDATE IGNORE project_progress SET is_stale = 1 where project_id = @PROJECT
   AND version = @VERSION 
   AND language_id = NEW.language_id;

  INSERT IGNORE INTO project_progress SET is_stale = 1, project_id = @PROJECT,
   version = @VERSION, 
   language_id = NEW.language_id;
   
END;
;;
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

DROP TABLE IF EXISTS `scoreboard`;
CREATE TABLE `scoreboard` (
  `id` int unsigned not null auto_increment,
  `itemid` char(6) NOT NULL,
  `value` varchar(256) NOT NULL default '',
  `quantity` double NOT NULL default 0,
  PRIMARY KEY  (`id`),
  KEY(`quantity`)
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
insert into users set userid = 1, username = "babel@eclipse.org",first_name="babel",last_name="fish",email="babel@eclipse.org",primary_language_id = "",password_hash = "HSD9a.ShTTdvo", is_committer = true,  updated_on = NOW(), updated_at='',created_on = NOW(), created_at='';
insert into users set userid = 2, username = "genie@eclipse.org",first_name="babel",last_name="genie",email="genie@eclipse.org",primary_language_id = "",password_hash = "HSD9a.ShTTdvo", is_committer = true,  updated_on = NOW(), updated_at='',created_on = NOW(), created_at='';
insert into users set userid = 3, username = "syncup@eclipse.org",first_name="babel",last_name="syncup",email="syncup@eclipse.org",primary_language_id = "",password_hash = "HSD9a.ShTTdvo", is_committer = true,  updated_on = NOW(), updated_at='',created_on = NOW(), created_at='';

insert into languages values (1,    "en",    null,          "English",    1);
insert into languages values (null, "fr",    null,          "French",     1);
insert into languages values (null, "de",    null,          "German",     1);
insert into languages values (null, "es",    null,          "Spanish",    1);
insert into languages values (null, "it",    null,          "Italian",    1);
insert into languages values (null, "ja",    null,          "Japanese",   1);
insert into languages values (null, "ko",    null,          "Korean",     1);
insert into languages values (null, "pt_BR", "Brazilian",   "Portuguese", 1);
insert into languages values (null, "zh",    "Simplified",  "Chinese",    1);
insert into languages values (null, "zh_TW", "Traditional", "Chinese",    1);
insert into languages values (null, "cs",    null,          "Czech",      1);
insert into languages values (null, "hu",    null,          "Hungarian",  1);
insert into languages values (null, "pl",    null,          "Polish",     1);
insert into languages values (null, "ru",    null,          "Russian",    1);
insert into languages values (null, "da",    null,          "Danish",     1);
insert into languages values (null, "nl",    null,          "Dutch",      1);
insert into languages values (null, "fi",    null,          "Finnish",    1);
insert into languages values (null, "el",    null,          "Greek",      1);
insert into languages values (null, "no",    null,          "Norwegian",  1);
insert into languages values (null, "pt",    null,          "Portuguese", 1);
insert into languages values (null, "sv",    null,          "Swedish",    1);
insert into languages values (null, "tr",    null,          "Turkish",    1);
insert into languages values (null, "ar",    null,          "Arabic",     1);
insert into languages values (null, "iw",    null,          "Hebrew",     1);
insert into languages values (null, "hi",    null,          "Hindi",      1);
insert into languages values (null, "ro",    null,          "Romanian",   1);
insert into languages values (null, "uk",    null,          "Ukrainian",  1);
insert into languages values (null, "ca",    null,          "Catalan",    1);
insert into languages values (null, "et",    null,          "Estonian",   1);
insert into languages values (null, "en_CA", "Canadian",    "English",    1);
insert into languages values (null, "en_AU", "Australian",  "English",    1);
insert into languages values (null, "mn",    null,          "Mongolian",  1);
insert into languages values (null, "id",    null,          "Indonesian", 1);
insert into languages values (null, "bg",    null,          "Bulgarian",  1);
insert into languages values (null, "sl",    null,          "Slovenian",  1);
insert into languages values (null, "fa",    null,          "Persian",    1);
insert into languages values (null, "sq",    null,          "Albanian",   1);
insert into languages values (null, "ku",    null,          "Kurdish",    1);
insert into languages values (null, "sr",    null,          "Serbian",    1);
insert into languages values (null, "sk",    null,          "Slovak",     1);
insert into languages values (null, "eu",    null,          "Basque",     1);
insert into languages values (null, "ml",    null,          "Malayalam",  1);
insert into languages values (null, "my",    null,          "Myanmar",    1);
insert into languages values (null, "th",    null,          "Thai",       1);
insert into languages values (null, "lt",    null,          "Lithuanian", 1);
insert into languages values (null, "ne",    null,          "Nepali",     1);
insert into languages values (null, "tl",    null,          "Klingon",    1);

insert into release_trains values ("kepler", "4.3.0", 1);

insert into projects values ("eclipse", 1);
insert into projects values ("eclipse.orion", 1);

insert into project_versions values ("eclipse", "4.3", 1);
insert into project_versions values ("eclipse.orion", "3.0", 1);

insert into release_train_projects values ("kepler", "eclipse", "4.3");
insert into release_train_projects values ("kepler", "eclipse.orion", "3.0");

insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/equinox/rt.equinox.framework.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/equinox/rt.equinox.p2.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/jdt/eclipse.jdt.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/jdt/eclipse.jdt.core.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/jdt/eclipse.jdt.debug.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/jdt/eclipse.jdt.ui.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/pde/eclipse.pde.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/pde/eclipse.pde.ui.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/platform/eclipse.platform.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/platform/eclipse.platform.debug.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/platform/eclipse.platform.team.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/platform/eclipse.platform.text.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/platform/eclipse.platform.ua.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse", "4.3", "http://git.eclipse.org/c/platform/eclipse.platform.ui.git/snapshot/R4_3.zip");
insert into project_source_locations values ("eclipse.orion", "3.0", "http://git.eclipse.org/c/orion/org.eclipse.orion.client.git/snapshot/R3_0.zip");

insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^com\\.ibm\\.icu.*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^com\\.jcraft\\..*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^javax\\..*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^org\\.apache\\..*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^org\\.hamcrest\\..*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^org\\.junit.*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^org\\.mortbay\\..*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^org\\.objectweb\\..*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^org\\.sat4j\\..*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^org\\.w3c\\..*$/");
insert into plugin_exclude_patterns values ("eclipse", "4.3", "/^.*\\/jface\\/resource\\/.*$/");
insert into plugin_exclude_patterns values ("eclipse.orion", "3.0", "/^.*\\/bundle.properties$/");

/* populate file_progress table  */
/* See also: dbmaintenance_15min.php */
truncate table file_progress;
INSERT INTO file_progress
select f.file_id, l.language_id, IF(COUNT(s.string_id) > 0, COUNT(t.string_id)/COUNT(s.string_id)*100,100) AS translate_percent
FROM files AS f
        INNER JOIN languages as l ON l.is_active = 1
        LEFT JOIN strings as s ON (s.file_id = f.file_id AND s.is_active AND s.value <> "" AND s.non_translatable = 0) 
        LEFT JOIN translations AS t ON (s.string_id = t.string_id 
           AND t.language_id = l.language_id AND t.is_active = 1)
WHERE f.is_active = 1 
GROUP BY f.file_id, l.language_id;
DELETE FROM file_progress WHERE pct_complete = 0;

/* populate project_progress table */
truncate table project_progress;
INSERT INTO project_progress
SELECT 
    p.project_id, 
    v.version, 
    l.language_id,
    IF(COUNT(s.string_id) > 0, ROUND(COUNT(t.string_id)/COUNT(s.string_id) * 100, 2), 0) AS pct_complete,
    0
FROM projects as p 
    INNER JOIN project_versions AS v ON v.project_id = p.project_id 
    INNER JOIN files AS f 
        ON (f.project_id = p.project_id AND f.version = v.version AND f.is_active) 
    INNER JOIN strings AS s 
        ON (s.file_id = f.file_id AND s.is_active) 
    INNER JOIN languages AS l
    LEFT JOIN translations AS t 
        ON (t.string_id = s.string_id AND t.language_id = l.language_id AND t.is_active) 
WHERE
    s.value <> ""
    AND s.non_translatable = 0
    AND p.is_active 
GROUP BY p.project_id, v.version, l.language_id;

/* populate scoreboard */
truncate table scoreboard;
INSERT INTO scoreboard SELECT NULL, "LANGPR", CONCAT(b.name,IF(ISNULL(b.locale),"",CONCAT(" ", b.locale))), count(*) as StringCount from translations as a inner join languages as b on b.language_id = a.language_id where a.value <> '' and a.is_active = 1 group by a.language_id order by StringCount desc;
INSERT INTO scoreboard SELECT NULL, "TOPTR", CONCAT(first_name, IF(ISNULL(last_name),"",CONCAT(" ", last_name))), count(translations.string_id) as cnt from translations inner join users on users.userid = translations.userid where is_active=1 group by first_name, last_name order by cnt desc limit 20;
INSERT INTO scoreboard SELECT NULL, "LASGEN", "Scoreboard Last Generated", MAX(translation_id) FROM translations;

/* create a holding record for the MOTD */
INSERT INTO sys_values VALUES ("MOTD", NULL);

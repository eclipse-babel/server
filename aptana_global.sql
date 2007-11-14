CREATE TABLE `entries` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(256) NOT NULL default '',
  `value` text,
  `rating` int(11) NOT NULL default '0',
  `repo_path` text NOT NULL,
  `language_id` int(11) NOT NULL default '0',
  `project_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `updated_on` date NOT NULL,
  `updated_at` time NOT NULL,
  `created_on` date NOT NULL,
  `created_at` time NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `languages` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(256) NOT NULL default '',
  `iso_code` varchar(255) NOT NULL default '',
  `updated_on` date NOT NULL,
  `updated_at` time NOT NULL,
  `created_on` date NOT NULL,
  `created_at` time NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


 CREATE TABLE `projects` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(256) NOT NULL default '',
  `package_name` varchar(256) NOT NULL default '',
  `updated_on` date NOT NULL,
  `updated_at` time NOT NULL,
  `created_on` date NOT NULL,
  `created_at` time NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `schema_info` (
  `version` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


 CREATE TABLE `sessions` (
  `id` int(11) NOT NULL auto_increment,
  `session_id` varchar(255) default NULL,
  `data` text,
  `updated_at` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `index_sessions_on_session_id` (`session_id`),
  KEY `index_sessions_on_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(256) NOT NULL default '',
  `first_name` varchar(256) NOT NULL default '',
  `last_name` varchar(256) NOT NULL default '',
  `email` varchar(256) NOT NULL default '',
  `primary_language_id` int(11) NOT NULL default '0',
  `hours_per_week` int(11) NOT NULL default '0',
  `password_salt` varchar(256) NOT NULL default '',
  `password_hash` varchar(256) NOT NULL default '',
  `updated_on` date NOT NULL,
  `updated_at` time NOT NULL,
  `created_on` date NOT NULL,
  `created_at` time NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* Language info  */
/* English needs to be language 0 */ 
insert into languages values(null, "English", "en", now(), now(), now(), now());

/* not sure if other languages need to be in a specific order */
insert into languages values(null, "German", "de", now(), now(), now(), now());
insert into languages values(null, "French", "fr", now(), now(), now(), now());
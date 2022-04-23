CREATE TABLE IF NOT EXISTS `#__cgisotope_page` (
`id` integer NOT NULL AUTO_INCREMENT,
`title` text NOT NULL,
`state` integer NOT NULL default 0,
`page_params` text NOT NULL,
`sections` text NOT NULL ,
`fieldslinks` text NOT NULL  ,
`created` datetime NULL default '1980-01-01 00:00:00' ,
`created_by` int(10) unsigned NOT NULL DEFAULT '0',
`modified` datetime NULL DEFAULT NULL,
`modified_by` int(10) unsigned NOT NULL DEFAULT '0',
`checked_out` INT unsigned NULL,
`checked_out_time` datetime NULL DEFAULT NULL,
`publish_up` datetime  NULL DEFAULT NULL,
`publish_down` datetime  NULL DEFAULT NULL,
`language` char(7) NOT NULL DEFAULT '',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='definition des sections';

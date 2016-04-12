-- Adminer 4.2.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `bit_peer`;
CREATE TABLE `bit_peer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(40) NOT NULL,
  `user_agent` varchar(80) DEFAULT NULL,
  `ip_address` varchar(40) NOT NULL,
  `key_hash` char(40) NOT NULL,
  `port` smallint(5) unsigned NOT NULL,
  `user` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash_key` (`hash`,`key_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `bit_peer_torrent`;
CREATE TABLE `bit_peer_torrent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `peer_id` int(10) unsigned NOT NULL,
  `torrent_id` int(10) unsigned NOT NULL,
  `uploaded` bigint(20) unsigned DEFAULT NULL,
  `downloaded` bigint(20) unsigned DEFAULT NULL,
  `remain` bigint(20) unsigned DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  `stopped` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `peer_torrent` (`peer_id`,`torrent_id`),
  KEY `update_torrent` (`torrent_id`,`stopped`,`last_updated`,`remain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `bit_torrent`;
CREATE TABLE `bit_torrent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(40) NOT NULL,
  `awe` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `categories` (`id`, `category`) VALUES
(1,	'Movies'),
(2,	'Series'),
(3,	'Music'),
(4,	'Games'),
(5,	'Apps'),
(6,	'Books'),
(7,	'XXX'),
(8,	'Misc'),
(9,	'Apps (Mobile)');

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `var` varchar(25) NOT NULL,
  `val` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`var`),
  KEY `variable` (`var`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`var`, `val`) VALUES
('copyright',	'&copy; 2016 Sandros Industries'),
('download_only_user',	'1'),
('is_private',	'0'),
('sleep_before_upload',	'2'),
('tagline',	'Because why not?'),
('title',	'AweTracker'),
('torrents_per_page',	'50'),
('upload_only_user',	'1');

DROP TABLE IF EXISTS `torrents`;
CREATE TABLE `torrents` (
  `id` int(10) unsigned NOT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `description` text,
  `size` bigint(13) unsigned DEFAULT NULL,
  `category` smallint(2) unsigned DEFAULT NULL,
  `visibility` tinyint(1) unsigned DEFAULT NULL,
  `time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tor_name` (`name`) USING BTREE,
  KEY `tor_commend` (`comment`(255)),
  KEY `tor_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL,
  `level` tinyint(1) unsigned DEFAULT NULL,
  `time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `verifications`;
CREATE TABLE `verifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent_id` int(10) unsigned NOT NULL,
  `verification` tinyint(1) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `torrent` (`torrent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

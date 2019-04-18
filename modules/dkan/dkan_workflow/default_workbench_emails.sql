# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 0.0.0.0 (MySQL 5.5.5-10.3.9-MariaDB)
# Database: drupal
# Generation Time: 2019-04-18 21:54:45 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table workbench_emails
# ------------------------------------------------------------

DROP TABLE IF EXISTS `workbench_emails`;

CREATE TABLE `workbench_emails` (
  `wid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'An auto increment id',
  `from_name` varchar(255) NOT NULL COMMENT 'The from state that the email exists',
  `to_name` varchar(255) NOT NULL COMMENT 'The to state that this email exists',
  `rid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'The role id',
  `subject` varchar(255) DEFAULT NULL COMMENT 'The subject of the email',
  `message` longtext DEFAULT NULL COMMENT 'The message of the email',
  `author` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'The author setting',
  `automatic` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'The automatic setting',
  PRIMARY KEY (`wid`),
  KEY `rid` (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Custom table to hold moderation emails';

LOCK TABLES `workbench_emails` WRITE;
/*!40000 ALTER TABLE `workbench_emails` DISABLE KEYS */;

INSERT INTO `workbench_emails` (`wid`, `from_name`, `to_name`, `rid`, `subject`, `message`, `author`, `automatic`)
VALUES
	(1,'draft','needs_review',0,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. To change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',1,1),
	(6,'draft','needs_review',200153891,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. Items that are not reviewed within 72 hours are filed under \"Stale Reviews.\"\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',0,1),
	(11,'draft','needs_review',200153901,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. Items that are not reviewed within 72 hours are filed under \"Stale Reviews.\"\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',0,1),
	(16,'needs_review','draft',0,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. If your draft has been pushed from Needs Review back to Draft, you may edit and submit it once more for review.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',1,1),
	(21,'needs_review','draft',200153891,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. Content that has been pushed from Needs Review back to Draft may be edited and submitted once more for review.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',0,1),
	(26,'needs_review','draft',200153901,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. Content that has been pushed from Needs Review back to Draft may be edited and submitted once more for review.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the topr.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',0,1),
	(31,'needs_review','published',0,'Workbench moderation update ([site:name]) - [node:content-type] \"[node:title]\" is now published','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. If you\'ve published content but wish to remove it from the site, you can change its status back to \"Draft\" or consult a Moderator or Site Manager.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',1,1),
	(36,'needs_review','published',200153891,'Workbench moderation update ([site:name]) - [node:content-type] \"[node:title]\" is now published','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. If you\'ve published content but wish to remove it from the site, you will need to un-publish it.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen. Under the moderation options is an \"Unpublish\" link that will let you set the state back to \"Draft\".\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',0,1),
	(41,'needs_review','published',200153901,'Workbench moderation update ([site:name]) - [node:content-type] \"[node:title]\" is now published','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published. If you\'ve published content but wish to remove it from the site, you will need to un-publish it.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen. Under the moderation options is an \"Unpublish\" link that will let you set the state back to \"Draft\".\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',0,1),
	(46,'published','needs_review',200153891,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',0,1),
	(51,'published','needs_review',200153901,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',0,1),
	(56,'published','needs_review',0,'Workbench moderation update ([site:name]) - Status of [node:content-type] \"[node:title]\" has changed.','[user:name],\n\nThe status of the [node:content-type] \"[node:title]\" has been changed from [workbench-email:email-transition].\n\nFor more details, view [node:content-type] at [node:url].\n\nThere are three moderation states for content in DKAN: Draft, Needs Review and Published.\n\nTo change the moderation state of content that you\'ve created or have permission to edit, click \"Edit\" and click the \"Moderate\" button located at the top of the screen.\n\nTo access your Workbench dashboard, log in and click \"My Workbench\" from the administration menu at the top.\n\nFor assistance or support, please contact a Site Manager or an administrator from your organization.',1,1);

/*!40000 ALTER TABLE `workbench_emails` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

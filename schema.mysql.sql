-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Host: iridium.local
-- Generation Time: Mar 19, 2014 at 12:09 PM
-- Server version: 5.6.14
-- PHP Version: 5.4.4-14+deb7u5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `csr`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `composite`
--
CREATE TABLE IF NOT EXISTS `composite` (
`tid` int(11) unsigned
,`title` varchar(255)
,`B` decimal(23,0)
,`G` decimal(23,0)
,`A` decimal(23,0)
,`S` decimal(23,0)
,`P` decimal(23,0)
);
-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE IF NOT EXISTS `post` (
  `pid` int(10) unsigned NOT NULL,
  `tid` int(10) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `tid` int(11) unsigned NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY (`tid`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

CREATE TABLE IF NOT EXISTS `topic` (
  `tid` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `composite`
--
DROP TABLE IF EXISTS `composite`;

CREATE ALGORITHM=UNDEFINED DEFINER=`csr`@`%` SQL SECURITY DEFINER VIEW `composite` AS select `topic`.`tid` AS `tid`,`topic`.`title` AS `title`,ifnull(sum((`post`.`rating` = 5)),0) AS `B`,ifnull(sum((`post`.`rating` = 4)),0) AS `G`,ifnull(sum((`post`.`rating` = 3)),0) AS `A`,ifnull(sum((`post`.`rating` = 2)),0) AS `S`,ifnull(sum((`post`.`rating` = 1)),0) AS `P` from (`topic` left join `post` on((`post`.`tid` = `topic`.`tid`))) group by `topic`.`tid`;

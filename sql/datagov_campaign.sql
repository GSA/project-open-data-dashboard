-- phpMyAdmin SQL Dump
-- version 3.5.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2014 at 05:03 PM
-- Server version: 5.5.28
-- PHP Version: 5.5.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `farm`
--

-- --------------------------------------------------------

--
-- Table structure for table `datagov_campaign`
--

CREATE TABLE IF NOT EXISTS `datagov_campaign` (
  `office_id` int(10) NOT NULL,
  `milestone` varchar(256) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `contact_name` text CHARACTER SET latin1,
  `contact_email` text CHARACTER SET latin1,
  `datajson_status` longtext CHARACTER SET latin1,
  `datapage_status` longtext CHARACTER SET latin1,
  `digitalstrategy_status` longtext CHARACTER SET latin1,
  `tracker_fields` longtext CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`office_id`,`milestone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

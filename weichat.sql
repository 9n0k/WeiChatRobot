-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 2017-08-29 08:38:01
-- 服务器版本： 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `weichat`
--

-- --------------------------------------------------------

--
-- 表的结构 `weichat_config`
--

CREATE TABLE `weichat_config` (
  `id` int(255) NOT NULL,
  `taobao_pid` varchar(100) CHARACTER SET utf8 NOT NULL,
  `alimama_appkey` varchar(255) CHARACTER SET utf8 NOT NULL,
  `alimama_secret` varchar(255) CHARACTER SET utf8 NOT NULL,
  `dataoke` varchar(255) CHARACTER SET utf8 NOT NULL,
  `weichatqun` varchar(255) CHARACTER SET utf8 NOT NULL,
  `robots_time` int(2) NOT NULL DEFAULT '10'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `weichat_config`
--

INSERT INTO `weichat_config` (`id`, `taobao_pid`, `alimama_appkey`, `alimama_secret`, `dataoke`, `weichatqun`, `robots_time`) VALUES
(1, '****************************', '****************************', '****************************', '****************************', '****************************', 15);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `weichat_config`
--
ALTER TABLE `weichat_config`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `weichat_config`
--
ALTER TABLE `weichat_config`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

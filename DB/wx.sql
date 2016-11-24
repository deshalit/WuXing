-- --------------------------------------------------------
-- Хост:                         localhost
-- Версия сервера:               5.5.50 - MySQL Community Server (GPL)
-- ОС Сервера:                   Win32
-- HeidiSQL Версия:              9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры базы данных wuxing
CREATE DATABASE IF NOT EXISTS `u7503481_wx` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `u7503481_wx`;


-- Дамп структуры для таблица wuxing.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regdate` datetime NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT 'Имя не указано',
  `lastname` varchar(50) NOT NULL DEFAULT 'Фамилия не указана',
  `email` varchar(50) NOT NULL DEFAULT 'unknown',
  `note` text,
  `status` smallint(6) NOT NULL DEFAULT '0',
  `promocode` varchar(20) DEFAULT NULL,
  `height` tinyint(3) unsigned NOT NULL,
  `eyecolor` varchar(20) NOT NULL,
  `haircolor` varchar(20) NOT NULL,
  `targetname` varchar(50) NOT NULL DEFAULT '''''',
  PRIMARY KEY (`id`),
  KEY `orders_date` (`status`,`regdate`),
  KEY `oders_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='Заказы на выполение работ. Создаются пользователем wx_user';

-- Дамп данных таблицы wuxing.orders: ~1 rows (приблизительно)
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`id`, `regdate`, `name`, `lastname`, `email`, `note`, `status`, `promocode`, `height`, `eyecolor`, `haircolor`, `targetname`) VALUES
	(0, '2016-10-01 00:00:00', 'Имя не указано', 'Фамилия не указана', 'unknown', 'Не удаляйте эту запись', 0, NULL, 170, 'blue', 'brown', 'Igor');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;


-- Дамп структуры для таблица wuxing.order_profiles
CREATE TABLE IF NOT EXISTS `order_profiles` (
  `id_order` int(11) NOT NULL,
  `id_profile` int(11) NOT NULL,
  UNIQUE KEY `UK_ORDER_PROFILE` (`id_order`,`id_profile`),
  CONSTRAINT `FK_ORDERPROFILE_ORDER` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Срезы';

-- Дамп данных таблицы wuxing.order_profiles: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `order_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_profiles` ENABLE KEYS */;


-- Дамп структуры для таблица wuxing.reports
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regdate` datetime NOT NULL,
  `id_order` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `note` text,
  `method` int(11) NOT NULL DEFAULT '1',
  `elems` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orderdate` (`regdate`),
  KEY `id_order` (`id_order`),
  KEY `email` (`email`),
  CONSTRAINT `FK_REPORT_ORDER` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Готовые отчеты';

-- Дамп данных таблицы wuxing.reports: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;


-- Дамп структуры для таблица wuxing.report_option
CREATE TABLE IF NOT EXISTS `report_option` (
  `id_report` int(11) NOT NULL,
  `type` enum('prof','risk') NOT NULL,
  `id_option` int(11) NOT NULL,
  PRIMARY KEY (`id_report`,`type`,`id_option`),
  CONSTRAINT `FK_RepOpt_Rep` FOREIGN KEY (`id_report`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='комментарии, риски';

-- Дамп данных таблицы wuxing.report_option: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `report_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_option` ENABLE KEYS */;


-- Дамп структуры для таблица wuxing.textdata
CREATE TABLE IF NOT EXISTS `textdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('prof','risk') NOT NULL,
  `data` text NOT NULL,
  `id_report` int(11) NOT NULL,
  `id_option` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`id_report`,`id_option`),
  KEY `texttype` (`type`),
  KEY `FKText_RepOpt` (`id_report`,`type`,`id_option`),
  CONSTRAINT `FKText_RepOpt` FOREIGN KEY (`id_report`, `type`, `id_option`) REFERENCES `report_option` (`id_report`, `type`, `id_option`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы wuxing.textdata: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `textdata` DISABLE KEYS */;
/*!40000 ALTER TABLE `textdata` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

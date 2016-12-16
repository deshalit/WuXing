SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL,
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
  `targetname` varchar(50) NOT NULL DEFAULT ''''''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Заказы на выполение работ. Создаются пользователем wx_user';

CREATE TABLE IF NOT EXISTS `order_profiles` (
  `id_order` int(11) NOT NULL,
  `id_profile` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Срезы';


ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_date` (`status`,`regdate`),
  ADD KEY `oders_email` (`email`),
  ADD KEY `orders_status` (`status`);

ALTER TABLE `order_profiles`
  ADD UNIQUE KEY `UK_ORDER_PROFILE` (`id_order`,`id_profile`);


ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `order_profiles`
  ADD CONSTRAINT `FK_ORDERPROFILE_ORDER` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

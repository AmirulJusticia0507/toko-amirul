/*
SQLyog Ultimate v12.5.1 (64 bit)
MySQL - 10.4.28-MariaDB : Database - db_toko_amirul
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_toko_amirul` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `db_toko_amirul`;

/*Table structure for table `amirulpay_transactions` */

DROP TABLE IF EXISTS `amirulpay_transactions`;

CREATE TABLE `amirulpay_transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`transaction_id`),
  UNIQUE KEY `unique_transaction` (`user_id`,`transaction_id`),
  CONSTRAINT `amirulpay_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `amirulpay_transactions` */

insert  into `amirulpay_transactions`(`transaction_id`,`user_id`,`amount`,`payment_method`,`transaction_date`,`status`) values 
(1,16,15000000.00,'AmirulPay','2024-06-30 01:32:07','pending');

/*Table structure for table `brands` */

DROP TABLE IF EXISTS `brands`;

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `brands` */

insert  into `brands`(`brand_id`,`brand_name`,`description`,`created_at`,`updated_at`) values 
(1,'Apple','perusahaan teknologi multinasional yang terkenal dengan produk-produk inovatifnya, seperti iPhone, iPad, MacBook, Apple Watch, dan layanan digital seperti iCloud dan Apple Music. Apple dikenal karena desainnya yang elegan, performa tinggi, dan ekosistem perangkat yang terintegrasi dengan baik, menyediakan pengalaman pengguna yang mulus dan berkualitas tinggi.','2024-06-29 14:01:48','2024-06-29 14:01:48'),
(4,'ASUS','produsen teknologi multinasional yang terkenal dengan produk-produk komputer, laptop, smartphone, dan komponen elektronik canggih.','2024-06-29 15:29:11','2024-06-29 15:29:11');

/*Table structure for table `cart_items` */

DROP TABLE IF EXISTS `cart_items`;

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_item_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `cart_items` */

insert  into `cart_items`(`cart_item_id`,`user_id`,`product_id`,`quantity`,`added_at`) values 
(3,16,'P20240629190532',1,'2024-06-30 00:16:50');

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `categories` */

insert  into `categories`(`category_id`,`category_name`,`description`,`created_at`,`updated_at`) values 
(2,'Handphone','Perangkat Keras Seluler','2024-06-29 08:37:06','2024-06-29 08:37:06'),
(3,'Pakaian Pria','Pakaian khusus Pria','2024-06-29 08:37:55','2024-06-29 08:37:55'),
(4,'Pakaian Wanita','Pakaian khusus wanita','2024-06-29 08:38:11','2024-06-29 08:38:11'),
(5,'Laptop','Perangkat Keras Komputer','2024-06-29 08:39:47','2024-06-29 08:39:47');

/*Table structure for table `log_delete_brands` */

DROP TABLE IF EXISTS `log_delete_brands`;

CREATE TABLE `log_delete_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_by` varchar(255) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `log_delete_brands_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `log_delete_brands` */

/*Table structure for table `log_delete_categories` */

DROP TABLE IF EXISTS `log_delete_categories`;

CREATE TABLE `log_delete_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_by` varchar(255) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `log_delete_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `log_delete_categories` */

/*Table structure for table `log_delete_products` */

DROP TABLE IF EXISTS `log_delete_products`;

CREATE TABLE `log_delete_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(20) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_by` varchar(255) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `log_delete_products_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `log_delete_products` */

/*Table structure for table `log_delete_users` */

DROP TABLE IF EXISTS `log_delete_users`;

CREATE TABLE `log_delete_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_by` varchar(255) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `log_delete_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `log_delete_users` */

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `product_id` varchar(20) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` decimal(30,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `status` enum('available','out of stock','discontinued') DEFAULT 'available',
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `weight` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `products` */

insert  into `products`(`product_id`,`product_name`,`description`,`price`,`stock_quantity`,`category_id`,`brand_id`,`product_image`,`status`,`average_rating`,`total_reviews`,`weight`,`created_at`,`updated_at`) values 
('P20240629190532','ASUS ROG','Laptop Gaming',15000000.00,9,5,4,'laptop_rog_strix.png','available',0.00,0,2.50,'2024-06-30 00:05:32','2024-06-30 00:40:49');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `tokenize` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `login_date` datetime DEFAULT NULL,
  `logout_date` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `no_hp` varchar(15) NOT NULL,
  `saldo` int(100) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `tanggalpengemasan` datetime DEFAULT NULL,
  `tanggalpengiriman` datetime DEFAULT NULL,
  `photo_profile` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`userid`,`username`,`password`,`fullname`,`tokenize`,`created_at`,`updated_at`,`login_date`,`logout_date`,`status`,`no_hp`,`saldo`,`alamat`,`tanggalpengemasan`,`tanggalpengiriman`,`photo_profile`) values 
(16,'amirul007','$2y$10$uJ0clQWr2QMo9uYDY1UKE.ONUil.fcrmmQzJoos5.6AFiFw3MTgkm','Amirul Putra Justicia','5acf120c6c819229fe868cb4d6697ae028bc0bd6621c62cb634b481042dc3129','2024-06-28 22:53:37',NULL,'2024-06-30 06:57:42','2024-06-30 09:42:36','Admin','082134402383',1000000000,NULL,NULL,NULL,'');

/*Table structure for table `wishlist` */

DROP TABLE IF EXISTS `wishlist`;

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `wishlist` */

insert  into `wishlist`(`id`,`user_id`,`product_id`,`created_at`) values 
(6,16,'0','2024-06-30 08:36:56');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

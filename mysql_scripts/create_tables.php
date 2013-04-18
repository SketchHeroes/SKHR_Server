<?php

const MYSQL_CREATE_CODE ='CREATE TABLE `users` (
									`user_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
									`type` INT(1) UNSIGNED ZEROFILL NOT NULL,
									`password` VARCHAR(12) NULL DEFAULT NULL,
									`id` INT(15) UNSIGNED NULL DEFAULT NULL,
									`name` VARCHAR(25) NULL DEFAULT NULL,
									`first_name` VARCHAR(25) NULL DEFAULT NULL,
									`last_name` VARCHAR(25) NULL DEFAULT NULL,
									`link` VARCHAR(50) NULL DEFAULT NULL,
									`username` VARCHAR(25) NULL DEFAULT NULL,
									`birthday` CHAR(10) NULL DEFAULT NULL,
									`gender` INT(1) UNSIGNED NULL DEFAULT NULL,
									`email` VARCHAR(35) NULL DEFAULT NULL,
									`timezone` INT(2) NULL DEFAULT NULL,
									`locale` VARCHAR(6) NULL DEFAULT NULL,
									`verified` BIT(1) NOT NULL,
									`updated_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
									`created_time` TIMESTAMP DEFAULT 0,
									PRIMARY KEY (`user_id`)
								)
								COLLATE=\'latin1_swedish_ci\'
								ENGINE=InnoDB
								AUTO_INCREMENT 320000200020001;';


?>
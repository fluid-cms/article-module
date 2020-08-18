CREATE TABLE IF NOT EXISTS `article_series` (
  `id`             int(11) NOT NULL AUTO_INCREMENT,
  `name`           varchar(200) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

ALTER TABLE `article_series` ADD UNIQUE `name` (`name`);

ALTER TABLE `article_page` ADD `series_id` int(11) NULL after `main_category_id`;

ALTER TABLE `article_page` ADD FOREIGN KEY (`series_id`) REFERENCES `article_series` (`id`) ON DELETE CASCADE;
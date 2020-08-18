CREATE TABLE IF NOT EXISTS `article_category` (
  `id`             int(11) NOT NULL AUTO_INCREMENT,
  `name`           varchar(200) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

ALTER TABLE `article_category` ADD UNIQUE `name` (`name`);

CREATE TABLE IF NOT EXISTS `article_page` (
  `id`                  int(11) NOT NULL AUTO_INCREMENT,
  `title`               varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `keywords`            varchar(256) COLLATE utf8_czech_ci DEFAULT NULL,
  `perex`               text COLLATE utf8_czech_ci NULL,
  `article`             text COLLATE utf8_czech_ci NOT NULL,
  `created_on`          datetime NOT NULL,
  `created_by_id`       int(11) COLLATE utf8_czech_ci NOT NULL,
  `published_on`        datetime DEFAULT NULL,
  `is_publish`          tinyint(1) NOT NULL DEFAULT '1',
  `can_add_comment`     tinyint(1) NOT NULL DEFAULT '1',
  `number_of_comments`  int NOT NULL DEFAULT '0',
  `main_category_id`    int(11) COLLATE utf8_czech_ci NOT NULL,
  `author_id`           int(11) COLLATE utf8_czech_ci NULL,
  `author_name`         varchar(64) COLLATE utf8_czech_ci NULL,
  `author_link`         varchar(256) COLLATE utf8_czech_ci NULL,
  `show_author`         tinyint(1) NOT NULL DEFAULT '1',
  `last_edited_by_id`   int(11) COLLATE utf8_czech_ci NULL,
  `last_edited_on`      datetime DEFAULT NULL,
  `photogallery_gallery_id` int(11) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

ALTER TABLE `article_page` ADD FOREIGN KEY (`photogallery_gallery_id`) REFERENCES `photogallery_gallery` (`id`) ON DELETE SET NULL; /*TODO vyresit mozny konflikt, kdy nemusi byt photogallery modul*/
ALTER TABLE `article_page` ADD FOREIGN KEY (`main_category_id`) REFERENCES `article_category` (`id`) ON DELETE RESTRICT;
ALTER TABLE `article_page` ADD FOREIGN KEY (`author_id`) REFERENCES `admin_user` (`id`) ON DELETE RESTRICT;
ALTER TABLE `article_page` ADD FOREIGN KEY (`last_edited_by_id`) REFERENCES `admin_user` (`id`) ON DELETE RESTRICT;
ALTER TABLE `article_page` ADD FOREIGN KEY (`created_by_id`) REFERENCES `admin_user` (`id`) ON DELETE RESTRICT;

CREATE TABLE `article_article_category` (
  `id`                  int(11) NOT NULL AUTO_INCREMENT,
  `article_id`          int(11) NOT NULL,
  `category_id`         int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

ALTER TABLE `article_article_category` ADD FOREIGN KEY (`article_id`) REFERENCES `article_page` (`id`) ON DELETE CASCADE;
ALTER TABLE `article_article_category` ADD FOREIGN KEY (`category_id`) REFERENCES `article_category` (`id`) ON DELETE RESTRICT;

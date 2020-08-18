CREATE TABLE IF NOT EXISTS `article_topic` (
  `id`             int(11) NOT NULL AUTO_INCREMENT,
  `name`           varchar(256) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `article_topic_article` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `article_id` int(11) NOT NULL,
  `article_topic_id` int(11) NOT NULL,
  FOREIGN KEY (`article_id`) REFERENCES `article_page` (`id`),
  FOREIGN KEY (`article_topic_id`) REFERENCES `article_topic` (`id`)
);
ALTER TABLE `article_page` ADD FULLTEXT `title_perex_article` (`title`, `perex`, `article`);
ALTER TABLE `article_page` ADD FULLTEXT `title` (`title`);
ALTER TABLE `article_page` ADD FULLTEXT `perex_article` (`perex`, `article`);
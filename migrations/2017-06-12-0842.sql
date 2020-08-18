ALTER TABLE `article_page`
ADD INDEX `is_publish` (`is_publish`),
ADD INDEX `published_on` (`published_on`),
ADD INDEX `author_name` (`author_name`),
ADD INDEX `main_category_id` (`main_category_id`),
ADD INDEX `photogallery_gallery_id` (`photogallery_gallery_id`);
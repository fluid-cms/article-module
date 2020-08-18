<?php

namespace Grapesc\GrapeFluid\ArticleModule\Model;

use Grapesc\GrapeFluid\Model\BaseModel;


class ArticleTopicArticleModel extends BaseModel
{

	/**
	 * @inheritdoc
	 */
	public function getTableName()
	{
		return "article_topic_article";
	}

}
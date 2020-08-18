<?php

namespace Grapesc\GrapeFluid\ArticleModule\Model;

use Grapesc\GrapeFluid\Model\BaseModel;


class ArticleArticleCategoryModel extends BaseModel
{

	/**
	 * @inheritdoc
	 */
	public function getTableName()
	{
		return "article_article_category";
	}


	/**
	 * @param $articleId
	 * @return mixed
	 */
	public function getCategoryIdsForArticle($articleId)
	{
		return $this->getTableSelection()
			->select('category_id')
			->where('article_id', $articleId)
			->fetchPairs('category_id', 'category_id');
	}

}
<?php

namespace Grapesc\GrapeFluid\ArticleModule\Model;

use Grapesc\GrapeFluid\Model\BaseModel;


class ArticlePageModel extends BaseModel
{

	/**
	 * @inheritdoc
	 */
	public function getTableName()
	{
		return "article_page";
	}


	/**
	 * @param $articleId
	 */
	public function addShow($articleId)
	{
		$this->getTableSelection()
			->where(['id' => $articleId])
			->update(['show_counter+=' => 1]);
	}


	/**
	 * @param int $seriesId
	 * @param int|null $notShowArticleId
	 * @param null $limit
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getItemsBySeries($seriesId, $notShowArticleId = null, $limit = null)
	{
		$selection = $this->getTableSelection()
			->select('*')
			->where('series_id', $seriesId);

		if ($notShowArticleId) {
			$selection->where('id != ?', $notShowArticleId);
		}

		if ($limit) {
			$selection->limit($limit);
		}

		return $selection->order('created_on DESC')->fetchAll();
	}

}
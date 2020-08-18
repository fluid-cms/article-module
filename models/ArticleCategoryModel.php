<?php

namespace Grapesc\GrapeFluid\ArticleModule\Model;

use Grapesc\GrapeFluid\Model\BaseModel;


class ArticleCategoryModel extends BaseModel
{

	/**
	 * @inheritdoc
	 */
	public function getTableName()
	{
		return "article_category";
	}

	/**
	 * Vrátí seznam katgorii použitelných pro SelectBox (["id" => "name"])
	 *
	 * @return array
	 */
	public function getIdNameForSelect()
	{
		$select = [];
		foreach ($this->getTableSelection()->order('id ASC') as $id => $value) {
			$select[$id] = $value['name'];
		}
		return $select;
	}

}
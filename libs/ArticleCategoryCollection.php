<?php
namespace Grapesc\GrapeFluid\ArticleModule\Collection;

use Grapesc\GrapeFluid\ArticleModule\Model\ArticleCategoryModel;
use Grapesc\GrapeFluid\LinkCollector\ILinkCollection;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;


/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class ArticleCategoryCollection implements ILinkCollection
{

	/** @var ArticleCategoryModel @inject */
	public $articleCategoryModel;


	/**
	 * @return array
	 */
	public function getLinks()
	{
		$collection = [];
		/** @var ActiveRow $page */
		foreach ($this->articleCategoryModel->getAllItems() as $category) {
			$collection[$category->name] = [":Article:Article:category", json_encode(["categoryId" => $category->id, "categoryName" => Strings::webalize($category->name), "page" => 1, "date" => null, "search" => null])];
		}
		return $collection;
	}

}
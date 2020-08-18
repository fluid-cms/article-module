<?php
namespace Grapesc\GrapeFluid\ArticleModule\RouteFilter;

use Grapesc\GrapeFluid\ArticleModule\Model\ArticlePageModel;
use Nette\Utils\Strings;


/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class Article
{

	/** @var ArticlePageModel */
	private $articlePageModel;


	public function __construct(ArticlePageModel $articlePageModel)
	{
		$this->articlePageModel = $articlePageModel;
	}


	/**
	 * @param int $arg
	 * @return string
	 */
	public function filterOut($arg)
	{
		$article = $this->articlePageModel->getItem($arg);
		return $article ? ($arg . '-' . Strings::webalize($article->title)) : $arg;
	}


	/**
	 * @param string $arg
	 * @return int
	 */
	public function filterIn($arg)
	{
		return explode('-', $arg)[0];
	}

}
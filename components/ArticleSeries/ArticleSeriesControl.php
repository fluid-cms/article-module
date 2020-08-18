<?php

namespace Grapesc\GrapeFluid\ArticleModule\Control\ArticleSeries;

use Grapesc\GrapeFluid\ArticleModule\Model\ArticlePageModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleSeriesModel;
use Grapesc\GrapeFluid\MagicControl\BaseMagicTemplateControl;


/**
 * Class ArticleSeriesControl
 * @package Grapesc\GrapeFluid\ArticleModule\Control
 * @usage: {magicControl articleSeries, ['uid', 'int id', 'int notShowArticleId']}
 */
class ArticleSeriesControl extends BaseMagicTemplateControl
{

	const LIMIT = 10;

	/** @var int */
	private $id;

	/** @var int */
	private $actualArticleId;

	/** @var string|null */
	protected $defaultTemplateFilename = __DIR__ . '/articleSeries.latte';

	/** @var ArticlePageModel @inject */
	public $articlePageModel;

	/** @var ArticleSeriesModel @inject */
	public $articleSeriesModel;



	/**
	 * @param array $params
	 */
	public function setParams(array $params = [])
	{
		if (isset($params[1]) && is_int($params[1])) {
			$this->id = $params[1];
		}

		if (isset($params[2]) && is_int($params[2])) {
			$this->actualArticleId = $params[2];
		}
	}


	public function render()
	{
		$this->template->seriesId = $this->id;
		if ($this->id) {
			$this->template->seriesName = $this->articleSeriesModel->getItemsBy($this->id)->fetch()['name'];
			$this->template->seriesArticles = $this->articlePageModel->getItemsBySeries($this->id, null, self::LIMIT);
			$this->template->actualArticleId = $this->actualArticleId;
		}
		$this->template->render();
	}

}

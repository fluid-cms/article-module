<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\ArticleModule\ArticleSeriesForm;
use Grapesc\GrapeFluid\ArticleModule\Grid\ArticleSeriesGrid;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleSeriesModel;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormControl;


class ArticleSeriesPresenter extends BasePresenter
{

	/** @var ArticleSeriesForm @inject */
	public $articleSeriesForm;

	/** @var ArticleSeriesModel @inject */
	public $articleSeriesModel;


	protected function createComponentArticleSeriesForm()
	{
		return new FluidFormControl($this->articleSeriesForm);
	}


	protected function createComponentArticleSeriesGrid()
	{
		return new ArticleSeriesGrid($this->articleSeriesModel, $this->translator);
	}


	public function renderEdit($id = 0)
	{
		$articleSeries = $this->articleSeriesModel->getItem($id);

		if ($articleSeries) {
			$this->getComponent("articleSeriesForm")->setDefaults($articleSeries);
		} else {
			$this->flashMessage("Požadovaná stránka neexistuje", "warning");
			$this->redirect(":Admin:ArticleSeries:default");
		}
	}

}
<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\ArticleModule\Grid\ArticleCategoryGrid;
use Grapesc\GrapeFluid\ArticleModule\ArticleCategoryForm;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleCategoryModel;
use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormControl;


class ArticleCategoryPresenter extends BasePresenter
{

	/** @var ArticleCategoryForm @inject */
	public $articleCategoryForm;

	/** @var ArticleCategoryModel @inject */
	public $articleCategoryModel;


	protected function createComponentArticleCategoryForm()
	{
		return new FluidFormControl($this->articleCategoryForm);
	}


	protected function createComponentArticleCategoryGrid()
	{
		return new ArticleCategoryGrid($this->articleCategoryModel, $this->translator);
	}


	public function renderEdit($id = 0)
	{
		$articleCategory = $this->articleCategoryModel->getItem($id);

		if ($articleCategory) {
			$this->getComponent("articleCategoryForm")->setDefaults($articleCategory);
		} else {
			$this->flashMessage("Požadovaná stránka neexistuje", "warning");
			$this->redirect(":Admin:ArticleCategory:default");
		}
	}

}
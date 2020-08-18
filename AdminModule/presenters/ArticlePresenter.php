<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\ArticleModule\ArticlePageForm;
use Grapesc\GrapeFluid\ArticleModule\Grid\ArticlePageGrid;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleArticleCategoryModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticlePageModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleTopicArticleModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleTopicModel;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormFactory;
use Grapesc\GrapeFluid\FluidGrid\FluidGridFactory;
use Grapesc\GrapeFluid\ModuleRepository;
use Nette\DI\Container;


class ArticlePresenter extends BasePresenter
{

	/** @var ArticlePageModel @inject */
	public $articlePageModel;

	/** @var ArticleArticleCategoryModel @inject */
	public $articleArticleCategoryModel;

	/** @var ArticleTopicModel @inject */
	public $articleTopicModel;

	/** @var ArticleTopicArticleModel @inject */
	public $articleTopicArticleModel;


	/** @var FluidGridFactory @inject */
	public $fluidGridFactory;

	/** @var FluidFormFactory @inject */
	public $fluidFormFactory;

	/** @var ModuleRepository @inject */
	public $moduleRepository;

	/** @var Container @inject */
	public $container;


	public function actionCreate()
	{
		$this->template->topics = $this->articleTopicModel->getTableSelection()->fetchPairs("id", "name");
	}


	public function actionEdit($id = 0)
	{
		$articlePage = $this->articlePageModel->getItem($id);

		if ($articlePage) {
			$this->template->topics = $this->articleTopicModel->getTableSelection()->fetchPairs("id", "name");
			$this->template->articlePage = $articlePage;
			$articlePage                 = $articlePage->toArray();
			$articlePage['categories']   = $this->articleArticleCategoryModel->getCategoryIdsForArticle($id);
			$topicIds                    = $this->articleTopicArticleModel->getTableSelection()->where('article_id', $id)->fetchPairs('article_topic_id', 'article_topic_id');
			$articlePage['topic']        = $this->articleTopicModel->getNamesByIds($topicIds);

			$this->getComponent("articlePageForm")->setDefaults($articlePage);
		} else {
			$this->flashMessage("Požadovaná stránka neexistuje", "warning");
			$this->redirect(":Admin:Article:default");
		}
	}


	public function handleCreatePhotogallery($id)
	{
		if ($this->moduleRepository->moduleExist('photoGallery')) {
			$articlePage = $this->articlePageModel->getItem($id);

			if ($articlePage AND !$articlePage->photogallery_gallery_id) {
				$galleryModel = $this->container->getByType(\Grapesc\GrapeFluid\PhotoGalleryModule\Model\GalleryModel::class);
				$itemId = $galleryModel->insert([
					'name'          => 'Fotogalerie k článku ' . $articlePage->title,
					'is_publish'    => $articlePage->is_publish,
					'created_by_id' => $this->user->getId(),
					'created_on'    => new \DateTime()
				]);
				$this->articlePageModel->update(['photogallery_gallery_id' => $itemId], $id);
				$this->redirect(':Admin:PhotoGallery:galleryPhotos', ['id' => $itemId]);
			} elseif($articlePage AND $articlePage->photogallery_gallery_id) {
				$this->redirect(':Admin:PhotoGallery:galleryPhotos', ['id' => $articlePage->photogallery_gallery_id]);
			}
		} else {
			$this->redirect('this');
		}
	}


	protected function createComponentArticlePageForm()
	{
		return $this->fluidFormFactory->create(ArticlePageForm::class);
	}


	protected function createComponentArticlePageGrid()
	{
		return $this->fluidGridFactory->create(ArticlePageGrid::class);
	}

}
<?php

namespace Grapesc\GrapeFluid\ArticleModule\Grid;

use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleCategoryModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleSeriesModel;
use Grapesc\GrapeFluid\FluidGrid;
use Grapesc\GrapeFluid\ModuleRepository;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use Nette\Security\User;
use TwiGrid\Components\Column;

/**
 * @model \Grapesc\GrapeFluid\ArticleModule\Model\ArticlePageModel
 */
class ArticlePageGrid extends FluidGrid
{

	/** @var ModuleRepository @inject */
	public $moduleRepository;

	/** @var Container @inject */
	public $container;

	/** @var User @inject */
	public $user;

	/** @var ArticleCategoryModel @inject */
	public $articleCategoryModel;

	/** @var ArticleSeriesModel @inject */
	public $articleSeriesModel;


	/** @var UserModel @inject */
	public $userModel;


	protected function build(): void
	{
		$this->addColumn('id')->setSortable(true);
		$this->setDefaultOrderBy("id", Column::DESC);
		$this->skipColumns(["keywords", "article", "perex", "created_by_id", "created_on", "author_name", "is_publish", "can_add_comment", "number_of_comments", "show_author", "last_edited_on", "photogallery_gallery_id", "link", "author_link"]);
		$this->setSortableColumns(["title", "published_on", "main_category_id", "show_counter"]);


		$this->setItemsPerPage(15);
		$this->enableFilters(['main_category_id', 'title']);

		$this->addRowAction("edit", "Upravit", function(ActiveRow $record) {
			$this->getPresenter()->redirect(":Admin:Article:edit", ["id" => $record->id]);
		});

		if ($this->moduleRepository->moduleExist('photoGallery')) {
			$this->addRowAction('photoGallery', "Spravovat fotografie k článku", function(ActiveRow $record) {
				$this->getPresenter()->redirect(":Admin:PhotoGallery:galleryPhotos", ["id" => $record->photogallery_gallery_id]);
			});

			$this->addRowAction('createPhotoGallery', "Přidata fotogalerii k článku", function(ActiveRow $record) {
				if ($record->photogallery_gallery_id) {
					return;
				}

				$galleryModel = $this->container->getByType(\Grapesc\GrapeFluid\PhotoGalleryModule\Model\GalleryModel::class);
				$itemId = $galleryModel->insert([
					'name'          => 'Fotogalerie k článku ' . $record->title,
					'is_publish'    => $record->is_publish,
					'created_by_id' => $this->user->getId(),
					'created_on'    => new \DateTime()
				])->id;
				$record->update(['photogallery_gallery_id' => $itemId]);

				$this->flashMessage("Fotogalerie vytvořena, můžete přidat fotografie", "success");
				$this->getPresenter()->redirect(":Admin:PhotoGallery:galleryPhotos", ["id" => $itemId]);
			});
		}

		if ($this->moduleRepository->moduleExist('video')) {
			$this->addRowAction('videoGallery', "Spravovat videa k článku", function(ActiveRow $record) {
				$this->getPresenter()->redirect(":Admin:Video:default", ["articleId" => $record->id]);
			});
		}

		$this->addRowAction("delete", "Smazat", function (ActiveRow $record) {
			try {
				$this->model->delete($record->id);
				$this->flashMessage("Článek smazán", "success");

				if ($this->moduleRepository->moduleExist('photoGallery') AND $record->photogallery_gallery_id) {
					$imageRepository = $this->container->getByType(\Grapesc\GrapeFluid\PhotoGallery\ImageRepository::class);
					$imageRepository->deleteGallery($record->photogallery_gallery_id);
					$this->flashMessage("Fotogaleriie smazána", "success");
				}

			} catch (\Exception $e) {
				$this->flashMessage("Článek se nepodařilo smazat", "error");
			}
			$this->getPresenter()->redrawControl('articleGrid');
			$this->getPresenter()->redrawControl('flashMessages');
		});

		$this->addRowAction("changePublish", "Změna publikace", function (ActiveRow $record) {
			try {
				$data = ['is_publish' => !$record->is_publish];
				if ($record->published_on == null) {
					$data['published_on'] = new \DateTime();
				}

				$this->model->update($data, $record->id);

				if ($this->moduleRepository->moduleExist('photoGallery') AND $record->photogallery_gallery_id) {
					$galleryModel = $this->container->getByType(\Grapesc\GrapeFluid\PhotoGalleryModule\Model\GalleryModel::class);
					$galleryModel->update(['is_publish' => !$record->is_publish], $record->photogallery_gallery_id);
				}

				if ($record->is_publish) {
					$this->flashMessage("Publikace článku '$record->title' zrušena", "success");
				} else {
					$this->flashMessage("Článek '$record->title' publikován", "success");
				}
			} catch (\Exception $e) {
				$this->flashMessage("Nepodařilo se změnit publikaci", "error");
			}

			$this->getPresenter()->redrawControl('articleGrid');
			$this->getPresenter()->redrawControl('flashMessages');
		});

		parent::build();

		$this->addColumn('topic', 'Téma' ,'series_id')
			->setSortable(false);

		$this->addColumn('categories', 'Další kategorie', 'topic')
			->setSortable(false);

	}


	protected function getFilterContainer()
	{
		$container = parent::getFilterContainer();
		$container->addSelect('main_category_id', 'Hlavní kategorie', $this->articleCategoryModel->getIdNameForSelect());
		$container->addSelect('author_id', 'Autor', $this->userModel->getTableSelection()->fetchPairs('id', 'name'));
		$container->addSelect('series_id', 'Seriál', $this->articleSeriesModel->getIdNameForSelect());
		return $container;
	}

}
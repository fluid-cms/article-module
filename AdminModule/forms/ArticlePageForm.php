<?php

namespace Grapesc\GrapeFluid\ArticleModule;

use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleArticleCategoryModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleCategoryModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticlePageModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleSeriesModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleTopicArticleModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleTopicModel;
use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Grapesc\GrapeFluid\ModuleRepository;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Nette\DI\Container;
use Nette\Security\User;


class ArticlePageForm extends FluidForm
{

	/** @var ArticlePageModel @inject */
	public $model;

	/** @var ArticleCategoryModel @inject */
	public $articleCategoryModel;

	/** @var ArticleArticleCategoryModel @inject */
	public $articleArticleCategoryModel;

	/** @var ArticleSeriesModel @inject */
	public $articleSeriesModel;

	/** @var ArticleTopicModel @inject */
	public $articleTopicModel;

	/** @var ArticleTopicArticleModel @inject */
	public $articleTopicArticleModel;

	/** @var UserModel @inject */
	public $userModel;

	/** @var User @inject */
	public $user;

	/** @var ModuleRepository @inject */
	public $moduleRepository;

	/** @var Container @inject */
	public $container;


	protected function build(Form $form)
	{
		$form->addHidden("id");

		$form->addText("title", "Titulek")
			->setAttribute("cols", 8)
			->addRule(Form::MAX_LENGTH, "Maximální velikost je %s znaků", 128)
			->setRequired("Titulek je povinný");

		$form->addSelect('main_category_id', 'Hlavní kategorie', $this->articleCategoryModel->getIdNameForSelect())
			->setAttribute("cols", 4)
			->setRequired(true)
			->setPrompt('-- vyberte --');

		//todo
//		$form->addText("keywords", "Klíčová slova (SEO keywords)")
//			->setRequired(false)
//			->setAttribute("cols", 10)
//			->setAttribute("help", "Jednotlivá slova (oddělujte čárkou)")
//			->addRule(Form::MAX_LENGTH, "Maximální velikost je %s znaků", 256);

		$form->addTextArea("perex", "Perex", null, 6)
			->setOption('description', "Krátký úvod článku, který naznačuje o čem článek bude (ideálně 2 -5 vět)")
			->setAttribute("cols", 8)
			->setRequired(false)
			->addCondition(Form::FILLED)
				->addRule(Form::MAX_LENGTH, "Maximální velikost je %s znaků", 65535);

		$form->addMultiSelect('categories', 'Další kategorie', $this->articleCategoryModel->getIdNameForSelect())
			->setRequired(false)
			->setAttribute("cols", 4);

		$form->addText('topic', 'Témata ke článku')
			->setRequired(false)
			->setAttribute("cols", 4)
			->setOption('description', "Zadejte jedno nebo více témat")
			->addCondition(Form::FILLED)
			->addRule(Form::MAX_LENGTH, "Maximální velikost je %s znaků", 256);

		$form->addSelect('series_id', 'Seriál', $this->articleSeriesModel->getIdNameForSelect())
			->setAttribute("cols", 4)
			->setRequired(false)
			->setPrompt('-- vyberte --');

		$form->addTextArea("article", "Obsah")
			->setAttribute("class", "form-summernote");

		$form['article']->addConditionOn($form['perex'], Form::BLANK)
			->addRule(Form::FILLED, "Musíte vyplnit perex nebo obsah.");

		$form->addGroup('Publikace a autor');

		if (!$this->isEditMode() || !$this->getDefaultValue('is_publish', false)) {
			$form->addHidden('is_publish', false);
		} else {
			$form->addCheckbox("is_publish", "Publikovat")
				->setDefaultValue(false)
				->setRequired(false)
				->setOption("cols", 2);
		}

		$form->addText("published_on", "Datum zveřejnění")
			->setRequired(false)
			->setAttribute("cols", 10)
			->setAttribute('class', 'dtpicker')
//			->setDefaultValue((new \DateTime())->format(DATE_ISO8601))
			->setOption('description', 'Pokud nastavíte budoucí čas, bude článek zveřejněn až po tomto datu');
//			->setAttribute("placeholder", "dd.mm.rrrr hh:mm:ss")
//			->addRule($form::PATTERN, "Datum musí být ve formátu dd.mm.rrrr hh:mm:ss", "(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[012])\.(19|20)\d\d ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])");

		$form->addCheckbox("show_author", "Zobrazit autora")
			->setDefaultValue(true)
			->setRequired(false)
			->setOption("cols", 12);

		if ($this->user->isInRole('admin')) {
			$form->addSelect('author_id', 'Autor', $this->userModel->getTableSelection()->fetchPairs('id', 'name'))
				->setPrompt("Zvolte autora")
				->setAttribute("cols", 4)
				->setRequired(false);
		} else {
			$form->addHidden('author', $this->user->getIdentity()->getId());
		}

		$form->addText('author_name', 'Jméno autora')
			->setAttribute("cols", 4)
			->addRule(Form::MAX_LENGTH, "Maximální velikost je %s znaků", 64)
			->setRequired(false);

		$form->addText('author_link', 'Odkaz na stránku autora')
			->setAttribute("cols", 4)
			->addRule(Form::MAX_LENGTH, "Maximální velikost je %s znaků", 256)
			->setRequired(false)
			->addCondition(Form::FILLED)
				->addRule(Form::URL, "Odkaz na stránku autora musí být platná webová adresa");


		$form->addGroup('Nastavení');

		if ($this->moduleRepository->moduleExist('comment')) {
			$form->addCheckbox("can_add_comment", "Povolit komentáře")
				->setDefaultValue(true)
				->setRequired(false)
				->setOption("cols", 3);
		}

		if ($this->moduleRepository->moduleExist('photoGallery')) {
			if ($this->isEditMode()) {
				$form->addHidden('photogallery_gallery_id');
			}
		}

	}


	/**
	 * @param Form $form
	 */
	protected function addButtons(Form $form)
	{
		parent::addButtons($form);

		if (!$this->isEditMode() || !$this->getDefaultValue('is_publish', false)) {
			$form['submit']->caption = 'Uložit a nepublikovat';
			$form->addSubmit('publish', "Uložit a publikovat")
				->setOption('description', 'Článek se uloží jako publikovatelný')
				->setAttribute('class', 'btn btn-success');
		}

		$form->addSubmit("save", "Uložit a Zůstat")
			->setAttribute('class', 'btn btn-info');
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	protected function submit(Control $control, Form $form)
	{
		$presenter              = $control->getPresenter();
		$values                 = $this->getValues(true);
		$values['is_publish']   = $form->isSubmitted()->getName() == 'publish' ? true : (isset($values['is_publish']) ? $values['is_publish'] : false);

		if ($form->isSubmitted()->getName() == 'publish' AND !$values['published_on']) {
			$values["published_on"] = new \DateTime();
		} else {
			$values["published_on"] = $values["published_on"] ? DateTime::from($values["published_on"]) : null;
		}

		$categories = $values["categories"];

		unset($values['categories']);

		if (isset($values['id']) && (int) $values['id'] > 0) {
			$values['last_edited_by_id'] = $this->user->getIdentity()->getId();
			$values['last_edited_on']    = new DateTime();

			if ($this->moduleRepository->moduleExist('photoGallery') AND isset($values['photogallery_gallery_id'])) {
				$galleryModel = $this->container->getByType(\Grapesc\GrapeFluid\PhotoGalleryModule\Model\GalleryModel::class);
				$galleryModel->update([
					'name'       => 'Fotogalerie k článku ' . $values['title'],
					'is_publish' => $values['is_publish']
				], $values['photogallery_gallery_id']);
			}

			unset($values['photogallery_gallery_id']);

			$this->articleArticleCategoryModel->delete($values['id'], 'article_id');
			foreach ($categories as $category) {
				if ($values['main_category_id'] != $category) {
					$this->articleArticleCategoryModel->insert(['article_id' => $values['id'], 'category_id' => $category]);
				}
			}

			$this->articleTopicArticleModel->delete($values['id'], 'article_id');
			$topics = $this->articleTopicModel->getTopicIdName($values['topic']);
			foreach (array_keys($topics) as $topicId) {
				$this->articleTopicArticleModel->insert(['article_id' => $values['id'], 'article_topic_id' => $topicId]);
			}

			$this->model->update($this->model->clearingValues($values), $values['id']);
			$presenter->flashMessage("Článek uložen", "success");
			$presenter->redrawControl("flashMessages");
		} else {
			unset($values['id']);
			$values['created_by_id'] = $this->user->getIdentity()->getId();
			$values['created_on']    = new DateTime();
			$values["published_on"]  = $values["published_on"] ?: null;

			$IRow = $this->model->insert($this->model->clearingValues($values));
			$this->createdId = $IRow->id;

			foreach ($categories as $category) {
				$this->articleArticleCategoryModel->insert(['article_id' => $IRow->id, 'category_id' => $category]);
			}

			$topics = $this->articleTopicModel->getTopicIdName($values['topic']);
			foreach (array_keys($topics) as $topicId) {
				$this->articleTopicArticleModel->insert(['article_id' => $IRow->id, 'article_topic_id' => $topicId]);
			}

			$presenter->flashMessage("Článek vytvořen", "success");
		}
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	protected function afterSucceedSubmit(Control $control, Form $form)
	{
		if ($form->isSubmitted()->getName() == 'save') {
			if ($this->isEditMode()) {
				$control->getPresenter()->redirect('this');
			} else {
				$control->getPresenter()->redirect('edit', ['id' => $this->getCreatedId()]);
			}
		} else {
			$control->getPresenter()->redirect('default');
		}
	}

}
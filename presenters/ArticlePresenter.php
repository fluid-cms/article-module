<?php

namespace Grapesc\GrapeFluid\ArticleModule\Presenters;

use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleCategoryModel;
use Grapesc\GrapeFluid\CoreModule\Service\BreadCrumbService;
use Grapesc\GrapeFluid\ModuleRepository;
use Nette\Application\BadRequestException;
use Nette\Utils\Strings;

class ArticlePresenter extends BasePresenter
{

	const ITEM_PER_PAGE = 10;
	/**
	 * @var int
	 * @persistent
	 */
	public $page = 1;

	/**
	 * @var string|null
	 * @persistent
	 */
	public $search = null;

	/**
	 * @var string|null
	 * @persistent
	 */
	public $date = null;

	/**
	 * @var string|null
	 * @persistent
	 */
	public $topic = null;

	/** @var BreadCrumbService @inject */
	public $breadCrumbService;

	/** @var ArticleCategoryModel @inject */
	public $articleCategoryModel;
	
	/** @var UserModel @inject */
	public $userModel;

	/** @var ModuleRepository @inject */
	public $moduleRepository;


	public function actionDefault($filter = [])
	{
		$this->template->itemPerPage = self::ITEM_PER_PAGE;
		$filter['yearMonth']         = $this->date;
		$filter['searchText']        = $this->search;
		$filter['topic']             = $this->topic;
		$this->template->filter      = $filter;

		$this->breadCrumbService->addLink('Archív');
	}


	public function actionCategory($categoryId, $categoryName, $filter = [])
	{
		$category = $this->articleCategoryModel->getItem($categoryId);

		if (!$category) {
			throw new BadRequestException();
		}

		$this->template->category    = $category;
		$filter['categoryId']        = $category->id;
		$filter['yearMonth']         = $this->date;
		$filter['searchText']        = $this->search;
		$filter['topic']             = $this->topic;
		$this->template->itemPerPage = self::ITEM_PER_PAGE;
		$this->template->filter      = $filter;

		$this->breadCrumbService->addLink($category->name, [':Article:Article:category', ['categoryId' => $category->id, 'categoryName' => Strings::webalize($category->name), 'page' => 1, 'search' => null, 'date' => null]]);
		$this->breadCrumbService->addLink('Archív');
	}


	public function actionAuthor($authorId, $authorName)
	{
		$author = $this->userModel->getItem($authorId);
		$this->template->author      = $author;
		$this->template->itemPerPage = self::ITEM_PER_PAGE;
		$this->template->filter      = ['authorId' => $authorId];

		$this->breadCrumbService->addLink('Články', [':Article:Article:default']);
		$this->breadCrumbService->addLink('Autoři');
		$this->breadCrumbService->addLink($author->name);
	}


	public function actionTopic($topic, $filter = [])
	{
		if (!$topic) {
			throw new BadRequestException();
		}

		$filter['yearMonth']         = $this->date;
		$filter['searchText']        = $this->search;
		$filter['topic']             = $topic;
		$this->template->topic       = $topic;
		$this->template->itemPerPage = self::ITEM_PER_PAGE;
		$this->template->filter      = $filter;

		$this->breadCrumbService->addLink($this->translator->translate('Téma') . ': ' . $topic, [':Article:Article:topic', ['topic' => $topic, 'page' => 1, 'search' => null, 'date' => null]]);
	}


	public function actionSeries($seriesId, $seriesName)
	{

		if (!$seriesId || !$seriesName) {
			throw new BadRequestException();
		}

		$this->template->seriesName  = $seriesName;
		$this->template->itemPerPage = self::ITEM_PER_PAGE;
		$this->template->filter      = ['seriesId' => $seriesId];

		$this->breadCrumbService->addLink($this->translator->translate('Seriál') . ': ' . $seriesName, [':Article:Article:series', ['seriesId' => $seriesId, 'seriesName' => $seriesName, 'page' => 1]]);
	}


	public function handleRemoveTopic()
	{
		$this->topic = null;
		$this->redirect('default');
	}

}
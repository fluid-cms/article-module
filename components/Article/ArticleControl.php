<?php

namespace Grapesc\GrapeFluid\ArticleModule\Control\Article;

use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleCategoryModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticlePageModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleTopicModel;
use Grapesc\GrapeFluid\MagicControl\BaseMagicTemplateControl;
use Grapesc\GrapeFluid\ModuleRepository;
use Grapesc\GrapeFluid\PhotoGalleryModule\Model\PhotoModel;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;
use Nette\Utils\Paginator;
use Nette\Utils\Strings;

/**
 * Class ArticleControl
 * @package Grapesc\GrapeFluid\ArticleModule\Control
 * @usage: {magicControl article, ['uid', 'int limit', 'int start', 'boolean showAll', 'array filter']}
 */
class ArticleControl extends BaseMagicTemplateControl
{

	//todo mozna nekde nastavovat
	/** @var integer -> number of pages go next or back*/
	protected $pageMove = 4;

	/** @var ArticlePageModel @inject */
	public $model;
	
	/** @var PhotoModel @inject */
	public $photoModel;

	/** @var ArticleCategoryModel @inject */
	public $articleCategoryModel;

	/** @var ArticleTopicModel @inject */
	public $articleTopicModel;

	/** @var UserModel @inject */
	public $userModel;

	/** @var ModuleRepository @inject */
	public $moduleRepository;

	/** @var integer */
	protected $limit = 5;

	/** @var integer */
	protected $offset = 0;

	/** @var boolean */
	protected $showAll = false;

	/** @var integer */
	protected $categoryId;

	/** @var array */
	protected $filter = [];

	/** @var string|null */
	protected $defaultTemplateFilename = __DIR__ . '/article.latte';


	/**
	 * @param array $params
	 */
	public function setParams(array $params = [])
	{

		if (isset($params[1]) && is_int($params[1])) {
			$this->limit = $params[1];
		}

		if (isset($params[2]) && is_int($params[2])) {
			$this->offset = $params[2];
		}

		if (isset($params[3])) {
			$this->showAll = $params[3];
		}

		if (isset($params[4])) {
			if (array_key_exists('categoryId', $params[4])) {
				$this->categoryId = $params[4]['categoryId'];
			}

			$this->filter = $params[4];
		}

	}


	public function render()
	{
		if ($this->showAll) {
			$paginator = new Paginator();
			$itemCount = $this->getItemCount();
			$paginator->setItemCount($itemCount);
			$paginator->setItemsPerPage($this->limit);

			$page         = $this->offset > 1 ? ceil($this->offset < $itemCount ? ($this->offset / $this->limit) : ($itemCount / $this->limit)) : 1;
			$this->offset = ($page - 1) * $this->limit;
			$paginator->setPage($page);

			$this->template->paginator  = $paginator;
			$this->template->pageMove   = $this->pageMove;
			$this->template->categories = $this->articleCategoryModel->getIdNameForSelect();
			$this->template->lastMonths = $this->getLastMonths(10);
			$this->template->categoryId = $this->categoryId;
			$this->template->yearMonth  = array_key_exists('yearMonth', $this->filter) ? $this->filter['yearMonth'] : null;
			$this->template->authorName = array_key_exists('authorName', $this->filter) ? $this->getArticleQuery()->fetch()->author_name : null;
			$this->template->authorId   = array_key_exists('authorId', $this->filter) ? (int)$this->filter['authorId'] : null;
			$this->template->searchText = array_key_exists('searchText', $this->filter) ? $this->filter['searchText'] : null;
			$this->template->topic      = array_key_exists('topic', $this->filter) ? $this->filter['topic'] : null;
			$this->template->seriesId   = array_key_exists('seriesId', $this->filter) ? $this->filter['seriesId'] : null;
		}
		$this->template->showAll  = $this->showAll;
		$this->template->articles = $this->getArticles();

		$this->template->render();
	}


	public function handleSubmitArticleFilter($extraFilter = [])
	{
		$filter     = [];
		$categoryId = $this->getPresenter()->getHttpRequest()->getPost('categorySelect');
		$yearMonth  = $this->getPresenter()->getHttpRequest()->getPost('yearMonthSelect');
		$searchText = $this->getPresenter()->getHttpRequest()->getPost('searchText');

		if ($categoryId) {
			$filter = ["categoryId" => $categoryId, "categoryName" => Strings::webalize($this->articleCategoryModel->getItem($categoryId)->name)];
		}

		if($yearMonth) {
			$filter['date'] = $yearMonth;
		}

		if ($searchText) {
			$filter['search'] = $searchText;
		}

		if ($extraFilter) {
			$filter['filter'] = $extraFilter;
		}


		$this->getPresenter()->redirect($categoryId ? ':Article:Article:category' : ':Article:Article:default', $filter);
	}


	/**
	 * @return array|\Nette\Database\Table\IRow[]|\Nette\Database\Table\Selection
	 */
	protected function getArticles()
	{
		return $this->getArticleQuery(true)->fetchAll();
	}


	/**
	 * @return int
	 */
	protected function getItemCount()
	{
		return $this->getArticleQuery(false)->count();
	}


	/**
	 * @param bool $useLimitAndOffset
	 * @return Selection
	 */
	protected function getArticleQuery($useLimitAndOffset = false)
	{
		$selection = $this->model->getTableSelection()
			->where('is_publish', true)
			->where('published_on <= ?', new DateTime());

		if ($useLimitAndOffset) {
			$selection->order("published_on DESC")->limit($this->limit, $this->offset);
		}

		if (isset($this->filter['yearMonth'])) {
			$selection->where('published_on >= ?', DateTime::from($this->filter['yearMonth']));
			$selection->where('published_on < ?', DateTime::from($this->filter['yearMonth'])->modify('+1 month'));
		}

		if (isset($this->filter['authorId'])) {
			$authorId = $this->filter['authorId'];

			if ($authorId > 0) {
				$selection->where('author_id', $authorId);
			} else {
				$selection->where('author_id IS NULL');
			}

			$selection->where('author_name IS NULL OR author_name = ?', '');
		}

		//todo fultext problem with ž, š, č , ř
		if (isset($this->filter['searchText'])) {
			if (strlen($this->filter['searchText']) > 3) {
				$selection->where('MATCH(title, perex, article) AGAINST(? IN BOOLEAN MODE)', $this->filter['searchText'])
					->order('5 * MATCH(title) AGAINST (?) + MATCH(perex, article) AGAINST (?) DESC', $this->filter['searchText'], $this->filter['searchText']);
			} else {
				$selection->where('(title LIKE ? OR perex LIKE ? OR article LIKE ?)', '%' . $this->filter['searchText'] . '%', '%' . $this->filter['searchText'] . '%', '%' . $this->filter['searchText'] . '%');
			}
		}

		if ($this->categoryId) {
			$selection->where('(main_category_id =? OR :article_article_category.category_id = ?)', $this->categoryId, $this->categoryId);
		}


		if (isset($this->filter['topic'])) {
			$topicId = $this->articleTopicModel->getTableSelection()
				->where('name', $this->filter['topic'])
				->fetch();

			$selection->where(':article_topic_article.article_topic_id', $topicId ? $topicId->id : 0);
		}

		if (isset($this->filter['seriesId'])) {
			$selection->where('series_id', $this->filter['seriesId']);
		}

		return $selection;
	}


	/**
	 * @param $galleryId
	 * @return bool|mixed|\Nette\Database\Table\IRow
	 */
	public function getImage($galleryId)
	{
		return $this->photoModel->getMainPhotoByGalleryId($galleryId);
	}


	/**
	 * @param $count
	 * @return array
	 */
	private function getLastMonths($count)
	{
		$months                  = [1 => 'Leden', 2 => 'Únor', 3 => 'Březen',  4 => 'Duben',  5 => 'Květen',  6 => 'Červen',  7 => 'Červenec',  8 => 'Srpen',  9 => 'Září',  10 => 'Říjen',  11 => 'Listopad',  12 => 'Prosinec'];
		$actualYear              = (int)date("Y");
		$actualMonth             = (int)date("m");
		$lastMonths[$actualYear] = [];

		for($i = 0; $i < $count; $i++) {
			if ($actualMonth < 1) {
				$actualMonth = 12;
				$actualYear--;
				$lastMonths[$actualYear] = [];
			}

			$lastMonths[$actualYear][$actualYear . '-' . $actualMonth] = $months[$actualMonth];
			$actualMonth--;
		}

		return $lastMonths;
	}

}

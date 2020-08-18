<?php

namespace Grapesc\GrapeFluid\ArticleModule\Control\ArticleAuthor;

use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Grapesc\GrapeFluid\MagicControl\BaseMagicTemplateControl;

/**
 * Class ArticleAuthorControl
 * @package Grapesc\GrapeFluid\ArticleModule\Control
 * @usage: {magicControl articleAuthor, ['uid', 'int authorId', 'string authorName', 'string authorLink']}
 */
class ArticleAuthorControl extends BaseMagicTemplateControl
{


	/** @var UserModel @inject */
	public $userModel;

	/** @var integer */
	private $authorId;

	/** @var string */
	protected $authorName;

	/** @var string */
	protected $authorLink;

	/** @var string|null */
	protected $defaultTemplateFilename = __DIR__ . '/articleAuthor.latte';


	/**
	 * @param array $params
	 */
	public function setParams(array $params = [])
	{

		if (isset($params[1]) && is_int($params[1])) {
			$this->authorId = $params[1];
		}

		if (isset($params[2])) {
			$this->authorName = $params[2];
		}

		if (isset($params[3])) {
			$this->authorLink = $params[3];
		}

	}


	public function render()
	{
		$this->template->author     = $this->getAuthor($this->authorId);
		$this->template->authorName = $this->authorName;
		$this->template->authorLink = $this->authorLink;
		$this->template->render();
	}


	/**
	 * @return bool|mixed|\Nette\Database\Table\IRow
	 */
	protected function getAuthor()
	{
		return $this->userModel->getItemsBy($this->authorId)->fetch();
	}

}

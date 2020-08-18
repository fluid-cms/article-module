<?php

namespace Grapesc\GrapeFluid\ArticleModule\Control\Filter;

use Grapesc\GrapeFluid\MagicControl\BaseMagicTemplateControl;
use Nette\Utils\Strings;


/**
 * Class FilterControl
 * @package Grapesc\GrapeFluid\ArticleModule\Control
 * @usage: {magicControl filter, ['uid']}
 */
class FilterControl extends BaseMagicTemplateControl
{

	/** @var string|null */
	protected $defaultTemplateFilename = __DIR__ . '/filter.latte';


	public function render()
	{
		$this->template->render();
	}


	/**
	 * @param array $params
	 */
	public function setParams(array $params = [])
	{
	}


	public function handleSearchText()
	{
		$this->getPresenter()->redirect(':Article:Article:default', ['search' => $this->getPresenter()->getHttpRequest()->getPost('searchText')]);
	}

}

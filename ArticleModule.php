<?php

namespace Grapesc\GrapeFluid\Module\Article;

use Grapesc\GrapeFluid\BaseModule;
use Nette\Configurator;


class ArticleModule extends BaseModule
{

	protected $parents = [
		"AdminModule"
	];


	/** {@inheritdoc} */
	protected function registerConfig(Configurator $configurator)
	{
		parent::registerConfig($configurator);
		$configurator->addConfig(__DIR__ . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "configuration.neon");
	}

}
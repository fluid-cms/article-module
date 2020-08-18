<?php

namespace Grapesc\GrapeFluid\ArticleModule\Control\ArticleTopic;

use Grapesc\GrapeFluid\ArticleModule\Model\ArticleTopicArticleModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticleTopicModel;
use Grapesc\GrapeFluid\MagicControl\BaseMagicTemplateControl;


/**
 * Class ArticleTopicControl
 * @package Grapesc\GrapeFluid\ArticleModule\Control
 * @usage: {magicControl articleTopic, ['uid', 'int articleId']}
 */
class ArticleTopicControl extends BaseMagicTemplateControl
{

	/** @var string */
	private $articleId;

	/** @var ArticleTopicArticleModel @inject */
	public $articleTopicArticleModel;

	/** @var ArticleTopicModel @inject */
	public $articleTopicModel;


	/** @var string|null */
	protected $defaultTemplateFilename = __DIR__ . '/articleTopic.latte';


	/**
	 * @param array $params
	 */
	public function setParams(array $params = [])
	{
		if (isset($params[1])) {
			$this->articleId = $params[1];
		}
	}


	public function render()
	{
		$topics                 = $this->getTopics($this->articleId);
		$this->template->topics = $topics;
		$this->template->render();
	}


	private function getTopics(int $articleId): array
	{
		$topicIds = $this->articleTopicArticleModel->getTableSelection()->where(['article_id' => $articleId])->fetchPairs('topic_id', 'topic_id');
		return !$topicIds ? [] : $this->articleTopicModel->getTableSelection()
			->select('id')
			->select('name')
			->where('id IN ?', $topicIds)
			->fetchPairs('id', 'name');
	}

}

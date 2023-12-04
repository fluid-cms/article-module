<?php

namespace Grapesc\GrapeFluid\ArticleModule\Presenters;

use Grapesc\GrapeFluid\ArticleModule\Model\ArticleArticleCategoryModel;
use Grapesc\GrapeFluid\ArticleModule\Model\ArticlePageModel;
use Grapesc\GrapeFluid\Configuration\Repository;
use Grapesc\GrapeFluid\CoreModule\Service\BreadCrumbService;
use Grapesc\GrapeFluid\ModuleRepository;
use Grapesc\GrapeFluid\PhotoGalleryModule\Model\PhotoModel;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use Nette\Security\User;
use Nette\Utils\Strings;


class ArticleDetailPresenter extends BasePresenter
{

	/** @var int */
	private $cookieHourInterval = 24;

	/** @var ArticlePageModel @inject */
	public $articlePageModel;

	/** @var PhotoModel @inject */
	public $photoModel;

	/** @var BreadCrumbService @inject */
	public $breadCrumbService;

	/** @var ArticleArticleCategoryModel @inject */
	public $articleArticleCategoryModel;

	/** @var ModuleRepository @inject */
	public $moduleRepository;

	/** @var Container @inject */
	public $container;

	/** @var User @inject */
	public $user;

	/** @var Repository @inject */
	public $configurationRepository;


	public function renderDefault($id = null)
	{
		/** @var $article ActiveRow */
		if ($id != null && $article = $this->articlePageModel->getItem($id)) {

			if (!$article->is_publish || $article->published_on > new \DateTime()) {
				if (!$this->user->isAllowed('backend:articles')) {
					throw new BadRequestException;
				} else {
					$this->template->noPublishedAlert = true;
				}
			} else {
				$this->template->noPublishedAlert = false;
			}

			if (!$this->getHttpRequest()->getCookie("fluid-article-view-$id")) {
				$this->articlePageModel->addShow($id);

				$response = $this->getHttpResponse();
				$response->setCookie("fluid-article-view-$id", date("d-m-Y G:i"), new \DateTime("+ $this->cookieHourInterval hour"));
			}

			$image = $this->photoModel->getMainPhotoByGalleryId($article->photogallery_gallery_id);

			$this->setMeta([
				"keywords"     => $article->keywords,
				"twitter:card" => $article->photogallery_gallery_id ? "summary_large_image" : "summary" // for twitter card
			]);

			$metaProperty = [
				"description"     => $article->perex,
				"og:url"          => $this->presenter->link('//:Article:ArticleDetail:default', ['id' => $id]),
				"og:description"  => $article->perex,
				"og:title"        => $article->title,
				"og:type"         => "article",
				"article:section" => $article->category->name
			];

			if ($image) {
				$metaProperty["og:image"] = $this->presenter->link('//:PhotoGallery:Photo:', [$image->photogallery_gallery_id, Strings::webalize($image->photogallery_gallery->name), $image->filename, '800x500']);;
			} else {
				$metaProperty["og:image"] = $this->configurationRepository->getValue('article.socials.defaultImage');
			}

			if ($this->moduleRepository->moduleExist('social')) {
				$socialService = $this->container->getByType(\Grapesc\GrapeFluid\SocialModule\Service\SocialService::class);
				$facebookAppId = $socialService->getAppId('facebook');
				if($facebookAppId) {
					$metaProperty["fb:app_id"] = $facebookAppId;
				}
			}

			$this->setMeta($metaProperty, 'property');

			$this->breadCrumbService->addLink($article->category->name, [':Article:Article:category', ['categoryId' => $article->category->id, 'categoryName' => Strings::webalize($article->category->name)]]);
			$this->breadCrumbService->addLink($article->title, [':Article:ArticleDetail:default', ['id' => $article->id, 'title' => Strings::webalize($article->title)]]);

			$this->template->categories = $this->articleArticleCategoryModel->getItemsBy($id, 'article_id');
			$this->template->image      = $image;
			$this->template->article    = $article;

			if ($this->moduleRepository->moduleExist('video')) {
				$articleVideoModel      = $this->container->getByType(\Grapesc\GrapeFluid\VideoModule\Model\ArticleVideoModel::class);
				$this->template->videos = $articleVideoModel->getItemsBy($id, 'article_id')->fetchPairs('video_id', 'video_id');
			}
		} else {
			throw new BadRequestException;
		}
	}

}

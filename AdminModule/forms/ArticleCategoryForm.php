<?php

namespace Grapesc\GrapeFluid\ArticleModule;

use Grapesc\GrapeFluid\ArticleModule\Model\ArticleCategoryModel;
use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;


class ArticleCategoryForm extends FluidForm
{

	/** @var ArticleCategoryModel @inject */
	public $articleCategoryModel;


	protected function build(Form $form)
	{
		$form->addHidden("id");

		$form->addText("name", "Název kategorie")
			->setAttribute("cols", 6)
			->addRule(Form::MAX_LENGTH, "Maximální velikost je %s znaků", 200)
			->setRequired("Název kategorie");;

		parent::build($form);
	}


	public function onSuccessEvent(Control $control, Form $form)
	{
		parent::onSuccessEvent($control, $form);

		$presenter = $control->getPresenter();
		$values    = $form->getValues('array');

		if (isset($values['id']) && (int) $values['id'] > 0) {
			try {
				$this->articleCategoryModel->update($values, $values['id']);
			} catch (UniqueConstraintViolationException $e) {
				$form->addError('Kategorie pod tímto názvem již existuje');
				return;
			} catch (\Exception $e) {
				$form->addError('Kategorii se nepodařilo uložit');
				return;
			}
			$presenter->flashMessage("Změny uloženy", "success");
			$presenter->redrawControl("flashMessages");
		} else {
			unset($values['id']);
			try {
				$this->articleCategoryModel->insert($values);
			} catch (UniqueConstraintViolationException $e) {
				$form->addError('Kategorie pod tímto názvem již existuje');
				return;
			} catch (\Exception $e) {
				$form->addError('Kategorii se nepodařilo vytvořit');
				return;
			}
			$presenter->flashMessage("Stránka vytvořena", "success");
		}
		$presenter->redirect("default");
	}

}
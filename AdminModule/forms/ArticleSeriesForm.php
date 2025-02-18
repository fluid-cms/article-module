<?php

namespace Grapesc\GrapeFluid\ArticleModule;

use Grapesc\GrapeFluid\ArticleModule\Model\ArticleSeriesModel;
use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;


class ArticleSeriesForm extends FluidForm
{

	/** @var ArticleSeriesModel @inject */
	public $articleSeriesModel;


	protected function build(Form $form)
	{
		$form->addHidden("id");

		$form->addText("name", "Název seriálu")
			->setAttribute("cols", 6)
			->addRule(Form::MAX_LENGTH, "Maximální velikost je %s znaků", 200)
			->setRequired("Název seriálu");

		parent::build($form);
	}


	public function onSuccessEvent(Control $control, Form $form)
	{
		parent::onSuccessEvent($control, $form);

		$presenter = $control->getPresenter();
		$values    = $form->getValues('array');

		if (isset($values['id']) && (int) $values['id'] > 0) {
			try {
				$this->articleSeriesModel->update($values, $values['id']);
			} catch (UniqueConstraintViolationException $e) {
				$form->addError('Serál pod tímto názvem již existuje');
				return;
			} catch (\Exception $e) {
				$form->addError('Seriál se nepodařilo uložit');
				return;
			}
			$presenter->flashMessage("Změny uloženy", "success");
			$presenter->redrawControl("flashMessages");
		} else {
			unset($values['id']);
			try {
				$this->articleSeriesModel->insert($values);
			} catch (UniqueConstraintViolationException $e) {
				$form->addError('Seriál pod tímto názvem již existuje');
				return;
			} catch (\Exception $e) {
				$form->addError('Seriál se nepodařilo vytvořit');
				return;
			}
			$presenter->flashMessage("Stránka vytvořena", "success");
		}
		$presenter->redirect("default");
	}

}
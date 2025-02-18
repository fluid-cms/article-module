<?php

namespace Grapesc\GrapeFluid\ArticleModule\Grid;

use Grapesc\GrapeFluid\FluidGrid;
use Nette\Database\Table\ActiveRow;
use TwiGrid\Components\Column;


class ArticleCategoryGrid extends FluidGrid
{

	protected function build(): void
	{
		$this->addColumn('id')->setSortable(true);
		$this->setDefaultOrderBy("name", Column::ASC);
		$this->setSortableColumns(["name"]);
		$this->setItemsPerPage(15);
		$this->addRowAction("edit", "Upravit", function(ActiveRow $record) {
			$this->getPresenter()->redirect(":Admin:ArticleCategory:edit", ["id" => $record->id]);
		});
		$this->addRowAction("delete", "Smazat", function (ActiveRow $record) {
			try {
				$this->model->delete($record->id);
				$this->flashMessage("Kategorie smazána", "success");
			} catch (\Exception $e) {
				$this->flashMessage("Kategorii se nepodařilo smazat", "error");
			}
			$this->getPresenter()->redrawControl('articleCategoryGrid');
			$this->getPresenter()->redrawControl('flashMessages');
		});

		parent::build();
	}

}
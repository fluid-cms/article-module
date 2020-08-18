<?php

namespace Grapesc\GrapeFluid\ArticleModule\Grid;

use Grapesc\GrapeFluid\FluidGrid;
use Nette\Database\Table\ActiveRow;
use TwiGrid\Components\Column;


class ArticleSeriesGrid extends FluidGrid
{

	protected function build()
	{
		$this->addColumn('id')->setSortable(true);
		$this->setDefaultOrderBy("name", Column::ASC);
		$this->setSortableColumns(["name"]);
		$this->setItemsPerPage(15);
		$this->addRowAction("edit", "Upravit", function(ActiveRow $record) {
			$this->getPresenter()->redirect(":Admin:ArticleSeries:edit", ["id" => $record->id]);
		});
		$this->addRowAction("delete", "Smazat", function (ActiveRow $record) {
			try {
				$this->model->delete($record->id);
				$this->flashMessage("Seriál smazán", "success");
			} catch (\Exception $e) {
				$this->flashMessage("Seriál se nepodařilo smazat", "error");
			}
			$this->getPresenter()->redrawControl('articleSeriesGrid');
			$this->getPresenter()->redrawControl('flashMessages');
		});

		parent::build();
	}

}
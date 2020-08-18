<?php

namespace Grapesc\GrapeFluid\ArticleModule\Model;

use Grapesc\GrapeFluid\Model\BaseModel;


class ArticleTopicModel extends BaseModel
{

	static $DELIMETER = ',';


	/**
	 * @inheritdoc
	 */
	public function getTableName()
	{
		return "article_topic";
	}


	public function getTopicIdName(string $topics): array
	{
		if (!trim($topics)) {
			return [];
		}

		$names  = array_filter(array_map('trim', explode(trim(self::$DELIMETER), $topics)));
		$result = $this->getTableSelection()
			->select('id')
			->select('name')
			->where('name IN ?', $names)
			->fetchPairs('id', 'name');

		if (count($names) == count($result)) {
			return $result;
		}

		foreach (array_diff($names, $result) as $name) {
			$row = $this->insert(['name' => $name]);
			if ($row) {
				$result[$row['id']] = $name;
			} else {
				throw new \RuntimeException('Cannot insert row with name ' . $name);
			}
		}

		return $result;
	}


	public function getNamesByIds(array $ids): string
	{
		$names = $this->getTableSelection()
			->select('id, name')
			->where('id', $ids)
			->fetchPairs('id', 'name');

		return $names ? implode(self::$DELIMETER, $names): '';
	}

}
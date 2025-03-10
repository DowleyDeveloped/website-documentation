<?php

namespace fortytwostudio\websitedocumentation\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use craft\records\EntryType;
use yii\db\ActiveQueryInterface;

class Navigation extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName(): string
	{
		return '{{%documentation_navigations}}';
	}

	/**
	 * Returns the content template's element.
	 *
	 * @return ActiveQueryInterface
	 */
	public function getElement(): ActiveQueryInterface
	{
		return $this->hasOne(Element::class, ['id' => 'id']);
	}

	/**
	 * Returns the content template's entry type.
	 *
	 * @return ActiveQueryInterface
	 */
	public function getHandle(): ActiveQueryInterface
	{
		return $this->hasOne(EntryType::class, ['handle' => 'handle']);
	}
}

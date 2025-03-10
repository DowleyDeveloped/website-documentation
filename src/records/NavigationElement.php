<?php
namespace fortytwostudio\websitedocumentation\records;

use craft\db\ActiveRecord;
use craft\records\Element;

use yii\db\ActiveQueryInterface;

class NavigationElement extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%documentation_navigation_elements}}';
    }

    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    public function getMenu(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'menuId']);
    }
}

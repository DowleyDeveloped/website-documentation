<?php
namespace dowleydeveloped\websitedocumentation\models;

use dowleydeveloped\websitedocumentation\WebsiteDocumentation;
use dowleydeveloped\websitedocumentation\elements\NavElement;
use dowleydeveloped\websitedocumentation\records\Nav as NavRecord;

use Craft;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\db\Table;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

class Navigation extends Model
{
    // Constants
    // =========================================================================

    public const DEFAULT_PLACEMENT_BEGINNING = 'beginning';
    public const DEFAULT_PLACEMENT_END = 'end';

    public const PROPAGATION_METHOD_NONE = 'none';
    public const PROPAGATION_METHOD_SITE_GROUP = 'siteGroup';
    public const PROPAGATION_METHOD_LANGUAGE = 'language';
    public const PROPAGATION_METHOD_ALL = 'all';


    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $siteId = null;
	public ?int $structureId = null;
	public ?int $fieldLayoutId = null;
    public ?string $name = null;
    public ?string $handle = null;
    public ?int $sortOrder = null;
    public string $propagationMethod = self::PROPAGATION_METHOD_ALL;
    public string $defaultPlacement = self::DEFAULT_PLACEMENT_END;
    public ?string $uid = null;


    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return Craft::t('site', $this->name) ?: static::class;
    }

    public function attributeLabels(): array
    {
        return [
            'handle' => Craft::t('app', 'Handle'),
            'name' => Craft::t('app', 'Name'),
        ];
    }

    public function validateFieldLayout(): void
    {
        $fieldLayout = $this->getFieldLayout();

        $fieldLayout->reservedFieldHandles = [
            'nav',
        ];

        if (!$fieldLayout->validate()) {
            $this->addModelErrors($fieldLayout, 'fieldLayout');
        }
    }

    public function getConfig(): array
    {
        $config = [
            'name' => $this->name,
            'handle' => $this->handle,
            'propagationMethod' => $this->propagationMethod,
			'siteId' => $this->siteId,
			'structure' => [
				'uid' => $this->structureId ? Db::uidById(Table::STRUCTURES, $this->structureId) : StringHelper::UUID(),
				'maxLevels' => null,
			],
            'sortOrder' => (int)$this->sortOrder,
            'defaultPlacement' => $this->defaultPlacement ?? self::DEFAULT_PLACEMENT_END,
        ];

        $fieldLayout = $this->getFieldLayout();

        if ($fieldLayoutConfig = $fieldLayout->getConfig()) {
            $config['fieldLayouts'] = [
                $fieldLayout->uid => $fieldLayoutConfig,
            ];
        }

        return $config;
    }


    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['id', 'fieldLayoutId'], 'number', 'integerOnly' => true];
        $rules[] = [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']];
        $rules[] = [['handle'], UniqueValidator::class, 'targetClass' => NavRecord::class];
        $rules[] = [['name', 'handle', 'propagationMethod'], 'required'];
        $rules[] = [['name', 'handle'], 'string', 'max' => 255];
        $rules[] = [['defaultPlacement'], 'in', 'range' => [self::DEFAULT_PLACEMENT_BEGINNING, self::DEFAULT_PLACEMENT_END]];
        $rules[] = [['fieldLayout'], 'validateFieldLayout'];

        $rules[] = [
            ['propagationMethod'], 'in', 'range' => [
                self::PROPAGATION_METHOD_NONE,
                self::PROPAGATION_METHOD_SITE_GROUP,
                self::PROPAGATION_METHOD_LANGUAGE,
                self::PROPAGATION_METHOD_ALL,
            ],
        ];

        return $rules;
    }

    protected function defineBehaviors(): array
    {
        $behaviors = parent::defineBehaviors();

        $behaviors['fieldLayout'] = [
            'class' => FieldLayoutBehavior::class,
            'elementType' => NavElement::class,
        ];

        return $behaviors;
    }

}

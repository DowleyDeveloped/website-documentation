<?php
namespace fortytwostudio\websitedocumentation\base;

use fortytwostudio\websitedocumentation\elements\NavElement;

use Craft;
use craft\base\Component;

abstract class ElementType extends Component implements ElementTypeUi
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('websitedocumentation', 'Element Type');
    }

    public static function hasTitle(): bool
    {
        return true;
    }

    public static function hasUrl(): bool
    {
        return true;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function getColor(): string
    {
        return '#1d1d1d';
    }


    // Properties
    // =========================================================================

    public ?NavElement $element = null;


    // Public Methods
    // =========================================================================

    public function getModalHtml(): ?string
    {
        return null;
    }

    public function getSettingsHtml(): ?string
    {
        return null;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function getDefaultTitle(): string
    {
        return static::displayName();
    }

    public function beforeSaveElement(bool $isNew): bool
    {
        return true;
    }
}

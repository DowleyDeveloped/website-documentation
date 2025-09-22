<?php
namespace dowleydeveloped\websitedocumentation\elementtypes;

use dowleydeveloped\websitedocumentation\base\ElementType;

use Craft;

class StyleGuideType extends ElementType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('websitedocumentation', 'Style Guide');
    }

    public static function hasTitle(): bool
    {
        return true;
    }

    public static function hasUrl(): bool
    {
        return false;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function getColor(): string
    {
        return '#031c86';
    }
}

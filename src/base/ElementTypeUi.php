<?php
namespace dowleydeveloped\websitedocumentation\base;

use craft\base\ComponentInterface;

interface ElementTypeUi extends ComponentInterface
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string;

    public static function hasTitle(): bool;

    public static function hasUrl(): bool;

    public static function hasNewWindow(): bool;

    public static function getColor(): string;


    // Public Methods
    // =========================================================================

    public function getModalHtml(): ?string;

    public function getSettingsHtml(): ?string;

    public function getUrl(): ?string;
}

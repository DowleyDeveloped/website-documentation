<?php
namespace dowleydeveloped\websitedocumentation\events;

use dowleydeveloped\websitedocumentation\elements\NavElement;

use yii\base\Event;

class ElementActiveEvent extends Event
{
    // Properties
    // =========================================================================

    public NavElement $element;
    public ?bool $isActive = null;
}

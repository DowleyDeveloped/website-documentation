<?php
namespace fortytwostudio\websitedocumentation\events;

use fortytwostudio\websitedocumentation\elements\NavElement;

use yii\base\Event;

class ElementActiveEvent extends Event
{
    // Properties
    // =========================================================================

    public NavElement $element;
    public ?bool $isActive = null;
}

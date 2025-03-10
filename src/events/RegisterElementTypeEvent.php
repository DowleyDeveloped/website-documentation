<?php
namespace fortytwostudio\websitedocumentation\events;

use yii\base\Event;

class RegisterElementTypeEvent extends Event
{
    // Properties
    // =========================================================================

    public array $types = [];
}

<?php

namespace ipl\Tests\Html\TestDummy;

use ipl\Html\BaseHtmlElement;

class ElementWithCallbackAttributes extends BaseHtmlElement
{
    protected $name;

    public function __construct()
    {
        $this->registerCallbacks();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    protected function registerCallbacks()
    {
        $this->getAttributes()->setCallback('name', [$this, 'getName'], [$this, 'setName']);
    }
}

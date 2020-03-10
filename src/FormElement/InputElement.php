<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attribute;
use ipl\Html\Attributes;

class InputElement extends BaseFormElement
{
    /** @var string */
    protected $type;

    protected $tag = 'input';

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = (string) $type;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getTypeAttribute()
    {
        return new Attribute('type', $this->getType());
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        parent::registerAttributeCallbacks($attributes);

        $attributes->registerAttributeCallback(
            'type',
            [$this, 'getTypeAttribute'],
            [$this, 'setType']
        );
    }
}

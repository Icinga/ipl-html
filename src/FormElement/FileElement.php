<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;
use Psr\Http\Message\UploadedFileInterface;
use ipl\Html\Common\MultipleAttribute;

class FileElement extends InputElement
{
    use MultipleAttribute;

    protected $type = 'file';

    /** @var UploadedFileInterface|UploadedFileInterface[] */
    protected $value;

    public function __construct($name, $attributes = null)
    {
        $this->getAttributes()->get('accept')->setSeparator(', ');

        parent::__construct($name, $attributes);
    }

    public function getValueAttribute()
    {
        // Value attributes of file inputs are set only client-side.
        return null;
    }

    public function getNameAttribute()
    {
        $name = parent::getNameAttribute();

        return $this->isMultiple() ? ($name . '[]') : $name;
    }

    public function hasValue()
    {
        if ($this->value === null) {
            return false;
        }

        $file = $this->value;

        if ($this->isMultiple()) {
            return $file[0]->getError() !== UPLOAD_ERR_NO_FILE;
        }

        return $file->getError() !== UPLOAD_ERR_NO_FILE;
    }

    public function getValue()
    {
        return $this->hasValue() ? $this->value : null;
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        parent::registerAttributeCallbacks($attributes);
        $this->registerMultipleAttributeCallback($attributes);
    }
}

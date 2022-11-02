<?php

namespace ipl\Html\FormElement;

use Psr\Http\Message\UploadedFileInterface;

class FileElement extends InputElement
{
    protected $type = 'file';

    /** @var UploadedFileInterface */
    protected $value;

    public function getValueAttribute()
    {
        // Value attributes of file inputs are set only client-side.
        return null;
    }

    public function getNameAttribute()
    {
        if ($this->getAttributes()->get('multiple')->getValue() === true) {
            return $this->getName() . '[]';
        }

        return parent::getNameAttribute();
    }

    public function hasValue()
    {
        $file = $this->value;

        if ($this->getAttributes()->get('multiple')->getValue() === true) {
            return $file[0]->getError() !== UPLOAD_ERR_NO_FILE;
        }

        return $file->getError() !== UPLOAD_ERR_NO_FILE;
    }

    public function getValue()
    {
        return $this->hasValue() ? $this->value : null;
    }
}

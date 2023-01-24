<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;
use ipl\Validator\FileValidator;
use ipl\Validator\ValidatorChain;
use Psr\Http\Message\UploadedFileInterface;
use ipl\Html\Common\MultipleAttribute;

class FileElement extends InputElement
{
    use MultipleAttribute;

    protected $type = 'file';

    /** @var UploadedFileInterface|UploadedFileInterface[] */
    protected $value;

    /** @var int The default maximum file size */
    protected static $defaultMaxFileSize;

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

    protected function addDefaultValidators(ValidatorChain $chain): void
    {
        $chain->add(new FileValidator([
            'maxSize' => $this->getDefaultMaxFileSize(),
            'mimeType' => array_filter(
                (array) $this->getAttributes()->get('accept')->getValue(),
                function ($type) {
                    // file inputs also allow file extensions in the accept attribute. These
                    // must not be passed as they don't resemble valid mime type definitions.
                    return is_string($type) && ltrim($type)[0] !== '.';
                }
            )
        ]));
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        parent::registerAttributeCallbacks($attributes);
        $this->registerMultipleAttributeCallback($attributes);
    }

    /**
     * Get the system's default maximum file upload size
     *
     * @return int
     */
    public function getDefaultMaxFileSize(): int
    {
        if (static::$defaultMaxFileSize === null) {
            $ini = $this->convertIniToInteger(trim(static::getPostMaxSize()));
            $max = $this->convertIniToInteger(trim(static::getUploadMaxFilesize()));
            $min = max($ini, $max);
            if ($ini > 0) {
                $min = min($min, $ini);
            }

            if ($max > 0) {
                $min = min($min, $max);
            }

            static::$defaultMaxFileSize = $min;
        }

        return static::$defaultMaxFileSize;
    }

    /**
     * Converts a ini setting to a integer value
     *
     * @param string $setting
     *
     * @return int
     */
    private function convertIniToInteger(string $setting): int
    {
        if (! is_numeric($setting)) {
            $type = strtoupper(substr($setting, -1));
            $setting = (int) substr($setting, 0, -1);

            switch ($type) {
                case 'K':
                    $setting *= 1024;
                    break;

                case 'M':
                    $setting *= 1024 * 1024;
                    break;

                case 'G':
                    $setting *= 1024 * 1024 * 1024;
                    break;

                default:
                    break;
            }
        }

        return (int) $setting;
    }

    /**
     * Get the `post_max_size` INI setting
     *
     * @return string
     */
    protected static function getPostMaxSize(): string
    {
        return ini_get('post_max_size') ?: '8M';
    }

    /**
     * Get the `upload_max_filesize` INI setting
     *
     * @return string
     */
    protected static function getUploadMaxFilesize(): string
    {
        return ini_get('upload_max_filesize') ?: '2M';
    }
}

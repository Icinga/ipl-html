<?php

namespace ipl\Html\FormElement;

use GuzzleHttp\Psr7\UploadedFile;
use InvalidArgumentException;
use ipl\Html\Attributes;
use ipl\Html\Form;
use ipl\Html\HtmlDocument;
use ipl\Html\HtmlElement;
use ipl\Html\Text;
use ipl\I18n\Translation;
use ipl\Validator\FileValidator;
use ipl\Validator\ValidatorChain;
use ipl\Web\Widget\Icon;
use Psr\Http\Message\UploadedFileInterface;
use ipl\Html\Common\MultipleAttribute;

use function ipl\Stdlib\get_php_type;

/**
 * File upload element
 *
 * Once the file element is added to the form and the form attribute `enctype` is not set,
 * it is automatically set to `multipart/form-data`.
 */
class FileElement extends InputElement
{
    use MultipleAttribute;
    use Translation;

    protected $type = 'file';

    /** @var UploadedFileInterface|UploadedFileInterface[] */
    protected $value;

    /** @var UploadedFileInterface[] Files that are stored on disk */
    protected $files = [];

    /** @var string[] Files to be removed from disk */
    protected $filesToRemove = [];

    /** @var ?string The path where to store the file contents */
    protected $destination;

    /** @var int The default maximum file size */
    protected static $defaultMaxFileSize;

    public function __construct($name, $attributes = null)
    {
        $this->getAttributes()->get('accept')->setSeparator(', ');

        parent::__construct($name, $attributes);
    }

    /**
     * Set the path where to store the file contents
     *
     * @param string $path
     *
     * @return $this
     */
    public function setDestination(string $path): self
    {
        $this->destination = $path;

        return $this;
    }

    /**
     * Get the path where file contents are stored
     *
     * @return ?string
     */
    public function getDestination(): ?string
    {
        return $this->destination;
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
            $files = $this->loadFiles();
            if (empty($files)) {
                return false;
            }

            if (! $this->isMultiple()) {
                $files = $files[0];
            }

            $this->value = $files;
        }

        return $this->value !== null;
    }

    public function getValue()
    {
        if (! $this->hasValue()) {
            return null;
        }

        if (! $this->hasFiles()) {
            $files = $this->value;
            if (! $this->isMultiple()) {
                $files = [$files];
            }

            $storedFiles = $this->storeFiles(...$files);
            if (! $this->isMultiple()) {
                $storedFiles = $storedFiles[0];
            }

            $this->value = $storedFiles;
        }

        return $this->value;
    }

    public function setValue($value)
    {
        if (! empty($value)) {
            $fileToTest = $value;
            if ($this->isMultiple()) {
                $fileToTest = $value[0];
            }

            if (! $fileToTest instanceof UploadedFileInterface) {
                throw new InvalidArgumentException(
                    sprintf('%s is not an uploaded file', get_php_type($fileToTest))
                );
            }

            if ($fileToTest->getError() === UPLOAD_ERR_NO_FILE && ! $fileToTest->getClientFilename()) {
                $value = null;
            }
        } else {
            $value = null;
        }

        return parent::setValue($value);
    }

    /**
     * Get whether there are any files stored on disk
     *
     * @return bool
     */
    protected function hasFiles(): bool
    {
        return $this->destination !== null && reset($this->files);
    }

    /**
     * Load and return all files stored on disk
     *
     * @return UploadedFileInterface[]
     */
    protected function loadFiles(): array
    {
        if (empty($this->files) || $this->destination === null) {
            return [];
        }

        foreach ($this->files as $name => $_) {
            $filePath = $this->getFilePath($name);
            if (! is_readable($filePath) || ! is_file($filePath)) {
                // If one file isn't accessible, none is
                return [];
            }

            if (in_array($name, $this->filesToRemove, true)) {
                @unlink($filePath);
            } else {
                $this->files[$name] = new UploadedFile(
                    $filePath,
                    filesize($filePath),
                    0,
                    $name,
                    mime_content_type($filePath)
                );
            }
        }

        $this->files = array_diff_key($this->files, array_flip($this->filesToRemove));

        return array_values($this->files);
    }

    /**
     * Store the given files on disk
     *
     * @param UploadedFileInterface ...$files
     *
     * @return UploadedFileInterface[]
     */
    protected function storeFiles(UploadedFileInterface ...$files): array
    {
        if ($this->destination === null || ! is_writable($this->destination)) {
            return $files;
        }

        foreach ($files as $file) {
            $name = $file->getClientFilename();
            $path = $this->getFilePath($name);

            $file->moveTo($path);

            // Re-created to ensure moveTo() still works if called externally
            $this->files[$name] = new UploadedFile(
                $path,
                $file->getSize(),
                0,
                $name,
                $file->getClientMediaType()
            );
        }

        return array_values($this->files);
    }

    /**
     * Get the file path on disk of the given file
     *
     * @param string $name
     *
     * @return string
     */
    protected function getFilePath(string $name): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->destination, sha1($name)]);
    }

    public function onRegistered(Form $form)
    {
        if (! $form->hasAttribute('enctype')) {
            $form->setAttribute('enctype', 'multipart/form-data');
        }

        $chosenFiles = (array) $form->getPopulatedValue('chosen_file_' . $this->getName(), []);
        foreach ($chosenFiles as $chosenFile) {
            $this->files[$chosenFile] = null;
        }

        $this->filesToRemove = (array) $form->getPopulatedValue('remove_file_' . $this->getName(), []);
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
        $this->getAttributes()->registerAttributeCallback('destination', null, [$this, 'setDestination']);
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

    protected function assemble()
    {
        $doc = new HtmlDocument();
        if ($this->hasFiles()) {
            foreach ($this->files as $file) {
                $doc->addHtml(new HiddenElement('chosen_file_' . $this->getNameAttribute(), [
                    'value' => $file->getClientFilename()
                ]));
            }

            $this->prependWrapper($doc);
        }
    }

    public function renderUnwrapped()
    {
        if (! $this->hasValue() || ! $this->hasFiles()) {
            return parent::renderUnwrapped();
        }

        $uploadedFiles = new HtmlElement('ul', Attributes::create(['class' => 'uploaded-files']));
        foreach ($this->files as $file) {
            $uploadedFiles->addHtml(new HtmlElement(
                'li',
                null,
                (new ButtonElement('remove_file_' . $this->getNameAttribute(), Attributes::create([
                    'type' => 'submit',
                    'formnovalidate' => true,
                    'class' => 'remove-uploaded-file',
                    'value' => $file->getClientFilename(),
                    'title' => sprintf($this->translate('Remove file "%s"'), $file->getClientFilename())
                ])))->addHtml(new HtmlElement(
                    'span',
                    null,
                    new Icon('remove'),
                    Text::create($file->getClientFilename())
                ))
            ));
        }

        return $uploadedFiles->render();
    }
}

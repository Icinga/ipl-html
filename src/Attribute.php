<?php

namespace ipl\Html;

use InvalidArgumentException;

/**
 * HTML Attribute
 *
 * Every single HTML attribute is (or should be) an instance of this class.
 * This guarantees that every attribute is safe and escaped correctly.
 *
 * Usually attributes are not instantiated directly, but created through an HTML
 * element's exposed methods.
 */
class Attribute
{
    /** @var string */
    protected $name;

    /** @var string|array|bool|null */
    protected $value;

    /**
     * Create a new HTML attribute from the given name and value
     *
     * @param   string                  $name   The name of the attribute
     * @param   string|bool|array|null  $value  The value of the attribute
     *
     * @throws  InvalidArgumentException        If the name of the attribute contains special characters
     */
    public function __construct($name, $value = null)
    {
        $this->setName($name)->setValue($value);
    }

    /**
     * @param $name
     * @param $value
     * @return static
     * @throws InvalidArgumentException
     */
    public static function create($name, $value)
    {
        return new static($name, $value);
    }

    /**
     * @param $name
     * @return static
     * @throws InvalidArgumentException
     */
    public static function createEmpty($name)
    {
        return new static($name, null);
    }

    /**
     * Get the name of the attribute
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the attribute
     *
     * @param   string  $name
     *
     * @return  $this
     *
     * @throws  InvalidArgumentException    If the name contains special characters
     */
    protected function setName($name)
    {
        if (! preg_match('/^[a-z][a-z0-9:-]*$/i', $name)) {
            throw new InvalidArgumentException(sprintf(
                'Attribute names with special characters are not yet allowed: %s',
                $name
            ));
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of the attribute
     *
     * @return  string|bool|array|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of the attribute
     *
     * @param   string|bool|array|null  $value
     *
     * @return  $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function addValue($value)
    {
        if (! is_array($this->value)) {
            $this->value = [$this->value];
        }

        if (is_array($value)) {
            $this->value = array_merge($this->value, $value);
        } else {
            $this->value[] = $value;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isBoolean()
    {
        return is_bool($this->value);
    }

    /**
     * Render the attribute to HTML
     *
     * If the value of the attribute is of type boolean, it will be rendered as
     * {@link http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes boolean attribute}.
     * Note that in this case if the value of the attribute is false, the empty string will be returned.
     *
     * If the value of the attribute is null or an empty array,
     * the empty string will be returned as well.
     *
     * Escaping of the attribute's value takes place automatically using {@link Html::escape()}.
     *
     * @return  string
     */
    public function render()
    {
        if ($this->isEmpty()) {
            return '';
        }

        if ($this->isBoolean()) {
            if ($this->value) {
                return $this->renderName();
            }

            return '';
        } else {
            return sprintf(
                '%s="%s"',
                $this->renderName(),
                $this->renderValue()
            );
        }
    }

    /**
     * @return string
     */
    public function renderName()
    {
        return static::escapeName($this->name);
    }

    /**
     * @return string
     */
    public function renderValue()
    {
        return static::escapeValue($this->value);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return null === $this->value || $this->value === [];
    }

    /**
     * @param $name
     * @return string
     */
    public static function escapeName($name)
    {
        // TODO: escape
        return (string) $name;
    }

    /**
     * @param $value
     * @return string
     */
    public static function escapeValue($value)
    {
        // TODO: escape differently
        if (is_array($value)) {
            return Html::escape(implode(' ', $value));
        } else {
            return Html::escape((string) $value);
        }
    }
}

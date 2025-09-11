<?php

namespace ipl\Html;

use ipl\Html\Common\BaseAttribute;

/**
 * @phpstan-import-type AttributeValue from BaseAttribute
 */
class Attribute extends BaseAttribute
{
    public function __construct($name, $value = null)
    {
        $this->setName($name)->setValue($value);
    }

    /**
     * Set the separator to concatenate multiple values with
     *
     * @param string $separator
     *
     * @return $this
     */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Set the value of the attribute
     *
     * @param AttributeValue $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Add the given value(s) to the attribute
     *
     * @param AttributeValue $value The value(s) to add
     *
     * @return $this
     */
    public function addValue($value)
    {
        $this->value = array_merge((array) $this->value, (array) $value);

        return $this;
    }

    /**
     * Remove the given value(s) from the attribute
     *
     * The current value is set to null if it matches the value to remove
     * or is in the array of values to remove.
     *
     * If the current value is an array, all elements are removed which
     * match the value(s) to remove.
     *
     * Does nothing if there is no such value to remove.
     *
     * @param AttributeValue $value The value(s) to remove
     *
     * @return $this
     */
    public function removeValue($value)
    {
        $value = (array) $value;

        $current = $this->getValue();

        if (is_array($current)) {
            $this->setValue(array_diff($current, $value));
        } elseif (in_array($current, $value, true)) {
            $this->setValue(null);
        }

        return $this;
    }

    public function isImmutable(): false
    {
        return false;
    }
}

<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;
use ipl\Html\Contract\FormElement;

class Collection extends FieldsetElement
{
    protected const GROUP_CSS_CLASS = 'form-element-collection';

    /** @var callable */
    protected $onAssembleGroup;

    /** @var FormElement */
    protected $addElement;

    /** @var FormElement */
    protected $removeElement;

    /** @var string[] */
    protected $defaultAttributes = [
        'class' => 'collection'
    ];

    /**
     * @param callable $callback
     *
     * @return void
     */
    public function onAssembleGroup(callable $callback): void
    {
        $this->onAssembleGroup = $callback;
    }

    /**
     * @param FormElement $element
     *
     * @return $this
     */
    public function setAddElement(FormELement $element): self
    {
        $this->addElement = clone $element;

        return $this;
    }

    /**
     * @param FormElement $element
     *
     * @return $this
     */
    public function setRemoveElement(FormElement $element): self
    {
        $this->removeElement = clone $element;

        return $this;
    }

    /**
     * @param $group
     * @param $addElement
     * @param $removeElement
     *
     * @return $this
     */
    protected function assembleGroup($group, $addElement, $removeElement): self
    {
        if (is_callable($this->onAssembleGroup)) {
            call_user_func($this->onAssembleGroup, $group, $addElement, $removeElement);
        }

        return $this;
    }

    protected function assemble()
    {
        $values = $this->getPopulatedValues();

        $valid = true;
        foreach ($values as $key => $items) {
            if ($this->removeElement !== null && isset($items[0][$this->removeElement->getName()])) {
                continue;
            }

            $group = $this->addGroup($key);

            if (empty($group->getValue($this->addElement->getName()))) {
                $valid = false;
            }
        }

        if ($valid) {
            $lastKey = $values ? key(array_slice($values, -1, 1, true)) + 1 : 0;
            $this->addGroup($lastKey);
        }
    }

    protected function addGroup($key): FieldsetElement
    {
        $group = new FieldsetElement(
            $key,
            Attributes::create(['class' => static::GROUP_CSS_CLASS])
        );

        $this
            ->registerElement($group)
            ->assembleGroup(
                $group,
                $this->addElement ? clone $this->addElement : null,
                $this->removeElement ? clone $this->removeElement : null
            )
            ->addHtml($group);

        return $group;
    }
}

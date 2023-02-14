<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;

class Collection extends FieldsetElement
{
    protected const GROUP_CSS_CLASS = 'form-element-collection';

    /** @var callable */
    protected $onAssembleGroup;

    /** @var array */
    protected $addElement = [
        'type'    => null,
        'name'    => null,
        'options' => null
    ];

    /** @var array */
    protected $removeElement = [
        'type'    => null,
        'name'    => null,
        'options' => null
    ];

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
     * @param string $typeOrElement
     * @param string|null $name
     * @param null $options
     *
     * @return $this
     */
    public function setAddElement(string $typeOrElement, string $name = null, $options = null): self
    {
        $this->addElement = ['type' => $typeOrElement, 'name' => $name, 'options' => $options];

        return $this;
    }

    /**
     * @param string $typeOrElement
     * @param string|null $name
     * @param null $options
     *
     * @return $this
     */
    public function setRemoveElement(string $typeOrElement, string $name = null, $options = null): self
    {
        $this->removeElement = ['type' => $typeOrElement, 'name' => $name, 'options' => $options];

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
            if ($this->removeElement !== null && isset($items[0][$this->removeElement['name']])) {
                continue;
            }

            $group = $this->addGroup($key);

            if (empty($group->getValue($this->addElement['name']))) {
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
                $this->addElement['type'] ? $this->createElement(
                    $this->addElement['type'],
                    $this->addElement['name'],
                    $this->addElement['options']
                ) : null,
                $this->removeElement['type'] ? $this->createElement(
                    $this->removeElement['type'],
                    $this->removeElement['name'],
                    $this->removeElement['options']
                ) : null
            )
            ->addHtml($group);

        return $group;
    }
}

<?php

namespace ipl\Html\FormElement;

use InvalidArgumentException;
use ipl\Html\BaseHtmlElement;
use ipl\Stdlib\Loader\PluginLoader;

trait FormElementContainer
{
    use PluginLoader;

    /** @var BaseFormElement[] */
    private $elements = [];

    private $populatedValues = [];

    /** @var BaseHtmlElement|null */
    protected $defaultElementDecorator;

    /**
     * @return BaseFormElement[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    public function getValues()
    {
        $values = [];
        foreach ($this->getElements() as $element) {
            if (! $element->isIgnored()) {
                $values[$element->getName()] = $element->getValue();
            }
        }

        return $values;
    }

    public function populate($values)
    {
        foreach ($values as $name => $value) {
            $this->populatedValues[$name] = $value;
            if ($this->hasElement($name)) {
                $this->getElement($name)->setValue($value);
            }
        }
    }

    public function addElementLoader($namespace, $classPostfix = null)
    {
        $this->eventuallyRegisterDefaultElementLoader();

        return $this->addPluginLoader('element', $namespace, $classPostfix);
    }

    protected function eventuallyRegisterDefaultElementLoader()
    {
        if (! $this->hasPluginLoadersFor('element')) {
            $this->addPluginLoader('element', __NAMESPACE__, 'Element');
        }

        return $this;
    }

    /**
     * @param $name
     * @return BaseFormElement
     */
    public function getElement($name)
    {
        if (! array_key_exists($name, $this->elements)) {
            throw new InvalidArgumentException(sprintf(
                'Trying to get non-existent element "%s"',
                $name
            ));
        }
        return $this->elements[$name];
    }

    /**
     * @param string|BaseFormElement $element
     * @return bool
     */
    public function hasElement($element)
    {
        if (is_string($element)) {
            return array_key_exists($element, $this->elements);
        } elseif ($element instanceof BaseFormElement) {
            return in_array($element, $this->elements, true);
        } else {
            return false;
        }
    }

    /**
     * @param string $name
     * @param string|BaseFormElement $type
     * @param array|null $options
     * @return $this
     */
    public function addElement($type, $name = null, $options = null)
    {
        $this->registerElement($type, $name, $options);
        if ($name === null) {
            $name = $type->getName();
        }

        $element = $this->getElement($name);
        if ($this instanceof BaseHtmlElement) {
            $element = $this->decorate($element);
        }

        $this->add($element);

        return $this;
    }

    protected function decorate(BaseFormElement $element)
    {
        if ($this->hasDefaultElementDecorator()) {
            $this->getDefaultElementDecorator()->wrap($element);
        }

        return $element;
    }

    /**
     * @param string $name
     * @param string|BaseFormElement $type
     * @param array|null $options
     * @return $this
     */
    public function registerElement($type, $name = null, $options = null)
    {
        if (is_string($type)) {
            $type = $this->createElement($type, $name, $options);
        } elseif ($type instanceof BaseHtmlElement) {
            if ($name === null) {
                $name = $type->getName();
            }
        } else {
            throw new InvalidArgumentException(sprintf(
                'FormElement or element type is required' // TODO: got %s
            ));
        }

        $this->elements[$name] = $type;

        $this->onElementRegistered($name, $type);

        return $this;
    }

    public function onElementRegistered($name, BaseFormElement $element)
    {
        if ($element instanceof SubmitElement && ! $this->hasSubmitButton()) {
            $this->setSubmitButton($element);
        }

        if (array_key_exists($name, $this->populatedValues)) {
            $element->setValue($this->populatedValues[$name]);
        }
    }

    /**
     * @param string $type
     * @param string $name
     * @param mixed $attributes
     * @return BaseFormElement
     */
    public function createElement($type, $name, $attributes = null)
    {
        $this->eventuallyRegisterDefaultElementLoader();

        $class = $this->eventuallyGetPluginClass('element', $type);
        /** @var BaseFormElement $element */
        $element = new $class($name);
        if ($attributes !== null) {
            $element->addAttributes($attributes);
        }

        return $element;
    }

    /**
     * @param FormElementContainer $form
     */
    public function addElementsFrom(FormElementContainer $form)
    {
        foreach ($form->getElements() as $name => $element) {
            $this->addElement($element);
        }
    }

    public function setDefaultElementDecorator(BaseHtmlElement $decorator)
    {
        $this->defaultElementDecorator = $decorator;

        return $this;
    }

    public function hasDefaultElementDecorator()
    {
        return $this->defaultElementDecorator !== null;
    }

    /**
     * @return BaseHtmlElement
     */
    public function getDefaultElementDecorator()
    {
        return $this->defaultElementDecorator;
    }
}

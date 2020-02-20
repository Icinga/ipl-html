<?php

namespace ipl\Html\FormElement;

use InvalidArgumentException;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Form;
use ipl\Html\FormDecorator\DecoratorInterface;
use ipl\Html\ValidHtml;
use ipl\Stdlib\EventEmitter;
use ipl\Stdlib\Loader\PluginLoader;

trait FormElementContainer
{
    use EventEmitter;
    use PluginLoader;

    /** @var DecoratorInterface|BaseHtmlElement|null */
    protected $defaultElementDecorator;

    /** @var BaseFormElement[] */
    private $elements = [];

    /** @var array */
    private $populatedValues = [];

    /**
     * Get all elements
     *
     * @return BaseFormElement[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Get whether the given element exists
     *
     * @param string|BaseFormElement $element
     *
     * @return bool
     */
    public function hasElement($element)
    {
        if (\is_string($element)) {
            return \array_key_exists($element, $this->elements);
        } elseif ($element instanceof BaseFormElement) {
            return \in_array($element, $this->elements, true);
        } else {
            return false;
        }
    }

    /**
     * Get the element by the given name
     *
     * @param string $name
     *
     * @return BaseFormElement
     *
     * @throws InvalidArgumentException If no element with the given name exists
     */
    public function getElement($name)
    {
        if (! \array_key_exists($name, $this->elements)) {
            throw new InvalidArgumentException(sprintf(
                'Trying to get non-existent element "%s"',
                $name
            ));
        }
        return $this->elements[$name];
    }

    /**
     * Add an element
     *
     * @param string|BaseFormElement $typeOrElement Type of the element as string or an instance of BaseFormElement
     * @param string                 $name          Name of the element
     * @param mixed                  $options       Element options as key-value pairs
     *
     * @return $this
     */
    public function addElement($typeOrElement, $name = null, $options = null)
    {
        $this->registerElement($typeOrElement, $name, $options);
        if ($name === null) {
            $name = $typeOrElement->getName();
        }

        $element = $this->getElement($name);
        if ($this instanceof BaseHtmlElement) {
            $element = $this->decorate($element);
        }

        $this->add($element);

        return $this;
    }

    /**
     * Create an element
     *
     * @param string $type    Type of the element
     * @param string $name    Name of the element
     * @param mixed  $options Element options as key-value pairs
     *
     * @return BaseFormElement
     */
    public function createElement($type, $name, $options = null)
    {
        $this->eventuallyRegisterDefaultElementLoader();

        $class = $this->eventuallyGetPluginClass('element', $type);
        /** @var BaseFormElement $element */
        $element = new $class($name);
        if ($options !== null) {
            $element->addAttributes($options);
        }

        return $element;
    }

    /**
     * Register an element
     *
     * Registers the element for value and validation handling but does not add it to the render stack.
     *
     * @param string|BaseFormElement $typeOrElement Type of the element as string or an instance of BaseFormElement
     * @param string                 $name          Name of the element
     * @param mixed                  $options       Element options as key-value pairs
     *
     * @return $this
     */
    public function registerElement($typeOrElement, $name = null, $options = null)
    {
        if (is_string($typeOrElement)) {
            $typeOrElement = $this->createElement($typeOrElement, $name, $options);
            // TODO: } elseif ($type instanceof FormElementInterface) {
        } elseif ($typeOrElement instanceof BaseHtmlElement) {
            if ($name === null) {
                $name = $typeOrElement->getName();
            }
        } else {
            throw new InvalidArgumentException(sprintf(
                'FormElement or element type is required' // TODO: got %s
            ));
        }

        $this->elements[$name] = $typeOrElement;

        if (array_key_exists($name, $this->populatedValues)) {
            $typeOrElement->setValue($this->populatedValues[$name]);
        }

        $this->onElementRegistered($typeOrElement);
        $this->emit(Form::ON_ELEMENT_REGISTERED, [$typeOrElement]);

        return $this;
    }

    /**
     * Returns the value for the $name element
     *
     * Returns $default in case the element does not exist or has no value
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     */
    public function getValue($name, $default = null)
    {
        if ($this->hasElement($name)) {
            $value = $this->getElement($name)->getValue();
            if ($value !== null) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Returns a name => value array for all but ignored elements
     *
     * @return array
     */
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

    public function addDecoratorLoader($namespace, $classPostfix = null)
    {
        $this->eventuallyRegisterDefaultDecoratorLoader();

        return $this->addPluginLoader('decorator', $namespace, $classPostfix);
    }

    protected function eventuallyRegisterDefaultDecoratorLoader()
    {
        if (! $this->hasPluginLoadersFor('decorator')) {
            $this->addPluginLoader(
                'decorator',
                'ipl\\Html\\FormDecorator',
                'Decorator'
            );
        }

        return $this;
    }

    /**
     * @param BaseFormElement $element
     * @return BaseFormElement
     */
    protected function decorate(BaseFormElement $element)
    {
        if ($this->hasDefaultElementDecorator()) {
            $decorator = $this->getDefaultElementDecorator();
            if ($decorator instanceof DecoratorInterface) {
                $decorator->decorate($element);
            } elseif ($decorator instanceof BaseHtmlElement) {
                $decorator->wrap($element);
            } else {
                die('WTF');
            }
        }

        return $element;
    }

    /**
     * @param   Form|SubFormElement $form
     */
    public function addElementsFrom($form)
    {
        foreach ($form->getElements() as $name => $element) {
            $this->addElement($element);
        }
    }

    public function remove(ValidHtml $html)
    {
        // TODO: FormElementInterface?
        if ($html instanceof BaseFormElement) {
            if ($this->hasElement($html)) {
                if ($this->submitButton === $html) {
                    $this->submitButton = null;
                }

                $kill = [];
                foreach ($this->elements as $key => $element) {
                    if ($element === $html) {
                        $kill[] = $key;
                    }
                }

                foreach ($kill as $key) {
                    unset($this->elements[$key]);
                }
            }
        }

        parent::remove($html);
    }

    public function setDefaultElementDecorator($decorator)
    {
        if (
            $decorator instanceof BaseHtmlElement
            || $decorator instanceof DecoratorInterface
        ) {
            $this->defaultElementDecorator = $decorator;
        } else {
            $this->eventuallyRegisterDefaultDecoratorLoader();
            $this->defaultElementDecorator = $this->loadPlugin('decorator', $decorator);
        }

        return $this;
    }

    public function hasDefaultElementDecorator()
    {
        return $this->defaultElementDecorator !== null;
    }

    /**
     * @return DecoratorInterface
     */
    public function getDefaultElementDecorator()
    {
        return $this->defaultElementDecorator;
    }

    public function isValidEvent($event)
    {
        return \in_array($event, [
            Form::ON_SUCCESS,
            Form::ON_ERROR,
            Form::ON_REQUEST,
            Form::ON_ELEMENT_REGISTERED,
        ]);
    }

    protected function onElementRegistered(BaseFormElement $element)
    {
    }
}

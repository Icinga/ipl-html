<?php

namespace ipl\Html\FormElement;

use InvalidArgumentException;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Form;
use ipl\Html\FormDecorator\DecoratorInterface;
use ipl\Html\ValidHtml;
use ipl\Stdlib\EventEmitter;
use ipl\Stdlib\Loader\PluginLoader;

use function ipl\Stdlib\get_php_type;

trait FormElementContainer
{
    use EventEmitter;
    use PluginLoader;

    /** @var DecoratorInterface|BaseHtmlElement|null */
    private $defaultElementDecorator;

    /** @var bool Whether the default element decorator loader has been registered */
    private $defaultElementDecoratorLoaderRegistered = false;

    /** @var bool Whether the default element loader has been registered */
    private $defaultElementLoaderRegistered = false;

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
        if (is_string($element)) {
            return array_key_exists($element, $this->elements);
        }

        if ($element instanceof BaseFormElement) {
            return in_array($element, $this->elements, true);
        }

        return false;
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
        if (! array_key_exists($name, $this->elements)) {
            throw new InvalidArgumentException(sprintf(
                "Can't get element '%s'. Element does not exist",
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
        $this->ensureDefaultElementLoaderRegistered();

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
     *
     * @throws InvalidArgumentException If $typeOrElement is neither a string nor an instance of BaseFormElement
     */
    public function registerElement($typeOrElement, $name = null, $options = null)
    {
        if (is_string($typeOrElement)) {
            $typeOrElement = $this->createElement($typeOrElement, $name, $options);
            // TODO: } elseif ($type instanceof FormElementInterface) {
        } elseif ($typeOrElement instanceof BaseFormElement) {
            if ($name === null) {
                $name = $typeOrElement->getName();
            }
        } else {
            throw new InvalidArgumentException(sprintf(
                '%s() expects parameter 1 to be a string or an instance of %s, %s given',
                __METHOD__,
                BaseFormElement::class,
                get_php_type($typeOrElement)
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
     * Get whether a default element decorator exists
     *
     * @return bool
     */
    public function hasDefaultElementDecorator()
    {
        return $this->defaultElementDecorator !== null;
    }

    /**
     * Get the default element decorator, if any
     *
     * @return DecoratorInterface|BaseHtmlElement|null
     */
    public function getDefaultElementDecorator()
    {
        return $this->defaultElementDecorator;
    }

    /**
     * Set the default element decorator
     *
     * If $decorator is a string, the decorator will be automatically created from a registered decorator loader.
     * A loader for the namespace ipl\\Html\\FormDecorator is automatically registered by default.
     * See {@link addDecoratorLoader()} for registering a custom loader.
     *
     * @param DecoratorInterface|BaseHtmlElement|string $decorator
     *
     * @return $this
     *
     * @throws InvalidArgumentException If $decorator is a string and can't be loaded from registered decorator loaders
     *                                  or if a decorator loader does not return an instance of
     *                                  {@link DecoratorInterface} or {@link BaseHtmlElement}
     */
    public function setDefaultElementDecorator($decorator)
    {
        if ($decorator instanceof DecoratorInterface || $decorator instanceof BaseHtmlElement) {
            $this->defaultElementDecorator = $decorator;
        } else {
            $this->ensureDefaultElementDecoratorLoaderRegistered();

            $d = $this->loadPlugin('decorator', $decorator);

            if (! $d instanceof DecoratorInterface || ! $d instanceof BaseHtmlElement) {
                throw new InvalidArgumentException(sprintf(
                    "Expected instance of DecoratorInterface or BaseHtmlElement for decorator '%s'."
                    . " Got '%s' from a decorator loader instead",
                    $decorator,
                    get_php_type($d)
                ));
            }

            $this->defaultElementDecorator = $d;
        }

        return $this;
    }

    /**
     * Get the value of the element specified by name
     *
     * Returns $default if the element does not exist or has no value.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
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
     * Get the values for all but ignored elements
     *
     * @return array Values as name-value pairs
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

    /**
     * Populate values of registered elements
     *
     * @param iterable $values Values as name-value pairs
     *
     * @return $this
     */
    public function populate($values)
    {
        foreach ($values as $name => $value) {
            $this->populatedValues[$name] = $value;
            if ($this->hasElement($name)) {
                $this->getElement($name)->setValue($value);
            }
        }

        return $this;
    }

    /**
     * Add an element loader
     *
     * @param string $namespace Namespace of the elements
     * @param string $postfix   Element name postfix, if any
     *
     * @return $this
     */
    public function addElementLoader($namespace, $postfix = null)
    {
        $this->ensureDefaultElementLoaderRegistered();

        $this->addPluginLoader('element', $namespace, $postfix);

        return $this;
    }

    protected function ensureDefaultElementLoaderRegistered()
    {
        if (! $this->defaultElementLoaderRegistered) {
            $this->addPluginLoader('element', __NAMESPACE__, 'Element');

            $this->defaultElementLoaderRegistered = true;
        }

        return $this;
    }

    /**
     * Add a decorator loader
     *
     * @param string $namespace Namespace of the decorators
     * @param string $postfix   Decorator name postfix, if any
     *
     * @return $this
     */
    public function addDecoratorLoader($namespace, $postfix = null)
    {
        $this->addPluginLoader('decorator', $namespace, $postfix);

        return $this;
    }

    protected function ensureDefaultElementDecoratorLoaderRegistered()
    {
        if (! $this->defaultElementDecoratorLoaderRegistered) {
            $this->addPluginLoader(
                'decorator',
                'ipl\\Html\\FormDecorator',
                'Decorator'
            );

            $this->defaultElementDecoratorLoaderRegistered = true;
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

    public function isValidEvent($event)
    {
        return in_array($event, [
            Form::ON_SUCCESS,
            Form::ON_ERROR,
            Form::ON_REQUEST,
            Form::ON_ELEMENT_REGISTERED,
        ]);
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

    protected function onElementRegistered(BaseFormElement $element)
    {
    }
}

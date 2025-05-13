<?php

namespace ipl\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\Contract\Decorator;
use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\HiddenElement;
use ipl\Stdlib\Plugins;
use UnexpectedValueException;

use function ipl\Stdlib\get_php_type;

class DecoratorChain
{
    use Plugins;

    /** @var array<string, Decorator> Decorator chain */
    protected array $decorators = [];

    /**
     * Create a new decorator chain
     */
    public function __construct()
    {
        $this->addDefaultPluginLoader('decorator', __NAMESPACE__, 'Decorator');
    }

    /**
     * Add a decorator loader
     *
     * @param string $namespace Namespace of the decorators
     * @param string $postfix   Decorator name postfix, if any
     *
     * @return $this
     */
    public function addDecoratorLoader(string $namespace, string $postfix = ''): self
    {
        $this->addPluginLoader('decorator', $namespace, $postfix);

        return $this;
    }

    /**
     * Add a decorator to the chain
     *
     * @param Decorator $decorator
     *
     * @return $this
     *
     */
    public function addDecorator(Decorator $decorator, string $name = null): self
    {
        if ($name === null) {
            $name = $decorator::class;
        }

        $this->decorators[$name] = $decorator;

        return $this;
    }

    /**
     * Get all decorators
     *
     * @return array<string, Decorator>
     */
    public function getDecorators(): array
    {
        return $this->decorators;
    }

    /**
     * Add the decorators from the given decorator specification to the chain
     *
     * @param static|iterable<int|string, mixed> $decorators
     *
     * @return $this
     *
     * @throws InvalidArgumentException If $decorators is not iterable or if the decorator specification is invalid
     */
    public function addDecorators(DecoratorChain|iterable $decorators): self
    {
        if ($decorators instanceof static) {
            return $this->merge($decorators);
        }

        if (! is_iterable($decorators)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects parameter one to be iterable, got %s instead',
                __METHOD__,
                get_php_type($decorators)
            ));
        }

        foreach ($decorators as $name => $decorator) {
            if (! $decorator instanceof Decorator) {
                if (is_int($name)) {
                    if (! is_array($decorator)) {
                        $name = $decorator;
                        $decorator = null;
                    } else {
                        if (! isset($decorator['name'])) {
                            throw new InvalidArgumentException(
                                'Invalid decorator array specification: Key "name" is missing'
                            );
                        }

                        $name = $decorator['name'];
                        unset($decorator['name']);
                    }
                }

                if (is_array($decorator) && isset($decorator['options'])) {
                    $options = $decorator['options'];

                    unset($decorator['options']);

                    $decorator += $options;
                }

                $decorator = $this->createDecorator($name, $decorator);
            }

            $this->addDecorator($decorator, $name);
        }

        return $this;
    }


    /**
     * Remove all decorators from the chain
     *
     * @return $this
     */
    public function clearDecorators(): self
    {
        $this->decorators = [];

        return $this;
    }

    /**
     * Create a decorator from the given name and options
     *
     * @param string $name
     * @param ?array $options
     *
     * @return Decorator
     */
    public function createDecorator(string $name, array $options = null): Decorator
    {
        $class = $this->loadPlugin('decorator', $name);

        if (! $class) {
            throw new InvalidArgumentException(sprintf(
                "Can't load decorator '%s'. decorator unknown",
                $name
            ));
        }

        if (empty($options)) {
            $decorator = new $class();
        } else {
            $decorator = new $class($options);
        }

        if (! $decorator instanceof Decorator) {
            throw new UnexpectedValueException(sprintf(
                "%s expects loader to return an instance of %s for decorator '%s', got %s instead",
                __METHOD__,
                Decorator::class,
                $name,
                get_php_type($decorator)
            ));
        }

        return $decorator;
    }

    /**
     * Merge all decorators from the given chain into this one
     *
     * @param DecoratorChain $decoratorChain
     *
     * @return $this
     */
    public function merge(DecoratorChain $decoratorChain): self
    {
        foreach ($decoratorChain->decorators as $decorator) {
            $this->addDecorator($decorator);
        }

        return $this;
    }

    /**
     * Get whether the chain has any decorators
     *
     * @return bool
     */
    public function hasDecorators(): bool
    {
        return ! empty($this->decorators);
    }

    /**
     * Apply the decorator chain to the given form element
     *
     * @param BaseFormElement $formElement The form element to decorate
     *
     * @return string The decorated form element
     */
    public function apply(BaseFormElement $formElement): string
    {
        if ($formElement instanceof HiddenElement) {
            // no need to decorate hidden elements
            return $formElement->render();
        }

        $results = (new DecorationResults())->append($formElement);
        foreach ($this->decorators as $decorator) {
            $decorator->decorate($results, $formElement);
        }

        return (string) $results;
    }
}

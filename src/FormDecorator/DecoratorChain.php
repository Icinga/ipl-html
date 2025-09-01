<?php

namespace ipl\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Stdlib\Plugins;
use UnexpectedValueException;

use function ipl\Stdlib\get_php_type;

/**
 * DecoratorChain for form elements
 *
 * @phpstan-type decoratorOptionsFormat array<non-empty-string, mixed>
 * @phpstan-type decoratorFormat non-empty-string | Decorator | decoratorOptionsFormat
 * @phpstan-type decoratorsFormat array<int|non-empty-string, Decorator|decoratorOptionsFormat>
 */
class DecoratorChain
{
    use Plugins;

    /** @var Decorator[] Decorator chain */
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
     * @param string $suffix   Decorator class name suffix, if any
     *
     * @return $this
     */
    public function addDecoratorLoader(string $namespace, string $suffix = ''): static
    {
        $this->addPluginLoader('decorator', $namespace, $suffix);

        return $this;
    }

    /**
     * Add a decorator to the chain.
     *
     * Please see {@see static::addDecorators()} for the supported array formats, where each array entry defines
     * a decorator.
     *
     * @param decoratorFormat $decorator
     *
     * @return $this
     *
     * @throws InvalidArgumentException If array specification is invalid
     */
    public function addDecorator(Decorator|array|string $decorator): static
    {
        if (empty($decorator)) {
            return $this;
        }

        if (! $decorator instanceof Decorator) {
            $options = [];
            if (is_string($decorator)) {
                $name = $decorator;
            } else {
                if (isset($decorator['name'])) {
                    $name = $decorator['name'];
                    unset($decorator['name']);
                } else {
                    $name = array_key_first($decorator);

                    if (is_int($name)) {
                        $name = $decorator[$name];
                    } else {
                        $decorator = $decorator[$name];
                        if (! is_array($decorator)) {
                            throw new InvalidArgumentException(sprintf(
                                "Invalid decorator specification: The key must be a decorator name and value"
                                . " must be an array of options, got value of type '%s' for key '%s'",
                                get_php_type($decorator),
                                $name,
                            ));
                        }
                    }
                }

                if (isset($decorator['options'])) {
                    $options = $decorator['options'];

                    unset($decorator['options']);
                }

                $options += $decorator;
            }

            $decorator = $this->createDecorator($name, $options);
        }

        $this->decorators[] = $decorator;

        return $this;
    }

    /**
     * Add the decorators from the given decorator specification to the chain
     *
     *   *The following array formats are supported:*
     *
     * ```
     * // When no options are required or defaults are sufficient
     * $decorators = [
     *      'HtmlTag',
     *      'Label'
     * ];
     *
     * // Override default options by defining the option key and value
     *
     * // key: decorator name, value: options
     * $decorators = [
     *      'HtmlTag' => ['tag' => 'span', 'placement' => 'append'],
     *      'Label' => ['class' => 'element-label']
     * ];
     *
     * // or define the `name` and `options` key
     * $decorators = [
     *      ['name' => 'HtmlTag', 'options' => ['tag' => 'span', 'placement' => 'append']],
     *      ['name' => 'Label', 'options' => ['class' => 'element-label']]
     * ];
     *
     * // or add Decorator instances
     * $decorators = [
     *      (new HtmlTagDecorator())->getAttributes()->add(['tag' => 'span', 'placement' => 'append']),
     *      (new LabelDecorator())->getAttributes()->add(['class' => 'element-label'])
     * ];
     * ```
     *
     * @param static|decoratorsFormat $decorators
     *
     * @return $this
     */
    public function addDecorators(DecoratorChain|array $decorators): static
    {
        if ($decorators instanceof static) {
            $decorators = $decorators->getDecorators();
        }

        foreach ($decorators as $name => $decorator) {
            if (is_string($name) && is_array($decorator)) {
                $decorator = [$name => $decorator];
            }

            $this->addDecorator($decorator);
        }

        return $this;
    }

    /**
     * Get all decorators
     *
     * @return Decorator[]
     */
    public function getDecorators(): array
    {
        return $this->decorators;
    }

    /**
     * Remove all decorators from the chain
     *
     * @return $this
     */
    public function clearDecorators(): static
    {
        $this->decorators = [];

        return $this;
    }

    /**
     * Create a decorator from the given name and options
     *
     * @param non-empty-string $name
     * @param decoratorOptionsFormat $options
     *
     * @return Decorator
     */
    protected function createDecorator(string $name, array $options = []): Decorator
    {
        $class = $this->loadPlugin('decorator', $name);

        if (! $class) {
            throw new InvalidArgumentException(sprintf(
                "Can't load decorator '%s'. decorator unknown",
                $name
            ));
        }

        $decorator = new $class();

        if (! $decorator instanceof Decorator) {
            throw new UnexpectedValueException(sprintf(
                "%s expects loader to return an instance of %s for decorator '%s', got %s instead",
                __METHOD__,
                Decorator::class,
                $name,
                get_php_type($decorator)
            ));
        }

        if (! empty($options)) {
            if (! $decorator instanceof DecoratorOptionsInterface) {
                throw new InvalidArgumentException(sprintf("Decorator '%s' does not support options", $name));
            }

            $decorator->getAttributes()->add($options);
        }

        return $decorator;
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
     * @param FormElement $formElement The form element to decorate
     *
     * @return string The decorated form element
     */
    public function apply(FormElement $formElement): string
    {
        $results = (new DecorationResults())->append($formElement);
        foreach ($this->decorators as $decorator) {
            $decorator->decorate($results, $formElement);
        }

        return (string) $results;
    }
}

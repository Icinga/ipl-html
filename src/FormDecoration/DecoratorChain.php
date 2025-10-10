<?php

namespace ipl\Html\FormDecoration;

use Generator;
use InvalidArgumentException;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Stdlib\Plugins;
use IteratorAggregate;
use UnexpectedValueException;

use function ipl\Stdlib\get_php_type;

/**
 * DecoratorChain for form elements
 *
 * @template TDecorator of object
 * @implements IteratorAggregate<int, TDecorator>
 *
 * @phpstan-type Ident string|class-string
 * @phpstan-type decoratorOptionsFormat array<string, mixed>
 * @phpstan-type _decoratorsFormat1 array<Ident, decoratorOptionsFormat>
 * @phpstan-type _decoratorsFormat2 array<int, Ident|TDecorator|array{name: Ident, options?: decoratorOptionsFormat}>
 * @phpstan-type decoratorsFormat _decoratorsFormat1 | _decoratorsFormat2
 */
class DecoratorChain implements IteratorAggregate
{
    use Plugins;

    /** @var class-string<TDecorator> The type of decorator to accept */
    private string $decoratorType;

    /** @var TDecorator[] All registered decorators */
    private array $decorators = [];

    /**
     * Create a new decorator chain
     *
     * @param class-string<TDecorator> $decoratorType The type of decorator to accept
     */
    public function __construct(string $decoratorType)
    {
        $this->decoratorType = $decoratorType;

        $this->addDefaultPluginLoader('decorator', __NAMESPACE__, 'Decorator');
    }

    /**
     * Add a decorator loader
     *
     * @param string $namespace Namespace of the decorator(s)
     * @param string $suffix    Decorator class name suffix, if any
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
     * @param TDecorator|Ident       $decorator
     * @param decoratorOptionsFormat $options Only allowed if parameter 1 is a string
     *
     * @return $this
     *
     * @throws InvalidArgumentException If the decorator specification is invalid
     */
    public function addDecorator(object|string $decorator, array $options = []): static
    {
        if (! empty($options) && ! is_string($decorator)) {
            throw new InvalidArgumentException('No options are allowed with parameter 1 of type Decorator');
        }

        if (is_string($decorator)) {
            $decorator = $this->createDecorator($decorator, $options);
        } elseif (! $decorator instanceof $this->decoratorType) {
            throw new InvalidArgumentException(sprintf(
                'Expects parameter 1 to be a string or an instance of %s, got %s instead',
                $this->decoratorType,
                get_php_type($decorator)
            ));
        }

        $this->decorators[] = $decorator;

        return $this;
    }

    /**
     * Add the decorators from the given decorator specification to the chain
     *
     * The order of the decorators is important, as it determines the rendering order.
     *
     *   *The following array formats are supported:*
     *
     * ```
     * // When no options are required or defaults are sufficient
     * $decorators = [
     *     'HtmlTag',
     *     'Label'
     * ];
     *
     * // Override default options by defining the option key and value
     *
     * // key: decorator name, value: options
     * $decorators = [
     *     'HtmlTag' => ['tag' => 'span', 'placement' => 'append'],
     *     'Label' => ['class' => 'element-label']
     * ];
     *
     * // or define the `name` and `options` key
     * $decorators = [
     *     ['name' => 'HtmlTag', 'options' => ['tag' => 'span', 'placement' => 'append']],
     *     ['name' => 'Label', 'options' => ['class' => 'element-label']]
     * ];
     *
     * // or add Decorator instances
     * $decorators = [
     *     (new HtmlTagDecorator())->getAttributes()->add(['tag' => 'span', 'placement' => 'append']),
     *     (new LabelDecorator())->getAttributes()->add(['class' => 'element-label'])
     * ];
     *
     * // Class paths are also supported
     * $decorators = [
     *     LabelDecorator::class,
     *     ['name' => HtmlTagDecorator::class, ['tag' => 'span', 'placement' => 'append']]
     * ];
     * ```
     *
     * @param static<TDecorator>|decoratorsFormat $decorators
     *
     * @return $this
     *
     * @throws InvalidArgumentException If the decorator specification is invalid
     */
    public function addDecorators(DecoratorChain|array $decorators): static
    {
        if ($decorators instanceof static) {
            foreach ($decorators->decorators as $decorator) {
                $this->addDecorator($decorator);
            }

            return $this;
        }

        foreach ($decorators as $decoratorName => $decoratorOptions) {
            $position = $decoratorName;
            if (is_int($decoratorName)) {
                if (is_array($decoratorOptions)) {
                    if (! isset($decoratorOptions['name'])) {
                        throw new InvalidArgumentException("Key 'name' is missing");
                    }

                    $decoratorName = $decoratorOptions['name'];
                    unset($decoratorOptions['name']);

                    $options = [];
                    if (isset($decoratorOptions['options'])) {
                        $options = $decoratorOptions['options'];

                        unset($decoratorOptions['options']);
                    }

                    if (! empty($decoratorOptions)) {
                        throw new InvalidArgumentException(
                            sprintf(
                                "No other keys except 'name' and 'options' are allowed, got '%s'",
                                implode("', '", array_keys($decoratorOptions))
                            )
                        );
                    }

                    $decoratorOptions = $options;
                } else {
                    $decoratorName = $decoratorOptions;
                    $decoratorOptions = [];
                }
            }

            if (! is_array($decoratorOptions)) {
                throw new InvalidArgumentException(sprintf(
                    "The key must be a decorator name and value must be an array of options, got value of type"
                    . " '%s' for key '%s'",
                    get_php_type($decoratorOptions),
                    $decoratorName,
                ));
            }

            if (! is_string($decoratorName) && ! $decoratorName instanceof $this->decoratorType) {
                throw new InvalidArgumentException(sprintf(
                    'Expects array value at position %d to be a string or an instance of %s, got %s instead',
                    $position,
                    $this->decoratorType,
                    get_php_type($decoratorName)
                ));
            }

            $this->addDecorator($decoratorName, $decoratorOptions);
        }

        return $this;
    }

    /**
     * Clear all decorators from the chain
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
     * @param Ident $name
     * @param decoratorOptionsFormat $options
     *
     * @return TDecorator
     *
     * @throws InvalidArgumentException If the given decorator is unknown or not an instance of the expected type
     * @throws UnexpectedValueException If the loaded decorator is not an instance of the expected type
     */
    protected function createDecorator(string $name, array $options = []): object
    {
        if (class_exists($name)) {
            $decorator = new $name();
            if (! $decorator instanceof $this->decoratorType) {
                throw new InvalidArgumentException(sprintf(
                    "Invalid decorator class '%s'. decorator must be an instance of %s",
                    $name,
                    $this->decoratorType,
                ));
            }
        } else {
            $class = $this->loadPlugin('decorator', $name);
            if (! $class) {
                throw new InvalidArgumentException(sprintf(
                    "Can't load decorator '%s'. decorator unknown",
                    $name
                ));
            }

            $decorator = new $class();
            if (! $decorator instanceof $this->decoratorType) {
                throw new UnexpectedValueException(sprintf(
                    "%s expects loader to return an instance of %s for decorator '%s', got %s instead",
                    __METHOD__,
                    $this->decoratorType,
                    $name,
                    get_php_type($decorator)
                ));
            }
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
     * Get whether the chain has decorators
     *
     * @return bool
     */
    public function hasDecorators(): bool
    {
        return ! empty($this->decorators);
    }

    /**
     * Iterate over all decorators
     *
     * @return Generator<TDecorator>
     */
    #[\Override]
    public function getIterator(): Generator
    {
        foreach ($this->decorators as $decorator) {
            yield $decorator;
        }
    }
}

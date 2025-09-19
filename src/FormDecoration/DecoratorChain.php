<?php

namespace ipl\Html\FormDecoration;

use InvalidArgumentException;
use ipl\Html\Contract\FormElementDecoration as Decorator;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\ValidHtml;
use ipl\Stdlib\Plugins;
use LogicException;
use UnexpectedValueException;

use function ipl\Stdlib\get_php_type;

/**
 * DecoratorChain for form elements
 *
 * @phpstan-type decoratorOptionsFormat array<string, mixed>
 * @phpstan-type _decoratorsFormat1 array<string, decoratorOptionsFormat>
 * @phpstan-type _decoratorsFormat2 array<int, string|Decorator|array{name: string, options?: decoratorOptionsFormat}>
 * @phpstan-type decoratorsFormat _decoratorsFormat1 | _decoratorsFormat2
 */
class DecoratorChain
{
    use Plugins;

    /** @var Decorator[] All registered decorators */
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
     * @param Decorator|string          $decorator
     * @param decoratorOptionsFormat    $options Only allowed if parameter 1 is a string
     *
     * @return $this
     *
     * @throws InvalidArgumentException If the decorator specification is invalid
     */
    public function addDecorator(Decorator|string $decorator, array $options = []): static
    {
        if (! empty($options) && ! is_string($decorator)) {
            throw new InvalidArgumentException('No options are allowed with parameter 1 of type Decorator');
        }

        if (! $decorator instanceof Decorator) {
            $decorator = $this->createDecorator($decorator, $options);
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
     *
     * @throws InvalidArgumentException If the decorator specification is invalid
     */
    public function addDecorators(DecoratorChain|array $decorators): static
    {
        if ($decorators instanceof static) {
            foreach ($decorators->getDecorators() as $decorator) {
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

            if (! is_string($decoratorName) && ! $decoratorName instanceof Decorator) {
                throw new InvalidArgumentException(sprintf(
                    'Expects array value at position %d to be a string or an instance of %s, got %s instead',
                    $position,
                    Decorator::class,
                    get_php_type($decoratorName)
                ));
            }

            $this->addDecorator($decoratorName, $decoratorOptions);
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
     * @param string $name
     * @param decoratorOptionsFormat $options
     *
     * @return Decorator
     *
     * @throws InvalidArgumentException If the given decorator is unknown
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
     * Get whether the chain has decorators
     *
     * @return bool
     */
    public function hasDecorators(): bool
    {
        return ! empty($this->decorators);
    }

    /**
     * Apply the registered decorators to the given form element
     *
     * @param FormElement $formElement The form element to decorate
     *
     * @return ValidHtml
     *
     * @throws LogicException If a decorator wants to skip a decorator that has already been applied
     */
    public function apply(FormElement $formElement): ValidHtml
    {
        $results = new DecorationResults();
        $appliedDecorators = [];
        $toSkip = [];
        foreach ($this->decorators as $decorator) {
            $decoratorName = $decorator->getName();
            if (in_array($decoratorName, $toSkip, true)) {
                continue;
            }

            $decorator->decorate($results, $formElement);

            $appliedDecorators[] = $decoratorName;
            $toSkip = $results->getSkipDecorators();
            $alreadyApplied = array_intersect($toSkip, $appliedDecorators);
            if (! empty($alreadyApplied)) {
                throw new LogicException(sprintf(
                    "Cannot skip Decorator(s) '%s', Decoration already applied",
                    implode("', '", $alreadyApplied)
                ));
            }
        }

        return $results;
    }
}

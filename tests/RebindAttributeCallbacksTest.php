<?php

namespace ipl\Tests\Html;

use Closure;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Tests\Html\Lib\ElementWithAttributeCallbacks;
use ReflectionFunction;
use ReflectionProperty;

class RebindAttributeCallbacksTest extends TestCase
{
    public function testCallbacksOfClonedAttributesPointToTheirClone()
    {
        $element = new class extends BaseHtmlElement {
            protected $value;

            protected $noGetterOrSetter;

            public function setValue($value)
            {
                $this->value = $value;
            }

            public function getValue()
            {
                return $this->value;
            }

            protected function registerAttributeCallbacks(Attributes $attributes)
            {
                $attributes->registerAttributeCallback('value', [$this, 'getValue'], [$this, 'setValue']);
                $attributes->registerAttributeCallback('data-ngos', function () {
                    return $this->noGetterOrSetter;
                }, function ($value) {
                    $this->noGetterOrSetter = $value;
                });
            }
        };

        $element->setAttribute('value', 'foo');

        $clone = clone $element;

        $clone->setAttribute('value', 'bar')
            ->setAttribute('data-ngos', true);

        $this->assertSame(
            ' value="foo"',
            $element->getAttributes()->render(),
            'Attribute callbacks are not rebound to their new owner'
        );
        $this->assertSame(
            ' value="bar" data-ngos',
            $clone->getAttributes()->render(),
            'Attribute callbacks are not rebound to their new owner'
        );
    }

    public function testHtmlOutput(): void
    {
        $original = new ElementWithAttributeCallbacks();
        $original->getAttributes()->set('class', 'original_class');

        $firstClone = clone $original;
        $firstClone->getAttributes()->set('class', 'first_clone_class');

        $secondClone = clone $firstClone;
        $secondClone->getAttributes()->set('class', 'second_clone_class');

        $originalHtml = <<<'HTML'
<p class="original_class"
    test-instance-scope-noop-inline="inline"
    test-instance-noop-attribute="static_callback"
    test-closure-static-scope-noop="static_callback"
    test-closure-instance-scope-noop="static_callback">
</p>
HTML;

        $firstCloneHtml = <<<'HTML'
<p class="first_clone_class"
    test-instance-scope-noop-inline="inline"
    test-instance-noop-attribute="static_callback"
    test-closure-static-scope-noop="static_callback"
    test-closure-instance-scope-noop="static_callback">
</p>
HTML;


        $secondCloneHtml = <<<'HTML'
<p class="second_clone_class"
    test-instance-scope-noop-inline="inline"
    test-instance-noop-attribute="static_callback"
    test-closure-static-scope-noop="static_callback"
    test-closure-instance-scope-noop="static_callback">
</p>
HTML;

        $this->assertHtml($originalHtml, $original);
        $this->assertHtml($firstCloneHtml, $firstClone);
        $this->assertHtml($secondCloneHtml, $secondClone);
    }

    public function testElementCallbacksCloning(): void
    {
        $element = new ElementWithAttributeCallbacks();
        $element->getAttributes();

        $clone = clone $element;

        $this->assertCallbacksFor($element);
        $this->assertCallbacksFor($clone);
    }

    protected function getCallbackThis(callable $callback): ?object
    {
        if (! $callback instanceof Closure) {
            if (is_array($callback) && ! is_string($callback[0])) {
                return $callback[0];
            } else {
                return null;
            }
        }

        return (new ReflectionFunction($callback))
            ->getClosureThis();
    }

    protected function isCallbackGlobalOrStatic(callable $callback): bool
    {
        if (! $callback instanceof Closure) {
            if (is_array($callback) && ! is_string($callback[0])) {
                return false;
            }
        } else {
            $closureThis = (new ReflectionFunction($callback))
                ->getClosureThis();

            if ($closureThis) {
                return false;
            }
        }

        return true;
    }

    protected function getAttributeCallback(Attributes $attributes, string $name): callable
    {
        $callbacksProperty = new ReflectionProperty(get_class($attributes), 'callbacks');
        $callbacksProperty->setAccessible(true);
        $callbacks = $callbacksProperty->getValue($attributes);

        return $callbacks[$name];
    }

    protected function assertCallbacksFor(ElementWithAttributeCallbacks $element)
    {
        $this->assertCallbackBelongsTo($element->getAttributes(), 'test-instance-scope-noop-inline', $element);
        $this->assertCallbackBelongsTo(
            $element->getAttributes(),
            'test-instance-noop-attribute',
            $element
        );
        $this->assertGlobalOrStaticCallback(
            $element->getAttributes(),
            'test-closure-static-scope-noop'
        );
        $this->assertGlobalOrStaticCallback(
            $element->getAttributes(),
            'test-closure-instance-scope-noop'
        );
    }

    protected function assertGlobalOrStaticCallback(Attributes $attributes, string $callbackName)
    {
        $callback = $this->getAttributeCallback($attributes, $callbackName);
        $this->assertTrue($this->isCallbackGlobalOrStatic($callback));
    }

    protected function assertCallbackBelongsTo(Attributes $attributes, string $callbackName, object $owner)
    {
        $callback = $this->getAttributeCallback($attributes, $callbackName);
        $callbackThis = $this->getCallbackThis($callback);
        $this->assertSame($callbackThis, $owner);
    }
}

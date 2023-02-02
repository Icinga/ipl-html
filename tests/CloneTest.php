<?php

namespace ipl\Tests\Html;

use Closure;
use ipl\Html\Attribute;
use ipl\Html\Attributes;
use ipl\Tests\Html\Lib\CloningDummyElement;
use ReflectionFunction;
use ReflectionProperty;

class CloneTest extends TestCase
{
    public function testHtmlOutput(): void
    {
        $original = new CloningDummyElement();
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
        $element = new CloningDummyElement();
        $element->getAttributes();

        $clone = clone $element;

        $this->assertCallbacksFor($element);
        $this->assertCallbacksFor($clone);
    }

    public function testCloningAttributes(): void
    {
        $original = Attributes::create([Attribute::create('class', 'class01')]);

        $clone = clone $original;
        foreach ($clone->getAttributes() as $attribute) {
            if ($attribute->getName() === 'class') {
                $attribute->setValue('class02');
            }
        }

        $this->assertSame($original->get('class')->getValue(), 'class01');
        $this->assertSame($clone->get('class')->getValue(), 'class02');
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

    protected function assertCallbacksFor(CloningDummyElement $element)
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

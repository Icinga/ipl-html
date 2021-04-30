<?php

namespace ipl\Tests\Html;

use Exception;
use ipl\Html\Attribute;
use ipl\Html\Attributes;
use ipl\Tests\Html\TestDummy\ElementWithCallbackAttributes;
use RuntimeException;
use UnexpectedValueException;

class AttributesTest extends TestCase
{
    public function testGetWithNonexistentAttribute()
    {
        $attributes = new Attributes();

        $attributes
            ->get('name')
            ->setValue('value');

        $this->assertSame(
            'value',
            $attributes->get('name')->getValue()
        );
    }

    public function testForeach()
    {
        $attrs = ['foo' => 'bar', 'baz' => 'qux'];

        $attributes = new Attributes($attrs);

        reset($attrs);

        foreach ($attributes as $attribute) {
            $name = key($attrs);
            $value = current($attrs);

            $this->assertSame($name, $attribute->getName());
            $this->assertSame($value, $attribute->getValue());

            next($attrs);
        }
    }

    public function testGetterCallbackInGet()
    {
        $callback = function () {
            return new Attribute('callback', 'value from callback');
        };

        $attributes = (new Attributes())
            ->setCallback('callback', $callback);

        $this->assertSame($attributes->get('callback')->getValue(), 'value from callback');
    }

    public function testSetterCallbackInSet()
    {
        $element = new ElementWithCallbackAttributes();

        $attributes = $element->getAttributes();

        $attributes->set('name', 'name from test');

        $this->assertSame('name from test', $attributes->get('name')->getValue());
        $this->assertSame('name from test', $element->getName());
    }

    public function testSetterCallbackInAdd()
    {
        $element = new ElementWithCallbackAttributes();

        $attributes = $element->getAttributes();

        $attributes->add('name', 'name from test');

        $this->assertSame('name from test', $attributes->get('name')->getValue());
        $this->assertSame('name from test', $element->getName());
    }

    public function testSetterCallbackIsProxied()
    {
        $element = new ElementWithCallbackAttributes();

        $attributes = $element->getAttributes();

        $attributes->get('name')->setValue('name from test');

        $this->assertSame('name from test', $attributes->get('name')->getValue());
        $this->assertSame('name from test', $element->getName());
    }

    public function testCantOverrideCallbacks()
    {
        $callback = function () {
            return new Attribute('callback', 'value from callback');
        };

        $attributes = (new Attributes())
            ->setCallback('callback', $callback);

        $this->expectException(RuntimeException::class);
        $attributes->set('callback', 'overridden');
    }

    public function testGetterCallbackRuntimeException()
    {
        $callback = function () {
            throw new Exception();
        };

        $attributes = (new Attributes())
            ->setCallback('callback', $callback);

        $this->expectException(RuntimeException::class);
        $attributes->get('callback');
    }

    public function testGetterCallbackValueException()
    {
        $callback = function () {
            return [];
        };

        $attributes = (new Attributes())
            ->setCallback('callback', $callback);

        $this->expectException(UnexpectedValueException::class);
        $attributes->get('callback');
    }
}

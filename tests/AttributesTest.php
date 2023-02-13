<?php

namespace ipl\Tests\Html;

use ipl\Html\Attribute;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;

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

    public function testNativeAttributesAndCallbacks()
    {
        $objectOne = new class extends BaseHtmlElement {
            protected $defaultAttributes = ['class' => 'foo'];

            protected $attr;

            public function getAttr()
            {
                return $this->attr;
            }

            public function setAttr($val)
            {
                $this->attr = $val;
            }
        };

        $objectOne->getAttributes()->registerAttributeCallback(
            'class',
            [$objectOne, 'getAttr'],
            [$objectOne, 'setAttr']
        );

        $objectOne->getAttributes()->set('class', 'bar');

        $this->assertEquals('bar', $objectOne->getAttr());
        $this->assertEquals(' class="foo bar"', $objectOne->getAttributes()->render());
        $this->assertEquals('foo', $objectOne->getAttributes()->getAttributes()['class']->getValue());
    }

    public function testAttributesMerge()
    {
        $emptyAttributes = new Attributes();
        $filledAttributes = Attributes::create([
            'foo' => 'bar',
            'bar' => 'foo'
        ]);

        $emptyAttributes->merge($filledAttributes);

        $this->assertEquals(' foo="bar" bar="foo"', $emptyAttributes->render());

        $moreAttributes = Attributes::create(['foo' => 'rab']);

        $moreAttributes->merge($filledAttributes);

        $this->assertEquals(' foo="rab bar" bar="foo"', $moreAttributes->render());
    }

    public function testAttributesMergeWithCallbacks()
    {
        $attributes = Attributes::create(['foo' => 'bar']);
        $callbacks = (new Attributes())
            ->registerAttributeCallback('foo', function () {
                return 'rab';
            })
            ->registerAttributeCallback(
                'bar',
                function () use (&$value) {
                    return $value;
                },
                function ($v) use (&$value) {
                    $value = $v;
                }
            );

        $attributes->merge($callbacks);

        $attributes->set('bar', 'foo');

        $this->assertEquals(' foo="bar rab" bar="foo"', $attributes->render());
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

    public function testAttributesAreDeepCloned()
    {
        $attributes = Attributes::create(['class' => 'one']);

        $clone = clone $attributes;
        $clone->add('class', 'two');

        $this->assertNotSame(
            $attributes->get('class'),
            $clone->get('class'),
            'Attribute instances are not cloned'
        );
        $this->assertSame(
            'one',
            $attributes->get('class')->getValue(),
            'Attribute instances are not cloned correctly'
        );
        $this->assertSame(
            ['one', 'two'],
            $clone->get('class')->getValue(),
            'Attribute instances are not cloned correctly'
        );
    }
}

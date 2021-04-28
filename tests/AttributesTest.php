<?php

namespace ipl\Tests\Html;

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
}

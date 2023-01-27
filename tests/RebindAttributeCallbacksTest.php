<?php

namespace ipl\Tests\Html;

use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;

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
}

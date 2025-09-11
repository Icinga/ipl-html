<?php

namespace ipl\Tests\Html;

use ipl\Html\Attributes;
use ipl\Html\HtmlElement;

class HtmlElementTest extends TestCase
{
    public function testHtmlElementAcceptsAttributesAsIs(): void
    {
        $element = new HtmlElement(
            'div',
            Attributes::create(['title' => 'bar'])
                ->registerAttributeCallback('class', fn() => 'foo')
        );

        $this->assertEquals('<div title="bar" class="foo"></div>', $element->render());
    }
}

<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\FormDecorator\ErrorsDecorator;
use ipl\Html\FormElement\TextElement;
use ipl\Html\HtmlString;
use ipl\Tests\Html\TestCase;

class ErrorsDecoratorTest extends TestCase
{
    public function testDefaultCssClassExists(): void
    {
        $this->assertNotEmpty((new ErrorsDecorator())->getClass());
    }

    public function testSetClass(): void
    {
        $decorator = (new ErrorsDecorator())->setClass('testing the setter');
        $this->assertSame('testing the setter', $decorator->getClass());

        $decorator->setClass(['testing the setter']);
        $this->assertSame(['testing the setter'], $decorator->getClass());

        $decorator->setClass(['testing', 'the', 'setter']);
        $this->assertSame(['testing', 'the', 'setter'], $decorator->getClass());

        $decorator->setClass([]);
        $this->assertSame([], $decorator->getClass());

        $decorator->setClass('');
        $this->assertSame('', $decorator->getClass());
    }

    public function testWithoutErrorMessages(): void
    {
        $results = new DecorationResults();
        (new ErrorsDecorator())->decorate($results, new TextElement('test'));

        $this->assertSame('', (string) $results);
    }

    public function testWithErrorMessages(): void
    {
        $element = (new TextElement('test'))->setMessages(['First error', 'Second error']);
        $results = new DecorationResults();
        (new ErrorsDecorator())->decorate($results, $element);

        $html = <<<HTML
<ul class="form-element-errors">
  <li>First error</li>
  <li>Second error</li>
</ul>
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }
}

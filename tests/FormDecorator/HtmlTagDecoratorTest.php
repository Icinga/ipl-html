<?php

namespace ipl\Tests\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecoration\DecorationResults;
use ipl\Html\FormDecoration\HtmlTagDecorator;
use ipl\Html\FormDecoration\Transformation;
use ipl\Html\FormElement\TextElement;
use ipl\Tests\Html\TestCase;
use RuntimeException;

class HtmlTagDecoratorTest extends TestCase
{
    protected HtmlTagDecorator $decorator;

    public function setUp(): void
    {
        $this->decorator = new HtmlTagDecorator();
    }

    public function testMethodGetName(): void
    {
        $this->assertNotEmpty($this->decorator->getName());
    }
    public function testExceptionThrownWhenNoTagSpecified(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Option "tag" must be set');

        $this->decorator->getTag();
    }

    public function testTag(): void
    {
        $this->assertSame('div', $this->decorator->setTag('div')->getTag());
        $this->assertSame('span', $this->decorator->setTag('span')->getTag());
        $this->assertSame('custom', $this->decorator->setTag('custom')->getTag());
    }

    public function testPlacement(): void
    {
        $this->assertSame(Transformation::Wrap, $this->decorator->getTransformation());

        $this->decorator->setTransformation(Transformation::Append);
        $this->assertSame(Transformation::Append, $this->decorator->getTransformation());

        $this->decorator->setTransformation(Transformation::Prepend);
        $this->assertSame(Transformation::Prepend, $this->decorator->getTransformation());

        $this->decorator->setTransformation(Transformation::Wrap);
        $this->assertSame(Transformation::Wrap, $this->decorator->getTransformation());
    }

    public function testCondition(): void
    {
        $formElement = new TextElement('test');

        $results = new DecorationResults();
        $this->decorator
            ->setTag('div')
            ->setCondition(fn($formElement) => false)
            ->decorate($results, $formElement);

        $this->assertSame('', $results->assemble()->render());

        $this->decorator
            ->setCondition(fn($formElement) => true)
            ->decorate($results, $formElement);

        $this->assertSame('<div></div>', $results->assemble()->render());
    }

    public function testConditionCallbackThrowsExceptionWhenCallbackFailed(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Condition callback failed');

        $this->decorator
            ->setCondition(fn() => notExist())
            ->decorate(new DecorationResults(), new TextElement('test'));
    }

    /**
     * @depends testCondition
     */
    public function testConditionCallbackThrowsExceptionWhenCallReturnsNonBoolValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition callback must return a boolean, got integer');

        $this->decorator
            ->setCondition(fn() => 1)
            ->decorate(new DecorationResults(), new TextElement('test'));
    }

    public function testMethodDecorateWithDefaultPlacementWrap(): void
    {
        $element = new TextElement('test');
        $results = (new DecorationResults())->append($element);
        $this->decorator
            ->setTag('div')
            ->decorate($results, $element);

        $html = <<<'HTML'
<div>
  <input type="text" name="test">
</div>
HTML;

        $this->assertHtml($html, $results->assemble());
    }

    public function testMethodDecorateWithPlacementAppend(): void
    {
        $element = new TextElement('test');
        $results = (new DecorationResults())->append($element);
        $this->decorator
            ->setTag('div')
            ->setTransformation(Transformation::Append)
            ->decorate($results, $element);

        $html = <<<'HTML'
<input type="text" name="test">
<div></div>
HTML;

        $this->assertHtml($html, $results->assemble());
    }

    public function testMethodDecorateWithPlacementPrepend(): void
    {
        $element = new TextElement('test');
        $results = (new DecorationResults())->append($element);
        $this->decorator
            ->setTag('div')
            ->setTransformation(Transformation::Prepend)
            ->decorate($results, $element);

        $html = <<<'HTML'
<div></div>
<input type="text" name="test">
HTML;

        $this->assertHtml($html, $results->assemble());
    }

    public function testNonHtmlFormElementsAreSupported(): void
    {
        $results = new DecorationResults();
        $element = $this->createStub(FormElement::class);
        $element->method('render')->willReturn('<input type="text" name="test">');

        $this->decorator->setTag('div');
        $results->append($element);

        $this->decorator->decorate($results, $element);

        $html = <<<'HTML'
<div>
  <input type="text" name="test">
</div>
HTML;

        $this->assertHtml($html, $results->assemble());
    }
}

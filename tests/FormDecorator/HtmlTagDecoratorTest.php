<?php

namespace ipl\Tests\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\FormDecorator\HtmlTagDecorator;
use ipl\Html\FormDecorator\Placement;
use ipl\Html\FormElement\TextElement;
use ipl\Html\HtmlString;
use ipl\Tests\Html\TestCase;

class HtmlTagDecoratorTest extends TestCase
{
    public function testExceptionThrownWhenNoTagSpecified(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "tag" must be set');

        (new HtmlTagDecorator())->getTag();
    }

    public function testTag(): void
    {
        $htmlTag = new HtmlTagDecorator();

        $this->assertSame('div', $htmlTag->setTag('div')->getTag());
        $this->assertSame('span', $htmlTag->setTag('span')->getTag());
        $this->assertSame('custom', $htmlTag->setTag('custom')->getTag());
    }

    public function testPlacement(): void
    {
        $htmlTag = new HtmlTagDecorator();

        $this->assertSame(Placement::Wrap, $htmlTag->getPlacement());

        $htmlTag->setPlacement(Placement::Append);
        $this->assertSame(Placement::Append, $htmlTag->getPlacement());

        $htmlTag->setPlacement(Placement::Prepend);
        $this->assertSame(Placement::Prepend, $htmlTag->getPlacement());

        $htmlTag->setPlacement(Placement::Wrap);
        $this->assertSame(Placement::Wrap, $htmlTag->getPlacement());
    }

    public function testCondition(): void
    {
        $formElement = new TextElement('test');
        $htmlTag = (new HtmlTagDecorator())
            ->setCondition(fn($formElement) => $formElement->getName() === 'test');

        $callback = $htmlTag->getCondition();

        $this->assertIsCallable($callback);
        $this->assertSame(true, $callback($formElement));
    }

    /**
     * @depends testCondition
     */
    public function testConditionCallbackThrowsExceptionWhenCallReturnsNonBoolValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition callback must return a boolean, got integer');

        (new HtmlTagDecorator())
            ->setCondition(fn() => 1)
            ->decorate(new DecorationResults(), new TextElement('test'));
    }

    public function testMethodDecorateWithDefaultPlacementWrap(): void
    {
        $element = new TextElement('test');
        $results = (new DecorationResults())->append($element);
        (new HtmlTagDecorator())
            ->setTag('div')
            ->decorate($results, $element);

        $html = <<<'HTML'
<div>
  <input type="text" name="test">
</div>
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testMethodDecorateWithPlacementAppend(): void
    {
        $element = new TextElement('test');
        $results = (new DecorationResults())->append($element);
        (new HtmlTagDecorator())
            ->setTag('div')
            ->setPlacement(Placement::Append)
            ->decorate($results, $element);

        $html = <<<'HTML'
<input type="text" name="test">
<div></div>
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testMethodDecorateWithPlacementPrepend(): void
    {
        $element = new TextElement('test');
        $results = (new DecorationResults())->append($element);
        (new HtmlTagDecorator())
            ->setTag('div')
            ->setPlacement(Placement::Prepend)
            ->decorate($results, $element);

        $html = <<<'HTML'
<div></div>
<input type="text" name="test">
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }
}

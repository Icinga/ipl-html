<?php

namespace ipl\Tests\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\FormDecorator\HtmlTagDecorator;
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

    public function testInvalidPlacementThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown placement "5" given');

        (new HtmlTagDecorator())->setPlacement(5)->getPlacement();
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

        $this->assertSame(HtmlTagDecorator::PLACEMENT_WRAP, $htmlTag->getPlacement());

        $htmlTag->setPlacement(HtmlTagDecorator::PLACEMENT_APPEND);
        $this->assertSame(HtmlTagDecorator::PLACEMENT_APPEND, $htmlTag->getPlacement());

        $htmlTag->setPlacement(HtmlTagDecorator::PLACEMENT_PREPEND);
        $this->assertSame(HtmlTagDecorator::PLACEMENT_PREPEND, $htmlTag->getPlacement());

        $htmlTag->setPlacement(HtmlTagDecorator::PLACEMENT_WRAP);
        $this->assertSame(HtmlTagDecorator::PLACEMENT_WRAP, $htmlTag->getPlacement());
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

    public function testMethodDDecorateWithPlacementAppend(): void
    {
        $element = new TextElement('test');
        $results = (new DecorationResults())->append($element);
        (new HtmlTagDecorator())
            ->setTag('div')
            ->setPlacement(HtmlTagDecorator::PLACEMENT_APPEND)
            ->decorate($results, $element);

        $html = <<<'HTML'
<input type="text" name="test">
<div></div>
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testMethodDDecorateWithDefaultPlacementPrepend(): void
    {
        $element = new TextElement('test');
        $results = (new DecorationResults())->append($element);
        (new HtmlTagDecorator())
            ->setTag('div')
            ->setPlacement(HtmlTagDecorator::PLACEMENT_PREPEND)
            ->decorate($results, $element);

        $html = <<<'HTML'
<div></div>
<input type="text" name="test">
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }
}

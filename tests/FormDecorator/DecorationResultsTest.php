<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\FormDecoration\DecorationResults;
use ipl\Html\FormDecoration\Transformation;
use ipl\Html\Html;
use ipl\Tests\Html\TestCase;

class DecorationResultsTest extends TestCase
{
    protected DecorationResults $results;

    public function setUp(): void
    {
        $this->results = new DecorationResults();
    }

    public function testEmptyDecorationResultsRenderEmptyString(): void
    {
        $this->assertSame('', $this->results->assemble()->render());
    }

    public function testMethodSkipDecorators(): void
    {
        $this->assertSame([], $this->results->getSkipDecorators());
        $this->assertSame(
            ['Decorator1'],
            $this->results->skipDecorators('Decorator1')->getSkipDecorators()
        );
        $this->assertSame(
            ['Decorator1', 'Decorator2', 'Decorator3'],
            $this->results->skipDecorators('Decorator2', 'Decorator3')->getSkipDecorators()
        );
    }

    public function testMethodAppend(): void
    {
        $this->results
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->append(Html::tag('div', ['class' => 'second'], 'Second'))
            ->append(Html::tag('div', ['class' => 'third'], 'Third'));

        $html = <<<'HTML'
<div class="first">First</div>
<div class="second">Second</div>
<div class="third">Third</div>
HTML;

        $this->assertHtml($html, $this->results->assemble());
    }

    public function testMethodPrepend(): void
    {
        $this->results
            ->prepend(Html::tag('div', ['class' => 'third'], 'Third'))
            ->prepend(Html::tag('div', ['class' => 'second'], 'Second'))
            ->prepend(Html::tag('div', ['class' => 'first'], 'First'));

        $html = <<<'HTML'
<div class="first">First</div>
<div class="second">Second</div>
<div class="third">Third</div>
HTML;

        $this->assertHtml($html, $this->results->assemble());
    }

    public function testMethodWrap(): void
    {
        $this->results
            ->wrap(Html::tag('div', ['class' => 'wrapper-1']))
            ->wrap(Html::tag('div', ['class' => 'wrapper-2']))
            ->wrap(Html::tag('div', ['class' => 'wrapper-3']));

        $html = <<<'HTML'
<div class="wrapper-3">
  <div class="wrapper-2">
    <div class="wrapper-1"></div>
  </div>
</div>
HTML;

        $this->assertHtml($html, $this->results->assemble());
    }

    public function testAppendAfterWrap(): void
    {
        $this->results
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->wrap(Html::tag('div', ['class' => 'wrapper']))
            ->append(Html::tag('div', ['class' => 'second'], 'Second'));

        $html = <<<'HTML'
<div class="wrapper">
  <div class="first">First</div>
</div>
<div class="second">Second</div>
HTML;

        $this->assertHtml($html, $this->results->assemble());
    }
    public function testPrependAfterWrap(): void
    {
        $this->results
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->wrap(Html::tag('div', ['class' => 'wrapper']))
            ->prepend(Html::tag('div', ['class' => 'second'], 'Second'));

        $html = <<<'HTML'
<div class="second">Second</div>
<div class="wrapper">
  <div class="first">First</div>
</div>
HTML;
        $this->assertHtml($html, $this->results->assemble());
    }

    public function testMixed(): void
    {
        $this->results
            ->append(Html::tag('tag1'))
            ->wrap(Html::tag('tag2'))
            ->prepend(Html::tag('tag3'))
            ->wrap(Html::tag('tag4'))
            ->append(Html::tag('tag5'))
            ->prepend(Html::tag('tag6'))
            ->wrap(Html::tag('tag7'))
            ->wrap(Html::tag('tag8'))
            ->prepend(Html::tag('tag9'))
            ->prepend(Html::tag('tag10'))
            ->wrap(Html::tag('tag11'))
            ->append(Html::tag('tag12'))
            ->prepend(Html::tag('tag13'))
            ->append(Html::tag('tag14'));

        $html = <<<'HTML'
<tag13></tag13>
<tag11>
  <tag10></tag10>
  <tag9></tag9>
  <tag8>
   <tag7>
    <tag6></tag6>
    <tag4>
      <tag3></tag3>
      <tag2>
        <tag1></tag1>
      </tag2>
    </tag4>
    <tag5></tag5>
   </tag7>
  </tag8>
</tag11>
<tag12></tag12>
<tag14></tag14>
HTML;
        $this->assertHtml($html, $this->results->assemble());
    }

    public function testMethodTransformSupportAllCasesAndDoNotThrowAnException(): void
    {
        foreach (Transformation::cases() as $transformation) {
            $this->results->transform($transformation, Html::tag('div'));
        }

        $this->assertNotEmpty($this->results->assemble()->render());
    }

    public function testMethodTransformResultsSameAsAppendPrependAndWrap(): void
    {
        $transform = new DecorationResults();

        $this->assertSame(
            $this->results->append(Html::tag('tag1'))->assemble()->render(),
            $transform->transform(Transformation::Append, Html::tag('tag1'))->assemble()->render()
        );

        $this->assertSame(
            $this->results->wrap(Html::tag('tag2'))->assemble()->render(),
            $transform->transform(Transformation::Wrap, Html::tag('tag2'))->assemble()->render()
        );

        $this->assertSame(
            $this->results->prepend(Html::tag('tag3'))->assemble()->render(),
            $transform->transform(Transformation::Prepend, Html::tag('tag3'))->assemble()->render()
        );

        $this->assertSame(
            $this->results->append(Html::tag('tag4'))->assemble()->render(),
            $transform->transform(Transformation::Append, Html::tag('tag4'))->assemble()->render()
        );
    }
}

<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\Html;
use ipl\Html\HtmlElement;
use ipl\Html\HtmlString;
use ipl\Tests\Html\TestCase;

class DecorationResultsTest extends TestCase
{
    public function testEmptyDecorationResultsRenderEmptyString(): void
    {
        $this->assertSame('', (string) new DecorationResults());
    }

    public function testMethodAppend(): void
    {
        $results = (new DecorationResults())
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->append(Html::tag('div', ['class' => 'second'], 'Second'))
            ->append(Html::tag('div', ['class' => 'third'], 'Third'));

        $html = <<<'HTML'
<div class="first">First</div>
<div class="second">Second</div>
<div class="third">Third</div>
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testMethodPrepend(): void
    {
        $results = (new DecorationResults())
            ->prepend(Html::tag('div', ['class' => 'third'], 'Third'))
            ->prepend(Html::tag('div', ['class' => 'second'], 'Second'))
            ->prepend(Html::tag('div', ['class' => 'first'], 'First'));

        $html = <<<'HTML'
<div class="first">First</div>
<div class="second">Second</div>
<div class="third">Third</div>
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testMethodWrap(): void
    {
        $results = (new DecorationResults())
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

        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testAppendAfterWrap(): void
    {
        $results = (new DecorationResults())
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->wrap(Html::tag('div', ['class' => 'wrapper']))
            ->append(Html::tag('div', ['class' => 'second'], 'Second'));

        $html = <<<'HTML'
<div class="wrapper">
  <div class="first">First</div>
</div>
<div class="second">Second</div>
HTML;

        $this->assertHtml($html, HtmlString::create($results));
    }
    public function testPrependAfterWrap(): void
    {
        $results = (new DecorationResults())
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->wrap(Html::tag('div', ['class' => 'wrapper']))
            ->prepend(Html::tag('div', ['class' => 'second'], 'Second'));

        $html = <<<'HTML'
<div class="second">Second</div>
<div class="wrapper">
  <div class="first">First</div>
</div>
HTML;
        $this->assertHtml($html, HtmlString::create($results));
    }
}

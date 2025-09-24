<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Form;
use ipl\Html\FormDecoration\FormDecorationResult;
use ipl\Html\Html;
use ipl\Tests\Html\TestCase;

class FormDecorationResultTest extends TestCase
{
    public function testMethodAppend(): void
    {
        $form = $this->createMock(Form::class);
        $form->expects($this->exactly(3))->method('addHtml');
        $result = new FormDecorationResult($form);

        $result
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->append(Html::tag('div', ['class' => 'second'], 'Second'))
            ->append(Html::tag('div', ['class' => 'third'], 'Third'));
    }

    public function testMethodPrepend(): void
    {
        $form = $this->createMock(Form::class);
        $form->expects($this->exactly(3))->method('prependHtml');
        $result = new FormDecorationResult($form);

        $result
            ->prepend(Html::tag('div', ['class' => 'first'], 'First'))
            ->prepend(Html::tag('div', ['class' => 'second'], 'Second'))
            ->prepend(Html::tag('div', ['class' => 'third'], 'Third'));
    }

    public function testMethodWrap(): void
    {
        $form = new Form();
        $result = new FormDecorationResult($form);

        $result
            ->wrap(Html::tag('div', ['class' => 'wrapper-1']))
            ->wrap(Html::tag('div', ['class' => 'wrapper-2']))
            ->wrap(Html::tag('div', ['class' => 'wrapper-3']));

        $html = <<<'HTML'
<div class="wrapper-3">
  <div class="wrapper-2">
    <div class="wrapper-1">
      <form method="POST"></form>
    </div>
  </div>
</div>
HTML;

        $this->assertHtml($html, $form);
    }

    public function testAppendAfterWrap(): void
    {
        $form = new Form();
        $result = new FormDecorationResult($form);

        $result
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->wrap(Html::tag('div', ['class' => 'wrapper']))
            ->append(Html::tag('div', ['class' => 'second'], 'Second'));

        $html = <<<'HTML'
<div class="wrapper">
  <form method="POST">
    <div class="first">First</div>
  </form>
  <div class="second">Second</div>
</div>
HTML;

        $this->assertHtml($html, $form);
    }

    public function testPrependAfterWrap(): void
    {
        $form = new Form();
        $result = new FormDecorationResult($form);

        $result
            ->append(Html::tag('div', ['class' => 'first'], 'First'))
            ->wrap(Html::tag('div', ['class' => 'wrapper']))
            ->prepend(Html::tag('div', ['class' => 'second'], 'Second'));

        $html = <<<'HTML'
<div class="wrapper">
  <div class="second">Second</div>
  <form method="POST">
    <div class="first">First</div>
  </form>
</div>
HTML;
        $this->assertHtml($html, $form);
    }

    public function testMixed(): void
    {
        $form = new Form();
        $result = new FormDecorationResult($form);

        $result
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
<tag11>
    <tag13></tag13>
    <tag8>
        <tag10></tag10>
        <tag9></tag9>
        <tag7>
            <tag4>
                <tag6></tag6>
                <tag2>
                    <tag3></tag3>
                    <form method="POST">
                        <tag1></tag1>
                    </form>
                </tag2>
                <tag5></tag5>
            </tag4>
        </tag7>
    </tag8>
    <tag12></tag12>
    <tag14></tag14>
</tag11>
HTML;
        $this->assertHtml($html, $form);
    }
}

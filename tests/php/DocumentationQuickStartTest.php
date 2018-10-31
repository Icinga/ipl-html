<?php

namespace ipl\Tests\Html;

use ipl\Html\Html;

class DocumentationQuickStartTest extends TestCase
{
    public function testEscapingStrings()
    {
        $html = <<<'HTML'
<h1>
Hello there!
</h1>
HTML;

        $this->assertRendersHtml(
            $html,
            Html::tag('h1', 'Hello there!')
        );
    }

    public function testHtmlTagHelper()
    {
        $html = <<<'HTML'
<h1>
Hello there!
</h1>
HTML;

        $this->assertRendersHtml(
            $html,
            Html::h1('Hello there!')
        );

        $html = <<<'HTML'
<h1>
Hello &lt;&gt; world!
</h1>
HTML;

        $this->assertRendersHtml(
            $html,
            Html::tag('h1', 'Hello <> world!')
        );
    }

    public function testContentSeparator()
    {
        $this->assertRendersHtml(
            '<span>Hello&lt;name&gt;outthere!</span>',
            Html::tag('span', ['Hello', '<name>', 'out' ,'there!'])
        );
        $this->assertRendersHtml(
            '<span> Hello &lt;name&gt; out there! </span>',
            Html::tag('span', ['Hello', '<name>', 'out' ,'there!'])->setSeparator(' ')
        );
        $this->assertRendersHtml(
            '<span> * Hello * out * there! * </span>',
            Html::tag('span', ['Hello', 'out' ,'there!'])->setSeparator(' * ')
        );
    }

    public function testHtmlAttributes()
    {
        $html = <<<'HTML'
<p class="error">
Something failed
</p>
HTML;
        $this->assertRendersHtml(
            $html,
            Html::tag('p', ['class' => 'error'], 'Something failed')
        );

        $this->assertRendersHtml(
            '<ul role="menu"></ul>',
            Html::tag('ul', ['role' => 'menu'])
        );
    }

    public function testNestedElements()
    {

        $html = <<<'HTML'
<ul role="menu">
<li>
A point
</li>
</ul>
HTML;
        $this->assertRendersHtml(
            $html,
            Html::tag('ul', ['role' => 'menu'], Html::tag('li', 'A point'))
        );

        $html = <<<'HTML'
<ul role="menu">
<li>
First point
</li>
<li>
Second point
</li>
<li>
Third point
</li>
</ul>
HTML;
        $this->assertRendersHtml(
            $html,
            Html::tag('ul', ['role' => 'menu'], [
                Html::tag('li', 'First point'),
                Html::tag('li', 'Second point'),
                Html::tag('li', 'Third point'),
            ])
        );

        $html = <<<'HTML'
<p>
Hi 
<strong>there</strong>
, are you okay?
</p>
HTML;
        $this->assertRendersHtml(
            $html, //'<p>Hi <strong>there</strong>, are you ok?</p>',
            Html::tag('p', [
                'Hi ',
                Html::tag('strong', 'there'),
                ', are you okay?'
            ])
        );
    }

    public function testFormattedStrings()
    {
        $this->assertEquals(
            'Hi <strong>there</strong>, are you okay?',
            (string) Html::sprintf(
                'Hi %s, are you okay?',
                Html::strong('there')
            )
        );
    }
}

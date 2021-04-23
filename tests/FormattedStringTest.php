<?php

namespace ipl\Tests\Html;

use ipl\Html\FormattedString;
use ipl\Html\Html;
use ipl\Tests\Html\TestDummy\ObjectThatCanBeCastedToString;

class FormattedStringTest extends TestCase
{
    public function testSupportsValidHtmlForStringArguments()
    {
        $this->assertHtml(
            'some <strong>text</strong>',
            FormattedString::create(
                'some %s',
                Html::tag('strong', 'text')
            )
        );
    }

    public function testEscapingForStringArguments()
    {
        $this->assertHtml(
            'some &lt;strong&gt;text&lt;/strong&gt;',
            FormattedString::create(
                'some %s',
                '<strong>text</strong>'
            )
        );
    }

    public function testEscapingForStringArgumentsThatCanBeTreatedLikeAString()
    {
        $this->assertHtml(
            'Some String &lt;:-)',
            FormattedString::create(
                '%s',
                new ObjectThatCanBeCastedToString()
            )
        );
    }

    public function testSprintfLikeBehaviorForNonStringArguments()
    {
        $this->assertHtml(
            'number 1',
            FormattedString::create(
                'number %d',
                1
            )
        );
    }

    public function testSupportsArrayArguments()
    {
        $this->assertHtml(
            '<span>some</span><strong>text</strong>',
            FormattedString::create(
                '%s',
                [
                    Html::tag('span', 'some'),
                    Html::tag('strong', 'text')
                ]
            )
        );
    }

    public function testSupportsMustache()
    {
        $test = FormattedString::create(
            '{{#total}}{{#first}}First{{/first}}{{/total}} %d or {{#second}}Second{{/second}} %d',
            1,
            2
        );
        $test->getMustaches(
            ['total' => Html::tag('h1'),
                'first' => Html::tag('span'),
                'second' => Html::tag('span')
            ]
        );

        $this->assertHtml(
            '<h1><span>First</span></h1> 1 or <span>Second</span> 2',
            $test
        );
    }
}

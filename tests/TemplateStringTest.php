<?php

namespace ipl\Tests\Html;

use ipl\Html\TemplateString;
use ipl\Html\Html;

class TemplateStringTest extends TestCase
{
    public function testSupportsMustache()
    {
        $this->assertHtml(
            'Wow! <h1><span>First</span></h1> 1 or <span>Second</span> 2 <span>wow</span><span>wow</span>',
            TemplateString::create(
                '%s {{#total}}{{#first}}First{{/first}}{{/total}} %d or {{#second}}Second{{/second}} %d %s',
                'Wow!',
                ['second' => Html::tag('span')],
                1,
                2,
                ['total' => Html::tag('h1'),
                    'first' => Html::tag('span')],
                [Html::tag('span', 'wow'),
                    Html::tag('span', 'wow')]
            )
        );
    }

    public function testSupportsFlatTemplates()
    {
        $this->assertHtml(
            '<span>Foo</span> <i>Bar</i>',
            TemplateString::create(
                '{{#for-foo}}Foo{{/for-foo}} {{#for-bar}}Bar{{/for-bar}}',
                [
                    'for-foo' => Html::tag('span'),
                    'for-bar' => Html::tag('i')
                ]
            )
        );
    }

    /**
     * @depends testSupportsFlatTemplates
     */
    public function testSupportsNestedTemplates()
    {
        $this->assertHtml(
            '<span>Foo <i>Bar</i></span>',
            TemplateString::create(
                '{{#for-foo-and-bar}}Foo {{#only-for-bar}}Bar{{/only-for-bar}}{{/for-foo-and-bar}}',
                [
                    'for-foo-and-bar'   => Html::tag('span'),
                    'only-for-bar'      => Html::tag('i')
                ]
            )
        );
    }

    public function testSupportsSimplePlaceholders()
    {
        $this->assertHtml(
            'Foo 42 Bar !',
            TemplateString::create('Foo %d Bar %s', 42, '!')
        );
    }

    /**
     * @depends testSupportsFlatTemplates
     * @depends testSupportsSimplePlaceholders
     */
    public function testSupportsTemplatesAndSimplePlaceholdersAtTheSameTime()
    {
        $this->assertHtml(
            '<span>Foo</span> 42',
            TemplateString::create(
                '{{#for-foo}}Foo{{/for-foo}} %d',
                ['for-foo' => Html::tag('span')],
                42
            )
        );
    }

    /**
     * @depends testSupportsTemplatesAndSimplePlaceholdersAtTheSameTime
     */
    public function testSupportsSimplePlaceholdersWithinTemplates()
    {
        $this->assertHtml(
            '<span>Foo</span> <i>42</i>',
            TemplateString::create(
                '{{#for-foo}}Foo{{/for-foo}} {{#for-bar}}%d{{/for-bar}}',
                [
                    'for-foo' => Html::tag('span'),
                    'for-bar' => Html::tag('i')
                ],
                42
            )
        );
    }

    /**
     * @depends testSupportsFlatTemplates
     */
    public function testRendersAdditionalContentCorrectly()
    {
        $this->assertHtml(
            '<span><i>Foo</i>Bar</span>',
            TemplateString::create(
                '{{#for-bar}}Bar{{/for-bar}}',
                ['for-bar' => Html::tag('span', Html::tag('i', 'Foo'))]
            )
        );
    }

    /**
     * @depends testSupportsFlatTemplates
     * @depends testRendersAdditionalContentCorrectly
     */
    public function testProcessesTemplatesDeferred()
    {
        $title = Html::tag('h1');
        $template = TemplateString::create('{{#title}}Foo Bar{{/total}}', ['title' => $title]);

        $title->addAttributes(['class' => 'main']);
        $title->add('Main: ');

        $this->assertHtml(
            '<h1 class="main">Main: Foo Bar</h1>',
            $template
        );
    }
}

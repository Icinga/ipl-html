<?php

namespace ipl\Tests\Html;

use ipl\Html\MustachedTemplate;
use ipl\Html\Html;

class MustachedTemplateTest extends TestCase
{
    public function testSupportsMustache()
    {
        $test = new MustachedTemplate();
        $test->replaceMustaches(
            '{{#total}}{{#first}}First{{/first}}{{/total}} 1 or {{#second}}Second{{/second}} 2',
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

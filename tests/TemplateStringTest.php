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
}

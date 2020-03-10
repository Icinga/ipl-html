<?php

namespace ipl\Tests\Html;

use ipl\Html\Html;

class HtmlTest extends TestCase
{
    public function testTagSupportsIterable()
    {
        $content = function () {
            yield Html::tag('b', 'foo');
            yield Html::tag('b', 'bar');
        };

        $html = Html::tag('div', $content());

        $this->assertHtml('<div><b>foo</b><b>bar</b></div>', $html);
    }

    public function testWrapsListsWithSimpleHtmlTags()
    {
        $this->assertXmlStringEqualsXmlString(
            '<ul><li>a</li><li>b</li><li>c</li></ul>',
            Html::tag('ul', Html::wrapEach(['a', 'b', 'c'], 'li'))->render()
        );
    }

    public function testWrapsListsWithCallback()
    {
        $options = [
            'val1' => 'Label 1',
            'val2' => 'Label 2',
            'val3' => 'Label 3',
        ];
        $select = Html::tag('select', Html::wrapEach($options, function ($name, $value) {
            return Html::tag('option', [
                'value' => $name
            ], $value);
        }));

        $this->assertXmlStringEqualsXmlString(
            '<select>'
            . '<option value="val1">Label 1</option>'
            . '<option value="val2">Label 2</option>'
            . '<option value="val3">Label 3</option>'
            . '</select>',
            $select->render()
        );
    }
}

<?php

namespace ipl\Tests\Html;

use ipl\Html\Html as h;
use ipl\Html\HtmlDocument;

class HtmlDocumentTest extends TestCase
{
    public function testStaticCallsGiveValidElements()
    {
        $this->assertInstanceOf('ipl\\Html\\HtmlElement', h::span());
    }

    public function testStaticCallsAcceptContentAsFirstAttribute()
    {
        $this->assertRendersHtml('<span>&gt;5</span>', h::span('>5'));
        $this->assertRendersHtml('<span>&gt;5</span>', h::span(['>5']));
        $this->assertRendersHtml(
            '<span><b>&gt;5</b>&lt;</span>',
            h::span([h::b(['>5']), '<'])
        );
    }

    public function testStaticCallsAcceptAttributesAsFirstAttribute()
    {
        $this->assertRendersHtml(
            '<span class="test it" />',
            h::span(['class' => 'test it'])
        );
        $this->assertRendersHtml(
            '<span class="test it">&gt;5</span>',
            h::span(['class' => 'test it'], '>5')
        );
    }

    public function testAttributesAndContentAreAccepted()
    {
        $this->assertRendersHtml(
            '<span class="test it">&gt;5</span>',
            h::span(['class' => 'test it'], ['>5'])
        );
    }

    public function testDocumentSupportsMultipleWrappers()
    {
        $sample = <<<'HTML'
<b>
<a>
Just some content
</a>
</b>
HTML;
        $a = h::tag('a');
        $b = h::tag('b');
        $c = (new HtmlDocument())->add('Just some content');
        $c->addWrapper($a);
        $c->addWrapper($b);
        $this->assertRendersHtml(
            $sample,
            $c
        );
    }

    public function testDocumentSupportsMultiplePrependedWrappers()
    {
        $a = h::tag('a');
        $b = h::tag('b');
        $c = (new HtmlDocument())->add('Just some content');
        $c->prependWrapper($a);
        $c->prependWrapper($b);
        $this->assertRendersHtml(
            '<a><b>Just some content</b></a>',
            $c
        );
    }
}

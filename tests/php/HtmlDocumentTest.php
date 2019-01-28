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
        $a = h::tag('a');
        $b = h::tag('b');
        $c = (new HtmlDocument())->add('Just some content');
        $c->addWrapper($a);
        $c->addWrapper($b);
        $this->assertRendersHtml(
            '<b><a>Just some content</a></b>',
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

    public function testAcceptsObjectsWhichCanBeCastedToString()
    {
        require_once __DIR__ . '/helpers/DummyObjectThatCanBeCastedToString.php';
        $object = new \DummyObjectThatCanBeCastedToString();
        $a = new HtmlDocument();
        $a->add($object);
        $this->assertEquals('Some String &lt;:-)', $a->render());
    }

    public function testSkipsNullValues()
    {
        $a = new HtmlDocument();
        $a->setSeparator('x');
        $a->add([null, null]);
        $a->add(null);
        $a->add(null);
        $this->assertEquals('', $a->render());
    }
}

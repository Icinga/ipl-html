<?php

namespace ipl\Tests\Html;

use ipl\Html\BaseHtmlElement;
use ipl\Html\Html as h;
use ipl\Html\HtmlDocument;
use ipl\Tests\Html\TestDummy\AddsContentDuringAssemble;
use ipl\Tests\Html\TestDummy\AddsWrapperDuringAssemble;
use ipl\Tests\Html\TestDummy\IterableElement;
use ipl\Tests\Html\TestDummy\ObjectThatCanBeCastedToString;

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

    public function testWrapperAddedDuringAssemble()
    {
        $addsWrapperDuringAssemble = new AddsWrapperDuringAssemble();
        $addsWrapperDuringAssemble->add(h::tag('p', 'some text'));
        $this->assertRendersHtml(
            '<div><p>some text</p></div>',
            $addsWrapperDuringAssemble
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
        $object = new ObjectThatCanBeCastedToString();
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

    public function testElementIsCleanlyRemoved()
    {
        $a = new HtmlDocument();
        $s1 = h::tag('b', 'one');
        $s2 = h::tag('b', 'two');
        $s3 = h::tag('b', 'three');
        $a->add([$s1, $s2, $s3]);
        $a->ensureAssembled();
        $a->remove($s2);
        $this->assertEquals(
            '<b>one</b><b>three</b>',
            $a->render()
        );
        $a->add($s1);
        $this->assertEquals(
            '<b>one</b><b>three</b><b>one</b>',
            $a->render()
        );
        $a->remove($s1);
        $this->assertEquals(
            '<b>three</b>',
            $a->render()
        );
    }

    public function testAddFromCorrectlyPassesElements()
    {
        $ul = h::tag('ul');
        $ul->add(h::tag('li', ['style' => 'margin:0'], 'one'));
        $ul->add(h::tag('li', 'two'));
        $ul->add(h::tag('li', 'three'));

        $ol = h::tag('ol');
        $ol->addFrom($ul);

        $this->assertRendersHtml(
            '<ol><li style="margin:0">one</li><li>two</li><li>three</li></ol>',
            $ol
        );
    }

    public function testAddFromCorrectlyPassesElementsWithCallback()
    {
        $ul = h::tag('ul');
        $ul->add(h::tag('li', ['id' => '1'], 'one'));
        $ul->add(h::tag('li', ['id' => '2'], 'two'));
        $ul->add(h::tag('li', ['id' => '3'], 'three'));

        $div = h::tag('div');
        $div->addFrom($ul, function (BaseHtmlElement $item) {
            if ($item->getAttributes()->get('id')->getValue() !== '2') {
                $item->setTag('p');
                $item->getAttributes()->remove('id');
                return $item;
            }
        });

        $this->assertRendersHtml(
            '<div><p>one</p><p>three</p></div>',
            $div
        );
    }

    public function testAddSupportsIterable()
    {
        $content = function () {
            yield h::tag('b', 'foo');
            yield h::tag('b', 'bar');
        };

        $html = (new HtmlDocument())
            ->add($content());

        $this->assertHtml('<b>foo</b><b>bar</b>', $html);
    }

    public function testPrependSupportsIterable()
    {
        $content = function () {
            yield h::tag('b', 'foo');
        };

        $html = (new HtmlDocument())
            ->add(h::tag('b', 'bar'))
            ->prepend($content());

        $this->assertHtml('<b>foo</b><b>bar</b>', $html);
    }

    public function testAddSupportsIterableValidHtml()
    {
        $html = (new HtmlDocument())
            ->add(new IterableElement());

        $this->assertHtml('<b>foo</b><b>bar</b>', $html);
    }

    public function testPrependSupportsIterableValidHtml()
    {
        $html = (new HtmlDocument())
            ->add(h::tag('b', 'baz'))
            ->prepend(new IterableElement());

        $this->assertHtml('<b>foo</b><b>bar</b><b>baz</b>', $html);
    }

    public function testIsEmptyRespectsContentAddedInAssemble()
    {
        $this->assertFalse((new AddsContentDuringAssemble())->isEmpty());
    }
}

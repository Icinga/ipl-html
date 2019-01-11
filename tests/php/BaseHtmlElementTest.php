<?php

namespace ipl\Tests\Html;

use ipl\Html\BaseHtmlElement;

// @codingStandardsIgnoreStart
class DefaultAttributesAsProperty extends BaseHtmlElement
{
    protected $tag = 'div';

    protected $defaultAttributes = ['class' => 'test'];
}

class DefaultAttributesAsMethod extends BaseHtmlElement
{
    protected $tag = 'div';

    public function getDefaultAttributes()
    {
        return ['class' => 'test'];
    }
}

class VoidElementWithContent extends BaseHtmlElement
{
    protected $tag = 'img';

    protected function assemble()
    {
        $this->add('content');
    }
}

class Img extends BaseHtmlElement
{
    protected function tag()
    {
        return 'img';
    }
}

class Div extends BaseHtmlElement
{
    protected $tag = 'div';
}

class Span extends BaseHtmlElement
{
    protected $tag = 'span';
}

class NoTag extends BaseHtmlElement
{

}

class BaseHtmlElementTest extends TestCase
{
    // @codingStandardsIgnoreEnd
    public function testRenderDefaultAttributesAsProperty()
    {
        $this->assertXmlStringEqualsXmlString(
            '<div class="test"></div>',
            (new DefaultAttributesAsProperty())->render()
        );
    }

    public function testRenderDefaultAttributesAsMethod()
    {
        $this->assertXmlStringEqualsXmlString(
            '<div class="test"></div>',
            (new DefaultAttributesAsMethod())->render()
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionThrownForVoidElementsWithContent()
    {
        (new VoidElementWithContent())->render();
    }

    public function testGetTag()
    {
        $element = new Img();

        $this->assertSame('img', $element->getTag());
        $this->assertTrue($element->isVoid());
        $this->assertFalse($element->wantsClosingTag());
    }

    public function testSetTag()
    {
        $element = new Div();

        $this->assertSame('div', $element->getTag());
        $this->assertFalse($element->isVoid());
        $this->assertTrue($element->wantsClosingTag());

        $element->setTag('img');

        $this->assertSame('img', $element->getTag());
        $this->assertTrue($element->isVoid());
        $this->assertFalse($element->wantsClosingTag());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAssertTagInRender()
    {
        (new NoTag())->render();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAssertTagInIsVoid()
    {
        (new NoTag())->isVoid();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAssertTagInGetTag()
    {
        (new NoTag())->getTag();
    }

    public function testSetVoid()
    {
        $element = new Img();
        $this->assertFalse($element->wantsClosingTag());
        $this->assertEquals('<img />', $element->render());
        $element->setVoid();
        $this->assertFalse($element->wantsClosingTag());
        $this->assertEquals('<img />', $element->render());
        $element->setVoid(false);
        $this->assertTrue($element->wantsClosingTag());
        $this->assertEquals('<img></img>', $element->render());
        $element->setVoid(true);
        $this->assertFalse($element->wantsClosingTag());
        $this->assertEquals('<img />', $element->render());
        $element->setVoid(null);
        $this->assertFalse($element->wantsClosingTag());
        $this->assertEquals('<img />', $element->render());
    }

    public function testPhrasingContent()
    {
        $wrapper = new Div();
        $content = (new Span())->setContent('bla');

        $this->assertRendersHtml('<span><span>bla</span></span>', (new Span())->setContent($content));

        $this->assertRendersHtml('<div><span>bla</span></div>', $wrapper->setContent($content));

        $this->assertRendersHtml('<div><div></div></div>', $wrapper->setContent(new Div()));

        $html = <<<'HTML'
<div>
<div>
bla
</div>
</div>
HTML;
        $this->assertRendersHtml($html, $wrapper->setContent((new Div())->setContent('bla')));
    }
}

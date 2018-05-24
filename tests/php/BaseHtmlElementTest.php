<?php

namespace ipl\Tests\Html;

use ipl\Html\BaseHtmlElement;

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
    public function getTag()
    {
        return 'img';
    }
}

class Div extends BaseHtmlElement
{
    protected $tag = 'div';
}

class BaseHtmlElementTest extends \PHPUnit_Framework_TestCase
{
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
        $this->assertTrue($element->isVoidElement());
        $this->assertFalse($element->wantsClosingTag());
    }

    public function testSetTag()
    {
        $element = new Div();

        $this->assertSame('div', $element->getTag());
        $this->assertFalse($element->isVoidElement());
        $this->assertTrue($element->wantsClosingTag());

        $element->setTag('img');

        $this->assertSame('img', $element->getTag());
        $this->assertTrue($element->isVoidElement());
        $this->assertFalse($element->wantsClosingTag());
    }
}

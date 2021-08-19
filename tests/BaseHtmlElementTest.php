<?php

namespace ipl\Tests\Html;

use ipl\Html\BaseHtmlElement;
use ipl\Html\HtmlString;

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

class NoTag extends BaseHtmlElement
{

}

class SpecialHtmlString extends HtmlString
{
    public $state;

    public function render()
    {
        $html = parent::render();

        $this->state = 42;

        return $html;
    }
}

class AttributeValueDependingOnContent extends BaseHtmlElement
{
    protected $tag = 'div';

    protected function assemble()
    {
        $specialHtmlString = new SpecialHtmlString('<hr>');
        $this->addHtml($specialHtmlString);

        $this->getAttributes()->registerAttributeCallback('state', function () use ($specialHtmlString) {
            return $specialHtmlString->state;
        });
    }
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

    public function testExceptionThrownForVoidElementsWithContent()
    {
        $this->expectException(\RuntimeException::class);
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

    public function testAssertTagInRender()
    {
        $this->expectException(\RuntimeException::class);
        (new NoTag())->render();
    }

    public function testAssertTagInIsVoid()
    {
        $this->expectException(\RuntimeException::class);
        (new NoTag())->isVoid();
    }

    public function testAssertTagInGetTag()
    {
        $this->expectException(\RuntimeException::class);
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

    public function testAttributeValueDependingOnContent()
    {
        $element = new AttributeValueDependingOnContent();

        $this->assertHtml(
            '<div state="42"><hr></div>',
            $element
        );
    }
}

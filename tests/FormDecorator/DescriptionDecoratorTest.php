<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecoration\DecorationResults;
use ipl\Html\FormDecoration\DescriptionDecorator;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\FormElement\TextElement;
use ipl\Tests\Html\TestCase;

class DescriptionDecoratorTest extends TestCase
{
    protected DescriptionDecorator $decorator;

    public function setUp(): void
    {
        $this->decorator = new DescriptionDecorator();
    }

    public function testDefaultCssClassExists(): void
    {
        $this->assertNotEmpty($this->decorator->getClass());
    }

    public function testSetClass(): void
    {
        $decorator = $this->decorator->setClass('testing the setter');
        $this->assertSame('testing the setter', $decorator->getClass());

        $decorator->setClass(['testing the setter']);
        $this->assertSame(['testing the setter'], $decorator->getClass());

        $decorator->setClass(['testing', 'the', 'setter']);
        $this->assertSame(['testing', 'the', 'setter'], $decorator->getClass());

        $decorator->setClass([]);
        $this->assertSame([], $decorator->getClass());

        $decorator->setClass('');
        $this->assertSame('', $decorator->getClass());
    }

    public function testWithDescriptionAttributeOnly(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new TextElement('test', ['description' => 'Testing']));

        $this->assertHtml('<p class="form-element-description">Testing</p>', $results->assemble());
    }

    public function testWithDescriptionAndIdAttribute(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate(
            $results,
            new TextElement('test', ['description' => 'Testing', 'id' => 'test-id'])
        );

        $this->assertHtml('<p class="form-element-description" id="desc_test-id">Testing</p>', $results->assemble());
    }

    public function testWithEmptyDescriptionAttribute(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new TextElement('test', ['description' => '']));

        $this->assertHtml('<p class="form-element-description"></p>', $results->assemble());
    }

    public function testWithoutDescriptionAttribute(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new TextElement('test'));

        $this->assertSame('', $results->assemble()->render());
    }

    public function testFieldsetElementsAreIgnored(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new FieldsetElement('test'));

        $this->assertSame('', $results->assemble()->render());
    }

    public function testNonHtmlFormElementsAreSupported(): void
    {
        $results = new DecorationResults();
        $element = $this->createStub(FormElement::class);
        $element->method('getDescription')->willReturn('Testing');
        $element->expects($this->never())->method('getAttributes');

        $this->decorator->decorate($results, $element);

        $this->assertHtml('<p class="form-element-description">Testing</p>', $results->assemble());
    }
}

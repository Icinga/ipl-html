<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\FormDecorator\DescriptionDecorator;
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

    public function testMethodGetName(): void
    {
        $this->assertNotEmpty($this->decorator->getName());
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

        $this->assertHtml('<p class="form-element-description">Testing</p>', $results);
    }

    public function testWithDescriptionAndIdAttribute(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate(
            $results,
            new TextElement('test', ['description' => 'Testing', 'id' => 'test-id'])
        );

        $this->assertHtml('<p class="form-element-description" id="desc_test-id">Testing</p>', $results);
    }

    public function testWithEmptyDescriptionAttribute(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new TextElement('test', ['description' => '']));

        $this->assertHtml('<p class="form-element-description"></p>', $results);
    }

    public function testWithoutDescriptionAttribute(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new TextElement('test'));

        $this->assertSame('', $results->render());
    }

    public function testFieldsetElementsAreIgnored(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new FieldsetElement('test'));

        $this->assertSame('', $results->render());
    }
}

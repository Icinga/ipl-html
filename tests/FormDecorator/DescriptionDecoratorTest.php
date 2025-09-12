<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\FormDecorator\DescriptionDecorator;
use ipl\Html\FormElement\TextElement;
use ipl\Html\HtmlString;
use ipl\Tests\Html\TestCase;

class DescriptionDecoratorTest extends TestCase
{
    public function testMethodGetName(): void
    {
        $this->assertNotEmpty((new DescriptionDecorator())->getName());
    }

    public function testDefaultCssClassExists(): void
    {
        $this->assertNotEmpty((new DescriptionDecorator())->getClass());
    }

    public function testSetClass(): void
    {
        $decorator = (new DescriptionDecorator())->setClass('testing the setter');
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
        (new DescriptionDecorator())->decorate($results, new TextElement('test', ['description' => 'Testing']));

        $this->assertHtml('<p class="form-element-description">Testing</p>', $results);
    }

    public function testWithDescriptionAndIdAttribute(): void
    {
        $results = new DecorationResults();
        (new DescriptionDecorator())->decorate(
            $results,
            new TextElement('test', ['description' => 'Testing', 'id' => 'test-id'])
        );

        $this->assertHtml('<p class="form-element-description" id="desc_test-id">Testing</p>', $results);
    }

    public function testWithEmptyDescriptionAttribute(): void
    {
        $results = new DecorationResults();
        (new DescriptionDecorator())->decorate($results, new TextElement('test', ['description' => '']));

        $this->assertHtml('<p class="form-element-description"></p>', $results);
    }

    public function testWithoutDescriptionAttribute(): void
    {
        $results = new DecorationResults();
        (new DescriptionDecorator())->decorate($results, new TextElement('test'));

        $this->assertSame('', $results->render());
    }
}

<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\FormDecorator\LabelDecorator;
use ipl\Html\FormElement\TextElement;
use ipl\Html\HtmlString;
use ipl\Tests\Html\TestCase;

class LabelDecoratorTest extends TestCase
{
    public function testMethodGetName(): void
    {
        $this->assertNotEmpty((new LabelDecorator())->getName());
    }

    public function testDefaultCssClassExists(): void
    {
        $this->assertNotEmpty((new LabelDecorator())->getClass());
    }

    public function testSetClass(): void
    {
        $decorator = (new LabelDecorator())->setClass('testing the setter');
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

    public function testWithLabelAttributeOnly(): void
    {
        $results = new DecorationResults();
        (new LabelDecorator())->decorate($results, new TextElement('test', ['label' => 'Label Here']));

        $this->assertHtml(
            '<label class="form-element-label">Label Here</label>',
            $results
        );
    }

    public function testWithLabelAndIdAttribute(): void
    {
        $results = new DecorationResults();
        (new LabelDecorator())->decorate(
            $results,
            new TextElement('test', ['label' => 'Label Here', 'id' => 'test-id'])
        );

        $this->assertHtml(
            '<label for="test-id" class="form-element-label">Label Here</label>',
            $results
        );
    }

    public function testWithEmptyLabelAttribute(): void
    {
        $results = new DecorationResults();
        (new LabelDecorator())->decorate($results, new TextElement('test', ['label' => '']));

        $this->assertHtml(
            '<label class="form-element-label"></label>',
            $results
        );
    }

    public function testWithoutLabelAttribute(): void
    {
        $results = new DecorationResults();
        (new LabelDecorator())->decorate($results, new TextElement('test'));

        $this->assertSame('', $results->render());
    }
}

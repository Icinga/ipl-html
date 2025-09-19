<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecoration\DecorationResults;
use ipl\Html\FormDecoration\LabelDecorator;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\FormElement\SubmitButtonElement;
use ipl\Html\FormElement\SubmitElement;
use ipl\Html\FormElement\TextElement;
use ipl\Tests\Html\TestCase;

class LabelDecoratorTest extends TestCase
{
    protected LabelDecorator $decorator;

    public function setUp(): void
    {
        $this->decorator = new LabelDecorator();
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
        $this->assertSame('', $this->decorator->setClass('')->getClass());
        $this->assertSame([], $this->decorator->setClass([])->getClass());
        $this->assertSame('testing the setter', $this->decorator->setClass('testing the setter')->getClass());
        $this->assertSame(['testing the setter'], $this->decorator->setClass(['testing the setter'])->getClass());
        $this->assertSame(
            ['testing', 'the', 'setter'],
            $this->decorator->setClass(['testing', 'the', 'setter'])->getClass()
        );
    }

    public function testWithLabelAttributeOnly(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new TextElement('test', ['label' => 'Label Here']));

        $this->assertHtml(
            '<label class="form-element-label">Label Here</label>',
            $results
        );
    }

    public function testWithLabelAndIdAttribute(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate(
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
        $this->decorator->decorate($results, new TextElement('test', ['label' => '']));

        $this->assertHtml(
            '<label class="form-element-label"></label>',
            $results
        );
    }

    public function testWithoutLabelAttribute(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new TextElement('test'));

        $this->assertSame('', $results->render());
    }

    public function testThatSubmitElementsAndFieldsetElementsAreIgnored(): void
    {
        $results = new DecorationResults();
        $this->decorator->decorate($results, new FieldsetElement('test'));
        $this->decorator->decorate($results, new SubmitButtonElement('test'));
        $this->decorator->decorate($results, new SubmitElement('test'));

        $this->assertSame('', $results->render());
    }

    public function testNonHtmlFormElementsAreSupported(): void
    {
        $results = new DecorationResults();
        $element = $this->createStub(FormElement::class);
        $element->method('getLabel')->willReturn('Testing');
        $element->expects($this->never())->method('getAttributes');

        $this->decorator->decorate($results, $element);

        $this->assertHtml('<label class="form-element-label">Testing</label>', $results);
    }
}

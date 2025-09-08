<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\FormDecorator\FieldsetDecorator;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Tests\Html\TestCase;

class FieldsetDecoratorTest extends TestCase
{
    public function testMethodGetName(): void
    {
        $this->assertNotEmpty((new FieldsetDecorator())->getName());
    }
    public function testWithDescriptionAttributeOnly(): void
    {
        $fieldset = new FieldsetElement('test', ['description' => 'Testing']);
        (new FieldsetDecorator())->decorate(new DecorationResults(), $fieldset);

        $html = <<<HTML
<fieldset name="test">
  <p>Testing</p>
</fieldset>
HTML;

        $this->assertHtml($html, $fieldset);
    }

    public function testWithDescriptionAndIdAttributeOnly(): void
    {
        $fieldset = new FieldsetElement('test', ['description' => 'Testing', 'id' => 'test-id']);
        (new FieldsetDecorator())->decorate(new DecorationResults(), $fieldset);

        $html = <<<HTML
<fieldset aria-describedby="desc_test-id" id="test-id" name="test">
  <p id="desc_test-id">Testing</p>
</fieldset>
HTML;

        $this->assertHtml($html, $fieldset);
    }

    public function testWithLabelAttributeOnly(): void
    {
        $fieldset = new FieldsetElement('test', ['label' => 'Testing']);
        (new FieldsetDecorator())->decorate(new DecorationResults(), $fieldset);

        $html = <<<HTML
<fieldset name="test">
  <legend>Testing</legend>
</fieldset>
HTML;

        $this->assertHtml($html, $fieldset);
    }

    public function testWithLabelAndDescriptionAttribute(): void
    {
        $fieldset = new FieldsetElement('test', ['label' => 'Testing', 'description' => 'Testing']);
        (new FieldsetDecorator())->decorate(new DecorationResults(), $fieldset);

        $html = <<<HTML
<fieldset name="test">
  <legend>Testing</legend>
  <p>Testing</p>
</fieldset>
HTML;

        $this->assertHtml($html, $fieldset);
    }

    public function testWithoutLabelAndDescriptionAttribute(): void
    {
        $fieldset = new FieldsetElement('test');
        (new FieldsetDecorator())->decorate(new DecorationResults(), $fieldset);

        $html = <<<HTML
<fieldset name="test"></fieldset>
HTML;

        $this->assertHtml($html, $fieldset);
    }
}

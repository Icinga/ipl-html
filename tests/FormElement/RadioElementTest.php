<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Html\FormElement\RadioElement;
use ipl\I18n\NoopTranslator;
use ipl\I18n\StaticTranslator;
use ipl\Tests\Html\TestCase;

class RadioElementTest extends TestCase
{
    public function testCreateRadioElementsCorrectly()
    {
        $radio = new RadioElement('test', [
            'label' => 'Test',
            'options' => [
                'foo' => 'Foo',
                'bar' => 'Bar',
                'yes' => 'Yes'
            ]
        ]);

        $html = <<<HTML
<label class="radio-label"><input value="foo" name="test" type="radio">Foo</label>
<label class="radio-label"><input value="bar" name="test" type="radio">Bar</label>
<label class="radio-label"><input value="yes" name="test" type="radio">Yes</label>
HTML;

        $this->assertHtml($html, $radio);
    }

    public function testCheckCorrectRadio()
    {
        $radio = new RadioElement('test', [
            'label' => 'Test',
            'options' => [
                'foo' => 'Foo',
                'bar' => 'Bar',
                'yes' => 'Yes'
            ],
            'value' => 'bar'
        ]);

        $html = <<<HTML
<label class="radio-label"><input value="foo" name="test" type="radio">Foo</label>
<label class="radio-label"><input value="bar" name="test" type="radio" checked>Bar</label>
<label class="radio-label"><input value="yes" name="test" type="radio">Yes</label>
HTML;
        $this->assertHtml($html, $radio);

        $radio->setValue('yes');

        $html = <<<HTML
<label class="radio-label"><input value="foo" name="test" type="radio">Foo</label>
<label class="radio-label"><input value="bar" name="test" type="radio">Bar</label>
<label class="radio-label"><input value="yes" name="test" type="radio" checked>Yes</label>
HTML;
        $this->assertHtml($html, $radio);

        $radio->setValue('no');

        $html = <<<HTML
<label class="radio-label"><input value="foo" name="test" type="radio">Foo</label>
<label class="radio-label"><input value="bar" name="test" type="radio">Bar</label>
<label class="radio-label"><input value="yes" name="test" type="radio">Yes</label>
HTML;
        $this->assertHtml($html, $radio);
    }

    public function testDisabledRadioOptions()
    {
        $radio = new RadioElement('test', [
            'label'             => 'Test',
            'options'           => [
                'foo' => 'Foo',
                'bar' => 'Bar',
                'yes' => 'Yes',
                'no'  => 'No'
            ],
            'value'             => 'bar',
            'disabledOptions'    => ['yes']
        ]);

        $html = <<<HTML
<label class="radio-label"><input value="foo" name="test" type="radio">Foo</label>
<label class="radio-label"><input value="bar" name="test" type="radio" checked>Bar</label>
<label class="radio-label"><input value="yes" name="test" type="radio" disabled>Yes</label>
<label class="radio-label"><input value="no" name="test" type="radio">No</label>
HTML;
        $this->assertHtml($html, $radio);

        $radio->disabledOptions(['yes', 'no', 'foo']);

        $html = <<<HTML
<label class="radio-label"><input value="foo" name="test" type="radio" disabled>Foo</label>
<label class="radio-label"><input value="bar" name="test" type="radio" checked>Bar</label>
<label class="radio-label"><input value="yes" name="test" type="radio" disabled>Yes</label>
<label class="radio-label"><input value="no" name="test" type="radio" disabled>No</label>
HTML;
        $this->assertHtml($html, $radio);

        $radio->disabledOptions(['yes', 'no', 'foo', 'bar']);

        $html = <<<HTML
<label class="radio-label"><input value="foo" name="test" type="radio" disabled>Foo</label>
<label class="radio-label"><input value="bar" name="test" type="radio" checked disabled>Bar</label>
<label class="radio-label"><input value="yes" name="test" type="radio" disabled>Yes</label>
<label class="radio-label"><input value="no" name="test" type="radio" disabled>No</label>
HTML;
        $this->assertHtml($html, $radio);
    }

    public function testRadioNotValidIfCheckedValueIsInvalid()
    {
        $this->markTestSkipped('Requires https://github.com/Icinga/ipl-validator/pull/8');

        StaticTranslator::$instance = new NoopTranslator();
        $radio = new RadioElement('test', [
            'label'             => 'Test',
            'options'           => [
                'foo' => 'Foo',
                'bar' => 'Bar',
                'yes' => 'Yes'
            ],
            'value'             => 'bar',
            'disabledOptions'    => ['yes']
        ]);

        $this->assertTrue($radio->isValid());

        $radio->setValue('no');
        $this->assertFalse($radio->isValid());

        $radio->setValue('tom');
        $this->assertFalse($radio->isValid());
    }

    public function testRadioNotValidIfCheckedValueIsDisabled()
    {
        $this->markTestSkipped('Requires https://github.com/Icinga/ipl-validator/pull/8');

        StaticTranslator::$instance = new NoopTranslator();
        $radio = new RadioElement('test', [
            'label'             => 'Test',
            'options'           => [
                'foo' => 'Foo',
                'bar' => 'Bar',
                'yes' => 'Yes'
            ],
            'value'             => 'bar',
            'disabledOptions'    => ['yes']
        ]);

        $this->assertTrue($radio->isValid());

        $radio->setValue('yes');
        $this->assertFalse($radio->isValid());

        $radio->setValue('bar');
        $this->assertTrue($radio->isValid());

        $radio->disabledOptions(['bar', 'yes', 'foo']);
        $this->assertFalse($radio->isValid());
    }
}

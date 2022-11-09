<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Html\FormElement\SelectElement;
use ipl\Html\FormElement\SelectOption;
use ipl\I18n\NoopTranslator;
use ipl\I18n\StaticTranslator;
use ipl\Tests\Html\TestCase;
use UnexpectedValueException;

class SelectElementTest extends TestCase
{
    public function testFlatOptions()
    {
        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
            ],
        ]);

        $html = <<<'HTML'
<select name="elname">
    <option value="">Please choose</option>
    <option value="1">The one</option>
    <option value="4">Four</option>
    <option value="5">Hi five</option>
</select>
HTML;

        $this->assertHtml($html, $select);
    }

    public function testOptionValidity()
    {
        StaticTranslator::$instance = new NoopTranslator();
        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'value'   => '3',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
                'sub' => [
                    'Down' => 'Here'
                ]
            ],
        ]);
        $this->assertFalse($select->isValid());
        $select->setValue(3);
        $this->assertFalse($select->isValid());
        $select->setValue(4);
        $this->assertTrue($select->isValid());
        $select->setValue('4');
        $this->assertTrue($select->isValid());
        $select->setValue('Down');
        $this->assertTrue($select->isValid());
        $select->setValue('Here');
        $this->assertFalse($select->isValid());
    }

    public function testSelectingDisabledOptionIsNotPossible()
    {
        StaticTranslator::$instance = new NoopTranslator();
        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'value'   => '4',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
                'sub' => [
                    'Down' => 'Here'
                ]
            ],
        ]);

        $this->assertTrue($select->isValid());
        $select->disableOption(4);
        $this->assertFalse($select->isValid());
    }

    public function testNestedOptions()
    {
        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'options' => [
                null => 'Please choose',
                'Some Options' => [
                    '1'  => 'The one',
                    '4'  => 'Four',
                ],
                'More options' => [
                    '5'  => 'Hi five',
                ]
            ],
        ]);

        $html = <<<'HTML'
<select name="elname">
    <option value="">Please choose</option>
    <optgroup label="Some Options">
        <option value="1">The one</option>
        <option value="4">Four</option>
    </optgroup>
    <optgroup label="More options">
        <option value="5">Hi five</option>
    </optgroup>
 </select>
HTML;

        $this->assertHtml($html, $select);
    }

    public function testDisabledNestedOptions()
    {
        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'options' => [
                null => 'Please choose',
                'Some options' => [
                    '1'  => 'The one',
                    '4'  => 'Four',
                ],
                'More options' => [
                    '5'  => 'Hi five',
                ]
            ],
        ]);

        $select->disableOptions([4, '5']);

        $html = <<<'HTML'
<select name="elname">
    <option value="">Please choose</option>
    <optgroup label="Some options">
        <option value="1">The one</option>
        <option value="4" disabled>Four</option>
    </optgroup>
    <optgroup label="More options">
        <option value="5" disabled>Hi five</option>
    </optgroup>
 </select>
HTML;

        $this->assertHtml($html, $select);
    }

    public function testDeeplyDisabledNestedOptions()
    {
        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'options' => [
                null => 'Please choose',
                'Some options' => [
                    '1'  => 'The one',
                    '4'  => [
                        'Deeper' => [
                            '4x4' => 'Fourfour',
                        ],
                    ],
                ],
                'More options' => [
                    '5'  => 'Hi five',
                ]
            ],
        ]);

        $select->disableOptions([4, '5']);

        $html = <<<'HTML'
<select name="elname">
    <option value="">Please choose</option>
    <optgroup label="Some options">
        <option value="1">The one</option>
        <optgroup label="4">
            <optgroup label="Deeper">
                <option value="4x4">Fourfour</option>
            </optgroup>
        </optgroup>
    </optgroup>
    <optgroup label="More options">
        <option value="5" disabled>Hi five</option>
    </optgroup>
</select>
HTML;

        $this->assertHtml($html, $select);
    }

    public function testDefaultValueIsSelected()
    {
        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'value'   => '1',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
            ]
        ]);

        $html = <<<'HTML'
<select name="elname">
    <option value="">Please choose</option>
    <option selected value="1">The one</option>
    <option value="4">Four</option>
    <option value="5">Hi five</option>
</select>
HTML;

        $this->assertHtml($html, $select);
    }

    public function testSetValueSelectsAnOption()
    {
        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
            ]
        ]);

        $select->setValue('1');

        $html = <<<'HTML'
<select name="elname">
    <option value="">Please choose</option>
    <option selected value="1">The one</option>
    <option value="4">Four</option>
    <option value="5">Hi five</option>
</select>
HTML;

        $this->assertHtml($html, $select);

        $select->setValue('5');

        $html = <<<'HTML'
<select name="elname">
    <option value="">Please choose</option>
    <option value="1">The one</option>
    <option value="4">Four</option>
    <option selected value="5">Hi five</option>
</select>
HTML;

        $this->assertHtml($html, $select);

        $select->setValue(null);

        $html = <<<'HTML'
<select name="elname">
    <option value="">Please choose</option>
    <option value="1">The one</option>
    <option value="4">Four</option>
    <option value="5">Hi five</option>
</select>
HTML;

        $this->assertHtml($html, $select);
    }

    public function testSetArrayAsValueWithoutMultipleAttributeThrowsException()
    {
        $select = new SelectElement('elname', [
            'label'     => 'Customer',
            'options'   => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
            ]
        ]);

        $select->setValue(['1', 5]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Value cannot be an array without setting the `multiple` attribute to `true`');

        $select->render();
    }

    public function testSetNonArrayAsValueWithMultipleAttributeThrowsException()
    {
        $select = new SelectElement('elname', [
            'label'     => 'Customer',
            'multiple'  => true,
            'options'   => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
            ]
        ]);

        $select->setValue(1);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Value must be an array when the `multiple` attribute is set to `true`'
        );

        $select->render();
    }

    public function testSetArrayAsValueWithMultipleAttributeSetTheOptions()
    {

        $select = new SelectElement('elname', [
            'label'   => 'Customer',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
            ]
        ]);

        $select->setAttribute('multiple', true);

        $select->setValue(['1']);

        $html = <<<'HTML'
<select name="elname[]" multiple>
    <option value="">Please choose</option>
    <option selected value="1">The one</option>
    <option value="4">Four</option>
    <option value="5">Hi five</option>
</select>
HTML;

        $this->assertHtml($html, $select);

        $select->setValue(['5', 4, 6]);

        $html = <<<'HTML'
<select name="elname[]" multiple>
    <option value="">Please choose</option>
    <option value="1">The one</option>
    <option selected value="4">Four</option>
    <option selected value="5">Hi five</option>
</select>
HTML;

        $this->assertHtml($html, $select);

        $select->setValue(null);

        $html = <<<'HTML'
<select name="elname[]" multiple>
    <option value="">Please choose</option>
    <option value="1">The one</option>
    <option value="4">Four</option>
    <option value="5">Hi five</option>
</select>
HTML;

        $this->assertHtml($html, $select);
    }

    public function testLabelCanBeChanged()
    {
        $option = new SelectOption('value', 'Original label');
        $option->setLabel('New label');
        $this->assertHtml('<option value="value">New label</option>', $option);
    }

    public function testRendersCheckMultipleAttribute()
    {
        $select = new SelectElement('test', ['multiple' => true]);

        $html = <<<'HTML'
<select name="test[]" multiple="multiple">
</select>
HTML;

        $this->assertHtml($html, $select);
    }

    public function testSetMultipleAttribute()
    {
        $select = new SelectElement('test');

        $select->setAttribute('multiple', true);

        $html = <<<'HTML'
<select name="test[]" multiple="multiple">
</select>
HTML;

        $this->assertHtml($html, $select);
        $this->assertTrue($select->isMultiple());

        $select->setAttribute('multiple', false);

        $html = <<<'HTML'
<select name="test">
</select>
HTML;

        $this->assertHtml($html, $select);
        $this->assertFalse($select->isMultiple());
    }

    public function testGetValueReturnsAnArrayWhenMultipleAttributeIsSet()
    {
        $select = new SelectElement('test');

        $this->assertNull($select->getValue());

        $select->setAttribute('multiple', true);
        $this->assertSame([], $select->getValue());
    }
}

<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Html\FormElement\MultiselectElement;
use ipl\I18n\NoopTranslator;
use ipl\I18n\StaticTranslator;
use ipl\Tests\Html\TestCase;

class MultiselectElementTest extends TestCase
{
    public function testFlatOptions()
    {
        $select = new MultiselectElement('elname', [
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five'
            ],
        ]);

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<option value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );
    }

    public function testRequiredAttribute()
    {
        $select = new MultiselectElement('elname', [
            'required' => true,
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five'
            ],
        ]);

        $this->assertHtml(
            '<select name="elname[]" multiple required>'
            . '<option value="">Please choose</option>'
            . '<option value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );
    }

    public function testOptionValidity()
    {
        StaticTranslator::$instance = new NoopTranslator();
        $select = new MultiselectElement('elname', [
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
        $select->setValue('sub');
        $this->assertFalse($select->isValid());

        $select->setValue(['1', 4, 'Down']);
        $this->assertTrue($select->isValid());

        $select->disableOption(4);
        $select->setValue([ 4, '5']);
        $this->assertFalse($select->isValid());
    }

    public function testSelectingMultipleOptions()
    {
        $select = new MultiselectElement('elname', [
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

        $this->assertEquals($select->getValue(), [4]);
        $select->setValue([1, '5', 'Down']);
        $this->assertEquals($select->getValue(), [1, '5', 'Down']);
    }

    public function testSelectingDisabledOptionIsNotPossible()
    {
        StaticTranslator::$instance = new NoopTranslator();
        $select = new MultiselectElement('elname', [
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

    public function testSelectingMultipleDisabledOptionsIsNotPossible()
    {
        StaticTranslator::$instance = new NoopTranslator();
        $select = new MultiselectElement('elname', [
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
        $select->disableOptions([4, 5]);
        $select->setValue(['4', 5, 'Down']);
        $this->assertFalse($select->isValid());
    }

    public function testNestedOptions()
    {
        $select = new MultiselectElement('elname', [
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

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<optgroup label="Some Options">'
            . '<option value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '</optgroup>'
            . '<optgroup label="More options">'
            . '<option value="5">Hi five</option>'
            . '</optgroup>'
            . '</select>',
            $select
        );
    }

    public function testDisabledNestedOptions()
    {
        $select = new MultiselectElement('elname', [
            'options' => [
                null => 'Please choose',
                'Some options' => [
                    '1'  => 'The one',
                    '4'  => 'Four'
                ],
                'More options' => [
                    '5'  => 'Hi five'
                ]
            ],
        ]);

        $select->disableOptions([4, '5']);

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<optgroup label="Some options">'
            . '<option value="1">The one</option>'
            . '<option value="4" disabled>Four</option>'
            . '</optgroup>'
            . '<optgroup label="More options">'
            . '<option value="5" disabled>Hi five</option>'
            . '</optgroup>'
            . '</select>',
            $select
        );
    }

    public function testDeeplyDisabledNestedOptions()
    {
        $select = new MultiselectElement('elname', [
            'options' => [
                null => 'Please choose',
                'Some options' => [
                    '1'  => 'The one',
                    '4'  => [
                        'Deeper' => [
                            '4x4' => 'Fourfour'
                        ],
                    ],
                ],
                'More options' => [
                    '5'  => 'Hi five'
                ]
            ],
        ]);

        $select->disableOptions([4, '5']);

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<optgroup label="Some options">'
            . '<option value="1">The one</option>'
            . '<optgroup label="4">'
            . '<optgroup label="Deeper">'
            . '<option value="4x4">Fourfour</option>'
            . '</optgroup>'
            . '</optgroup>'
            . '</optgroup>'
            . '<optgroup label="More options">'
            . '<option value="5" disabled>Hi five</option>'
            . '</optgroup>'
            . '</select>',
            $select
        );
    }

    public function testDefaultValueIsSelected()
    {
        $select = new MultiselectElement('elname', [
            'value'   => '1',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five'
            ]
        ]);

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<option selected value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );
    }

    public function testMultiDefaultValuesAreSelected()
    {
        $select = new MultiselectElement('elname', [
            'value'   => ['1', '5'],
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five'
            ]
        ]);

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<option selected value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option selected value="5">Hi five</option>'
            . '</select>',
            $select
        );
    }

    public function testSetValueSelectsAnOption()
    {
        $select = new MultiselectElement('elname', [
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five'
            ]
        ]);

        $select->setValue('1');

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<option selected value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );

        $select->setValue('5');

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<option value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option selected value="5">Hi five</option>'
            . '</select>',
            $select
        );

        $select->setValue(null);

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<option value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );
    }

    public function testSetValueSelectsMultipleOptions()
    {
        $select = new MultiselectElement('elname', [
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five'
            ]
        ]);

        $select->setValue(['1', 4]);

        $this->assertHtml(
            '<select name="elname[]" multiple>'
            . '<option value="">Please choose</option>'
            . '<option selected value="1">The one</option>'
            . '<option selected value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );
    }
}

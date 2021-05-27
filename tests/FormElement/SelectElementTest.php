<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Html\FormElement\SelectElement;
use ipl\Tests\Html\TestCase;

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

        $this->assertHtml(
            '<select name="elname">'
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

        $this->assertHtml(
            '<select name="elname">'
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

        $this->assertHtml(
            '<select name="elname">'
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

        $this->assertHtml(
            '<select name="elname">'
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

        $this->assertHtml(
            '<select name="elname" value="1">'
            . '<option value="">Please choose</option>'
            . '<option selected value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );
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

        $this->assertHtml(
            '<select name="elname" value="1">'
            . '<option value="">Please choose</option>'
            . '<option selected value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );

        $select->setValue('5');

        $this->assertHtml(
            '<select name="elname" value="5">'
            . '<option value="">Please choose</option>'
            . '<option value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option selected value="5">Hi five</option>'
            . '</select>',
            $select
        );

        $select->setValue(null);

        $this->assertHtml(
            '<select name="elname">'
            . '<option value="">Please choose</option>'
            . '<option value="1">The one</option>'
            . '<option value="4">Four</option>'
            . '<option value="5">Hi five</option>'
            . '</select>',
            $select
        );
    }
}

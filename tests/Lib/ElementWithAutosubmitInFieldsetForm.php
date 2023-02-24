<?php

namespace ipl\Tests\Html\Lib;

use ipl\Html\Form;
use ipl\Html\FormElement\FieldsetElement;

class ElementWithAutosubmitInFieldsetForm extends Form
{
    protected function assemble()
    {
        $foo1 = (new FieldsetElement('foo1', [
            'label' => 'foo1'
        ]));

        $this->addElement($foo1);

        $foo1->addElement(
            'select',
            'select',
            [
                'label'     => 'Select',
                'class'     => 'autosubmit',
                'options'   => [
                    'option1' => 'option1',
                    'option2' => 'option2'
                ]
            ]
        );

        $foo2 = (new FieldsetElement('foo2', [
            'label' => 'foo2'
        ]));

        $this->addElement($foo2);

        $foo2->addElement(
            'password',
            'password',
            [
                'label' => 'Password'
            ]
        );
    }
}

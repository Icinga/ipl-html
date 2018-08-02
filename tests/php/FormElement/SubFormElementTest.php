<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Html\Form;
use ipl\Html\FormElement\SubFormElement;
use ipl\Tests\Html\TestCase;

class SubFormElementTest extends TestCase
{
    public function testSubFormValueHandling()
    {
        $form = new Form();
        $form->addElement('text', 'first_name', [
            'value' => 'Otto'
        ]);

        $sub = new SubFormElement('owner');
        $form->addElement($sub);
        $sub->addElement('text', 'last_name', [
            'label' => 'Last name',
            'value' => 'Doe',
        ])->addElement('text', 'first_name', [
            'label' => 'First name',
            'value' => 'John',
        ])->setSeparator("\n");

        $form->addElement('subForm', 'consumer', [
            'value' => [
                'first_name' => 'Jane',
                'last_name'  => 'Doe',
            ]
        ]);
        $this->assertEquals([
            'first_name' => 'Otto',
            'owner' => [
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ],
            'consumer' => []
        ], $form->getValues());

        $form->populate([
            'owner' => [
                'first_name' => 'Jane',
                'address'    => 'Nowhere'
            ]
        ]);

        if ($sub->getValue('first_name') === 'Jane') {
            $sub->addElement('text', 'address');
            $form->getElement('consumer')->addElement('text', 'first_name');
        }

        $this->assertEquals([
            'first_name' => 'Otto',
            'owner' => [
                'first_name' => 'Jane',
                'last_name'  => 'Doe',
                'address'    => 'Nowhere',
            ],
            'consumer' => [
                'first_name' => 'Jane'
            ]
        ], $form->getValues());
    }
}

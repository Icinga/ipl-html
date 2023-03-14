<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Html\Form;
use ipl\Html\FormElement\SubmitButtonElement;
use ipl\Tests\Html\TestCase;

class SubmitButtonElementTest extends TestCase
{
    public function testSubmitButtonIsOnlyPressedIfPopulated()
    {
        $button = new SubmitButtonElement('test', [
            'label' => 'Test'
        ]);

        $this->assertFalse($button->hasBeenPressed(), 'Submit buttons seem to be always pressed');

        $form = new Form();
        $form->addElement('submitButton', 'test', [
            'label' => 'Test'
        ]);
        $form->addElement('submitButton', 'test2', [
            'label' => 'Test 2'
        ]);
        $form->populate(['test2' => 'y']);

        $this->assertFalse(
            $form->getElement('test')->hasBeenPressed(),
            'A submit button is pressed even if another one is'
        );
    }
}

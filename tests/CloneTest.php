<?php

namespace ipl\Tests\Html;

use ipl\Html\Attribute;
use ipl\Html\Attributes;
use ipl\Html\Form;
use ipl\Html\FormElement\SelectElement;
use ipl\Html\FormElement\SubmitElement;

class CloneTest extends TestCase
{
    public function testCloningSubmitElement(): void
    {
        $originalButton = new SubmitElement('exampleButton', ['class' => 'class01']);

        $clonesButton = clone $originalButton;
        $clonesButton->getAttributes()->setAttribute(Attribute::create('class', 'class02'));

        $this->assertNotSame(
            $originalButton->getAttributes()->get('class')->getValue(),
            $clonesButton->getAttributes()->get('class')->getValue()
        );
        $this->assertNotSame($originalButton->render(), $clonesButton->render());
    }

    public function testCloningSelectElement()
    {
        $original = new SelectElement('test_select', [
            'label'   => 'Test Select',
            'options' => [
                1 => 'One',
                2 => 'Two'
            ]
        ]);
        $clone = clone $original;

        $clone->getAttributes()->setAttribute(Attribute::create('class', 'class02'));

        $this->assertNotSame($original->render(), $clone->render());
    }

    public function testCloningForm(): void
    {
        $original = new Form();
        $original->addElement('input', 'test_input', [
            'label' => 'Test Input'
        ]);

        $clone = clone $original;
        $clone->addElement('submit', 'test_submit', [
            'label' => 'Test Submit'
        ]);
        $clone->getAttributes()->setAttribute(Attribute::create('class', 'class02'));

        $this->assertNotSame($original->render(), $clone->render());
    }

    public function testCloningAttributes(): void
    {
        $original = Attributes::create([Attribute::create('class', 'class01')]);

        $clone = clone $original;
        $clone->setAttribute(Attribute::create('class', 'class02'));

        $this->assertNotSame($original, $clone);
    }
}

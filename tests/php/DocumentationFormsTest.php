<?php

namespace ipl\Tests\Html;

use ipl\Html\Form;
use ipl\Html\FormElement\TextElement;

class DocumentationFormsTest extends TestCase
{
    public function testFirstForm()
    {
        $form = new Form();
        $form->setAction('/your/url');
        $form->addElement('text', 'name', ['label' => 'Your name']);
        $this->assertRendersHtml(
            '<form action="/your/url"><input name="name" type="text" /></form>',
            $form
        );
    }

    public function testSelectElement()
    {
        $form = new Form();
        $form->addElement('select', 'customer', [
            'label'   => 'Customer',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
            ],
        ]);

        $form->getElement('customer')
            ->addAttributes([
                'class' => ['important', 'customer'],
                'id'    => 'customer-field'
            ])->setSeparator("\n");

        $this->assertEquals(
            '<select name="customer" class="important customer" id="customer-field">' . "\n"
            . '<option value="" selected>Please choose</option>' . "\n"
            . '<option value="1">The one</option>' . "\n"
            . '<option value="4">Four</option>' . "\n"
            . '<option value="5">Hi five</option>' . "\n"
            . '</select>',
            $form->getElement('customer')->render()
        );
    }

    public function testSetValues()
    {
        $form = new Form();
        $form->setAction('/your/url');
        $form->addElement('text', 'name', ['label' => 'Your name']);
        $form->addElement('select', 'customer', [
            'label'   => 'Customer',
            'options' => [
                null => 'Please choose',
                '1'  => 'The one',
                '4'  => 'Four',
                '5'  => 'Hi five',
            ],
        ]);

        $form->populate([
            'name'     => 'John Doe',
            'customer' => '5'
        ]);
        $form->getElement('customer')->setValue('4');

        $this->assertEquals([
            'name'     => 'John Doe',
            'customer' => '4'
        ], $form->getValues());
    }

    public function testManuallyCreateTextElement()
    {
        $form = new Form();
        $nameElement = new TextElement('name', ['class' => 'important']);
        $form->addElement($nameElement);
    }
}

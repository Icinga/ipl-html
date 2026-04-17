<?php

namespace ipl\Tests\Html;

use ipl\Html\Form;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\Test\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class FormTest extends TestCase
{
    public function testIsEmptyValue()
    {
        $this->assertTrue(Form::isEmptyValue(''), "`''` is not empty");
        $this->assertTrue(Form::isEmptyValue(null), '`null` is not empty');
        $this->assertTrue(Form::isEmptyValue([]), '`[]` is not empty');
        $this->assertTrue(Form::isEmptyValue('  '), '`  ` is not empty');

        $this->assertFalse(Form::isEmptyValue(0), '`0` is empty');
        $this->assertFalse(Form::isEmptyValue('0'), "`'0'` is empty");
        $this->assertFalse(Form::isEmptyValue(false), '`false` is empty');

        $this->assertFalse(Form::isEmptyValue('foo'), "`'foo' is empty");
        $this->assertFalse(Form::isEmptyValue(1), '`1` is empty');
        $this->assertFalse(Form::isEmptyValue(['']), "`['']` is empty");
        $this->assertFalse(Form::isEmptyValue([0]), '`[0]` is empty');
    }

    public function testOnRequestIsTriggeredIfNotSent(): void
    {
        $form = (new class extends Form {
            protected function assemble()
            {
                $this->add('bogus');
            }
        })->on(Form::ON_REQUEST, function ($req, Form $form) {
            $this->assertFalse($form->hasBeenSent(), 'ON_REQUEST is triggered for sent forms');
            $this->assertEmpty($form->getContent(), 'Form has been assembled before ON_REQUEST');
        });

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->exactly(2))->method('getMethod')->willReturn('GET');

        $form->handleRequest($request);

        $request2 = $this->createStub(ServerRequestInterface::class);
        $request2->method('getMethod')->willReturn('POST');
        $request2->method('getParsedBody')->willReturn([]);
        $request2->method('getUploadedFiles')->willReturn([]);

        $form->handleRequest($request2);
    }

    public function testValidatePartialOnlyValidatesFieldsetChildrenWithAValue(): void
    {
        $fieldset = (new FieldsetElement('set'))
            ->addElement('text', 'filled', ['required' => true])
            ->addElement('text', 'empty', ['required' => true]);
        $fieldset->getElement('filled')->setValue('value');

        $form = (new Form())
            ->addElement($fieldset)
            ->validatePartial();

        $this->assertTrue(
            $form->getElement('set')->getElement('filled')->isValid(),
            'Fieldset child with a value is not validated during partial validation'
        );
        $this->assertEmpty(
            $form->getElement('set')->getElement('empty')->getMessages(),
            'Empty fieldset child produces a required error during partial validation'
        );
    }

    public function testValidatePartialRecursesIntoNestedFieldsets(): void
    {
        $innerFieldset = (new FieldsetElement('inner'))
            ->addElement('text', 'deep', ['required' => true]);
        $innerFieldset->getElement('deep')->setValue('value');

        $outerFieldset = (new FieldsetElement('outer'))
            ->addElement($innerFieldset);

        $form = (new Form())
            ->addElement($outerFieldset)
            ->validatePartial();

        $this->assertTrue(
            $form->getElement('outer')->getElement('inner')->getElement('deep')->isValid(),
            'Nested fieldset child with a value is not validated during partial validation'
        );
    }
}

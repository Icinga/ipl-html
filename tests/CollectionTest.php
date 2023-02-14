<?php

namespace ipl\Tests\Html;

use InvalidArgumentException;
use ipl\Html\Form;
use ipl\Html\FormElement\Collection;
use ipl\Html\FormElement\InputElement;
use ipl\Html\FormElement\SelectElement;
use ipl\Html\FormElement\SubmitButtonElement;
use ipl\Tests\Html\TestDummy\SimpleFormElementDecorator;

class CollectionTest extends TestCase
{
    /** @var string */
    private $label;

    /** @var Form */
    private $form;

    public function setup(): void
    {
        parent::setup();

        $this->label = "Test Collection Label";
        $this->form = (new Form())->setDefaultElementDecorator(new SimpleFormElementDecorator());
    }

    public function testCanBeConstructed()
    {
        $collection = new Collection('testCollection');
        $this->assertInstanceOf(Collection::class, $collection);
    }

    public function testSetLabel()
    {
        $collection = new Collection('testCollection');

        $this->assertSame($collection, $collection->setLabel($this->label));
        $this->assertSame($this->label, $collection->getLabel());

        $this->form->addHtml($collection);

        $expected = <<<'HTML'
<form method="POST">
  <fieldset class="collection" name="testCollection">
    <fieldset class="form-element-collection" name="testCollection[0]"/>
  </fieldset>
</form>
HTML;
        $this->assertHtml($expected, $this->form);
    }

    public function testNoAddTriggerProvided()
    {
        $this->expectException(InvalidArgumentException::class);

        $collection = new Collection('testCollection');
        $collection->onAssembleGroup(function ($group, $addElement, $removeElement) {
            // Throws Exception, because $addElement is null.
            $group->addElement($addElement);
        });

        $collection->render();
    }

    public function testNoRemoveTriggerProvided()
    {
        $this->expectException(InvalidArgumentException::class);

        $collection = new Collection('testCollection');
        $collection->onAssembleGroup(function ($group, $addElement, $removeElement) {
            // Throws Exception, because $removeElement is null.
            $group->addElement($removeElement);
        });

        $collection->render();
    }

    public function testAddTrigger()
    {
        $collection = new Collection('testCollection');
        $collection
            ->setAddElement('select', 'add_element', [
                'required' => false,
                'label'    => 'Add Trigger',
                'options'  => [null => 'Please choose', 'first' => 'First Option'],
                'class'    => 'autosubmit'
            ])
            ->onAssembleGroup(function ($group, $addElement, $removeElement) {
                $group
                    ->addElement($addElement)
                    ->addElement('input', 'test_input', [
                        'label' => 'Test Input'
                    ]);
            });

        $this->form->addHtml($collection);

        $expected = <<<'HTML'
<form method="POST">
  <fieldset class="collection" name="testCollection">
    <fieldset class="form-element-collection" name="testCollection[0]">
      <select class="autosubmit" name="testCollection[0][add_element]">
        <option selected="selected" value="">Please choose</option>
        <option value="first">First Option</option>
      </select>
      <input name="testCollection[0][test_input]"/>
    </fieldset>
  </fieldset>
</form>
HTML;

        $this->assertHtml($expected, $this->form);
    }

    public function testRemoveTrigger()
    {
        $collection = new Collection('testCollection');
        $collection
            ->setRemoveElement('submitButton', 'remove_trigger', [
                'label' => 'Remove Trigger',
            ])
            ->onAssembleGroup(function ($group, $addElement, $removeElement) {
                $group->addElement('input', 'test_input', [
                    'label' => 'Test Input'
                ]);

                $group->addElement($removeElement);
            });

        $this->form->addHtml($collection);

        $expected = <<<'HTML'
<form method="POST">
  <fieldset class="collection" name="testCollection">
    <fieldset class="form-element-collection" name="testCollection[0]">
      <input name="testCollection[0][test_input]"/>
      <button name="testCollection[0][remove_trigger]" type="submit" value="y">Remove Trigger</button>
    </fieldset>
  </fieldset>
</form>
HTML;

        $this->assertHtml($expected, $this->form);
    }

    public function testFullCollection()
    {
        $collection = (new Collection('testCollection'))
            ->setAddElement('select', 'add_element', [
                'required' => false,
                'label'    => 'Add Trigger',
                'options'  => [null => 'Please choose', 'first' => 'First Option'],
                'class'    => 'autosubmit'
            ])
            ->setRemoveElement('submitButton', 'remove_trigger', [
                'label' => 'Remove Trigger',
                'value' => 'Remove Trigger'
            ]);

        $collection->onAssembleGroup(function ($group, $addElement, $removeElement) {
            $group->addElement($addElement);

            $group->addElement('input', 'test_input', [
                'label' => 'Test Input'
            ]);
            $group->addElement('input', 'test_select', [
                'label' => 'Test Select'
            ]);

            $group->addElement($removeElement);
        });

        $this->form->addHtml($collection);

        $expected = <<<'HTML'
<form method="POST">
  <fieldset class="collection" name="testCollection">
    <fieldset class="form-element-collection" name="testCollection[0]">
      <select class="autosubmit" name="testCollection[0][add_element]">
        <option selected="selected" value="">Please choose</option>
        <option value="first">First Option</option>
      </select>
      <input name="testCollection[0][test_input]"/>
      <input name="testCollection[0][test_select]"/>
      <button name="testCollection[0][remove_trigger]" type="submit" value="Remove Trigger">Remove Trigger</button>
    </fieldset>
  </fieldset>
</form>
HTML;

        $this->assertHtml($expected, $this->form);
    }

    public function testMultipleCollections()
    {
        $collection = (new Collection('testCollection'))
            ->setAddElement('select', 'add_element', [
                'required' => false,
                'label'    => 'Add Trigger',
                'options'  => [null => 'Please choose', 'first' => 'First Option']
            ]);

        $collection->onAssembleGroup(function ($group, $addElement, $removeElement) {
            $group->addElement($addElement);

            $inner = (new Collection('innerCollection'))
                ->setLabel('Inner Collection')
                ->setAddElement(new SubmitButtonElement('inner_add_trigger', [
                    'label' => 'Inner Add Trigger'
                ]));

            $inner->onAssembleGroup(function ($innerGroup, $innerAddElement, $innerRemoveElement) {
                $innerGroup->addElement($innerAddElement);
                $innerGroup->addElement('input', 'test_input');
            });

            $group->addElement($inner);
            $group->addElement('input', 'test_input');
        });

        $this->form->addHtml($collection);

        $expected = <<<'HTML'
<form method="POST">
  <fieldset class="collection" name="testCollection">
    <fieldset class="form-element-collection" name="testCollection[0]">
      <select name="testCollection[0][add_element]">
        <option selected="selected" value="">Please choose</option>
        <option value="first">First Option</option>
      </select>
      <fieldset class="collection" name="testCollection[0][innerCollection]">
        <fieldset class="form-element-collection" name="testCollection[0][innerCollection][0]">
          <button name="testCollection[0][innerCollection][0][inner_add_trigger]" type="submit" value="y">
            Inner Add Trigger
          </button>
          <input name="testCollection[0][innerCollection][0][test_input]"/>
        </fieldset>
      </fieldset>
      <input name="testCollection[0][test_input]"/>
    </fieldset>
  </fieldset>
</form>
HTML;

        $this->assertHtml($expected, $this->form);
    }

    public function testPopulatingCollection(): void
    {
        $collection = (new Collection('testCollection'))
            ->setAddElement('select', 'test_select1', [
                'options' => [
                    'key1' => 'value1',
                    'key2' => 'value2'
                ]
            ]);

        $collection->onAssembleGroup(function ($group, $addElement) {
            $group->addElement(new InputElement('test_input'));
            $group->addElement($addElement);
            $group->addElement(new SelectElement('test_select2', [
                'options' => [
                    'key3' => 'value3',
                    'key4' => 'value4'
                ]
            ]));
            $group->addElement('select', 'test_select3', [
                'options' => [
                    'key5' => 'value5',
                    'key6' => 'value6'
                ]
            ]);
        });

        $this->form
            ->registerElement($collection)
            ->addHtml($collection)
            ->populate([
                'testCollection' => [
                    [
                        'test_input'   => 'test_value',
                        'test_select1' => '',
                        'test_select2' => 'key4',
                        'test_select3' => 'key6'
                    ]
                ]
            ]);

        $expected = <<<'HTML'
<form method="POST">
  <fieldset class="collection" name="testCollection">
    <fieldset class="form-element-collection" name="testCollection[0]">
      <input name="testCollection[0][test_input]" value="test_value"/>
      <select name="testCollection[0][test_select1]">
        <option value="key1">value1</option>
        <option value="key2">value2</option>
      </select>
      <select name="testCollection[0][test_select2]">
        <option value="key3">value3</option>
        <option value="key4" selected="selected">value4</option>
      </select>
      <select name="testCollection[0][test_select3]">
        <option value="key5">value5</option>
        <option selected="selected" value="key6">value6</option>
      </select>
    </fieldset>
  </fieldset>
</form>
HTML;

        $this->assertHtml($expected, $this->form);
    }

    public function testCollidingElementNames(): void
    {
        $firstCollection = (new Collection('first_collection'))
            ->setAddElement('select', 'add_element', ['options' => ['key1' => 'value1', 'key2' => 'value2']]);
        $secondCollection = (new Collection('second_collection'))
            ->setAddElement('select', 'add_element', ['options' => ['key1' => 'value1', 'key2' => 'value2']]);

        $firstCollection->onAssembleGroup(function ($group, $addElement) {
            $group->addElement($addElement);
        });

        $secondCollection->onAssembleGroup(function ($group, $addElement) {
            $group->addElement($addElement);
        });

        $this->form
            ->registerElement($firstCollection)
            ->addHtml($firstCollection)
            ->registerElement($secondCollection)
            ->addHtml($secondCollection)
            ->addElement(new SubmitButtonElement('add_element'))
            ->populate([
                'first_collection'  => [
                    [
                        'add_element' => 'key1'
                    ]
                ],
                'second_collection' => [
                    [
                        'add_element' => 'key2'
                    ],
                    [
                        'add_element' => 'key1'
                    ]
                ]
            ]);

        $expected = <<<'HTML'
    <form method="POST">
      <fieldset class="collection" name="first_collection">
        <fieldset class="form-element-collection" name="first_collection[0]">
          <select name="first_collection[0][add_element]">
            <option value="key1" selected="selected">value1</option>
            <option value="key2">value2</option>
          </select>
        </fieldset>
        <fieldset class="form-element-collection" name="first_collection[1]">
          <select name="first_collection[1][add_element]">
            <option value="key1">value1</option>
            <option value="key2">value2</option>
          </select>
        </fieldset>
      </fieldset>
      <fieldset class="collection" name="second_collection">
        <fieldset class="form-element-collection" name="second_collection[0]">
          <select name="second_collection[0][add_element]">
            <option value="key1">value1</option>
            <option value="key2" selected="selected">value2</option>
          </select>
        </fieldset>
        <fieldset class="form-element-collection" name="second_collection[1]">
          <select name="second_collection[1][add_element]">
            <option value="key1" selected="selected">value1</option>
            <option value="key2">value2</option>
          </select>
        </fieldset>
        <fieldset class="form-element-collection" name="second_collection[2]">
          <select name="second_collection[2][add_element]">
            <option value="key1">value1</option>
            <option value="key2">value2</option>
          </select>
        </fieldset>
      </fieldset>
      <div class="simple-decorator">
        <button name="add_element" type="submit" value="y"/>
      </div>
    </form>
HTML;

        $this->assertHtml($expected, $this->form);
    }
}

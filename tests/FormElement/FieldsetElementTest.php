<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Html\Form;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Tests\Html\TestCase;
use ipl\Tests\Html\TestDummy\SimpleFormElementDecorator;
use LogicException;

class FieldsetElementTest extends TestCase
{
    private const SELECT_OPTIONS_TO_TEST = [
        null  => 'Nothing',
        1     => 'One',
        'two' => 2,
    ];

    public function testElementLoading(): void
    {
        $form = (new Form())
            ->addElement('fieldset', 'test_fieldset');

        $this->assertInstanceOf(FieldsetElement::class, $form->getElement('test_fieldset'));
    }

    public function testGetValueWithDefaultWithoutNameThrowsException()
    {
        $this->expectException(LogicException::class);

        (new FieldsetElement('test_fieldset'))->getValue(null, 'default');
    }

    public function testOneDimension(): void
    {
        $fieldset = (new FieldsetElement('test_fieldset'))
            ->addElement('select', 'test_select', ['options' => self::SELECT_OPTIONS_TO_TEST])
            ->populate(['test_select' => 1]);

        $expected = <<<'HTML'
<fieldset name="test_fieldset">
  <select name="test_fieldset[test_select]">
    <option value="">Nothing</option>
    <option selected="selected" value="1">One</option>
    <option value="two">2</option>
  </select>
</fieldset>
HTML;

        $this->assertHtml($expected, $fieldset);
    }

    public function testTwoDimensions(): void
    {
        $inner = (new FieldsetElement('inner'))
            ->addElement('select', 'test_select', ['options' => self::SELECT_OPTIONS_TO_TEST]);
        $outer = (new FieldsetElement('outer'))
            ->addElement($inner)
            ->populate([
                'inner' => ['test_select' => 'two']
            ]);

        $expected = <<<'HTML'
<fieldset name="outer">
  <fieldset name="outer[inner]">
    <select name="outer[inner][test_select]">
      <option value="">Nothing</option>
      <option value="1">One</option>
      <option selected="selected" value="two">2</option>
    </select>
  </fieldset>
</fieldset>
HTML;

        $this->assertHtml($expected, $outer);
    }

    public function testMultipleDimensions(): void
    {
        $outer = new FieldsetElement('outer');

        foreach (static::SELECT_OPTIONS_TO_TEST as $key => $value) {
            $inner = (new FieldsetElement($key))
                ->addElement('select', 'test_select', [
                    'value'   => $value,
                    'options' => self::SELECT_OPTIONS_TO_TEST
                ]);
            $outer->addElement($inner);
        }

        $expected = <<<'HTML'
<fieldset name="outer">
  <fieldset name="outer[]">
    <select name="outer[][test_select]">
      <option value="">Nothing</option>
      <option value="1">One</option>
      <option value="two">2</option>
    </select>
  </fieldset>
  <fieldset name="outer[1]">
    <select name="outer[1][test_select]">
      <option value="">Nothing</option>
      <option value="1">One</option>
      <option value="two">2</option>
    </select>
  </fieldset>
  <fieldset name="outer[two]">
    <select name="outer[two][test_select]">
      <option value="">Nothing</option>
      <option value="1">One</option>
      <option value="two">2</option>
    </select>
  </fieldset>
</fieldset>
HTML;

        $this->assertHtml($expected, $outer);
    }

    public function testOriginalElementNames(): void
    {
        $inner = (new FieldsetElement('inner'))
            ->addElement('select', 'test_select');
        $outer = (new FieldsetElement('outer'))
            ->addElement($inner);

        $this->assertSame(
            'test_select',
            $outer->getElement('inner')->getElement('test_select')->getName()
        );
    }

    public function testDecoratorPropagation(): void
    {
        // Order is important because addElement() calls decorate(). Could be fixed.
        $fieldset = (new FieldsetElement('test_fieldset'));
        (new Form())
            ->setDefaultElementDecorator(new SimpleFormElementDecorator())
            ->addElement($fieldset);
        $fieldset->addElement('select', 'test_select');

        $expected = <<<'HTML'
<div class="simple-decorator">
  <fieldset name="test_fieldset">
    <div class="simple-decorator">
      <select name="test_fieldset[test_select]"></select>
    </div>
  </fieldset>
</div>
HTML;

        $this->assertHtml($expected, $fieldset);
    }
}

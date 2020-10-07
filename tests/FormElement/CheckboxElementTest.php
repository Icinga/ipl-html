<?php

namespace ipl\Tests\Html;

use ipl\Html\FormElement\CheckboxElement;

class CheckboxElementTest extends TestCase
{
    public function testRendersCheckedValueAsValueAttribute()
    {
        $checkbox = new CheckboxElement('test');

        $this->assertHtml('<input type="checkbox" name="test" value="y">', $checkbox);
    }

    public function testIsCheckedReturnsFalseByDefault()
    {
        $checkbox = new CheckboxElement('test');

        $this->assertFalse($checkbox->isChecked());
    }

    public function testIsCheckedReturnsFalseIfValueDoesNotMatchCheckedValue()
    {
        $checkbox = new CheckboxElement('test');

        $checkbox->setValue('noop');
        $this->assertFalse($checkbox->isChecked());
    }

    public function testIsCheckedReturnsTrueIfValueMatchesCheckedValue()
    {
        $checkbox = new CheckboxElement('test');

        $checkbox->setValue($checkbox->getCheckedValue());
        $this->assertTrue($checkbox->isChecked());
    }

    public function testRendersCheckedAttributeIfIsChecked()
    {
        $checkbox = new CheckboxElement('test');

        $checkbox->setChecked(true);
        $this->assertHtml('<input checked="checked" type="checkbox" name="test" value="y">', $checkbox);
    }

    public function testSetCheckedValue()
    {
        $checkbox = new CheckboxElement('test');
        $checkedValue = 'checked';

        $checkbox->setCheckedValue($checkedValue);
        $this->assertSame($checkedValue, $checkbox->getCheckedValue());
        $this->assertHtml('<input type="checkbox" name="test" value="checked">', $checkbox);

        $this->assertFalse($checkbox->isChecked());
        $checkbox->setValue($checkedValue);
        $this->assertTrue($checkbox->isChecked());
        $this->assertHtml('<input checked="checked" type="checkbox" name="test" value="checked">', $checkbox);
    }
}

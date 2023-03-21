<?php

namespace ipl\Tests\Html;

use ipl\Html\Form;

class FormTest extends TestCase
{
    public function testIsEmptyValue()
    {
        $this->assertTrue(Form::isEmptyValue(''), "`''` is not empty");
        $this->assertTrue(Form::isEmptyValue(null), '`null` is not empty');
        $this->assertTrue(Form::isEmptyValue([]), '`[]` is not empty');

        $this->assertFalse(Form::isEmptyValue(0), '`0` is empty');
        $this->assertFalse(Form::isEmptyValue('0'), "`'0'` is empty");
        $this->assertFalse(Form::isEmptyValue(false), '`false` is empty');

        $this->assertFalse(Form::isEmptyValue('foo'), "`'foo' is empty");
        $this->assertFalse(Form::isEmptyValue(1), '`1` is empty');
        $this->assertFalse(Form::isEmptyValue(['']), "`['']` is empty");
        $this->assertFalse(Form::isEmptyValue([0]), '`[0]` is empty');
    }
}

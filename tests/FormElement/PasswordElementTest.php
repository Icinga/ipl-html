<?php

namespace ipl\Tests\Html;

use ipl\Html\Form;
use ipl\Html\FormElement\PasswordElement;
use ipl\Tests\Html\TestDummy\ObscurePassword;

class PasswordElementTest extends TestCase
{
    public function testPasswordValueRenderedObscured()
    {
        $form = (new Form())
            ->addElement('password', 'password')
            ->populate(['password' => 'secret']);

        $html = <<<'HTML'
<form method="POST">
  <input type="password" name="password" value="%s">
</form>
HTML;
        $this->assertHtml(sprintf($html, ObscurePassword::get()), $form);
    }

    public function testGetValueReturnsNullIfNoPasswordSet()
    {
        $password = new PasswordElement('password');
        $this->assertNull($password->getValue());
        $this->assertFalse($password->hasValue());
    }

    public function testGetValueReturnsPassword()
    {
        $form = (new Form())
            ->addElement('password', 'password')
            ->populate(['password' => 'secret']);

        $password = $form->getElement('password');
        $this->assertEquals($password->getValue(), 'secret');
        $this->assertTrue($password->hasValue());
    }

    public function testGetValueReturnsNewPassword()
    {
        $form = (new Form())
            ->addElement('password', 'password')
            ->populate(['password' => 'secret'])
            ->populate(['password' => 'topsecret']);

        $password = $form->getElement('password');
        $this->assertEquals($password->getValue(), 'topsecret');
        $this->assertTrue($password->hasValue());
    }

    public function testGetValueReturnsNullIfPasswordReset()
    {
        $form = (new Form())
            ->addElement('password', 'password')
            ->populate(['password' => 'secret'])
            ->populate(['password' => '']);

        $password = $form->getElement('password');
        $this->assertNull($password->getValue());
        $this->assertFalse($password->hasValue());
    }
}

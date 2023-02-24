<?php

namespace ipl\Tests\Html;

use ipl\Html\Form;
use ipl\Html\FormElement\PasswordElement;
use ipl\Tests\Html\Lib\ElementWithAutosubmitInFieldsetForm;
use ipl\Tests\Html\TestDummy\ObscurePassword;

class PasswordElementTest extends TestCase
{
    public function testPasswordValueRenderedObscured()
    {
        $form = (new Form())
            ->populate(['password' => 'secret'])
            ->addElement('password', 'password');

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
            ->populate(['password' => 'secret'])
            ->addElement('password', 'password');

        $password = $form->getElement('password');
        $this->assertEquals($password->getValue(), 'secret');
        $this->assertTrue($password->hasValue());
    }

    public function testGetValueReturnsNewPassword()
    {
        $form = (new Form())
            ->populate(['password' => 'secret'])
            ->populate(['password' => 'topsecret'])
            ->addElement('password', 'password');

        $password = $form->getElement('password');
        $this->assertEquals($password->getValue(), 'topsecret');
        $this->assertTrue($password->hasValue());
    }

    public function testGetValueReturnsNullIfPasswordReset()
    {
        $form = (new Form())
            ->populate(['password' => 'secret'])
            ->populate(['password' => ''])
            ->addElement('password', 'password');

        $password = $form->getElement('password');
        $this->assertNull($password->getValue());
        $this->assertFalse($password->hasValue());
    }

    public function testGetValueReturnsNullIfNotChanged()
    {
        $form = (new Form())
            ->populate(['password' => ObscurePassword::get()])
            ->addElement('password', 'password');

        $password = $form->getElement('password');
        $this->assertNull($password->getValue());
    }

    /**
     * Represents a form that requires a password to be set as confirmation.
     * If another element is invalid, the password element should have no
     * value, as otherwise the user thinks the password is still set, although
     * it's the obscured password.
     *
     * @return void
     */
    public function testObscuredValueNotVisibleAfterFormValidationFailed()
    {
        $form = (new Form())
            ->populate(['password' => 'secret'])
            ->addElement('password', 'password')
            ->addElement('text', 'username', ['required' => true]);

        $this->assertFalse($form->isValid());

        $html = <<<'HTML'
<form method="POST">
  <input type="password" name="password">
  <input name="username" required type="text"/>
</form>
HTML;
        $this->assertHtml($html, $form);
    }

    /**
     * Represents a controller action that populates saved data and
     * allows the user to change it. If the password isn't changed,
     * the saved password must be preserved.
     *
     * @return void
     */
    public function testOriginalPasswordMustBePreserved()
    {
        $form = (new Form());

        // The action populates always first
        $form->populate(['password' => 'secret']);

        // handleRequest() then another time
        $form->populate(['password' => ObscurePassword::get()]);

        // assemble() then registers the element
        $form->addElement('password', 'password');

        $password = $form->getElement('password');
        $this->assertEquals('secret', $password->getValue());
    }

    /**
     * Represents a controller action that populates saved data and
     * allows the user to change it. If the password isn't changed,
     * the password must persist when the value of an element
     * with autosubmit class is changed.
     *
     * @return void
     */
    public function testOriginalPasswordMustPersistOnAutoSubmit()
    {
        $form = (new ElementWithAutosubmitInFieldsetForm());

        // The action populates always first
        $form->populate([
            'foo1' => ['select' => 'option1'],
            'foo2' => ['password' => 'secret']
        ]);

        // handleRequest() then another time
        $form->populate([
            'foo1' => ['select' => 'option2'],
            'foo2' => ['password' => ObscurePassword::get()]
        ]);

        $form->ensureAssembled();

        $password = $form->getElement('foo2')->getValue('password');
        $this->assertEquals('secret', $password);
    }
}

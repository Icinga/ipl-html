<?php

namespace ipl\Tests\Html;

use ipl\Html\Form;

class FormTest extends TestCase
{
    /** @var Form */
    private $form;

    protected function setUp(): void
    {
        $this->form = new Form();
    }

    public function testSubmitButtonPrefixApplied(): void
    {
        $submitButton = $this->form->createElement('submit', 'submitCreate');
        $this->form->registerElement($submitButton);

        $this->form->addElement('submit', 'submitDelete');
        $this->form->addHtml($submitButton);

        $this->form->setSubmitButton($submitButton);

        $expected = <<<'HTML'
    <form method="POST">
      <input name="submit_pre" style="border: 0;height: 0;margin: 0;padding: 0;visibility: hidden;width: 0;position: absolute" type="submit" value="submit_pre"/>
      <input name="submitDelete" type="submit" value="submitDelete"/>
      <input name="submitCreate" type="submit" value="submitCreate"/>
    </form>
HTML;

        $this->assertHtml($expected, $this->form);
    }

    public function testSubmitButtonPrefixOmitted(): void
    {
        $submitButton = $this->form->createElement('submit', 'submitCreate');
        $this->form->registerElement($submitButton);

        $this->form->addElement('submit', 'submitDelete');
        $this->form->addHtml($submitButton);

        $expected = <<<'HTML'
    <form method="POST">
      <input name="submitDelete" type="submit" value="submitDelete"/>
      <input name="submitCreate" type="submit" value="submitCreate"/>
    </form>
HTML;

        $this->assertHtml($expected, $this->form);
    }

    public function testPrefixSubmitButtonAddedOnlyOnce(): void
    {
        $submitButton = $this->form->createElement('submit', 'submitCreate');
        $this->form->registerElement($submitButton);

        $this->form->addElement('submit', 'submitDelete');
        $this->form->addHtml($submitButton);

        $this->form->setSubmitButton($submitButton);

        $expected = <<<'HTML'
    <form method="POST">
      <input name="submit_pre" style="border: 0;height: 0;margin: 0;padding: 0;visibility: hidden;width: 0;position: absolute" type="submit" value="submit_pre"/>
      <input name="submitDelete" type="submit" value="submitDelete"/>
      <input name="submitCreate" type="submit" value="submitCreate"/>
    </form>
HTML;

        $this->form->render();
        $this->form->render();

        $this->assertHtml($expected, $this->form);
    }
}

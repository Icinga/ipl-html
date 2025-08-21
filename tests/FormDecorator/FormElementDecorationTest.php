<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Form;
use ipl\Html\FormElement\TextElement;
use ipl\Tests\Html\TestCase;
use ipl\Tests\Html\TestDummy\SimpleFormElementDecorator;

class FormElementDecorationTest extends TestCase
{
    public function createForm(): Form
    {
        return (new Form())->addElementDecoratorLoaderPaths([[__NAMESPACE__, 'Decorator']]);
    }
    public function testDefaultElementDecorators(): void
    {
        $form = $this->createForm()
            ->setDefaultElementDecorators(['Test'])
            ->addElement('text', 'element-1');

        $html = <<<'HTML'
<form method="POST">
  <div class="test-decorator">
    <input type="text" name="element-1">
  </div>
</form>
HTML;

        $this->assertHtml($html, $form);
    }

    public function testLegacyDefaultElementDecoratorHasPriority(): void
    {
        $form = $this->createForm()
            ->setDefaultElementDecorator(new SimpleFormElementDecorator())
            ->setDefaultElementDecorators(['Test']) // no effect
            ->addElement('text', 'element-1');

        $html = <<<'HTML'
<form method="POST">
  <div class="simple-decorator">
    <input type="text" name="element-1">
  </div>
</form>
HTML;

        $this->assertHtml($html, $form);
    }

    public function testExplicitDecoratorsHavePriorityOverLegacyDefaultElementDecorator(): void
    {
        $form = $this->createForm()
            ->setDefaultElementDecorator(new SimpleFormElementDecorator())
            ->addElement('text', 'element-1', ['decorators' => ['Test']]);

        $html = <<<'HTML'
<form method="POST">
  <div class="test-decorator">
    <input type="text" name="element-1">
  </div>
</form>
HTML;

        $this->assertHtml($html, $form);
    }

    public function testDefaultElementDecoratorsAreOnlyAppliedWhenElementIsCreatedUsingCreateElementMethod(): void
    {
        $form = $this->createForm()
            ->setDefaultElementDecorators(['Test'])
            ->addElement(new TextElement('element-1'));

        $html = <<<'HTML'
<form method="POST">
    <input type="text" name="element-1">
</form>
HTML;

        $this->assertHtml($html, $form);
    }

    public function testFieldsetInheritsDefaultDecoratorsFromTheForm(): void
    {
        $form = $this->createForm()->setDefaultElementDecorators(['Test']);
        $fieldset = $form->createElement('fieldset', 'fieldset-1');
        $fieldset->addElement('text', 'element-1');

        $form->addElement($fieldset);

        $html = <<<'HTML'
<form method="POST">
  <div class="test-decorator">
    <fieldset name="fieldset-1">
      <div class="test-decorator">
        <input type="text" name="fieldset-1[element-1]">
      </div>
    </fieldset>
  </div>
</form>
HTML;

        $this->assertHtml($html, $form);
    }

    public function testExplicitDecoratorsOnFieldsetDoNotAffectTheDefaultDecoratorsForItsElements(): void
    {
        $form = $this->createForm()->setDefaultElementDecorators(['Test']);
        $fieldset = $form->createElement('fieldset', 'fieldset-1', ['decorators' => ['TestWithOptions']]);

        $fieldset->addElement('text', 'element-1');

        $form->addElement($fieldset);

        $html = <<<'HTML'
<form method="POST">
  <div class="test-with-options-decorator">
    <fieldset name="fieldset-1">
      <div class="test-decorator">
        <input type="text" name="fieldset-1[element-1]">
      </div>
    </fieldset>
  </div>
</form>
HTML;

        $this->assertHtml($html, $form);
    }
}

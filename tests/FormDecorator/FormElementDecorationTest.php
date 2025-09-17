<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Form;
use ipl\Html\FormElement\TextElement;
use ipl\Tests\Html\TestCase;
use ipl\Tests\Html\TestDummy\SimpleFormElementDecorator;

class FormElementDecorationTest extends TestCase
{
    protected Form $form;

    public function setUp(): void
    {
        $this->form = (new Form())->addElementDecoratorLoaderPaths([[__NAMESPACE__, 'Decorator']]);
    }

    public function testRenderFormWithoutDefaultDecorators(): void
    {
        $html = <<<'HTML'
<form method="POST">
    <input type="text" name="element-1">
</form>
HTML;

        $this->assertHtml($html, $this->form->addElement('text', 'element-1'));
    }

    public function testRenderFormWithDefaultDecoratorsWhichDoesNotContainADecoratorThatRendersTheElementItself(): void
    {
        $this->form
            ->setDefaultElementDecorators(['Test'])
            ->addElement('text', 'element-1');

        $html = <<<'HTML'
<form method="POST">
  <div class="test-decorator"></div>
  <input type="text" name="element-1">
</form>
HTML;

        $this->assertHtml($html, $this->form);
    }

    public function testRenderFormWithDefaultDecoratorsWhichContainADecoratorThatRendersTheElementItself(): void
    {
        $this->form
            ->setDefaultElementDecorators(['TestRenderElement', 'Test'])
            ->addElement('text', 'element-1');

        $html = <<<'HTML'
<form method="POST">
  <div class="test-decorator">
    <input type="text" name="element-1">
  </div>
</form>
HTML;

        $this->assertHtml($html, $this->form);
    }

    public function testLegacyDefaultElementDecoratorHasPriority(): void
    {
        $this->form
            ->setDefaultElementDecorator(new SimpleFormElementDecorator())
            ->setDefaultElementDecorators(['TestRenderElement', 'Test']) // no effect
            ->addElement('text', 'element-1');

        $html = <<<'HTML'
<form method="POST">
  <div class="simple-decorator">
    <input type="text" name="element-1">
  </div>
</form>
HTML;

        $this->assertHtml($html, $this->form);
    }

    public function testExplicitDecoratorsHavePriorityOverLegacyDefaultElementDecorator(): void
    {
        $this->form
            ->setDefaultElementDecorator(new SimpleFormElementDecorator())
            ->addElement('text', 'element-1', ['decorators' => ['TestRenderElement', 'Test']]);

        $html = <<<'HTML'
<form method="POST">
  <div class="test-decorator">
    <input type="text" name="element-1">
  </div>
</form>
HTML;

        $this->assertHtml($html, $this->form);
    }

    public function testDefaultElementDecoratorsAreOnlyAppliedWhenElementIsCreatedUsingCreateElementMethod(): void
    {
        $this->form
            ->setDefaultElementDecorators(['Test'])
            ->addElement(new TextElement('element-1'));

        $html = <<<'HTML'
<form method="POST">
    <input type="text" name="element-1">
</form>
HTML;

        $this->assertHtml($html, $this->form);
    }

    public function testFieldsetInheritsDefaultDecoratorsFromTheForm(): void
    {
        $this->form->setDefaultElementDecorators(['TestRenderElement', 'Test']);
        $fieldset = $this->form->createElement('fieldset', 'fieldset-1');
        $fieldset->addElement('text', 'element-1');

        $this->form->addElement($fieldset);

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

        $this->assertHtml($html, $this->form);
    }

    public function testExplicitDecoratorsOnFieldsetDoNotAffectTheDefaultDecoratorsForItsElements(): void
    {
        $this->form->setDefaultElementDecorators(['TestRenderElement', 'Test']);
        $fieldset = $this->form->createElement(
            'fieldset',
            'fieldset-1',
            [
                'decorators' => ['TestRenderElement', 'TestWithOptions']
            ]
        );

        $fieldset->addElement('text', 'element-1');

        $this->form->addElement($fieldset);

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

        $this->assertHtml($html, $this->form);
    }
}

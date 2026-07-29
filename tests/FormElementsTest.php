<?php

namespace ipl\Tests\Html;

use ipl\Html\Form;
use ipl\Html\FormElement\FormElements;
use ipl\Html\Test\TestCase;

class FormElementsTest extends TestCase
{
    public function testGetPopulatedValuesReturnsEmptyArrayForUnknownName(): void
    {
        $form = new Form();

        $this->assertSame([], $form->getPopulatedValues('unknown'));
    }

    public function testGetPopulatedValuesReturnsSingleItemArrayAfterOnePopulate(): void
    {
        $form = new Form();
        $form->populate(['name' => 'value']);

        $this->assertSame(['value'], $form->getPopulatedValues('name'));
    }

    public function testGetPopulatedValuesReturnsFullChainAfterMultiplePopulates(): void
    {
        $form = new Form();
        $form->populate(['name' => 'first']);
        $form->populate(['name' => 'second']);
        $form->populate(['name' => 'third']);

        $this->assertSame(['first', 'second', 'third'], $form->getPopulatedValues('name'));
    }

    public function testFormWithCustomElementLoader()
    {
        $form = $this->getFormWithCustomElementAndDecoratorLoader();

        $form->addElement('text', 'test');

        $this->assertTrue($form->hasEnsureDefaultElementLoaderRegisteredRun());
    }

    public function testFormWithCustomDecoratorLoader()
    {
        $form = $this->getFormWithCustomElementAndDecoratorLoader();

        $form->setDefaultElementDecorator('div');

        $this->assertTrue($form->hasEnsureDefaultElementDecoratorLoaderRegisteredRun());
    }

    private function getFormWithCustomElementAndDecoratorLoader()
    {
        return new class extends Form {
            use FormElements;

            /** @var bool */
            private $ensureDefaultElementLoaderRegisteredRun = false;

            /** @var bool */
            private $ensureDefaultElementDecoratorLoaderRegisteredRun = false;

            public function hasEnsureDefaultElementLoaderRegisteredRun(): bool
            {
                return $this->ensureDefaultElementLoaderRegisteredRun;
            }

            public function hasEnsureDefaultElementDecoratorLoaderRegisteredRun(): bool
            {
                return $this->ensureDefaultElementDecoratorLoaderRegisteredRun;
            }

            protected function ensureDefaultElementLoaderRegistered()
            {
                if (! $this->defaultElementLoaderRegistered) {
                    $this->ensureDefaultElementLoaderRegisteredRun = true;

                    parent::ensureDefaultElementLoaderRegistered();
                }

                return $this;
            }

            protected function ensureDefaultElementDecoratorLoaderRegistered()
            {
                if (! $this->defaultElementDecoratorLoaderRegistered) {
                    $this->ensureDefaultElementDecoratorLoaderRegisteredRun = true;

                    parent::ensureDefaultElementDecoratorLoaderRegistered();
                }

                return $this;
            }
        };
    }
}

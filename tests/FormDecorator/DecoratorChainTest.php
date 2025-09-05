<?php

namespace ipl\Tests\Html\FormDecorator;

use Exception;
use InvalidArgumentException;
use ipl\Html\FormDecorator\DecoratorChain;
use ipl\Html\FormElement\TextElement;
use ipl\Html\HtmlString;
use ipl\Tests\Html\TestCase;

class DecoratorChainTest extends TestCase
{
    public function createDecoratorChain(): DecoratorChain
    {
        return (new DecoratorChain())->addDecoratorLoader(__NAMESPACE__, 'Decorator');
    }

    public function testMethodAddDecorator(): void
    {
        $chain = $this->createDecoratorChain()->addDecorator('Test');

        $this->assertCount(1, $chain->getDecorators());
        $this->assertInstanceOf(TestDecorator::class, $chain->getDecorators()[0]);
    }

    /**
     * @depends testMethodAddDecorator
     */
    public function testMethodClearDecorators(): void
    {
         $chain = $this->createDecoratorChain()
            ->addDecorator('Test')
            ->addDecorator('TestWithOptions');

            $this->assertCount(2, $chain->getDecorators());
            $this->assertCount(0, $chain->clearDecorators()->getDecorators());
    }

    /**
     * @depends testMethodClearDecorators
     */
    public function testMethodHasDecorators(): void
    {
        $chain = $this->createDecoratorChain()
            ->addDecorator('Test')
            ->addDecorator('TestWithOptions');

        $this->assertTrue($chain->hasDecorators());
        $this->assertFalse($chain->clearDecorators()->hasDecorators());
    }

    /**
     * @depends testMethodClearDecorators
     */
    public function testMethodAddDecoratorWithAllowedParamFormats(): void
    {
        $chain = $this->createDecoratorChain()->addDecorator('TestWithOptions');
        $this->assertCount(1, $chain->getDecorators());
        $decorator = $chain->getDecorators()[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->assertSame([], $decorator->getOptions());
        $options = ['optionKey1' => 'optionValue1'];
        $this->assertSame($options, $decorator->setOptions($options)->getOptions());
        $chain->clearDecorators();

        $chain = $this->createDecoratorChain()->addDecorator(new TestWithOptionsDecorator());
        $this->assertCount(1, $chain->getDecorators());
        $decorator = $chain->getDecorators()[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->assertSame([], $decorator->getOptions());
        $options = ['optionKey1' => 'optionValue1'];
        $this->assertSame($options, $decorator->setOptions($options)->getOptions());
        $chain->clearDecorators();

        $chain = $this->createDecoratorChain()->addDecorator('TestWithOptions', $options);
        $this->assertCount(1, $chain->getDecorators());
        $decorator = $chain->getDecorators()[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->assertSame([], $decorator->getOptions());
        $options = ['options' => ['optionKey1' => 'optionValue1'], 'optionKey2' => 'optionValue2'];
        $this->assertSame($options, $decorator->setOptions($options)->getOptions());
        $chain->clearDecorators();
    }

    public function testMethodAddDecoratorThrowsExceptionWhenUnknownDecoratorNameIsPassed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Can't load decorator 'invalid'. decorator unknown");

        $this->createDecoratorChain()->addDecorator('invalid');
    }

    public function testMethodAddDecoratorThrowsExceptionWhenDecoratorInstanceWithOptionsIsPassed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No options are allowed with parameter 1 of type Decorator');
        $this->createDecoratorChain()->addDecorator(new TestDecorator(), ['optionKey1' => 'optionValue1']);
    }

    public function testMethodAddDecoratorsWithValidArrayAsParam(): void
    {
        $decoratorFormats = [
            'TestWithOptions',
            new TestWithOptionsDecorator(),
            'TestWithOptions' => ['optionKey1' => 'optionValue1', 'options' => ['optionKey2' => 'optionValue2']],
            ['name' => 'TestWithOptions', 'options' => ['optionKey2' => 'optionValue2']]
        ];

        $chain = $this->createDecoratorChain()->addDecorators($decoratorFormats);

        $this->assertCount(count($decoratorFormats), $chain->getDecorators());
    }

    /**
     * @depends testMethodAddDecoratorsWithValidArrayAsParam
     */
    public function testMethodAddDecoratorsWithDecoratorChainAsParam(): void
    {
        $decoratorFormats = [
            'TestWithOptions',
            new TestWithOptionsDecorator(),
            'TestWithOptions' => ['optionKey1' => 'optionValue1'],
            ['name' => 'TestWithOptions', 'options' => ['optionKey2' => 'optionValue2']]
        ];

        $chain = $this->createDecoratorChain()->addDecorators($decoratorFormats);

        $this->assertCount(
            count($decoratorFormats),
            $this->createDecoratorChain()->addDecorators($chain)->getDecorators()
        );
    }

    public function testMethodGetDecoratorsReturnsTheSameOrderAsTheArrayPassedToAddDecorators(): void
    {
        $chain = $this->createDecoratorChain();

        $decoratorFormats = ['TestWithOptions', 'Test'];
        $decorators = $chain->addDecorators($decoratorFormats)->getDecorators();
        $this->assertCount(count($decoratorFormats), $decorators);
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorators[0]);
        $this->assertInstanceOf(TestDecorator::class, $decorators[1]);

        $decoratorFormats = [5 => 'TestWithOptions', 0 => 'Test'];
        $chain->clearDecorators();
        $decorators = $chain->addDecorators($decoratorFormats)->getDecorators();
        $this->assertCount(count($decoratorFormats), $decorators);
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorators[0]);
        $this->assertInstanceOf(TestDecorator::class, $decorators[1]);

        $decoratorFormats = [
            ['name' => 'TestWithOptions'],
            ['name' => 'Test']
        ];
        $chain->clearDecorators();
        $decorators = $chain->addDecorators($decoratorFormats)->getDecorators();
        $this->assertCount(count($decoratorFormats), $decorators);
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorators[0]);
        $this->assertInstanceOf(TestDecorator::class, $decorators[1]);
    }

    public function testAddDecoratorsThrowsExceptionWhenADecoratorAsNonAssociativeArrayDoesNotDefineNameKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'name' is missing");

        $this->createDecoratorChain()->addDecorators([['TestWithOptions']]);
    }

    public function testAddDecoratorsThrowsExceptionWhenADecoratorAsNonAssociativeArrayDefinesUnknownKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("No other keys except 'name' and 'options' are allowed, got 'unknownKey', '5'");

        $this->createDecoratorChain()
            ->addDecorators([
                ['name' => 'TestWithOptions', 'unknownKey' => 'unknownValue', 5 => 'also unknown'],
            ]);
    }

    public function testAddDecoratorsThrowsExceptionWhenADecoratorAsAssociativeArrayDoesNotDefineOptionsAsArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "The key must be a decorator name and value must be an array of options, got value of type"
            . " 'string' for key 'something'",
        );

        $this->createDecoratorChain()->addDecorators(['something' => 'this is string instead of array']);
    }

    public function testAddDecoratorsThrowsExceptionWhenADecoratorAsAssociativeArrayDoesNotDefineOptionsAsArrayX(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expects variable $decoratorName to be a string or an instance of ipl\Html\Contract\Decorator,'
            . ' got ipl\Html\FormDecorator\DecoratorChain instead'
        );

        $this->createDecoratorChain()->addDecorators([$this->createDecoratorChain()]);
    }

    public function testMethodApplyWithoutADecoratorThatRendersTheElementItself(): void
    {
        $results = $this->createDecoratorChain()
            ->addDecorator('Test')
            ->apply(new TextElement('element-1'));

        $html = <<<'HTML'
<div class="test-decorator"></div>
HTML;
        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testMethodApplyWithADecoratorThatRendersTheElementItself(): void
    {
        $results = $this->createDecoratorChain()
            ->addDecorators(['TestRenderElement', 'Test'])
            ->apply(new TextElement('element-1'));

        $html = <<<'HTML'
<div class="test-decorator">
  <input type="text" name="element-1">
</div>
HTML;
        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testMethodApplySkipADecoratorThatShouldBeSkipped(): void
    {
        $results = $this->createDecoratorChain()
            ->addDecorators(['TestSkipRenderElement', 'TestRenderElement', 'Test'])
            ->apply(new TextElement('element-1'));

        $html = <<<'HTML'
<div class="test-decorator">
  <input type="text" name="element-1">
</div>
HTML;
        $this->assertHtml($html, HtmlString::create($results));
    }

    public function testMethodApplyThrowsExceptionWhenADecoratorShouldBeSkippedButIsAlreadyApplied(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot skip Decorator(s) 'TestRenderElement', Decoration already applied");

        $this->createDecoratorChain()
            ->addDecorators(['TestRenderElement', 'TestSkipRenderElement', 'Test'])
            ->apply(new TextElement('element-1'));
    }
}

<?php

namespace ipl\Tests\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\FormDecoration\DecoratorChain;
use ipl\Html\Contract\FormElementDecoration;
use ipl\Tests\Html\TestCase;

class DecoratorChainTest extends TestCase
{
    protected DecoratorChain $chain;

    public function setUp(): void
    {
        $this->chain = (new DecoratorChain(FormElementDecoration::class))
            ->addDecoratorLoader(__NAMESPACE__, 'Decorator');
    }

    public function testMethodAddDecorator(): void
    {
        $this->chain->addDecorator('Test');
        $decorators = iterator_to_array($this->chain);

        $this->assertNotEmpty($decorators);
        $this->assertInstanceOf(TestDecorator::class, $decorators[0]);;
    }

    /**
     * @depends testMethodAddDecorator
     */
    public function testMethodClearDecorators(): void
    {
         $this->chain
            ->addDecorator('Test')
            ->addDecorator('TestWithOptions');

        $this->assertCount(2, $this->chain);

        $this->chain->clearDecorators();

        $this->assertCount(0, $this->chain);
    }

    /**
     * @depends testMethodClearDecorators
     */
    public function testMethodHasDecorators(): void
    {
        $this->assertFalse($this->chain->hasDecorators());

        $this->chain
            ->addDecorator('Test')
            ->addDecorator('TestWithOptions');

        $this->assertTrue($this->chain->hasDecorators());
        $this->assertFalse($this->chain->clearDecorators()->hasDecorators());
    }

    /**
     * @depends testMethodClearDecorators
     */
    public function testMethodAddDecoratorWithAllowedParamFormats(): void
    {
        $this->chain->addDecorator('TestWithOptions');
        $decorators = iterator_to_array($this->chain);
        $this->assertCount(1, $decorators);
        $decorator = $decorators[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->chain->clearDecorators();

        $this->chain->addDecorator(new TestWithOptionsDecorator());
        $decorators = iterator_to_array($this->chain);
        $this->assertCount(1, $decorators);
        $decorator = $decorators[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->chain->clearDecorators();

        $options = [
            'attrs' => ['setter' => 'setter'], // set via setAttrs(), returned values of getAttrs() contains it
            'not-setter' => 'not-setter' // added as attribute, returned values of getAttrs() does not contain it
        ];
        $this->chain->addDecorator('TestWithOptions', $options);
        $decorators = iterator_to_array($this->chain);
        $this->assertCount(1, $decorators);
        $decorator = $decorators[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->assertSame($options['attrs'], $decorator->getAttrs());
        $this->chain->clearDecorators();
    }

    public function testMethodAddDecoratorThrowsExceptionWhenUnknownDecoratorNameIsPassed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Can't load decorator 'invalid'. decorator unknown");

        $this->chain->addDecorator('invalid');
    }

    public function testMethodAddDecoratorThrowsExceptionWhenDecoratorInstanceWithOptionsIsPassed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No options are allowed with parameter 1 of type Decorator');
        $this->chain->addDecorator(new TestDecorator(), ['optionKey1' => 'optionValue1']);
    }

    public function testMethodAddDecoratorsWithValidArrayAsParam(): void
    {
        $decoratorFormats = [
            'TestWithOptions',
            new TestWithOptionsDecorator(),
            'TestWithOptions' => ['optionKey1' => 'optionValue1', 'options' => ['optionKey2' => 'optionValue2']],
            ['name' => 'TestWithOptions', 'options' => ['optionKey2' => 'optionValue2']]
        ];

        $this->assertCount(count($decoratorFormats), $this->chain->addDecorators($decoratorFormats));
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

        $chain = (new DecoratorChain(FormElementDecoration::class))
            ->addDecoratorLoader(__NAMESPACE__, 'Decorator')
            ->addDecorators($decoratorFormats);

        $this->assertCount(
            count($decoratorFormats),
            $this->chain->addDecorators($chain)
        );
    }

    public function testMethodGetDecoratorsReturnsTheSameOrderAsTheArrayPassedToAddDecorators(): void
    {
        $decoratorFormats = ['TestWithOptions', 'Test'];
        $decorators = iterator_to_array($this->chain->addDecorators($decoratorFormats));
        $this->assertCount(count($decoratorFormats), $decorators);
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorators[0]);
        $this->assertInstanceOf(TestDecorator::class, $decorators[1]);
        $this->chain->clearDecorators();

        $decoratorFormats = [5 => 'TestWithOptions', 0 => 'Test'];
        $decorators = iterator_to_array($this->chain->addDecorators($decoratorFormats));
        $this->assertCount(count($decoratorFormats), $decorators);
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorators[0]);
        $this->assertInstanceOf(TestDecorator::class, $decorators[1]);
        $this->chain->clearDecorators();

        $decoratorFormats = [
            ['name' => 'TestWithOptions'],
            ['name' => 'Test']
        ];
        $decorators = iterator_to_array($this->chain->addDecorators($decoratorFormats));
        $this->assertCount(count($decoratorFormats), $decorators);
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorators[0]);
        $this->assertInstanceOf(TestDecorator::class, $decorators[1]);
    }

    public function testAddDecoratorsThrowsExceptionWhenADecoratorAsNonAssociativeArrayDoesNotDefineNameKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'name' is missing");

        $this->chain->addDecorators([['TestWithOptions']]);
    }

    public function testAddDecoratorsThrowsExceptionWhenADecoratorAsNonAssociativeArrayDefinesUnknownKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("No other keys except 'name' and 'options' are allowed, got 'unknownKey', '5'");

        $this->chain
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

        $this->chain->addDecorators(['something' => 'this is string instead of array']);
    }

    public function testAddDecoratorsThrowsExceptionWhenADecoratorAsAssociativeArrayDoesNotDefineOptionsAsArrayX(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expects array value at position 0 to be a string or an instance of ipl\Html\Contract\FormElementDecoration,'
            . ' got ipl\Html\FormDecoration\DecoratorChain instead'
        );

        $this->chain->addDecorators([$this->chain]);
    }
}

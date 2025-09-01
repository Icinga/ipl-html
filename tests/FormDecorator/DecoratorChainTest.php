<?php

namespace ipl\Tests\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\FormDecorator\DecoratorChain;
use ipl\Html\FormElement\TextElement;
use ipl\Html\HtmlString;
use ipl\Tests\Html\TestCase;

class DecoratorChainTest extends TestCase
{
    protected function createDecoratorChain(): DecoratorChain
    {
        return (new DecoratorChain())->addDecoratorLoader(__NAMESPACE__, 'Decorator');
    }

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
        $this->assertEmpty($decorator->getAttributes()->getAttributes());
        $chain->clearDecorators();

        $chain = $this->createDecoratorChain()->addDecorator(new TestWithOptionsDecorator());
        $this->assertCount(1, $chain->getDecorators());
        $decorator = $chain->getDecorators()[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->assertEmpty($decorator->getAttributes()->getAttributes());
        $chain->clearDecorators();

        $chain = $this->createDecoratorChain()->addDecorator(['TestWithOptions' => ['optionKey1' => 'optionValue1']]);
        $this->assertCount(1, $chain->getDecorators());
        $decorator = $chain->getDecorators()[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->assertNotEmpty($decorator->getAttributes()->getAttributes());
        $this->assertTrue($decorator->getAttributes()->has('optionKey1'));
        $this->assertSame('optionValue1', $decorator->getAttributes()->get('optionKey1')->getValue());
        $chain->clearDecorators();

        $chain = $this->createDecoratorChain()
            ->addDecorator([
                'name' => 'TestWithOptions',
                'optionKey1' => 'optionValue1'
            ]);
        $this->assertCount(1, $chain->getDecorators());
        $decorator = $chain->getDecorators()[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->assertNotEmpty($decorator->getAttributes()->getAttributes());
        $this->assertTrue($decorator->getAttributes()->has('optionKey1'));
        $this->assertSame('optionValue1', $decorator->getAttributes()->get('optionKey1')->getValue());
        $chain->clearDecorators();

        $chain = $this->createDecoratorChain()
            ->addDecorator([
                'name' => 'TestWithOptions',
                'options' => ['optionKey1' => 'optionValue1'],
                'optionKey2' => 'optionValue2'
            ]);
        $this->assertCount(1, $chain->getDecorators());
        $decorator = $chain->getDecorators()[0];
        $this->assertInstanceOf(TestWithOptionsDecorator::class, $decorator);
        $this->assertNotEmpty($decorator->getAttributes()->getAttributes());
        $this->assertTrue($decorator->getAttributes()->has('optionKey1'));
        $this->assertSame('optionValue1', $decorator->getAttributes()->get('optionKey1')->getValue());
        $this->assertTrue($decorator->getAttributes()->has('optionKey2'));
        $this->assertSame('optionValue2', $decorator->getAttributes()->get('optionKey2')->getValue());
        $chain->clearDecorators();
    }

    public function testMethodAddDecoratorWithUnknownDecoratorNameAsString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Can't load decorator 'invalid'. decorator unknown");

        $this->createDecoratorChain()->addDecorator('invalid');
    }

    public function testMethodAddDecoratorWithUnknownDecoratorNameAsArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Can't load decorator 'invalid'. decorator unknown");
        $this->createDecoratorChain()->addDecorator(['invalid']);
    }

    public function testMethodAddDecoratorWithWrongArrayFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Invalid decorator specification: The key must be a decorator name and value"
            . " must be an array of options, got value of type 'string' for key 'something'"
        );
        $this->createDecoratorChain()->addDecorator(['something' => 'invalid']);
    }

    public function testMethodAddDecoratorsWithArrayAsParam(): void
    {
        $decoratorFormats = [
            'TestWithOptions',
            new TestWithOptionsDecorator(),
            'TestWithOptions' => ['optionKey1' => 'optionValue1', 'options' => ['optionKey2' => 'optionValue2']],
           /* ['name' => 'TestWithOptions', 'optionKey1' => 'optionValue1'],
            ['name' => 'TestWithOptions', 'optionKey1' => 'optionValue1', 'options' => ['optionKey2' => 'optionValue2']]*/
        ];

        $chain = $this->createDecoratorChain()->addDecorators($decoratorFormats);

        $this->assertCount(count($decoratorFormats), $chain->getDecorators());
    }

    public function testMethodAddDecoratorsWithChainAsParam(): void
    {
        $decoratorFormats = [
            'TestWithOptions',
            new TestWithOptionsDecorator(),
            ['TestWithOptions' => ['optionKey1' => 'optionValue1']],
            ['name' => 'TestWithOptions', 'optionKey1' => 'optionValue1'],
            ['name' => 'TestWithOptions', 'optionKey1' => 'optionValue1', 'options' => ['optionKey2' => 'optionValue2']]
        ];

        $chain = $this->createDecoratorChain()->addDecorators($decoratorFormats);

        $this->assertCount(
            count($decoratorFormats),
            $this->createDecoratorChain()->addDecorators($chain)->getDecorators()
        );
    }

    public function testMethodApply(): void
    {
        $results = $this->createDecoratorChain()
            ->addDecorator('Test')
            ->apply(new TextElement('element-1'));

        $html = <<<'HTML'
<div class="test-decorator">
  <input type="text" name="element-1">
</div>
HTML;
        $this->assertHtml($html, HtmlString::create($results));
    }
}

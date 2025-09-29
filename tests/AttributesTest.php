<?php

namespace ipl\Tests\Html;

use Exception;
use InvalidArgumentException;
use ipl\Html\Attribute;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\ImmutableAttribute;
use LogicException;
use RuntimeException;
use UnexpectedValueException;

class AttributesTest extends TestCase
{
    /**
     * @depends testGetWithExistingAttribute
     */
    public function testConstructorAcceptsAttributeInstances(): void
    {
        $attrStub1 = $this->createStub(Attribute::class);
        $attrStub1->method('getName')->willReturn('foo');
        $attrStub1->method('getValue')->willReturn('bar');

        $attrStub2 = $this->createStub(Attribute::class);
        $attrStub2->method('getName')->willReturn('baz');
        $attrStub2->method('getValue')->willReturn('qux');

        $attributes = new Attributes([$attrStub1, $attrStub2]);

        $this->assertSame('foo', $attributes->get('foo')->getName());
        $this->assertSame('bar', $attributes->get('foo')->getValue());

        $this->assertSame('baz', $attributes->get('baz')->getName());
        $this->assertSame('qux', $attributes->get('baz')->getValue());
    }

    /**
     * @depends testGetWithExistingAttribute
     */
    public function testConstructorAcceptsAssociativeArrays(): void
    {
        $attributes = new Attributes([
            'foo' => 'bar',
            'baz' => 'qux'
        ]);

        $this->assertSame('bar', $attributes->get('foo')->getValue());
        $this->assertSame('qux', $attributes->get('baz')->getValue());
        $this->assertSame('foo', $attributes->get('foo')->getName());
        $this->assertSame('baz', $attributes->get('baz')->getName());
    }

    /**
     * @depends testGetWithExistingAttribute
     * @todo Not sure of the importance of this format. None of the other methods support this.
     */
    public function testConstructorAcceptsTwoElementTuples(): void
    {
        $attributes = new Attributes([
            ['foo', 'bar'],
            ['baz', 'qux']
        ]);

        $this->assertSame('bar', $attributes->get('foo')->getValue());
        $this->assertSame('qux', $attributes->get('baz')->getValue());
        $this->assertSame('foo', $attributes->get('foo')->getName());
        $this->assertSame('baz', $attributes->get('baz')->getName());
    }

    public function testWantAttributesAcceptsSelf(): void
    {
        $attributes = new Attributes();
        $gotAttributes = $attributes->wantAttributes($attributes);

        $this->assertSame($attributes, $gotAttributes);
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testConstructorAcceptsTwoElementTuples
     * @depends testGetWithExistingAttribute
     */
    public function testWantAttributesAcceptsSupportedArrayFormats(): void
    {
        $associative = Attributes::wantAttributes([
            'foo' => 'bar',
            'baz' => 'qux'
        ]);

        $this->assertSame('foo', $associative->get('foo')->getName());
        $this->assertSame('bar', $associative->get('foo')->getValue());
        $this->assertSame('baz', $associative->get('baz')->getName());
        $this->assertSame('qux', $associative->get('baz')->getValue());

        $twoElementTuples = Attributes::wantAttributes([
            ['foo', 'bar'],
            ['baz', 'qux']
        ]);

        $this->assertSame('foo', $twoElementTuples->get('foo')->getName());
        $this->assertSame('bar', $twoElementTuples->get('foo')->getValue());
        $this->assertSame('baz', $twoElementTuples->get('baz')->getName());
        $this->assertSame('qux', $twoElementTuples->get('baz')->getValue());
    }

    public function testWantAttributesAcceptsNull(): void
    {
        $this->assertInstanceOf(Attributes::class, Attributes::wantAttributes(null));
    }

    /**
     * @depends testWantAttributesAcceptsSelf
     * @depends testWantAttributesAcceptsSupportedArrayFormats
     */
    public function testWantAttributesThrowsOnInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Attributes instance, array or null expected. Got string instead.');

        Attributes::wantAttributes('foo');
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     */
    public function testGetAttributes(): void
    {
        $attributes = new Attributes([
            'foo' => 'bar',
            'baz' => 'qux'
        ]);

        $this->assertCount(2, $attributes->getAttributes());
        $this->assertSame('foo', $attributes->getAttributes()['foo']->getName());
        $this->assertSame('bar', $attributes->getAttributes()['foo']->getValue());
        $this->assertSame('baz', $attributes->getAttributes()['baz']->getName());
        $this->assertSame('qux', $attributes->getAttributes()['baz']->getValue());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testGetWithNonexistentAttribute
     */
    public function testAttributesMerge(): void
    {
        $attributes = new Attributes();
        $sourceAttributes = Attributes::create([
            'foo' => 'bar',
            'bar' => 'foo'
        ]);

        $attributes->merge($sourceAttributes);

        $this->assertSame('foo', $attributes->get('foo')->getName());
        $this->assertSame('bar', $attributes->get('foo')->getValue());
        $this->assertSame('bar', $attributes->get('bar')->getName());
        $this->assertSame('foo', $attributes->get('bar')->getValue());

        $moreAttributes = Attributes::create(['foo' => 'rab']);

        $attributes->merge($moreAttributes);

        $this->assertSame('foo', $attributes->get('foo')->getName());
        $this->assertSame(['bar', 'rab'], $attributes->get('foo')->getValue());
    }

    public function testHas(): void
    {
        $attributes = new Attributes();

        $this->assertFalse($attributes->has('name'));

        $attributes->set('name', 'value');

        $this->assertTrue($attributes->has('name'));
    }

    /**
     * @depends testSetAttribute
     */
    public function testGetWithExistingAttribute(): void
    {
        $attribute = $this->createStub(Attribute::class);
        $attribute->method('getName')->willReturn('name');
        $attribute->method('getValue')->willReturn('value');

        $attributes = new Attributes();
        $attributes->setAttribute($attribute);

        $this->assertSame('value', $attributes->get('name')->getValue());
    }

    public function testGetWithNonexistentAttribute(): void
    {
        $attributes = new Attributes();

        $this->assertNull($attributes->get('unknown')->getValue());

        $attributes
            ->get('name')
            ->setValue('value');

        $this->assertSame(
            'value',
            $attributes->get('name')->getValue()
        );
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testGetWithExistingAttribute
     */
    public function testSetAcceptsSelf(): void
    {
        $attributes = new Attributes(['foo' => 'bar']);

        $newAttributes = new Attributes();
        $newAttributes->set($attributes);

        $this->assertSame('bar', $newAttributes->get('foo')->getValue());
    }

    /**
     * @depends testGetWithExistingAttribute
     */
    public function testSetAcceptsAttributeInstances(): void
    {
        $attrStub = $this->createStub(Attribute::class);
        $attrStub->method('getName')->willReturn('foo');
        $attrStub->method('getValue')->willReturn('bar');

        $attributes = new Attributes();

        $attributes->set($attrStub);

        $this->assertSame('bar', $attributes->get('foo')->getValue());
    }

    /**
     * @depends testGetWithExistingAttribute
     */
    public function testSetAcceptsAssociativeArrays(): void
    {
        $attributes = new Attributes();

        $attributes->set([
            'foo' => 'bar'
        ]);

        $this->assertSame('bar', $attributes->get('foo')->getValue());
    }

    /**
     * @depends testGetWithExistingAttribute
     */
    public function testSetAcceptsNameAndValue(): void
    {
        $attributes = new Attributes();

        $attributes->set('foo', 'bar');

        $this->assertSame('bar', $attributes->get('foo')->getValue());
    }

    /**
     * @depends testGetAttributes
     */
    public function testAddAcceptsNull(): void
    {
        // for whatever reason…

        $attributes = new Attributes();

        $attributes->add(null);

        $this->assertSame(0, count($attributes->getAttributes()));
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testGetWithExistingAttribute
     */
    public function testAddAcceptsSelf(): void
    {
        $attributes = new Attributes(['foo' => 'bar']);

        $attributes->add(new Attributes([
            'foo' => 'baz',
            'bar' => 'qux'
        ]));

        $this->assertSame(['bar', 'baz'], $attributes->get('foo')->getValue());
        $this->assertSame('qux', $attributes->get('bar')->getValue());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testGetWithExistingAttribute
     */
    public function testAddAcceptsAssociativeArrays(): void
    {
        $attributes = new Attributes(['foo' => 'bar']);

        $attributes->add([
            'foo' => 'baz',
            'bar' => 'qux'
        ]);

        $this->assertSame(['bar', 'baz'], $attributes->get('foo')->getValue());
        $this->assertSame('qux', $attributes->get('bar')->getValue());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testGetWithExistingAttribute
     */
    public function testAddAcceptsAttributeInstances(): void
    {
        $attrStub1 = $this->createStub(Attribute::class);
        $attrStub1->method('getName')->willReturn('foo');
        $attrStub1->method('getValue')->willReturn('baz');

        $attrStub2 = $this->createStub(Attribute::class);
        $attrStub2->method('getName')->willReturn('bar');
        $attrStub2->method('getValue')->willReturn('qux');

        $attributes = new Attributes(['foo' => 'bar']);

        $attributes->add($attrStub1);
        $attributes->add($attrStub2);

        $this->assertSame(['bar', 'baz'], $attributes->get('foo')->getValue());
        $this->assertSame('qux', $attributes->get('bar')->getValue());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testGetWithExistingAttribute
     */
    public function testAddAcceptsNameAndValue(): void
    {
        $attributes = new Attributes(['foo' => 'bar']);

        $attributes->add('foo', 'baz');
        $attributes->add('bar', 'qux');

        $this->assertSame(['bar', 'baz'], $attributes->get('foo')->getValue());
        $this->assertSame('qux', $attributes->get('bar')->getValue());
    }

    public function testRemoveReturnsNullIfAttributeDoesNotExist(): void
    {
        $attributes = new Attributes();

        $this->assertNull($attributes->remove('foo'));
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testHas
     */
    public function testRemoveRemovesAllValuesIfNameIsGiven(): void
    {
        $attributes = new Attributes([
            'foo' => ['bar', 'baz']
        ]);

        $this->assertSame(['bar', 'baz'], $attributes->remove('foo')->getValue());
        $this->assertFalse($attributes->has('foo'));
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testGetWithExistingAttribute
     */
    public function testRemoveRemovesValueIfNameAndValueIsGiven(): void
    {
        $attributes = new Attributes([
            'foo' => ['bar', 'baz']
        ]);

        // Honestly, at first I wanted to use assertSame('bar', …), but the returned attribute is not a copy of the
        // original, it is the original. This means removing an entire attribute is not the same as removing a single
        // value. In the first case, remove returns the removed state, and in the second case it returns the attribute's
        // remaining state. Also, why is the remaining state not a reset array? The test only succeeds by asserting
        // that `'baz'` is *contained*. Comparing with `['baz']` is not possible as the index is different.
        $this->assertContains('baz', $attributes->remove('foo', 'bar')->getValue());
    }

    public function testSetAttribute(): void
    {
        $attrStub = $this->createStub(Attribute::class);
        $attrStub->method('getName')->willReturn('foo');
        $attrStub->method('getValue')->willReturn('bar');

        $attributes = new Attributes();
        $attributes->setAttribute($attrStub);

        $this->assertSame('bar', $attributes->get('foo')->getValue());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testGetWithExistingAttribute
     */
    public function testAddAttribute(): void
    {
        $attrStub1 = $this->createStub(Attribute::class);
        $attrStub1->method('getName')->willReturn('foo');
        $attrStub1->method('getValue')->willReturn('baz');

        $attrStub2 = $this->createStub(Attribute::class);
        $attrStub2->method('getName')->willReturn('bar');
        $attrStub2->method('getValue')->willReturn('qux');

        $attributes = new Attributes(['foo' => 'bar']);

        $attributes->addAttribute($attrStub1);
        $attributes->addAttribute($attrStub2);

        $this->assertSame(['bar', 'baz'], $attributes->get('foo')->getValue());
        $this->assertSame('qux', $attributes->get('bar')->getValue());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testAttributesAreRenderedAsHtmlAttributes
     */
    public function testNamesCanHavePrefixes(): void
    {
        $attributes = new Attributes(['foo' => 'bar']);
        $attributes->setPrefix('data-');

        $this->assertSame(' data-foo="bar"', $attributes->render());
    }

    public function testEmptyAttributesAreRenderedAsEmptyString(): void
    {
        $this->assertSame('', (new Attributes())->render());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     */
    public function testAttributesAreRenderedAsHtmlAttributes(): void
    {
        $attributes = new Attributes([
            'foo' => 'bar',
            'baz' => 'qux'
        ]);

        $this->assertSame(' foo="bar" baz="qux"', $attributes->render());
    }

    /**
     * @depends testAttributesAreRenderedAsHtmlAttributes
     * @depends testSetAttribute
     */
    public function testEmptyAttributesAreIgnoredWhenRendering(): void
    {
        $emptyAttribute = $this->createMock(Attribute::class);
        $emptyAttribute->expects($this->any())->method('getName')->willReturn('foo');
        $emptyAttribute->expects($this->any())->method('isEmpty')->willReturn(true);
        $emptyAttribute->expects($this->never())->method('render');

        $filledAttribute = $this->createMock(Attribute::class);
        $filledAttribute->expects($this->any())->method('getName')->willReturn('bar');
        $filledAttribute->expects($this->any())->method('isEmpty')->willReturn(false);
        $filledAttribute->expects($this->once())->method('render')->willReturn('bar="baz"');

        $attributes = new Attributes();
        $attributes->setAttribute($emptyAttribute);
        $attributes->setAttribute($filledAttribute);

        $this->assertSame(' bar="baz"', $attributes->render());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     */
    public function testArrayAccess(): void
    {
        $attributes = new Attributes(['foo' => 'bar']);

        $this->assertTrue(isset($attributes['foo']));
        $this->assertFalse(isset($attributes['bar']));
        $this->assertSame('bar', $attributes['foo']->getValue());
        $this->assertNull($attributes['bar']->getValue());

        $attributes['bar'] = 'baz';
        unset($attributes['foo']);

        $this->assertFalse(isset($attributes['foo']));
        $this->assertSame('baz', $attributes['bar']->getValue());
    }

    /**
     * @depends testConstructorAcceptsAssociativeArrays
     */
    public function testForeach(): void
    {
        $attrs = ['foo' => 'bar', 'baz' => 'qux'];

        $attributes = new Attributes($attrs);

        reset($attrs);

        foreach ($attributes as $attribute) {
            $name = key($attrs);
            $value = current($attrs);

            $this->assertSame($name, $attribute->getName());
            $this->assertSame($value, $attribute->getValue());

            next($attrs);
        }
    }

    /**
     * @depends testHas
     * @depends testGetWithExistingAttribute
     * @depends testConstructorAcceptsAssociativeArrays
     * @depends testSetAcceptsNameAndValue
     * @depends testRemoveRemovesAllValuesIfNameIsGiven
     */
    public function testClone(): void
    {
        $attributes = new Attributes(['foo' => 'bar']);

        $clone = clone $attributes;

        $attributes->set('bar', 'baz');
        $clone->set('foo', 'qux');

        $this->assertFalse($clone->has('bar'));
        $this->assertSame('bar', $attributes->get('foo')->getValue());

        $clone->remove('foo');

        $this->assertTrue($attributes->has('foo'));
    }

    /**
     * @depends testAttributesAreRenderedAsHtmlAttributes
     * @depends testGetWithExistingAttribute
     */
    public function testNativeAttributesAndCallbacks(): void
    {
        $objectOne = new class extends BaseHtmlElement {
            protected $defaultAttributes = ['class' => 'foo'];

            public function getAttr()
            {
                return 'bar';
            }
        };

        $objectOne->getAttributes()->setCallback(
            'class',
            $objectOne->getAttr(...)
        );

        $this->assertEquals(' class="foo bar"', $objectOne->getAttributes()->render());
        $this->assertEquals('foo', $objectOne->getAttributes()->get('class')->getValue());
    }

    /**
     * Merging attributes with callbacks is highly discouraged. Callbacks may hold references to other objects
     * and may cause memory leaks. We cannot prevent passing entire attributes instances around, but we must
     * not provide a native way to merge them.
     *
     * @depends testGetWithNonexistentAttribute
     * @depends testCallReturnsACallbackResult
     */
    public function testAttributesMergeDoesNotMergeCallbacks(): void
    {
        $attributes = new Attributes();
        $sourceAttributes = Attributes::create(['bar' => 'foo'])
            ->setCallback('foo', fn() => 'bar');

        $attributes->merge($sourceAttributes);

        $this->assertSame('foo', $attributes->get('bar')->getValue());
        $this->assertNull($attributes->call('foo')->getValue());
    }

    public function testCallReturnsACallbackResult(): void
    {
        $attributes = (new Attributes())->setCallback(
            'callback',
            fn() => ImmutableAttribute::create('callback', 'value from callback')
        )->setCallback(
            'callback2',
            fn() => 'value from callback2'
        );

        $this->assertSame('value from callback', $attributes->call('callback')->getValue());
        $this->assertSame('value from callback2', $attributes->call('callback2')->getValue());
    }

    public function testCallReturnsAnEmptyAttributeIfNoCallbackIsSetOrReturnsNull(): void
    {
        $attributes = new Attributes();

        $this->assertTrue($attributes->call('callback')->isEmpty());

        $attributes->setCallback('callback2', fn() => null);

        $this->assertTrue($attributes->call('callback2')->isEmpty());
    }

    public function testCallThrowsInCaseCallbackFails(): void
    {
        $attributes = (new Attributes())->setCallback(
            'callback',
            fn() => throw new Exception()
        );

        $this->expectException(RuntimeException::class);
        $attributes->call('callback');
    }

    public function testCallThrowsInCaseResultIsInvalid(): void
    {
        $attributes = (new Attributes())
            ->setCallback('callback', fn() => Attribute::createEmpty('test'));

        $this->expectException(UnexpectedValueException::class);
        $attributes->call('callback');
    }

    /**
     * @depends testCallReturnsACallbackResult
     */
    public function testCallbacksCanBeOverridden(): void
    {
        $attributes = (new Attributes())
            ->setCallback('callback', fn() => 'foo');

        $this->assertSame('foo', $attributes->call('callback')->getValue());

        $attributes->setCallback('callback', fn() => 'bar');

        $this->assertSame('bar', $attributes->call('callback')->getValue());
    }

    public function testRenderHandlesCallbackResultsCorrectly(): void
    {
        $attributes = (new Attributes())
            ->setCallback('callback', fn() => 'foo')
            ->setCallback('callback2', fn() => null);

        $this->assertSame(' callback="foo"', $attributes->render());
    }

    public function testRenderThrowsIfLegacyAndNewAttributeCallbacksConflict(): void
    {
        $attributes = (new Attributes())
            ->setCallback('callback', fn() => 'foo')
            ->registerAttributeCallback('callback', fn() => 'bar');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Cannot use both legacy and new attribute callbacks at the same time. Offending attributes: callback'
        );

        $attributes->render();
    }

    /**
     * @depends testRenderHandlesCallbackResultsCorrectly
     */
    public function testCallbacksAreResetUponClone(): void
    {
        $attributes = (new Attributes())
            ->setCallback('callback', fn() => 'foo')
            ->registerAttributeCallback('callback2', fn() => 'bar');

        $clone = clone $attributes;

        $this->assertSame('', $clone->render());
    }
}

<?php

namespace ipl\Tests\Html;

use ipl\Html\DeferredText;
use Exception;

class DeferredTextTest extends TestCase
{
    public function testCanBeConstructed()
    {
        $object = (object) ['message' => 'Some value'];
        $text = new DeferredText(function () use ($object) {
            return $object->message;
        });
        $object->message = 'Changed idea';

        $this->assertEquals(
            'Changed idea',
            $text->render()
        );
    }

    public function testCanBeInstantiatedStatically()
    {
        $object = (object) ['message' => 'Some value'];
        $text = DeferredText::create(function () use ($object) {
            return $object->message;
        });
        $object->message = 'Changed idea';

        $this->assertEquals(
            'Changed idea',
            $text
        );
    }

    public function testPassesEventualExceptionWhenRendered()
    {
        $this->expectException(\Exception::class);
        $text = new DeferredText(function () {
            throw new Exception('Boom');
        });

        $text->render();
    }

    public function testRendersEventualExceptionMessageWhenCastedToString()
    {
        $text = new DeferredText(function () {
            throw new Exception('Boom');
        });

        // TODO(el): Use assertMatchesRegularExpression instead
        $this->assertRegExp('/Boom.*/', (string) $text);
        $this->assertRegExp('/error/', (string) $text);
    }
}

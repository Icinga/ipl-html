<?php

namespace ipl\Tests\Html;

use ipl\Html\Form;
use Psr\Http\Message\ServerRequestInterface;

class FormTest extends TestCase
{
    public function testIsEmptyValue()
    {
        $this->assertTrue(Form::isEmptyValue(''), "`''` is not empty");
        $this->assertTrue(Form::isEmptyValue(null), '`null` is not empty');
        $this->assertTrue(Form::isEmptyValue([]), '`[]` is not empty');
        $this->assertTrue(Form::isEmptyValue('  '), '`  ` is not empty');

        $this->assertFalse(Form::isEmptyValue(0), '`0` is empty');
        $this->assertFalse(Form::isEmptyValue('0'), "`'0'` is empty");
        $this->assertFalse(Form::isEmptyValue(false), '`false` is empty');

        $this->assertFalse(Form::isEmptyValue('foo'), "`'foo' is empty");
        $this->assertFalse(Form::isEmptyValue(1), '`1` is empty');
        $this->assertFalse(Form::isEmptyValue(['']), "`['']` is empty");
        $this->assertFalse(Form::isEmptyValue([0]), '`[0]` is empty');
    }

    public function testOnRequestIsTriggeredIfNotSent(): void
    {
        $form = (new class extends Form {
            protected function assemble()
            {
                $this->add('bogus');
            }
        })->on(Form::ON_REQUEST, function ($req, Form $form) {
            $this->assertFalse($form->hasBeenSent(), 'ON_REQUEST is triggered for sent forms');
            $this->assertEmpty($form->getContent(), 'Form has been assembled before ON_REQUEST');
        });

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->exactly(2))->method('getMethod')->willReturn('GET');

        $form->handleRequest($request);

        $request2 = $this->createMock(ServerRequestInterface::class);
        $request2->expects($this->any())->method('getMethod')->willReturn('POST');
        $request2->expects($this->once())->method('getParsedBody')->willReturn([]);
        $request2->expects($this->once())->method('getUploadedFiles')->willReturn([]);

        $form->handleRequest($request2);
    }

    public function testEscapeReservedChars(): void
    {
        // Reserved chars '.', ' ', '[' and ']' are escaped
        $this->assertSame(Form::escapeReservedChars('foo.bar'), 'foo' . chr(28) . 'bar');
        $this->assertSame(Form::escapeReservedChars('foo bar'), 'foo' . chr(29) . 'bar');
        $this->assertSame(Form::escapeReservedChars('foo[bar]', true), 'foo' . chr(30) . 'bar' . chr(31));

        // The string remains the same
        $this->assertSame(Form::escapeReservedChars('foo[bar]'), 'foo[bar]');
        $this->assertSame(Form::escapeReservedChars('foo-bar123', true), 'foo-bar123');
    }
}

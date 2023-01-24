<?php

namespace ipl\Tests\Html\FormElement;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\UploadedFile;
use ipl\Html\Form;
use ipl\Html\FormElement\FileElement;
use ipl\Tests\Html\TestCase;

class FileElementTest extends TestCase
{
    public function testElementLoading(): void
    {
        $form = (new Form())
            ->addElement('file', 'test_file');

        $this->assertInstanceOf(FileElement::class, $form->getElement('test_file'));
    }

    public function testRendering()
    {
        $file = new FileElement('test_file', ['accept' => ['image/png', 'image/jpeg']]);

        $this->assertHtml('<input name="test_file" type="file" accept="image/png, image/jpeg">', $file);
    }

    public function testUploadedFiles()
    {
        $fileToUpload = new UploadedFile(
            'test/test.pdf',
            500,
            0,
            'test.pdf',
            'application/pdf'
        );

        $req = (new ServerRequest('POST', ServerRequest::getUriFromGlobals()))
            ->withUploadedFiles(['test_file' => $fileToUpload])
            ->withParsedBody([]);

        $form = (new Form())
            ->addElement('file', 'test_file')
            ->handleRequest($req);

        $this->assertSame($form->getValue('test_file'), $fileToUpload);
    }

    public function testMutipleAttributeAlsoChangesNameAttribute()
    {
        $file = new FileElement('test_file', ['multiple' => true]);

        $this->assertHtml('<input multiple name="test_file[]" type="file">', $file);
        $this->assertSame($file->getName(), 'test_file');
    }

    public function testValueAttributeIsNotRendered()
    {
        $file = new FileElement('test_file');

        $file->setValue('test');
        $this->assertHtml('<input name="test_file" type="file">', $file);
    }
}

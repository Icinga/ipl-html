<?php

namespace ipl\Tests\Html\FormElement;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\UploadedFile;
use ipl\Html\Form;
use ipl\Html\FormElement\FileElement;
use ipl\I18n\NoopTranslator;
use ipl\I18n\StaticTranslator;
use ipl\Tests\Html\Lib\FileElementWithAdjustableConfig;
use ipl\Tests\Html\TestCase;

class FileElementTest extends TestCase
{
    protected function setUp(): void
    {
        StaticTranslator::$instance = new NoopTranslator();
    }

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

    public function testDefaultMaxFileSizeAsBytesIsParsedCorrectly()
    {
        $element = new FileElementWithAdjustableConfig('test');
        $element->setValue(new UploadedFile('test', 500, 0));

        $element::$defaultMaxFileSize = null;
        $element::$uploadMaxFilesize = '500';
        $element::$postMaxSize = '1000'; // Just needs to be bigger than 500

        $this->assertTrue($element->isValid(), implode("\n", $element->getMessages()));
    }

    public function testDefaultMaxFileSizeAsKiloBytesIsParsedCorrectly()
    {
        $element = new FileElementWithAdjustableConfig('test');
        $element->setValue(new UploadedFile('test', 1024, 0));

        $element::$defaultMaxFileSize = null;
        $element::$uploadMaxFilesize = '1K';
        $element::$postMaxSize = '2K'; // Just needs to be bigger than 1K

        $this->assertTrue($element->isValid(), implode("\n", $element->getMessages()));
    }

    public function testDefaultMaxFileSizeAsMegaBytesIsParsedCorrectly()
    {
        $element = new FileElementWithAdjustableConfig('test');
        $element->setValue(new UploadedFile('test', 102400, 0));

        $element::$defaultMaxFileSize = null;
        $element::$uploadMaxFilesize = '1M';
        $element::$postMaxSize = '2M'; // Just needs to be bigger than 1M

        $this->assertTrue($element->isValid(), implode("\n", $element->getMessages()));
    }

    public function testDefaultMaxFileSizeAsGigaBytesIsParsedCorrectly()
    {
        $element = new FileElementWithAdjustableConfig('test');
        $element->setValue(new UploadedFile('test', 1073741824, 0));

        $element::$defaultMaxFileSize = null;
        $element::$uploadMaxFilesize = '1G';
        $element::$postMaxSize = '2G'; // Just needs to be bigger than 1G

        $this->assertTrue($element->isValid(), implode("\n", $element->getMessages()));
    }

    /**
     * @depends testDefaultMaxFileSizeAsKiloBytesIsParsedCorrectly
     */
    public function testUploadMaxFilesizeOverrulesPostMaxSize()
    {
        $element = new FileElementWithAdjustableConfig('test');
        $element->setValue(new UploadedFile('test', 1024, 0));

        $element::$defaultMaxFileSize = null;
        $element::$uploadMaxFilesize = '1K';
        $element::$postMaxSize = '2K'; // Just needs to be bigger than 1K

        $this->assertTrue($element->isValid(), implode("\n", $element->getMessages()));

        // ...if possible

        $element::$defaultMaxFileSize = null;
        $element::$uploadMaxFilesize = '2K';
        $element::$postMaxSize = '1K';

        $this->assertTrue($element->isValid(), implode("\n", $element->getMessages()));

        $element->setValue(new UploadedFile('test', 2048, 0));
        $element::$defaultMaxFileSize = null;

        $this->assertFalse($element->isValid());
    }
}

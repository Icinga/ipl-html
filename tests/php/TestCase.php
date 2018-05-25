<?php

namespace ipl\Tests\Html;

use ipl\Html\ValidHtml;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function assertRendersHtml($html, ValidHtml $element)
    {
        $this->assertXmlStringEqualsXmlString($html, $element->render());
    }
}

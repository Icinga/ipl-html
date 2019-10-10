<?php

namespace ipl\Tests\Html;

use ipl\Html\ValidHtml;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function assertRendersHtml($html, ValidHtml $element)
    {
        $this->assertXmlStringEqualsXmlString($html, $element->render());
    }
}

<?php

namespace ipl\Tests\Html;

use ipl\Html\ValidHtml;
use PHPUnit\Util\Xml;

// phpcs:disable
if (class_exists('PHPUnit_Util_XML')) {
    // Support older PHPUnit versions
    class_alias('PHPUnit_Util_XML', 'PHPUnit\\Util\\Xml');
}
// phpcs:enable

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Assert that HTML is equal
     *
     * @param string    $expectedHtml
     * @param ValidHtml $actual
     */
    protected function assertHtml($expectedHtml, ValidHtml $actual)
    {
        $this->assertEquals(Xml::load($expectedHtml, true), Xml::load($actual->render(), true));
    }

    /**
     * @deprecated Use {@link assertHtml()} instead. assertRendersHtml() suffers from the fact that the HTML being
     * processed must have a root node, e.g. the HTML `<b>foo</b><b>bar</b>` would always fail with "Extra content at
     * the end of the document". {@link assertHtml()} just does the job.
     */
    protected function assertRendersHtml($html, ValidHtml $element)
    {
        $this->assertXmlStringEqualsXmlString($html, $element->render());
    }
}

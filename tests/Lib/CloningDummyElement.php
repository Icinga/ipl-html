<?php

namespace ipl\Tests\Html\Lib;

use Closure;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;

class CloningDummyElement extends BaseHtmlElement
{
    protected $tag = 'p';

    public static function staticMethod(): string
    {
        return 'static_callback';
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        $attributes->registerAttributeCallback('test-instance-scope-noop-inline', function () {
            return 'inline';
        });
        $attributes->registerAttributeCallback('test-instance-noop-attribute', [$this, 'staticMethod']);
        $attributes->registerAttributeCallback(
            'test-closure-static-scope-noop',
            Closure::fromCallable(self::class . '::staticMethod')
        );

        $attributes->registerAttributeCallback(
            'test-closure-instance-scope-noop',
            Closure::fromCallable([$this, 'staticMethod'])
        );
    }
}

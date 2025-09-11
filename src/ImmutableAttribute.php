<?php

namespace ipl\Html;

use ipl\Html\Common\BaseAttribute;

class ImmutableAttribute extends BaseAttribute
{
    public function isImmutable(): true
    {
        return true;
    }
}

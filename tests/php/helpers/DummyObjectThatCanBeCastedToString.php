<?php

class DummyObjectThatCanBeCastedToString
{
    public function __toString()
    {
        return 'Some String <:-)';
    }
}

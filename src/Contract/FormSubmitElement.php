<?php

namespace ipl\Html\Contract;

interface FormSubmitElement
{
    /**
     * Get whether the element has been pressed
     *
     * @return bool
     */
    public function hasBeenPressed();
}

<?php

namespace ipl\Html\FormDecorator;

/**
 * Describes the placement of decorative element
 */
enum Placement
{
    /** Add the element at the end of the existing content */
    case Append;

    /** Add the element at the beginning of the existing content. */
    case Prepend;

    /** Wrap the existing content */
    case Wrap;
}

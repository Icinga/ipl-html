<?php

namespace ipl\Html\FormDecoration;

/**
 * Describes how the content should be transformed
 */
enum Transformation
{
    /** Add the element at the end of the existing content */
    case Append;

    /** Add the element at the beginning of the existing content. */
    case Prepend;

    /** Wrap the existing content */
    case Wrap;
}

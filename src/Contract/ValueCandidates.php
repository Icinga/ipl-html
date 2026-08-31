<?php

namespace ipl\Html\Contract;

/**
 * @deprecated Only {@see \ipl\Html\FormElement\PasswordElement} ever used this even
 * though {@see \ipl\Html\FormElement\BaseFormElement} implements it. No replacement
 * will be provided.
 */
interface ValueCandidates
{
    /**
     * Get value candidates of this element
     *
     * @return array<int, mixed>
     */
    public function getValueCandidates();

    /**
     * Set value candidates of this element
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function setValueCandidates(array $values);
}

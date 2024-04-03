<?php

namespace ipl\Html\Contract;

/** @template TValue of mixed */
interface ValueCandidates
{
    /**
     * Get value candidates of this element
     *
     * @return array<int, TValue>
     */
    public function getValueCandidates();

    /**
     * Set value candidates of this element
     *
     * @param array<int, TValue> $values
     *
     * @return $this
     */
    public function setValueCandidates(array $values);
}

<?php

namespace ipl\Html\FormDecoration;

use ipl\Html\Contract\DecorationResult;
use ipl\Html\Contract\MutableHtml;
use ipl\Html\Contract\Wrappable;
use ipl\Html\Form;
use ipl\Html\HtmlDocument;
use ipl\Html\ValidHtml;

class FormDecorationResult implements DecorationResult
{
    /** @var Form The form being decorated */
    private Form $form;

    /**
     * Create a new FormDecorationResult
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function append(ValidHtml $html): static
    {
        $this->form->addHtml($html);

        return $this;
    }

    public function prepend(ValidHtml $html): static
    {
        $this->form->prependHtml($html);

        return $this;
    }

    public function wrap(MutableHtml $html): static
    {
        if (! $html instanceof Wrappable) {
            // If it's not a wrappable, mimic what wrapping usually means
            $html->addHtml($this->form);
            $html = (new HtmlDocument())->addHtml($html);
        }

        $this->form->addWrapper($html);

        return $this;
    }
}

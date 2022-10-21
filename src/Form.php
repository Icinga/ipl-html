<?php

namespace ipl\Html;

use Exception;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormElement\FormElements;
use ipl\Stdlib\Messages;
use Psr\Http\Message\ServerRequestInterface;

class Form extends BaseHtmlElement
{
    use FormElements {
        FormElements::remove as private removeElement;
    }
    use Messages;

    public const ON_ELEMENT_REGISTERED = 'elementRegistered';
    public const ON_ERROR = 'error';
    public const ON_SUBMIT = 'submit';
    public const ON_SUCCESS = 'success';
    public const ON_VALIDATE = 'validate';

    /** @var string Form submission URL */
    protected $action;

    /** @var string HTTP method to submit the form with */
    protected $method = 'POST';

    /** @var FormSubmitElement Primary submit button */
    protected $submitButton;

    /** @var FormSubmitElement[] Other elements that may submit the form */
    protected $submitElements = [];

    /** @var bool Whether the form is valid */
    protected $isValid;

    /** @var ServerRequestInterface The server request being processed */
    protected $request;

    protected $tag = 'form';

    /**
     * Get the Form submission URL
     *
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the Form submission URL
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the HTTP method to submit the form with
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the HTTP method to submit the form with
     *
     * @param string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Get whether the form has a primary submit button
     *
     * @return bool
     */
    public function hasSubmitButton()
    {
        return $this->submitButton !== null;
    }

    /**
     * Get the primary submit button
     *
     * @return FormSubmitElement|null
     */
    public function getSubmitButton()
    {
        return $this->submitButton;
    }

    /**
     * Set the primary submit button
     *
     * @param FormSubmitElement $element
     *
     * @return $this
     */
    public function setSubmitButton(FormSubmitElement $element)
    {
        $this->submitButton = $element;

        return $this;
    }

    /**
     * Get the submit element used to send the form
     *
     * @return FormSubmitElement|null
     */
    public function getPressedSubmitElement()
    {
        foreach ($this->submitElements as $submitElement) {
            if ($submitElement->hasBeenPressed()) {
                return $submitElement;
            }
        }

        return null;
    }

    /**
     * @return ServerRequestInterface|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return $this
     */
    public function handleRequest(ServerRequestInterface $request)
    {
        $this->request = $request;

        if (! $this->hasBeenSent()) {
            return $this;
        }

        switch ($request->getMethod()) {
            case 'POST':
                $params = $request->getParsedBody();

                break;
            case 'GET':
                parse_str($request->getUri()->getQuery(), $params);

                break;
            default:
                $params = [];
        }
        $this->populate($params);

        // Assemble after populate in order to conditionally provide form elements
        $this->ensureAssembled();

        $submitElement = $this->getPressedSubmitElement();
        if (
            ! empty($this->submitElements)
            && $submitElement === null
        ) {
            // If elements are registered for submission, but none have been pressed,
            // the form was most likely submitted via auto-submit. In this case,
            // we validate all elements that have a value, but do nothing else.
            $this->validatePartial();

            return $this;
        }

        // From here, the form is considered submitted because either one of the submit elements has been pressed
        // or the form has been sent without a submit element being registered.
        if (
            $submitElement->getAttributes()->get('formnovalidate')->getValue() !== true
            && ! $this->isValid()
        ) {
            $this->onError();

            return $this;
        }
        try {
            if ($submitElement === $this->getSubmitButton()) {
                $this->onSuccess();
                $this->emitOnce(Form::ON_SUCCESS, [$this]);
            } else {
                $this->onSubmit($submitElement);
                $this->emit(static::ON_SUBMIT, [$submitElement]);
            }
        } catch (Exception $e) {
            $this->addMessage($e);
            $this->onError();
            $this->emit(Form::ON_ERROR, [$e, $this]);
        }

        return $this;
    }

    /**
     * Get whether the form has been sent
     *
     * A form is considered sent if the request's method equals the form's method.
     *
     * @return bool
     */
    public function hasBeenSent()
    {
        if ($this->request === null) {
            return false;
        }

        return $this->request->getMethod() === $this->getMethod();
    }

    /**
     * Get whether the form has been submitted
     *
     * A form is considered submitted because either it has been sent by pressing one of the registered submit elements
     * or the form has been sent without a submit element being registered.
     * This method calls {@link hasBeenSent()} in order to detect whether the form has been sent.
     *
     * @return bool
     */
    public function hasBeenSubmitted()
    {
        if (! $this->hasBeenSent()) {
            return false;
        }

        if (empty($this->submitElements)) {
            return true;
        }

        return $this->getPressedSubmitElement() !== null;
    }

    /**
     * Get whether the form is valid
     *
     * {@link validate()} is called automatically if the form has not been validated before.
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->isValid === null) {
            $this->validate();

            $this->emit(self::ON_VALIDATE, [$this]);
        }

        return $this->isValid;
    }

    /**
     * Validate all elements
     *
     * @return $this
     */
    public function validate()
    {
        $valid = true;
        foreach ($this->elements as $element) {
            if ($element->isRequired() && ! $element->hasValue()) {
                $element->addMessage('This field is required');
                $valid = false;
                continue;
            }
            if (! $element->isValid()) {
                $valid = false;
            }
        }

        $this->isValid = $valid;

        return $this;
    }

    /**
     * Validate all elements that have a value
     *
     * @return $this
     */
    public function validatePartial()
    {
        foreach ($this->getElements() as $element) {
            $element->validate();
        }

        return $this;
    }

    public function remove(ValidHtml $elementOrHtml)
    {
        if ($elementOrHtml instanceof FormSubmitElement) {
            if ($this->submitButton === $elementOrHtml) {
                $this->submitButton = null;
            }

            $key = array_search($elementOrHtml, $this->submitElements, true);
            if ($key !== false) {
                unset($this->submitElements[$key]);
            }
        }

        $this->removeElement($elementOrHtml);
    }

    protected function onError()
    {
        $errors = Html::tag('ul', ['class' => 'errors']);
        foreach ($this->getMessages() as $message) {
            if ($message instanceof Exception) {
                $message = $message->getMessage();
            }

            $errors->addHtml(Html::tag('li', $message));
        }

        if (! $errors->isEmpty()) {
            $this->prependHtml($errors);
        }
    }

    protected function onSubmit(FormSubmitElement $submitElement)
    {
    }

    protected function onSuccess()
    {
    }

    protected function onElementRegistered(FormElement $element)
    {
        if ($element instanceof FormSubmitElement) {
            $this->submitElements[$element->getName()] = $element;

            if (! $this->hasSubmitButton()) {
                $this->setSubmitButton($element);
            }
        }

        $element->onRegistered($this);
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        $attributes
            ->registerAttributeCallback('action', [$this, 'getAction'], [$this, 'setAction'])
            ->registerAttributeCallback('method', [$this, 'getMethod'], [$this, 'setMethod']);
    }
}

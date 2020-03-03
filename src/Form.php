<?php

namespace ipl\Html;

use Exception;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\FormElementContainer;
use ipl\Stdlib\MessageContainer;
use Psr\Http\Message\ServerRequestInterface;

class Form extends BaseHtmlElement
{
    use FormElementContainer;
    use MessageContainer;

    const ON_ELEMENT_REGISTERED = 'elementRegistered';
    const ON_ERROR = 'error';
    const ON_REQUEST = 'request';
    const ON_SUCCESS = 'success';

    /** @var string Form submission URL */
    protected $action;

    /** @var string HTTP method to submit the form with */
    protected $method;

    /** @var FormSubmitElement Primary submit button */
    protected $submitButton;

    /** @var FormSubmitElement[] Other elements that may submit the form */
    protected $submitElements = [];

    /** @var bool Whether the form is valid */
    private $isValid;

    /** @var ServerRequestInterface The server request being processed */
    private $request;

    protected $tag = 'form';

    /**
     * Get the Form submission URL
     *
     * @return string|null
     */
    public function getAction()
    {
        return $this->getAttributes()->get('action')->getValue();
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
        $this->getAttributes()->set('action', $action);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        $method = $this->getAttributes()->get('method')->getValue();
        if ($method === null) {
            // WRONG. Problem:
            // right now we get the method in assemble, that's too late.
            // TODO: fix this via getMethodAttribute callback
            return 'POST';
        }

        return $method;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->getAttributes()->set('method', strtoupper($method));

        return $this;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        $this->emit(Form::ON_REQUEST, [$request]);

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return $this
     */
    public function handleRequest(ServerRequestInterface $request)
    {
        $this->setRequest($request);
        if ($this->hasBeenSent()) {
            if ($request->getMethod() === 'POST') {
                $params = $request->getParsedBody();
            } elseif ($this->getMethod() === 'GET') {
                parse_str($request->getUri()->getQuery(), $params);
            } else {
                $params = [];
            }
            $this->populate($params);
        }

        $this->ensureAssembled();
        if ($this->hasBeenSubmitted()) {
            if ($this->isValid()) {
                try {
                    $this->onSuccess();
                    $this->emitOnce(Form::ON_SUCCESS, [$this]);
                } catch (Exception $e) {
                    $this->addMessage($e);
                    $this->onError();
                    $this->emit(Form::ON_ERROR, [$e, $this]);
                }
            } else {
                $this->onError();
            }
        } elseif ($this->hasBeenSent()) {
            $this->validatePartial();
        }

        return $this;
    }

    /**
     * @return ServerRequestInterface|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function onSuccess()
    {
        // $this->redirectOnSuccess();
    }

    public function onError()
    {
        $error = Html::tag('p', ['class' => 'error']);
        foreach ($this->getMessages() as $message) {
            if ($message instanceof Exception) {
                $error->add($message->getMessage());
            } else {
                $error->add($message);
            }
        }
        $this->prepend($error);
    }

    public function isValid()
    {
        if ($this->isValid === null) {
            $this->validate();
        }

        return $this->isValid;
    }

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
    }

    public function validatePartial()
    {
        foreach ($this->getElements() as $element) {
            if ($element->hasValue()) {
                $element->validate();
            }
        }
    }

    /**
     * @return bool
     */
    public function hasBeenSent()
    {
        if ($this->request === null) {
            return false;
        }

        if ($this->request->getMethod() !== $this->getMethod()) {
            return false;
        }

        // TODO: Check form name element

        return true;
    }

    /**
     * @return bool
     */
    public function hasBeenSubmitted()
    {
        if ($this->hasSubmitButton()) {
            return $this->getSubmitButton()->hasBeenPressed();
        } else {
            return $this->hasBeenSent();
        }
    }

    public function getSubmitButton()
    {
        return $this->submitButton;
    }

    public function hasSubmitButton()
    {
        return $this->submitButton !== null;
    }

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

    protected function onElementRegistered(BaseFormElement $element)
    {
        if ($element instanceof FormSubmitElement) {
            $this->submitElements[$element->getName()] = $element;

            if (! $this->hasSubmitButton()) {
                $this->setSubmitButton($element);
            }
        }
    }
}

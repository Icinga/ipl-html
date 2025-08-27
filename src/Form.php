<?php

namespace ipl\Html;

use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormElement\FormElements;
use ipl\Stdlib\Messages;
use ipl\Web\Url;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class Form extends BaseHtmlElement
{
    use FormElements {
        FormElements::remove as private removeElement;
    }
    use Messages;

    public const ON_ELEMENT_REGISTERED = 'elementRegistered';
    public const ON_ERROR = 'error';
    public const ON_REQUEST = 'request';
    public const ON_SUCCESS = 'success';
    public const ON_SENT = 'sent';
    public const ON_VALIDATE = 'validate';

    /** @var ?string Form submission URL */
    protected ?string $action = null;

    /** @var string HTTP method to submit the form with */
    protected $method = 'POST';

    /** @var ?FormSubmitElement Primary submit button */
    protected ?FormSubmitElement $submitButton = null;

    /** @var FormSubmitElement[] Other elements that may submit the form */
    protected array $submitElements = [];

    /** @var ?bool Whether the form is valid */
    protected ?bool $isValid = null;

    /** @var ?ServerRequestInterface The server request being processed */
    protected ?ServerRequestInterface $request = null;

    /** @var string|Url|null Form redirect url */
    protected ?string $redirectUrl = null;

    protected $tag = 'form';

    /**
     * Get whether the given value is empty
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isEmptyValue($value): bool
    {
        return $value === null || $value === [] || (is_string($value) && trim($value) === '');
    }

    /**
     * Get the Form submission URL
     *
     * @return string|null
     */
    public function getAction(): ?string
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
    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the HTTP method to submit the form with
     *
     * @return string
     */
    public function getMethod(): string
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
    public function setMethod(string $method): static
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Get whether the form has a primary submit button
     *
     * @return bool
     */
    public function hasSubmitButton(): bool
    {
        return $this->submitButton !== null;
    }

    /**
     * Get the primary submit button
     *
     * @return ?FormSubmitElement
     */
    public function getSubmitButton(): ?FormSubmitElement
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
    public function setSubmitButton(FormSubmitElement $element): static
    {
        $this->submitButton = $element;

        return $this;
    }

    /**
     * Get the submit element used to send the form
     *
     * @return ?FormSubmitElement
     */
    public function getPressedSubmitElement(): ?FormSubmitElement
    {
        foreach ($this->submitElements as $submitElement) {
            if ($submitElement->hasBeenPressed()) {
                return $submitElement;
            }
        }

        return null;
    }

    /**
     * @return ?ServerRequestInterface
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the url to redirect to on success
     *
     * @return null|string|Url
     */
    public function getRedirectUrl(): null|string|Url
    {
        return $this->redirectUrl;
    }

    /**
     * Set the url to redirect to on success
     *
     * @param Url|string $url
     *
     * @return $this
     */
    public function setRedirectUrl(Url|string $url): static
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return $this
     */
    public function handleRequest(ServerRequestInterface $request): static
    {
        $this->setRequest($request);

        if (! $this->hasBeenSent()) {
            $this->emit(Form::ON_REQUEST, [$request, $this]);

            // Always assemble
            $this->ensureAssembled();

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

        $params = array_merge_recursive($params, $request->getUploadedFiles());
        $this->populate($params);

        // Assemble after populate in order to conditionally provide form elements
        $this->ensureAssembled();

        if ($this->hasBeenSubmitted()) {
            if ($this->isValid()) {
                try {
                    $this->emit(Form::ON_SENT, [$this]);
                    $this->onSuccess();
                    $this->emitOnce(Form::ON_SUCCESS, [$this]);
                } catch (Throwable $e) {
                    $this->addMessage($e);
                    $this->onError();
                    $this->emit(Form::ON_ERROR, [$e, $this]);
                }
            } else {
                $this->onError();
            }
        } else {
            $this->validatePartial();
            $this->emit(Form::ON_SENT, [$this]);
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
    public function hasBeenSent(): bool
    {
        if ($this->request === null) {
            return false;
        }

        return $this->request->getMethod() === $this->getMethod();
    }

    /**
     * Get whether the form has been submitted
     *
     * A form is submitted when it has been sent and when the primary submit button, if set, has been pressed.
     * This method calls {@link hasBeenSent()} in order to detect whether the form has been sent.
     *
     * @return bool
     */
    public function hasBeenSubmitted()
    {
        if (! $this->hasBeenSent()) {
            return false;
        }

        if ($this->hasSubmitButton()) {
            return $this->getSubmitButton()->hasBeenPressed();
        }

        return true;
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
        $this->ensureAssembled();

        $valid = true;
        foreach ($this->getElements() as $element) {
            $element->validate();
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
    public function validatePartial(): static
    {
        $this->ensureAssembled();

        foreach ($this->getElements() as $element) {
            if ($element->hasValue()) {
                $element->validate();
            }
        }

        return $this;
    }

    public function remove(ValidHtml $content): static
    {
        if ($this->submitButton === $content) {
            $this->submitButton = null;
        }

        $this->removeElement($content);

        return $this;
    }

    protected function onError()
    {
        $errors = Html::tag('ul', ['class' => 'errors']);
        foreach ($this->getMessages() as $message) {
            if ($message instanceof Throwable) {
                $message = $message->getMessage();
            }

            $errors->addHtml(Html::tag('li', $message));
        }

        if (! $errors->isEmpty()) {
            $this->prependHtml($errors);
        }
    }

    protected function onSuccess()
    {
        // $this->redirectOnSuccess();
    }

    protected function onElementRegistered(FormElement $element): void
    {
        if ($element instanceof FormSubmitElement) {
            $this->submitElements[$element->getName()] = $element;

            if (! $this->hasSubmitButton()) {
                $this->setSubmitButton($element);
            }
        }

        $element->onRegistered($this);
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes
            ->registerAttributeCallback('action', [$this, 'getAction'], [$this, 'setAction'])
            ->registerAttributeCallback('method', [$this, 'getMethod'], [$this, 'setMethod']);
    }
}

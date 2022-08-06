<?php

namespace Refineon\Basic\Exceptions;

use Hyperf\Utils\Contracts\MessageBag;

class ValidatorException extends \Exception
{
    /**
     * @var MessageBag
     */
    protected $messageBag;

    /**
     * @param MessageBag $messageBag
     */
    public function __construct(MessageBag $messageBag)
    {
        parent::__construct('The given data was invalid.');
        $this->messageBag = $messageBag;
    }

    /**
     * @return MessageBag
     */
    public function errors()
    {
        return $this->messageBag;
    }

    /**
     * @return string
     */
    public function first()
    {
        return $this->messageBag->first();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'error' => 'validation_exception',
            'error_description' => $this->errors()
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

}

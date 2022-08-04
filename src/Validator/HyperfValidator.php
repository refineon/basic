<?php

namespace Meibuyu\Micro\Validator;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class HyperfValidator extends AbstractValidator
{

    /**
     * @var ValidatorFactoryInterface
     */
    protected $validator;

    public function __construct(ValidatorFactoryInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Pass the data and the rules to the validator
     *
     * @param string $action
     * @return boolean
     */
    public function passes($action = null)
    {
        $rules = $this->getRules($action);
        $messages = $this->getMessages();
        $attributes = $this->getAttributes();
        $validator = $this->validator->make($this->data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            $this->errors = $validator->errors();
            return false;
        }

        return true;
    }
}
<?php

namespace App\ConfGenerator;

use Webmozart\Assert\InvalidArgumentException;

interface ConfigValidatorInterface
{
    /**
     * @param array $toValidate prams array
     * @param array $validateAgainstDefinition base array
     * @return bool true when validated successfully
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $toValidate, array $validateAgainstDefinition): bool;
}

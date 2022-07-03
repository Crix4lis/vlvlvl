<?php

namespace App\ConfGenerator;

use App\Utils\IsAssocArrayTrait;
use Webmozart\Assert\Assert;

class ConfigValidator implements ConfigValidatorInterface
{
    use IsAssocArrayTrait;

    /** @inheritDoc */
    public function validate(array $toValidate, array $validateAgainstDefinition): bool
    {
        return $this->validateInput($toValidate, $validateAgainstDefinition);
    }

    /**
     * @param mixed $toValidate params array
     * @param mixed $validateAgainst base array
     * @return bool
     */
    private function validateInput(mixed $toValidate, mixed $validateAgainst): bool
    {
        foreach ($toValidate as $keyToValidate => $valueToValidate) {
            // make sure params key exists in definition
            Assert::keyExists($validateAgainst, $keyToValidate, sprintf('Missing key "%s"', $keyToValidate));

            // make sure value of params key is not json object if value of the same key in definition requires value or array of values
            if ($this->isAssocArray($valueToValidate) &&
                $this->isAssocArray($validateAgainst[$keyToValidate]) === false
            ) {
                Assert::true(false, sprintf(
                    'Value within "%s" key must not be associative array! Given: %s',
                    $keyToValidate,
                    json_encode($valueToValidate),
                ));
            }

            if ($this->isAssocArray($validateAgainst[$keyToValidate])) {
                // make sure value of params key is json object if value of the same key in definition is also json object
                if ($this->isAssocArray($valueToValidate) === false) {
                    Assert::true(false, sprintf(
                        'Value of key "%s" is expected to be assoc array',
                        $keyToValidate
                    ));
                }

                // if json object, check recursive
                $this->validateInput($valueToValidate, $validateAgainst[$keyToValidate]);
                continue;
            }

            // if definition key requires value or array of values, those values must match definition type
            if (is_array($valueToValidate)) {
                $expectedValuesType = gettype($validateAgainst[$keyToValidate]);

                foreach ($valueToValidate as $item) {
                    Assert::eq(
                        gettype($item),
                        $expectedValuesType,
                        sprintf(
                            'Value "%s" is expected to be of type "%s". Key: "%s"',
                            $item,
                            $expectedValuesType,
                            $keyToValidate
                        )
                    );
                }
            }
        }

        return true;
    }
}

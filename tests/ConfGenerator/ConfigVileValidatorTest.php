<?php

namespace Test\App\ConfGenerator;

use App\ConfGenerator\ConfigValidator;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class ConfigVileValidatorTest extends TestCase
{
    /**
     * @dataProvider matchingFilesDataProvider
     */
    public function testSuccessfullyValidatesInput(
        array $baseData,
        array $paramsData,
    ): void
    {
        $validatorToTest = new ConfigValidator();

        $this->assertTrue($validatorToTest->validate($paramsData, $baseData));
    }

    /**
     * @dataProvider baseIsMissingKeyDataProvider
     */
    public function testThrowsExceptionWhenBaseIsMissingKey(
        array $baseData,
        array $paramsData,
        string $missingKey,
    ): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Missing key "%s"', $missingKey));

        $validatorToTest = new ConfigValidator();
        $validatorToTest->validate($paramsData, $baseData);
    }

    /**
     * @dataProvider typesMissMatchDataProvider
     */
    public function testThrowsExceptionWhenTypesMissMatches(
        array $baseData,
        array $paramsData,
        array $errorMessageValues,
    ): void
    {
        $missMatchedValue = $errorMessageValues[0];
        $expectedType = $errorMessageValues[1];
        $missMatchInKey = $errorMessageValues[2];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Value "%s" is expected to be of type "%s". Key: "%s"',
            $missMatchedValue,
            $expectedType,
            $missMatchInKey,
        ));

        $validatorToTest = new ConfigValidator();
        $validatorToTest->validate($paramsData, $baseData);
    }

    /**
     * @dataProvider unexpectedAssocArrayInParamsDataProvider
     */
    public function testThrowsExceptionWhenUnexpectedAssocArrayInParamsGiven(
        array $baseData,
        array $paramsData,
        array $errorMessageValues,
    ): void
    {
        $keyName = $errorMessageValues[0];
        $invalidValue = json_encode($errorMessageValues[1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Value within "%s" key must not be associative array! Given: %s',
            $keyName,
            $invalidValue,
        ));

        $validatorToTest = new ConfigValidator();
        $validatorToTest->validate($paramsData, $baseData);
    }

    /**
     * @dataProvider paramsOverrideBaseDataProvider
     */
    public function testThrowsExceptionParamsOverrideBase(
        array $baseData,
        array $paramsData,
        string $invalidKey,
    ): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Value of key "%s" is expected to be assoc array', $invalidKey));

        $validatorToTest = new ConfigValidator();
        $validatorToTest->validate($paramsData, $baseData);
    }

    public function matchingFilesDataProvider(): array
    {
        return [
            'from e-mail' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                ],
                [
                    'details' => [
                        'buffer_size' => [2, 3, 6]
                    ]
                ],
            ],
            'from attachment' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                    'model' => [
                        'type' => 'A',
                        'unit' => 5,
                        'max_age' => 5,
                        'calculation_cost' => 0.3
                    ],
                ],
                [
                    'strategy_index' => [1, 2, 3, 4, 5],
                    'details' => [
                        'threshold' => [0.1, 0.2],
                    ],
                    'model' => [
                        'max_age' => [5, 10, 15, 20],
                        'type' => ['A','B','C'],
                    ],
                ]
            ],
        ];
    }

    public function baseIsMissingKeyDataProvider(): array
    {
        return [
            'missing buffer_size' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                    ],
                ],
                [
                    'details' => [
                        'buffer_size' => [2, 3, 6]
                    ]
                ],
                'buffer_size',
            ],
            'missing max_age key' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                    'model' => [
                        'type' => 'A',
                        'unit' => 5,
                        'calculation_cost' => 0.3
                    ],
                ],
                [
                    'strategy_index' => [1, 2, 3, 4, 5],
                    'details' => [
                        'threshold' => [0.1, 0.2],
                    ],
                    'model' => [
                        'max_age' => [5, 10, 15, 20],
                        'type' => ['A','B','C'],
                    ],
                ],
                'max_age',
            ],
        ];
    }

    public function typesMissMatchDataProvider(): array
    {
        return [
            'double instead of integer' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                ],
                [
                    'details' => [
                        'buffer_size' => [2, 3.2, 6]
                    ]
                ],
                [
                    3.2,
                    'integer',
                    'buffer_size',
                ]
            ],
            'string instead of integer' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                ],
                [
                    'details' => [
                        'buffer_size' => [2, 3, '6']
                    ]
                ],
                [
                    '6',
                    'integer',
                    'buffer_size',
                ]
            ],
            'integer instead of string' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                    'model' => [
                        'type' => 'A',
                        'unit' => 5,
                        'max_age' => 5,
                        'calculation_cost' => 0.3
                    ],
                ],
                [
                    'strategy_index' => [1, 2, 3, 4, 5],
                    'details' => [
                        'threshold' => [0.1, 0.2],
                    ],
                    'model' => [
                        'max_age' => [5, 10, 15, 20],
                        'type' => ['A','B',2],
                    ],
                ],
                [
                    2,
                    'string',
                    'type',
                ]
            ],
        ];
    }

    public function unexpectedAssocArrayInParamsDataProvider(): array
    {
        return [
            'buffer_size wrong value' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                ],
                [
                    'details' => [
                        'buffer_size' => [
                            6,
                            'ups_mistake' => 6,
                        ]
                    ]
                ],
                [
                    'buffer_size',
                    [
                        6,
                        'ups_mistake' => 6,
                    ]
                ],
            ],
            'max_age wrong value' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                    'model' => [
                        'type' => 'A',
                        'unit' => 5,
                        'max_age' => 5,
                        'calculation_cost' => 0.3
                    ],
                ],
                [
                    'strategy_index' => [1, 2, 3, 4, 5],
                    'details' => [
                        'threshold' => [0.1, 0.2],
                    ],
                    'model' => [
                        'max_age' => [5, 10, 15, 20 => [2]],
                        'type' => ['A','B','C'],
                    ],
                ],
                [
                    'max_age',
                    [5, 10, 15, 20 => [2]],
                ]
            ],
        ];
    }

    public function paramsOverrideBaseDataProvider(): array
    {
        return [
            'from e-mail with with invalid integer' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                ],
                [
                    'details' => 2
                ],
                'details'
            ],
            'from e-mail with invalid arr of integers' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                ],
                [
                    'details' => [2, 3]
                ],
                'details'
            ],
        ];
    }
}

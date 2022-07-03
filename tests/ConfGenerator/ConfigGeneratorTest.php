<?php

namespace Test\App\ConfGenerator;

use App\ConfGenerator\ConfigGenerator;
use App\ConfGenerator\ConfigValidatorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ConfigGeneratorTest extends TestCase
{
    use ProphecyTrait;

    private readonly ObjectProphecy|ConfigValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->prophesize(ConfigValidatorInterface::class);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTestsConfigGenerator(array $base, array $params, int $expected): void
    {
        $this->validator->validate(
            Argument::type('array'),
            Argument::type('array'),
        )->willReturn(true);

        $generator = new ConfigGenerator($this->validator->reveal());

        $result = $generator->generate($base, $params);

        $this->assertCount($expected, $result);
    }

    public function dataProvider(): array
    {
        return [
            'data from attachment extened' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                    'model' => [
                        'type' => "A",
                        'unit' => 5,
                        'max_age' => 5,
                        'calculation_cost' => 0.3,
                        'another_nest' => [
                            'nested_val' => 2,
                        ]
                    ],
                ],
                [
                    "strategy_index" => [1,2,3,4,5],
                    "details" => [
                        'threshold' => [0.1, 0.2],
                    ],
                    'model' => [
                        "max_age" => [5,10,15,20],
                        "type" => ["A","B","C"],
                        "another_nest" => [
                            'nested_val' => [1, 2, 3]
                        ],
                    ],
                ],
                44
            ],
            'data from attachment' => [
                [
                    'strategy_index' => 1,
                    'details' => [
                        'threshold' => 0.5,
                        'buffer_size' => 6,
                    ],
                    'model' => [
                        'type' => "A",
                        'unit' => 5,
                        'max_age' => 5,
                        'calculation_cost' => 0.3,
                        'another_nest' => [
                            'nested_val' => 2,
                        ]
                    ],
                ],
                [
                    "strategy_index" => [1,2,3,4,5],
                    "details" => [
                        'threshold' => [0.1, 0.2],
                    ],
                    'model' => [
                        "max_age" => [5,10,15,20],
                        "type" => ["A","B","C"],
                    ],
                ],
                38
            ],
        ];
    }
}

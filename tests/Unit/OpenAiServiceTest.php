<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OpenAiService;
use Psr\Log\LoggerInterface;
use Mockery;

class OpenAiServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a fake OpenAI client that returns a specific response
     */
    private function createFakeClient($responseContent)
    {
        return new class($responseContent) {
            private $responseContent;

            public function __construct($responseContent)
            {
                $this->responseContent = $responseContent;
            }

            public function chat()
            {
                $content = $this->responseContent;
                return new class($content) {
                    private $content;

                    public function __construct($content)
                    {
                        $this->content = $content;
                    }

                    public function create($params)
                    {
                        $content = $this->content;
                        return new class($content) {
                            public $choices;

                            public function __construct($content)
                            {
                                $messageObj = new class($content) {
                                    public $content;

                                    public function __construct($content)
                                    {
                                        $this->content = $content;
                                    }
                                };

                                $choiceObj = new class($messageObj) {
                                    public $message;

                                    public function __construct($messageObj)
                                    {
                                        $this->message = $messageObj;
                                    }
                                };

                                $this->choices = [$choiceObj];
                            }
                        };
                    }
                };
            }
        };
    }

    /**
     * Create a fake client that throws an exception
     */
    private function createFailingClient($exceptionMessage)
    {
        return new class($exceptionMessage) {
            private $exceptionMessage;

            public function __construct($exceptionMessage)
            {
                $this->exceptionMessage = $exceptionMessage;
            }

            public function chat()
            {
                $message = $this->exceptionMessage;
                return new class($message) {
                    private $message;

                    public function __construct($message)
                    {
                        $this->message = $message;
                    }

                    public function create($params)
                    {
                        throw new \Exception($this->message);
                    }
                };
            }
        };
    }

    /**
     * Feature 89: OpenAI service accepts client in constructor
     */
    public function test_service_accepts_client_in_constructor(): void
    {
        $fakeClient = $this->createFakeClient(json_encode([
            'name' => 'Test Recipe',
            'servings' => 4,
            'ingredients' => [],
            'steps' => []
        ]));

        $service = new OpenAiService($fakeClient);

        $this->assertInstanceOf(OpenAiService::class, $service);
    }

    /**
     * Feature 89: Service can be instantiated with mock client and returns expected data
     */
    public function test_service_with_mock_client_calls_parse_method(): void
    {
        $responseData = [
            'name' => 'Test Recipe',
            'servings' => 4,
            'ingredients' => [
                ['name' => 'flour', 'quantity' => 250, 'unit' => 'g']
            ],
            'steps' => [
                ['step_number' => 1, 'instruction' => 'Mix ingredients']
            ]
        ];

        $fakeClient = $this->createFakeClient(json_encode($responseData));

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('info')->withAnyArgs()->atLeast()->once();

        $service = new OpenAiService($fakeClient, $mockLogger);

        $result = $service->parseRecipeText('Test recipe text');

        $this->assertIsArray($result);
        $this->assertEquals('Test Recipe', $result['name']);
        $this->assertEquals(4, $result['servings']);
        $this->assertCount(1, $result['ingredients']);
        $this->assertEquals('flour', $result['ingredients'][0]['name']);
    }

    /**
     * Feature 90: OpenAI service logs API calls
     */
    public function test_service_logs_parsing_start(): void
    {
        $fakeClient = $this->createFakeClient(json_encode([
            'name' => 'Test Recipe',
            'servings' => 4,
            'ingredients' => [],
            'steps' => []
        ]));

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $loggedStart = false;

        $mockLogger->shouldReceive('info')->withAnyArgs()->atLeast()->once()
            ->andReturnUsing(function ($message, $context = []) use (&$loggedStart) {
                if ($message === 'Parsing recipe text' &&
                    isset($context['length']) && $context['length'] > 0) {
                    $loggedStart = true;
                }
            });

        $service = new OpenAiService($fakeClient, $mockLogger);
        $service->parseRecipeText('Test recipe text');

        $this->assertTrue($loggedStart, 'Parsing recipe text message was not logged');
    }

    /**
     * Feature 90: OpenAI service logs parsing completion
     */
    public function test_service_logs_parsing_completion(): void
    {
        $fakeClient = $this->createFakeClient(json_encode([
            'name' => 'Test Recipe',
            'servings' => 4,
            'ingredients' => [['name' => 'flour', 'quantity' => 250, 'unit' => 'g']],
            'steps' => [['step_number' => 1, 'instruction' => 'Mix']]
        ]));

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $loggedSuccessfully = false;

        $mockLogger->shouldReceive('info')->withAnyArgs()->atLeast()->once()
            ->andReturnUsing(function ($message, $context = []) use (&$loggedSuccessfully) {
                if ($message === 'Recipe parsed successfully' &&
                    isset($context['name']) &&
                    isset($context['servings']) &&
                    isset($context['ingredient_count']) &&
                    isset($context['step_count'])) {
                    $loggedSuccessfully = true;
                }
            });

        $service = new OpenAiService($fakeClient, $mockLogger);
        $service->parseRecipeText('Test recipe text');

        $this->assertTrue($loggedSuccessfully, 'Recipe parsed successfully message was not logged');
    }

    /**
     * Feature 91: OpenAI service handles timeout errors
     */
    public function test_service_handles_timeout_error(): void
    {
        $failingClient = $this->createFailingClient('Request timed out');

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('info')->withAnyArgs();
        $mockLogger->shouldReceive('error')
            ->once()
            ->with('Recipe parsing failed', Mockery::on(function ($context) {
                return isset($context['error']) && str_contains($context['error'], 'timed out');
            }));

        $service = new OpenAiService($failingClient, $mockLogger);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to parse recipe');

        $service->parseRecipeText('Test recipe text');
    }

    /**
     * Feature 91: Verify timeout error is logged
     */
    public function test_timeout_error_is_logged(): void
    {
        $failingClient = $this->createFailingClient('Connection timeout');

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $loggedError = false;

        $mockLogger->shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();
        $mockLogger->shouldReceive('error')->withAnyArgs()->atLeast()->once()
            ->andReturnUsing(function ($message, $context = []) use (&$loggedError) {
                if (isset($context['error']) && isset($context['trace'])) {
                    $loggedError = true;
                }
            });

        $service = new OpenAiService($failingClient, $mockLogger);

        try {
            $service->parseRecipeText('Test recipe text');
        } catch (\Exception $e) {
            // Expected exception
        }

        $this->assertTrue($loggedError, 'Error with trace was not logged');
    }

    /**
     * Feature 92: OpenAI service handles rate limit errors
     */
    public function test_service_handles_rate_limit_error(): void
    {
        $failingClient = $this->createFailingClient('Rate limit exceeded (429)');

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('info')->withAnyArgs();
        $mockLogger->shouldReceive('error')
            ->once()
            ->with('Chat response failed', Mockery::on(function ($context) {
                return isset($context['error']) && str_contains($context['error'], '429');
            }));

        $service = new OpenAiService($failingClient, $mockLogger);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to generate response');

        $service->chatResponse('Tell me a recipe');
    }

    /**
     * Feature 92: Rate limit error returns appropriate message
     */
    public function test_rate_limit_error_message_is_appropriate(): void
    {
        $failingClient = $this->createFailingClient('Too many requests, please try again later');

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('info')->withAnyArgs();
        $mockLogger->shouldReceive('error')->withAnyArgs();

        $service = new OpenAiService($failingClient, $mockLogger);

        try {
            $service->chatResponse('Test message');
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Failed to generate response', $e->getMessage());
        }
    }

    /**
     * Feature 93: OpenAI service handles invalid API key error
     */
    public function test_service_handles_invalid_api_key(): void
    {
        $failingClient = $this->createFailingClient('Invalid API key (401)');

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('info')->withAnyArgs();
        $mockLogger->shouldReceive('error')
            ->once()
            ->with('Recipe improvement failed', Mockery::on(function ($context) {
                return isset($context['error']) && str_contains($context['error'], '401');
            }));

        $service = new OpenAiService($failingClient, $mockLogger);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to improve recipe');

        $service->improveRecipe([
            'name' => 'Test Recipe',
            'ingredients' => [],
            'steps' => []
        ], 'Make it better');
    }

    /**
     * Feature 93: Invalid API key error returns appropriate message
     */
    public function test_invalid_api_key_returns_error_message(): void
    {
        $failingClient = $this->createFailingClient('Incorrect API key provided');

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('info')->withAnyArgs();
        $mockLogger->shouldReceive('error')->withAnyArgs();

        $service = new OpenAiService($failingClient, $mockLogger);

        try {
            $service->improveRecipe(['name' => 'Test', 'ingredients' => [], 'steps' => []], 'feedback');
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Failed to improve recipe', $e->getMessage());
            $this->assertStringContainsString('Incorrect API key', $e->getMessage());
        }
    }

    /**
     * Additional test: Verify chat method logs properly
     */
    public function test_chat_method_logs_request_details(): void
    {
        $fakeClient = $this->createFakeClient(json_encode([
            'message' => 'Test response',
            'has_recipe' => false
        ]));

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $loggedChat = false;

        $mockLogger->shouldReceive('info')->withAnyArgs()->atLeast()->once()
            ->andReturnUsing(function ($message, $context = []) use (&$loggedChat) {
                if ($message === 'Generating chat response' &&
                    isset($context['message_length']) &&
                    isset($context['history_count']) &&
                    isset($context['detect_recipe'])) {
                    $loggedChat = true;
                }
            });

        $service = new OpenAiService($fakeClient, $mockLogger);
        $service->chatResponse('Test message', [], true);

        $this->assertTrue($loggedChat, 'Generating chat response message was not logged');
    }

    /**
     * Additional test: Verify improveRecipe method logs properly
     */
    public function test_improve_recipe_logs_request_details(): void
    {
        $fakeClient = $this->createFakeClient(json_encode([
            'ingredients' => [],
            'steps' => [],
            'change_summary' => 'Test changes'
        ]));

        $mockLogger = Mockery::mock(LoggerInterface::class);
        $loggedImprovement = false;

        $mockLogger->shouldReceive('info')->withAnyArgs()->atLeast()->once()
            ->andReturnUsing(function ($message, $context = []) use (&$loggedImprovement) {
                if ($message === 'Improving recipe' &&
                    isset($context['recipe_name']) &&
                    isset($context['feedback_length'])) {
                    $loggedImprovement = true;
                }
            });

        $service = new OpenAiService($fakeClient, $mockLogger);
        $service->improveRecipe(['name' => 'Test Recipe', 'ingredients' => [], 'steps' => []], 'Make it spicier');

        $this->assertTrue($loggedImprovement, 'Improving recipe message was not logged');
    }
}

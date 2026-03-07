<?php

/*
 * This file is part of PapiAI,
 * A simple but powerful PHP library for building AI agents.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PapiAI\ElevenLabs;

use PapiAI\Core\AudioResponse;
use PapiAI\Core\Contracts\TextToSpeechProviderInterface;
use PapiAI\Core\Exception\AuthenticationException;
use PapiAI\Core\Exception\ProviderException;
use PapiAI\Core\Exception\RateLimitException;
use RuntimeException;

class ElevenLabsProvider implements TextToSpeechProviderInterface
{
    private const API_BASE = 'https://api.elevenlabs.io/v1';

    private const VOICES = [
        'Rachel' => '21m00Tcm4TlvDq8ikWAM',
        'Domi' => 'AZnzlk1XvdvUeBnXmlld',
        'Bella' => 'EXAVITQu4vr4xnSDxMaL',
        'Antoni' => 'ErXwobaYiN019PkySvjV',
        'Elli' => 'MF3mGyEYCl7XYWbV9V6O',
        'Josh' => 'TxGEqnHWrfWFTfGW9XjX',
        'Arnold' => 'VR6AewLTigWG4xSOukaG',
        'Adam' => 'pNInz6obpgDQGcFmaJgB',
        'Sam' => 'yoZ06aMxZJJ28mfd3POQ',
    ];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $defaultVoice = 'Rachel',
        private readonly string $defaultModel = 'eleven_multilingual_v2',
    ) {
    }

    public function synthesize(string $text, array $options = []): AudioResponse
    {
        $voice = $options['voice'] ?? $this->defaultVoice;
        $voiceId = self::VOICES[$voice] ?? $voice;
        $model = $options['model'] ?? $this->defaultModel;
        $format = $options['format'] ?? 'mp3_44100_128';

        $payload = [
            'text' => $text,
            'model_id' => $model,
        ];

        $url = self::API_BASE . '/text-to-speech/' . $voiceId . '?output_format=' . $format;

        $data = $this->request($url, $payload);

        return new AudioResponse(
            data: $data,
            format: $this->extractBaseFormat($format),
            model: $model,
        );
    }

    public function getName(): string
    {
        return 'elevenlabs';
    }

    /**
     * Extract the base audio format from the output_format parameter.
     */
    private function extractBaseFormat(string $outputFormat): string
    {
        $parts = explode('_', $outputFormat);

        return $parts[0];
    }

    /**
     * Make an API request to the ElevenLabs API.
     */
    protected function request(string $url, array $payload): string
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'xi-api-key: ' . $this->apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error !== '') {
            throw new RuntimeException("ElevenLabs API request failed: {$error}");
        }

        if (!is_string($response)) {
            throw new RuntimeException('ElevenLabs API request failed: no response received');
        }

        if ($httpCode >= 400) {
            $data = json_decode($response, true);
            $this->throwForStatusCode($httpCode, is_array($data) ? $data : null);
        }

        return $response;
    }

    /**
     * Throw the appropriate exception based on HTTP status code.
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws ProviderException
     */
    private function throwForStatusCode(int $httpCode, ?array $data): never
    {
        $errorMessage = is_array($data) ? ($data['detail']['message'] ?? $data['detail'] ?? 'Unknown error') : 'Unknown error';

        if ($httpCode === 401) {
            throw new AuthenticationException(
                $this->getName(),
                $httpCode,
                $data,
            );
        }

        if ($httpCode === 429) {
            throw new RateLimitException(
                $this->getName(),
                statusCode: $httpCode,
                responseBody: $data,
            );
        }

        throw new ProviderException(
            "ElevenLabs API error ({$httpCode}): {$errorMessage}",
            $this->getName(),
            $httpCode,
            $data,
        );
    }
}

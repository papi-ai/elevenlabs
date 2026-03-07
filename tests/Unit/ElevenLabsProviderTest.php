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

namespace PapiAI\ElevenLabs\Tests\Unit;

use PapiAI\Core\AudioResponse;
use PapiAI\Core\Contracts\TextToSpeechProviderInterface;
use PapiAI\ElevenLabs\ElevenLabsProvider;

/**
 * Test subclass that stubs HTTP methods for unit testing.
 */
class TestableElevenLabsProvider extends ElevenLabsProvider
{
    public string $lastUrl = '';
    public array $lastPayload = [];
    public string $fakeResponse = 'fake-audio-bytes';

    protected function request(string $url, array $payload): string
    {
        $this->lastUrl = $url;
        $this->lastPayload = $payload;

        return $this->fakeResponse;
    }
}

/**
 * Test subclass that exposes the real request method for error path testing.
 */
class RealRequestElevenLabsProvider extends ElevenLabsProvider
{
    public function callRequest(string $url, array $payload): string
    {
        return $this->request($url, $payload);
    }
}

describe('ElevenLabsProvider', function () {
    beforeEach(function () {
        $this->provider = new TestableElevenLabsProvider('test-api-key');
    });

    describe('construction', function () {
        it('implements TextToSpeechProviderInterface', function () {
            expect($this->provider)->toBeInstanceOf(TextToSpeechProviderInterface::class);
        });

        it('does not implement ProviderInterface', function () {
            expect($this->provider)->not->toBeInstanceOf(\PapiAI\Core\Contracts\ProviderInterface::class);
        });

        it('returns elevenlabs as the provider name', function () {
            expect($this->provider->getName())->toBe('elevenlabs');
        });
    });

    describe('synthesize', function () {
        it('synthesizes with default options', function () {
            $result = $this->provider->synthesize('Hello world');

            expect($result)->toBeInstanceOf(AudioResponse::class);
            expect($result->data)->toBe('fake-audio-bytes');
            expect($result->format)->toBe('mp3');
            expect($result->model)->toBe('eleven_multilingual_v2');

            expect($this->provider->lastUrl)->toContain('/text-to-speech/21m00Tcm4TlvDq8ikWAM');
            expect($this->provider->lastUrl)->toContain('output_format=mp3_44100_128');
            expect($this->provider->lastPayload)->toBe([
                'text' => 'Hello world',
                'model_id' => 'eleven_multilingual_v2',
            ]);
        });

        it('maps voice name to voice ID', function () {
            $this->provider->synthesize('Test', ['voice' => 'Josh']);

            expect($this->provider->lastUrl)->toContain('/text-to-speech/TxGEqnHWrfWFTfGW9XjX');
        });

        it('uses raw voice ID when not a known name', function () {
            $this->provider->synthesize('Test', ['voice' => 'custom-voice-id-123']);

            expect($this->provider->lastUrl)->toContain('/text-to-speech/custom-voice-id-123');
        });

        it('uses custom model when specified', function () {
            $this->provider->synthesize('Test', ['model' => 'eleven_turbo_v2']);

            expect($this->provider->lastPayload['model_id'])->toBe('eleven_turbo_v2');
        });

        it('uses custom format when specified', function () {
            $this->provider->synthesize('Test', ['format' => 'pcm_16000']);

            expect($this->provider->lastUrl)->toContain('output_format=pcm_16000');
        });

        it('parses response into AudioResponse', function () {
            $this->provider->fakeResponse = 'raw-audio-data-here';

            $result = $this->provider->synthesize('Hello');

            expect($result)->toBeInstanceOf(AudioResponse::class);
            expect($result->data)->toBe('raw-audio-data-here');
            expect($result->size())->toBe(strlen('raw-audio-data-here'));
        });

        it('extracts base format from output format string', function () {
            $result = $this->provider->synthesize('Test', ['format' => 'opus_16000']);

            expect($result->format)->toBe('opus');
        });
    });

    describe('request error handling', function () {
        it('throws RuntimeException on curl error', function () {
            $provider = new RealRequestElevenLabsProvider('test-key');

            expect(fn () => $provider->callRequest('http://0.0.0.0:1/', ['text' => 'test']))
                ->toThrow(\RuntimeException::class, 'ElevenLabs API request failed:');
        });

        it('throws AuthenticationException on HTTP 401 error', function () {
            $provider = new RealRequestElevenLabsProvider('invalid-key');

            expect(fn () => $provider->callRequest('https://api.elevenlabs.io/v1/text-to-speech/test', ['text' => 'test']))
                ->toThrow(\PapiAI\Core\Exception\AuthenticationException::class);
        });
    });

    describe('constructor defaults', function () {
        it('uses custom default voice', function () {
            $provider = new TestableElevenLabsProvider('key', 'Adam');
            $provider->synthesize('Test');

            expect($provider->lastUrl)->toContain('/text-to-speech/pNInz6obpgDQGcFmaJgB');
        });

        it('uses custom default model', function () {
            $provider = new TestableElevenLabsProvider('key', 'Rachel', 'eleven_turbo_v2');
            $provider->synthesize('Test');

            expect($provider->lastPayload['model_id'])->toBe('eleven_turbo_v2');
        });
    });
});

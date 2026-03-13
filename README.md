# PapiAI ElevenLabs Provider

[![CI](https://github.com/papi-ai/elevenlabs/workflows/CI/badge.svg)](https://github.com/papi-ai/elevenlabs/actions?query=workflow%3ACI) [![Latest Version](https://img.shields.io/packagist/v/papi-ai/elevenlabs.svg)](https://packagist.org/packages/papi-ai/elevenlabs) [![Total Downloads](https://img.shields.io/packagist/dt/papi-ai/elevenlabs.svg)](https://packagist.org/packages/papi-ai/elevenlabs) [![PHP Version](https://img.shields.io/packagist/php-v/papi-ai/elevenlabs.svg)](https://packagist.org/packages/papi-ai/elevenlabs) [![License](https://img.shields.io/packagist/l/papi-ai/elevenlabs.svg)](https://packagist.org/packages/papi-ai/elevenlabs)

ElevenLabs text-to-speech provider for [PapiAI](https://github.com/papi-ai/papi-core) - A simple but powerful PHP library for building AI agents.

## Installation

```bash
composer require papi-ai/elevenlabs
```

## Usage

```php
use PapiAI\ElevenLabs\ElevenLabsProvider;

$provider = new ElevenLabsProvider(
    apiKey: $_ENV['ELEVENLABS_API_KEY'],
);

$audio = $provider->synthesize('Hello world!');
$audio->save('output.mp3');
```

## Available Voices

Rachel (default), Domi, Bella, Antoni, Elli, Josh, Arnold, Adam, Sam

Custom voice IDs can be passed via options:

```php
$audio = $provider->synthesize('Hello!', [
    'voice' => 'Josh',
    'model' => 'eleven_multilingual_v2',
]);
```

## Features

- High-quality text-to-speech via ElevenLabs API
- Multiple built-in voices with name-to-ID mapping
- Custom voice ID support
- Multilingual model support

## License

MIT

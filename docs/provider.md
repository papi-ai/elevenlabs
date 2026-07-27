# ElevenLabs

ElevenLabs text-to-speech provider for PapiAI. This is a voice synthesis service, not an LLM provider. It implements `TextToSpeechProviderInterface` only.

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

## Voices

Built-in voices with name-to-ID mapping:

- Rachel (default)
- Domi
- Bella
- Antoni
- Elli
- Josh
- Arnold
- Adam
- Sam

Custom voice IDs can be passed via options:

```php
$audio = $provider->synthesize('Hello!', [
    'voice' => 'Josh',
    'model' => 'eleven_multilingual_v2',
]);
```

## Capabilities

| Capability | Supported |
|---|---|
| Text-to-speech | Yes |

ElevenLabs is a dedicated text-to-speech service. It does not support chat, tool calling, or any LLM capabilities. Use it alongside an LLM provider to add voice output to your agents.

## Requirements

- PHP 8.2+
- `ext-curl`
- `papi-ai/papi-core` ^0.14

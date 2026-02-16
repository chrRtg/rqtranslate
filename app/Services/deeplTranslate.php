<?php

namespace App\Services;

use Illuminate\Support\Str;
use DeepL\Translator;
use DeepL\TextResult;
use DeepL\Usage;

class DeeplTranslate
{
    protected $translator;

    protected $formalitySupported = ['DE', 'PL', 'FR', 'IT', 'ES'];

    public function __construct()
    {
        $this->translator = new Translator(env('DEEPL_API_KEY'));
    }

    /**
     * Translate the given text to the target language using DeepL API.
     * @param string $text
     * @param string $targetLang
     * @return DeepL\TextResult
     */
    public function translate(string $text, string $targetLang = 'EN-US'): TextResult
    {
        $options = [];
        $targetLang = strtoupper($targetLang);

        if (in_array($targetLang, $this->formalitySupported)) {
            $options['formality'] = 'less';
        }

        // sanitize the content before sending it to DeepL to avoid issues with non-printable characters,
        // weird whitespace, and mentions that could break the API or cause unwanted pings in Discord.
        $clean_text = $this->sanitizeContent($text);
        if (empty($clean_text) || strlen($clean_text) < 1) {
            return new TextResult('', null, 0);
        }

        // DeepL returns a TextResult object; we grab the 'text' property
        return $this->translator->translateText($clean_text, null, $targetLang, $options);
    }

    public function getUsage(): Usage
    {
        return $this->translator->getUsage();
    }

    /**
     * Sanitize the input text to ensure it's clean and won't cause issues with the DeepL API.
     *
     * @param string $content
     * @return array|bool|string|null
     */
    private function sanitizeContent(string $content): string
    {
        // 1. Remove non-printable control characters (except newlines/tabs)
        // This prevents "Glitch" text or hidden characters from breaking the API.
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);

        // 2. Normalize whitespace
        // DeepL counts every character; stripping double spaces/weird tabs saves money.
        $content = Str::squish($content);

        // 3. Neutralize Discord Mentions
        // Replaces '@' with a zero-width space after it so the bot doesn't
        // accidentally ping @everyone if someone puts that in the text.
        $content = str_replace(['@everyone', '@here'], ['@ everyone', '@ here'], $content);

        // 4. Force UTF-8 Encoding
        // DeepL ONLY supports UTF-8. This ensures no weird encoding busts the request.
        return mb_convert_encoding($content, 'UTF-8', 'UTF-8');
    }
}

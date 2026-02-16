<?php

namespace App\Services;

use DeepL\Translator;
use DeepL\TextResult;
use DeepL\Usage;

class DeeplTranslate
{
    protected $translator;

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
        // DeepL returns a TextResult object; we grab the 'text' property
        return $this->translator->translateText($text, null, $targetLang, ['formality' => 'less']);
    }

    public function getUsage(): Usage
    {
        return $this->translator->getUsage();
    }
}

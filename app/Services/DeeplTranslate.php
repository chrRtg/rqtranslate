<?php

namespace App\Services;

use DeepL\Translator;
use DeepL\TextResult;
use DeepL\Usage;
use League\CommonMark\CommonMarkConverter;
use League\HTMLToMarkdown\HtmlConverter;

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
     *
     * While the input is Discord Markdown, we will convert it to HTML for better translation results.
     * The output from DeepL will be in HTML format, which has to be concerted back to Markdown for Discord.
     * This needs to happen in the method calling this one.
     *
     * @param string $text
     * @param string $targetLang
     * @return \DeepL\TextResult
     */
    public function translate(string $text, string $targetLang = 'EN-US'): TextResult
    {
        $options = [];
        $targetLang = strtoupper($targetLang);

        $options['tag_handling'] = 'html';
        $options['preserve_formatting'] = true;
        $options['split_sentences'] = 'nonewlines';

        if (in_array($targetLang, $this->formalitySupported)) {
            $options['formality'] = 'less';
        }

        // Convert Discord Markdown to HTML for better translation results
        $converter = new CommonMarkConverter([
            'html_input' => 'strip', // Security: strip raw HTML
            'allow_unsafe_links' => false,
        ]);

        $html = $converter->convert($text)->getContent();

        //echo 'Sanitized Text: ' . $html . PHP_EOL;

        // Translate with DeepL, it returns a \DeepL\TextResult object;
        return $this->translator->translateText($html, null, $targetLang, $options);
    }

    /**
     * Convert translated HTML back to Markdown for Discord.
     *
     * @param string $html
     * @return string
     */
    public function htmlToDiscordMarkdown(string $html): string
    {
        // Convert the translated HTML to Markdown for Discord
        $converter = new HtmlConverter([
            'strip_tags' => true,   // Remove tags that don't have Markdown equivalents
            'hard_break' => true,   // Use GFM style line breaks
            'italic_style' => '_',  // Discord prefers _ for italics sometimes
            'bold_style' => '**',
            'header_style'=>'atx',  // Use ATX style headers (e.g., # Header)
        ]);

        return $converter->convert($html);
    }

    /**
     * Get the current usage of the DeepL API.
     *
     * @return Usage
     */
    public function getUsage(): Usage
    {
        return $this->translator->getUsage();
    }
}

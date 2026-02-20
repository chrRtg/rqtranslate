<?php

namespace App\Services;

use Illuminate\Support\Str;

class DiscordTools
{
    /**
     * @param string $text
     * @param int $limit Leave room for markers (default 1900)
     */
    public function splitMarkdown(string $text, int $limit = 1900): array
    {
        if (Str::length($text) <= $limit) {
            return [$text];
        }

        // Split into blocks, keeping the double newlines
        $blocks = preg_split('/(\n\n)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        // Filter out empty strings but keep newlines
        $blocks = array_values(array_filter($blocks, fn($b) => $b !== ''));

        $chunks = [];
        $currentChunk = "";

        for ($i = 0; $i < count($blocks); $i++) {
            $block = $blocks[$i];
            $isHeader = preg_match('/^(#{1,3}\s)/m', $block);

            // --- THE FIX: HEADER LOOK-AHEAD ---
            if ($isHeader) {
                $nextBlock = isset($blocks[$i + 1]) ? $blocks[$i + 1] : '';
                $nextNextBlock = isset($blocks[$i + 2]) ? $blocks[$i + 2] : '';

                // Calculate size of Header + Double Newline + First Paragraph
                $headerSectionSize = Str::length($block . $nextBlock . $nextNextBlock);

                // If the current chunk is already somewhat full OR
                // the header + its first paragraph won't fit in the remaining space...
                $remainingSpace = $limit - Str::length($currentChunk);

                if ($headerSectionSize > $remainingSpace || Str::length($currentChunk) > ($limit * 0.7)) {
                    if (!empty(trim($currentChunk))) {
                        $chunks[] = $this->repairMarkdown(trim($currentChunk));
                        $currentChunk = "";
                    }
                }
            }

            // Normal additive logic
            if (Str::length($currentChunk . $block) > $limit) {
                if (empty(trim($currentChunk))) {
                    // Force break a single massive block
                    $chunks[] = $this->repairMarkdown(Str::substr($block, 0, $limit));
                    $currentChunk = Str::substr($block, $limit);
                } else {
                    $chunks[] = $this->repairMarkdown(trim($currentChunk));
                    $currentChunk = $block;
                }
            } else {
                $currentChunk .= $block;
            }
        }

        if (!empty(trim($currentChunk))) {
            $chunks[] = $this->repairMarkdown(trim($currentChunk));
        }

        return $chunks;
    }

    /**
     * Closes unclosed Markdown tags.
     */
    private function repairMarkdown(string $text): string
    {
        $tags = ['***', '**', '*', '__', '_', '~~', '||', '```'];
        foreach ($tags as $tag) {
            if (substr_count($text, $tag) % 2 !== 0) {
                $text .= $tag;
            }
        }
        return $text;
    }
}

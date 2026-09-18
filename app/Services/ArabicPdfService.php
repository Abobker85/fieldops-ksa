<?php

namespace App\Services;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;

class ArabicPdfService
{
    protected static ?Arabic $arabic = null;

    /**
     * Get singleton instance of ArPHP Arabic processor.
     */
    public static function getArabic(): Arabic
    {
        if (!static::$arabic) {
            static::$arabic = new Arabic();
        }
        return static::$arabic;
    }

    /**
     * Reshape Arabic text in an HTML document for proper rendering in DomPDF.
     * Prevents reversed/disconnected Arabic letters and preserves HTML structure and LTR spans.
     */
    public static function reshapeHtml(string $html): string
    {
        $arabic = static::getArabic();

        // 1. Preserve <style> and <script> blocks
        $preservedBlocks = [];
        $html = preg_replace_callback('/<(style|script)\b[^>]*>(.*?)<\/\1>/is', function ($matches) use (&$preservedBlocks) {
            $key = '___PRESERVED_BLOCK_' . count($preservedBlocks) . '___';
            $preservedBlocks[$key] = $matches[0];
            return $key;
        }, $html);

        // 2. Preserve explicit LTR blocks (e.g. <span dir="ltr">...</span>)
        $html = preg_replace_callback('/<([a-z0-9]+)\b[^>]*\bdir=["\']ltr["\'][^>]*>(.*?)<\/\1>/is', function ($matches) use (&$preservedBlocks) {
            $key = '___PRESERVED_BLOCK_' . count($preservedBlocks) . '___';
            $preservedBlocks[$key] = $matches[0];
            return $key;
        }, $html);

        // 3. Reshape text nodes between HTML tags containing Arabic
        $html = preg_replace_callback('/>([^<]+)</u', function ($matches) use ($arabic) {
            $text = $matches[1];
            if (trim($text) === '' || !preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
                return '>' . $text . '<';
            }
            // Use utf8Glyphs with $hindo = false to maintain standard Western Arabic numerals
            return '>' . $arabic->utf8Glyphs($text, 1000, false) . '<';
        }, $html);

        // 4. Restore preserved blocks
        foreach ($preservedBlocks as $placeholder => $content) {
            $html = str_replace($placeholder, $content, $html);
        }

        return $html;
    }

    /**
     * Render a Blade view with Arabic glyph reshaping and return DomPDF instance.
     */
    public static function loadView(string $view, array $data = []): DomPdfWrapper
    {
        $rawHtml = view($view, $data)->render();
        $reshapedHtml = static::reshapeHtml($rawHtml);

        return Pdf::loadHTML($reshapedHtml)->setOptions([
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
        ]);
    }
}

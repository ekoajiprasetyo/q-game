<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizerService
{
    protected HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        
        // Define allowed HTML tags and attributes for rich text editor
        $config->set('HTML.Allowed', implode(',', [
            // Text formatting
            'p[style]',
            'br',
            'b', 'strong',
            'i', 'em',
            'u', 's', 'strike',
            'sub', 'sup',
            
            // Lists
            'ul', 'ol', 'li',
            
            // Links (optional, if editor supports)
            'a[href|target|title]',
            
            // Images
            'img[src|alt|style|width|height|class]',
            
            // Span for styling (highlight, color)
            'span[style|class]',
            
            // Div for alignment
            'div[style|class]',
            
            // Tables (if needed)
            'table[style|class|border]',
            'thead', 'tbody', 'tfoot',
            'tr', 'th[style]', 'td[style|colspan|rowspan]',
            
            // Headings
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            
            // Blockquote
            'blockquote',
            
            // Pre/Code for code blocks
            'pre', 'code',
        ]));
        
        // Define allowed CSS properties
        $config->set('CSS.AllowedProperties', implode(',', [
            'color',
            'background-color',
            'background',
            'text-align',
            'text-decoration',
            'font-size',
            'font-weight',
            'font-style',
            'font-family',
            'line-height',
            'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
            'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right',
            'border', 'border-color', 'border-width', 'border-style',
            'width', 'height', 'max-width', 'max-height',
            'float',
            // 'display', // Not supported by HTMLPurifier default CSS definition
            'vertical-align',
        ]));
        
        // Allow data URIs for images (from editor paste/upload)
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'data' => true, // For base64 images
        ]);
        
        // Allow target="_blank" for links
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        
        // Cache directory for HTMLPurifier
        $cacheDir = storage_path('framework/cache/htmlpurifier');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cacheDir);
        
        $this->purifier = new HTMLPurifier($config);
    }

    /**
     * Sanitize HTML content to prevent XSS attacks
     */
    public function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }
        
        return $this->purifier->purify($html);
    }

    /**
     * Sanitize options array (for multiple choice questions)
     */
    public function sanitizeOptions(array $options): array
    {
        return array_map(function ($option) {
            if (is_array($option)) {
                // Handle options with text property
                if (isset($option['text'])) {
                    $option['text'] = $this->sanitize($option['text']);
                }
                if (isset($option['value'])) {
                    $option['value'] = $this->sanitize($option['value']);
                }
            } elseif (is_string($option)) {
                $option = $this->sanitize($option);
            }
            return $option;
        }, $options);
    }
}

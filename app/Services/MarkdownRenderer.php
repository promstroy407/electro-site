<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Безопасный показ текстов из админки: Markdown → HTML,
 * сырой HTML экранируется, опасные ссылки (javascript:) отбрасываются.
 */
final class MarkdownRenderer
{
    public function toHtml(?string $markdown): HtmlString
    {
        if ($markdown === null || trim($markdown) === '') {
            return new HtmlString('');
        }

        return new HtmlString(Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]));
    }
}

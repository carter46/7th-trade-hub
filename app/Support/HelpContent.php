<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class HelpContent
{
    /** @var array<string, array>|null */
    private static ?array $cache = null;

    public static function path(): string
    {
        return (string) config('help.articles_path', resource_path('content/help'));
    }

    /**
     * @return array<string, array>
     */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $dir = self::path();
        $articles = [];

        if (is_dir($dir)) {
            foreach (glob($dir.DIRECTORY_SEPARATOR.'*.php') ?: [] as $file) {
                $slug = basename($file, '.php');
                $article = self::loadFile($file, $slug);
                if ($article !== null) {
                    $articles[$slug] = $article;
                }
            }
        }

        self::$cache = $articles;

        return self::$cache;
    }

    public static function find(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '' || preg_match('/[^a-z0-9\-]/', $slug)) {
            return null;
        }

        $file = self::path().DIRECTORY_SEPARATOR.$slug.'.php';
        if (! is_file($file)) {
            return null;
        }

        return self::loadFile($file, $slug);
    }

    /**
     * Flatten searchable text blobs for hub suggestion search.
     *
     * @return list<array{type: string, label: string, href: string, hint?: string, text: string}>
     */
    public static function searchIndex(): array
    {
        $index = [];

        foreach (self::all() as $slug => $article) {
            $url = route('help.article', $slug);
            $guideText = implode(' ', array_filter([
                $article['title'] ?? '',
                $article['intro'] ?? '',
                $article['summary'] ?? '',
            ]));

            $index[] = [
                'type' => 'guide',
                'label' => $article['title'] ?? $slug,
                'href' => $url,
                'hint' => 'Guide',
                'text' => $guideText,
            ];

            foreach ($article['sections'] ?? [] as $section) {
                $sectionId = $section['id'] ?? '';
                $sectionText = self::sectionSearchText($section);
                $index[] = [
                    'type' => 'section',
                    'label' => $section['title'] ?? $section['nav'] ?? $sectionId,
                    'href' => $url.($sectionId !== '' ? '#'.$sectionId : ''),
                    'hint' => $article['title'] ?? 'Guide',
                    'text' => $sectionText,
                ];
            }
        }

        foreach (config('help.faqs', []) as $faq) {
            $q = (string) ($faq['q'] ?? '');
            $a = (string) ($faq['a'] ?? '');
            $slug = (string) ($faq['article'] ?? '');
            $section = (string) ($faq['section'] ?? '');
            $href = $slug !== ''
                ? route('help.article', $slug).($section !== '' ? '#'.$section : '')
                : route('help').'#faqs';

            $index[] = [
                'type' => 'faq',
                'label' => $q,
                'href' => $href,
                'hint' => 'FAQ',
                'text' => $q.' '.$a,
            ];
        }

        return $index;
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private static function sectionSearchText(array $section): string
    {
        $parts = [
            $section['nav'] ?? '',
            $section['title'] ?? '',
        ];

        foreach ($section['blocks'] ?? [] as $block) {
            $type = $block['type'] ?? '';
            if (in_array($type, ['paragraph', 'tip', 'important', 'warning', 'success'], true)) {
                $parts[] = $block['title'] ?? '';
                $parts[] = $block['content'] ?? '';
            }
            if (in_array($type, ['bullets', 'checklist'], true)) {
                $parts = array_merge($parts, $block['items'] ?? []);
            }
            if ($type === 'faq') {
                foreach ($block['items'] ?? [] as $item) {
                    $parts[] = $item['q'] ?? '';
                    $parts[] = $item['a'] ?? '';
                }
            }
            if ($type === 'screenshot') {
                $parts[] = $block['title'] ?? '';
                $parts[] = $block['caption'] ?? '';
                $parts[] = $block['alt'] ?? '';
            }
        }

        foreach ($section['paragraphs'] ?? [] as $p) {
            $parts[] = $p;
        }
        foreach ($section['bullets'] ?? [] as $b) {
            $parts[] = $b;
        }
        foreach ($section['checklist'] ?? [] as $c) {
            $parts[] = $c;
        }

        return implode(' ', array_filter(array_map('strval', $parts)));
    }

    private static function loadFile(string $file, string $slug): ?array
    {
        /** @var mixed $data */
        $data = require $file;
        if (! is_array($data)) {
            return null;
        }

        $data['slug'] = $data['slug'] ?? $slug;
        $data['sections'] = array_map(
            fn (array $section) => self::normalizeSection($section),
            $data['sections'] ?? []
        );

        $mtime = @filemtime($file) ?: time();
        if (empty($data['updated_at'])) {
            $data['updated_at'] = Carbon::createFromTimestamp($mtime)->toDateString();
        }
        $data['updated_at_display'] = Carbon::parse($data['updated_at'])->format('F j, Y');
        $data['reading_minutes'] = self::estimateReadingMinutes($data);
        $data['_path'] = $file;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private static function normalizeSection(array $section): array
    {
        if (! empty($section['blocks'])) {
            return $section;
        }

        $blocks = [];
        foreach ($section['paragraphs'] ?? [] as $paragraph) {
            $blocks[] = ['type' => 'paragraph', 'content' => $paragraph];
        }
        if (! empty($section['bullets'])) {
            $blocks[] = ['type' => 'bullets', 'items' => $section['bullets']];
        }
        if (! empty($section['checklist'])) {
            $blocks[] = ['type' => 'checklist', 'items' => $section['checklist']];
        }
        if (! empty($section['faqs'])) {
            $blocks[] = ['type' => 'faq', 'items' => $section['faqs']];
        }
        foreach ($section['screenshots'] ?? [] as $shot) {
            $blocks[] = array_merge(['type' => 'screenshot'], $shot);
        }
        if (! empty($section['callout'])) {
            $blocks[] = $section['callout'];
        }

        $section['blocks'] = $blocks;

        return $section;
    }

    /**
     * @param  array<string, mixed>  $article
     */
    private static function estimateReadingMinutes(array $article): int
    {
        $text = ($article['title'] ?? '').' '.($article['intro'] ?? '').' '.($article['summary'] ?? '');
        foreach ($article['sections'] ?? [] as $section) {
            $text .= ' '.self::sectionSearchText($section);
        }
        $words = str_word_count(strip_tags($text));

        return max(1, (int) ceil($words / 200));
    }
}

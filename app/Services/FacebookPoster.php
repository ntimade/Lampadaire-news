<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Posts one real article — its own photo, its own FULL text, a link back to
 * the article — to the Le Lampadaire Facebook Page. Never invents content:
 * the caption is the article's own title + its complete body (HTML stripped,
 * paragraph breaks kept), not a summary — Facebook posts support up to
 * ~63,000 characters, far more than any article needs.
 */
class FacebookPoster
{
    protected string $graphVersion = 'v19.0';

    /**
     * @return bool true on a confirmed successful post.
     */
    public function post(News $article): bool
    {
        $pageId = config('services.facebook.page_id');
        $token = config('services.facebook.page_access_token');

        if (empty($pageId) || empty($token)) {
            Log::warning('FacebookPoster — page_id ou page_access_token manquant, publication annulée.');
            return false;
        }

        $caption = $this->buildCaption($article);
        $imageUrl = $this->publicImageUrl($article);

        try {
            if ($imageUrl) {
                $response = Http::asForm()->timeout(30)->post(
                    "https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos",
                    [
                        'url' => $imageUrl,
                        'caption' => $caption,
                        'access_token' => $token,
                    ]
                );
            } else {
                // No usable image (rare — e.g. a broken/missing path) : a
                // plain link post still shares the real article rather than
                // skipping it outright.
                $response = Http::asForm()->timeout(30)->post(
                    "https://graph.facebook.com/{$this->graphVersion}/{$pageId}/feed",
                    [
                        'message' => $caption,
                        'link' => $this->articleUrl($article),
                        'access_token' => $token,
                    ]
                );
            }

            if (! $response->successful()) {
                Log::warning('FacebookPoster — réponse API en erreur : ' . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('FacebookPoster — exception : ' . $e->getMessage());
            return false;
        }
    }

    protected function buildCaption(News $article): string
    {
        $body = $this->fullArticleText($article);

        // Facebook's own hard cap (~63,206 chars) — a safety net against a
        // freak oversized article, never a real editorial truncation.
        $body = mb_substr($body, 0, 60000);

        return trim($article->title) . "\n\n" . $body . "\n\n" . 'Lire la suite : ' . $this->articleUrl($article);
    }

    /**
     * The article's complete body as plain text, with paragraph/line breaks
     * preserved (unlike a blind strip_tags(), which would collapse the
     * whole article into one run-on paragraph).
     */
    protected function fullArticleText(News $article): string
    {
        $html = (string) $article->content;
        $html = preg_replace('/<(p|div|br|li|h[1-6])\b[^>]*>/i', "\n", $html);

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n[ \t]+/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    protected function articleUrl(News $article): string
    {
        return route('news-details', $article->slug);
    }

    /**
     * Facebook fetches the image server-side, so it needs a publicly
     * reachable absolute URL — fine once this runs from the real server
     * (config('app.url') is the public IP/domain there), not from a local
     * dev machine only reachable as localhost.
     */
    protected function publicImageUrl(News $article): ?string
    {
        if (empty($article->image)) {
            return null;
        }

        return asset($article->image);
    }
}

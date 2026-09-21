<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Expands a short RSS summary into a fuller, original article draft via the
 * free-tier Gemini API. Each rubrique can have its own dedicated API key
 * (config('services.gemini.keys_by_category'), each from a separate free
 * Google Cloud project) so usage spreads across independent quotas instead
 * of sharing one — falls back to the default key when a category has none
 * configured yet.
 *
 * Returns null on any failure (missing key, network error, empty/truncated
 * response) so callers can fall back to the plain summary + source link
 * instead of blocking on it.
 */
class GeminiArticleWriter
{
    protected string $model = 'gemini-3.6-flash';

    /**
     * Editorial angle per rubrique, requested explicitly for each category —
     * steers emphasis only, never invents facts beyond the summary provided.
     */
    protected array $categoryAngles = [
        'politique-faits-divers' => "Quand c'est pertinent, mets l'accent sur la CEMAC (Communauté Économique et Monétaire de l'Afrique Centrale) et sur les faits divers au Cameroun et en Afrique.",
        'culture' => "Mets en valeur les lieux culturels et le patrimoine du Cameroun et d'Afrique, ainsi que l'angle tourisme quand c'est pertinent.",
        'sante-prevention' => "Mets l'accent sur les nouveautés et évolutions médicales/sanitaires dans le monde et en Afrique.",
        'finance-et-bourse' => "Concentre-toi en priorité sur les bourses et marchés financiers africains ; élargis au reste du monde (y compris crypto-monnaies) seulement si le résumé source porte sur un sujet mondial.",
    ];

    /**
     * Site rubriques Gemini is allowed to pick from when reclassifying —
     * kept as a flat slug list so the prompt and the parsed answer both
     * stay simple strings, no dependency on the Category model here.
     */
    protected array $validCategorySlugs = [
        'culture', 'finance-et-bourse', 'business', 'sante-prevention',
        'politique-faits-divers', 'religion-spiritualite', 'international', 'sport',
    ];

    /**
     * Ask Gemini which rubrique actually fits this story — used instead of
     * the RSS feed's own <category> tag / title-keyword guess, which are
     * unreliable on untagged general feeds (Jeune Afrique's main feed has
     * no tags at all) and on feeds that mix topics under one name (RFI's
     * "Sciences & Santé" also carries pure-science, non-health items).
     * Always uses the default key (config('services.gemini.key')), not a
     * per-category one — classification happens before the category is
     * known. Returns null on any failure so the caller can keep its own
     * best-guess category instead of blocking.
     */
    public function classify(string $title, string $summary): ?string
    {
        $apiKey = config('services.gemini.key');

        if (empty($apiKey) || empty(trim($title))) {
            return null;
        }

        $slugList = implode(', ', $this->validCategorySlugs);

        $prompt = <<<PROMPT
Voici la liste exacte des rubriques d'un site d'actualité : {$slugList}

Étant donné ce titre et ce résumé d'article, réponds UNIQUEMENT avec le slug exact (parmi la liste ci-dessus) de la rubrique la plus pertinente. Rien d'autre : pas de phrase, pas de ponctuation, juste le slug.

Titre : {$title}
Résumé : {$summary}
PROMPT;

        $text = $this->callGemini($apiKey, $prompt, 2048, 0);

        if ($text === null) {
            return null;
        }

        // Gemini sometimes wraps the answer in quotes/backticks despite the
        // instruction — strip anything that isn't part of a slug.
        $slug = strtolower(trim(preg_replace('/[^a-z0-9\-]/i', '', trim($text))));

        return in_array($slug, $this->validCategorySlugs, true) ? $slug : null;
    }

    public function expand(string $title, string $summary, string $sourceName, ?string $categorySlug = null): ?string
    {
        $apiKey = ($categorySlug ? config("services.gemini.keys_by_category.{$categorySlug}") : null)
            ?: config('services.gemini.key');

        if (empty($apiKey) || empty(trim($summary))) {
            return null;
        }

        $angle = $categorySlug ? ($this->categoryAngles[$categorySlug] ?? '') : '';
        $angleBlock = $angle ? "\nAngle éditorial de cette rubrique : {$angle}\n" : '';

        $prompt = <<<PROMPT
Tu es journaliste pour "Le Lampadaire", un site d'actualité camerounais sérieux et rigoureux.

On te donne le titre et un résumé court (issu d'une dépêche externe : {$sourceName}) d'une actualité. Rédige un article de 300 à 450 mots en français, dans un style journalistique professionnel, clair et informatif, adapté à un lectorat camerounais/africain.
{$angleBlock}
Règles strictes :
- N'invente aucun fait, chiffre, citation ou nom qui n'est pas déjà présent dans le résumé fourni.
- Tu peux ajouter du contexte général et des explications, mais reste factuellement fidèle au résumé.
- N'écris jamais une simple reformulation phrase par phrase du résumé : structure un vrai article (accroche, développement, mise en perspective).
- Réponds uniquement avec le texte de l'article, en paragraphes séparés par un saut de ligne vide. Pas de titre, pas de markdown, pas de préambule.

Titre : {$title}
Résumé source : {$summary}
PROMPT;

        return $this->callGemini($apiKey, $prompt, 4096, 0.6);
    }

    /**
     * Generic, non-news "idée de business" article — evergreen advice, not
     * tied to any RSS source. Deliberately forbidden from naming real grant
     * programmes, amounts, or eligibility conditions: Gemini has no way to
     * verify those against a real, current source, so a specific-sounding
     * claim here would be a fabricated "fact" about real money a reader
     * might rely on — exactly the failure mode expand() guards against by
     * requiring a source summary to stay faithful to. Returns
     * ['title' => ..., 'body' => ...] or null on any failure.
     */
    public function writeBusinessIdeaArticle(string $theme, string $categorySlug = 'business'): ?array
    {
        $apiKey = config("services.gemini.keys_by_category.{$categorySlug}") ?: config('services.gemini.key');

        if (empty($apiKey)) {
            return null;
        }

        $prompt = <<<PROMPT
Tu es journaliste pour "Le Lampadaire", un site d'actualité camerounais sérieux et rigoureux.

Rédige un article de 300 à 450 mots en français, destiné à des entrepreneurs camerounais/africains, sur des idées de business rentables dans le secteur suivant : {$theme}.

Règles strictes, très importantes :
- Ne cite AUCUN nom précis de programme de subvention, bailleur, montant chiffré ou condition d'éligibilité officielle — tu ne peux pas vérifier ces informations et une erreur pourrait induire un lecteur en erreur sur de l'argent réel. Tu peux dire en termes généraux qu'il existe des dispositifs d'aide (banques, coopératives, institutions de microfinance, programmes publics ou internationaux) et conseiller au lecteur de se renseigner directement auprès des organismes officiels pour les conditions exactes — jamais inventer les détails.
- Concentre-toi sur : pourquoi le secteur est porteur, quelles compétences/ressources sont nécessaires pour démarrer, des exemples génériques de modèles d'affaires viables, et des conseils pratiques de lancement.
- Style journalistique clair, motivant, concret, adapté à un lectorat camerounais/africain.
- Réponds sur la première ligne par "TITRE: " suivi d'un titre accrocheur, puis une ligne vide, puis le corps de l'article en paragraphes séparés par une ligne vide. Pas de markdown.
PROMPT;

        $text = $this->callGemini($apiKey, $prompt, 4096, 0.7);

        if ($text === null) {
            return null;
        }

        if (! preg_match('/^TITRE\s*:\s*(.+?)\r?\n\r?\n(.+)$/su', trim($text), $matches)) {
            Log::warning('GeminiArticleWriter::writeBusinessIdeaArticle — format de réponse inattendu');
            return null;
        }

        return [
            'title' => trim($matches[1]),
            'body' => trim($matches[2]),
        ];
    }

    /**
     * Rewrite an article's body into a short, self-contained, well-written
     * summary for the printable PDF newspaper — plain prose in complete
     * sentences, factually faithful to the source, no HTML/markdown/lists.
     *
     * The PDF template (see newspaper.blade.php's $sentenceLimit) already
     * trims text at the last full sentence within a character budget, but
     * that only helps if the *source* text is itself coherent prose — raw
     * article bodies can carry stray HTML remnants, RSS boilerplate, or
     * abrupt mid-thought sentences that no truncation logic can fix. This
     * gives $sentenceLimit clean material to cut from instead.
     *
     * Uses its own dedicated key (config('services.gemini.key_journal'))
     * so the print pipeline never competes with the RSS conversion
     * pipeline's quota — and callers are expected to cache the result
     * (see News::print_excerpt) so re-downloading the journal never
     * re-spends it either. Returns null on any failure so the caller can
     * fall back to the raw content.
     */
    public function writeForPrint(string $title, string $plainContent): ?string
    {
        $apiKey = config('services.gemini.key_journal') ?: config('services.gemini.key');

        if (empty($apiKey) || empty(trim($plainContent))) {
            return null;
        }

        $prompt = <<<PROMPT
Tu prépares le texte d'un article pour l'édition imprimée (PDF) du journal "Le Lampadaire".

Rédige un résumé cohérent de cet article en français, en 4 à 6 phrases complètes formant un texte autonome et bien écrit : ton journalistique neutre, phrases entières bien reliées entre elles, aucune coupure abrupte, aucune liste à puces, aucun markdown, pas de titre répété en début de texte.

Règles strictes :
- N'invente aucun fait, chiffre, citation ou nom absent du texte source.
- Ne recopie pas le texte source mot pour mot : condense-le en gardant l'essentiel.
- Réponds uniquement avec le texte du résumé, sans préambule ni guillemets autour.

Titre : {$title}
Texte source : {$plainContent}
PROMPT;

        return $this->callGemini($apiKey, $prompt, 3072, 0.4);
    }

    /**
     * Shared request/response handling for every Gemini call in this class.
     * gemini-3.6-flash spends a large, variable share of the output-token
     * budget on internal "thinking" before writing the visible answer
     * (observed 500-2000+ thought tokens even for short prompts) — a low
     * ceiling truncates mid-thought and leaks reasoning fragments into the
     * response instead of real output, so callers must pass a generous
     * $maxOutputTokens. Returns the response text, or null on any failure
     * (missing key, HTTP error, non-STOP finish reason, empty text).
     */
    protected function callGemini(string $apiKey, string $prompt, int $maxOutputTokens, float $temperature): ?string
    {
        try {
            $response = Http::timeout(40)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => $maxOutputTokens,
                        'temperature' => $temperature,
                    ],
                ]
            );

            if (! $response->successful()) {
                Log::warning('GeminiArticleWriter — réponse API en erreur : ' . $response->body());
                return null;
            }

            $finishReason = $response->json('candidates.0.finishReason');
            $text = $response->json('candidates.0.content.parts.0.text');

            // MAX_TOKENS means generation was cut off mid-thought before (or
            // during) writing the answer — the partial text is unusable.
            if ($finishReason !== 'STOP' || empty($text)) {
                Log::warning("GeminiArticleWriter — génération incomplète (finishReason={$finishReason})");
                return null;
            }

            return trim($text);
        } catch (\Throwable $e) {
            Log::warning('GeminiArticleWriter — exception : ' . $e->getMessage());
            return null;
        }
    }
}

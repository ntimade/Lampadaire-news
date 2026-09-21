<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Category;
use App\Models\News;
use App\Models\Setting;
use App\Services\GeminiArticleWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateBusinessIdeaArticle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:generate-business-idea';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Publie un article générique d'idées de business (pas d'actu, pas de source RSS) — reste volontairement vague sur les subventions (aucun montant/programme/condition d'éligibilité inventé) pour ne jamais induire un lecteur en erreur sur de l'argent réel.";

    protected string $botEmail = 'veille-auto@lelampadaire.local';

    /**
     * Rotated one at a time (see Setting 'business_idea_theme_index') so
     * consecutive runs don't repeat the same sector.
     */
    protected array $themes = [
        'agriculture et transformation agroalimentaire',
        'technologies et services numériques',
        'artisanat et mode',
        'transport et logistique',
        'restauration et petite hôtellerie',
        'énergie solaire et solutions renouvelables',
        'commerce en ligne et e-commerce',
        'services aux entreprises (comptabilité, communication, conseil)',
        'économie circulaire et recyclage',
        'tourisme local et hébergement',
    ];

    public function handle(GeminiArticleWriter $writer): int
    {
        $bot = Admin::where('email', $this->botEmail)->first();
        $category = Category::where('slug', 'business')->first();

        if (! $bot || ! $category) {
            $this->error('Compte auteur ou rubrique Business introuvable.');
            return self::FAILURE;
        }

        $setting = Setting::firstOrCreate(['key' => 'business_idea_theme_index'], ['value' => '0']);
        $index = ((int) $setting->value) % count($this->themes);
        $theme = $this->themes[$index];

        $article = $writer->writeBusinessIdeaArticle($theme, $category->slug);

        if (! $article) {
            $this->warn("Échec de génération pour le thème « {$theme} » — retenté au prochain passage.");
            return self::FAILURE;
        }

        $body = collect(preg_split('/\r?\n\r?\n/', trim($article['body'])))
            ->filter()
            ->map(fn ($paragraph) => '<p>' . e(trim($paragraph)) . '</p>')
            ->implode('');

        $body .= '<p><em>Article de conseils général — renseignez-vous directement auprès des banques, coopératives ou organismes publics pour connaître les dispositifs de financement réellement disponibles et leurs conditions.</em></p>';

        $news = new News();
        $news->language = $category->language ?? 'fr';
        $news->category_id = $category->id;
        $news->auther_id = $bot->id;
        $news->image = 'frontend/assets/images/category-placeholders/business.jpg';
        $news->title = $article['title'];
        $news->slug = Str::slug($article['title']) . '-' . Str::random(5);
        $news->content = $body;
        $news->show_at_slider = 0;
        $news->show_at_popular = 0;
        $news->show_at_journal = 1;
        $news->is_journal_lead = 0;
        $news->status = 1;
        $news->is_approved = 1;
        $news->published_at = now();
        $news->save();

        $setting->update(['value' => (string) (($index + 1) % count($this->themes))]);

        $this->info("Publié : « {$article['title']} » (thème : {$theme})");

        return self::SUCCESS;
    }
}

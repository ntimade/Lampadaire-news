<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Veille éditoriale : interroge les flux RSS toutes les 30 min et
        // met en attente les nouveaux articles (voir
        // App\Console\Commands\CollectNewsFromSources).
        $schedule->command('news:collect')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        // Rédige (Gemini, clé par rubrique) et PUBLIE directement chaque
        // article de la file — explicitement demandé, sans validation admin
        // pour ce pipeline précis (voir App\Console\Commands\AutoPublishCollectedNews).
        $schedule->command('news:auto-publish')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        // Pré-génère (Gemini, clé dédiée "journal") un résumé imprimé propre
        // pour les articles de l'édition PDF du moment, avant qu'un lecteur
        // ne la télécharge — voir App\Console\Commands\GeneratePrintExcerpts
        // et App\Services\NewspaperEditionBuilder. Fait exprès en tâche de
        // fond (CLI, pas de limite de temps d'exécution) plutôt que dans
        // NewspaperController::download() : générer jusqu'à 8 résumés à la
        // volée dépassait la limite de temps d'une requête web.
        $schedule->command('news:generate-print-excerpts')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        // Publie sur la Page Facebook un article par rubrique, un par heure
        // (voir App\Console\Commands\PostDailyFacebookUpdates — priorité au
        // choix explicite d'un éditeur, sinon un article auto-publié).
        $schedule->command('facebook:post-daily')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        // Supprime définitivement TOUT article publié de plus de 48h,
        // y compris ceux écrits à la main — explicitement demandé, Le
        // Lampadaire n'est censé montrer que ~48h d'historique (voir
        // App\Console\Commands\PruneOldPublishedNews).
        $schedule->command('news:prune')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        // Un article générique "idées de business" par jour — contenu
        // toujours-valide, pas issu d'une source RSS (voir
        // App\Console\Commands\GenerateBusinessIdeaArticle — jamais de
        // montant/programme de subvention inventé).
        $schedule->command('news:generate-business-idea')
            ->daily()
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Seeder;

class TestNewsSeeder extends Seeder
{
    public function run()
    {
        $autherId = 1;
        $language = 'fr';

        $articles = [
            'Finance et Bourse' => [
                [
                    'title' => "Comprendre la BVMAC en 5 minutes : comment fonctionne la bourse d'Afrique centrale",
                    'content' => "<p>La Bourse des Valeurs Mobilières de l'Afrique Centrale (BVMAC) permet aux entreprises de lever des fonds auprès du public et aux particuliers d'investir dans ces sociétés. Concrètement, une entreprise cotée vend des actions ou des obligations, et les investisseurs — particuliers ou institutionnels — peuvent les acheter et les revendre.</p><p>Pour un particulier camerounais qui découvre le sujet, l'essentiel à retenir : investir en bourse n'exige pas des sommes importantes au départ, mais demande de comprendre les risques avant de se lancer. Nous reviendrons dans les prochaines semaines sur les étapes concrètes pour ouvrir un compte-titres.</p>",
                    'views' => 34,
                ],
                [
                    'title' => "Épargner ou investir ? Les bases pour les particuliers camerounais",
                    'content' => "<p>Beaucoup de Camerounais épargnent via des tontines ou des comptes bancaires classiques, mais peu connaissent les alternatives que propose le marché financier régional. Épargner protège le capital ; investir vise à le faire fructifier, avec une part de risque.</p><p>Avant de se lancer, les spécialistes recommandent de se constituer d'abord une épargne de précaution, puis de s'informer sérieusement sur les produits disponibles localement avant tout investissement.</p>",
                    'views' => 21,
                ],
            ],
            'Business' => [
                [
                    'title' => "Portrait : ces jeunes entrepreneurs camerounais qui misent sur le numérique",
                    'content' => "<p>De Douala à Yaoundé, une nouvelle génération d'entrepreneurs développe des solutions numériques adaptées aux réalités locales : livraison, paiement mobile, agro-plateformes. Leur point commun : partir de problèmes concrets rencontrés sur le terrain.</p><p>Ces initiatives restent souvent freinées par l'accès au financement et à des infrastructures fiables, mais elles témoignent d'un dynamisme entrepreneurial croissant dans le pays.</p>",
                    'views' => 47,
                ],
                [
                    'title' => "Comment financer son petit commerce sans passer par une banque",
                    'content' => "<p>Pour beaucoup de petits commerçants, l'accès au crédit bancaire classique reste difficile. Les tontines, les microfinances et certains programmes d'appui à l'entrepreneuriat offrent des alternatives concrètes, avec des conditions souvent plus adaptées aux petites structures.</p><p>Nous détaillerons dans un prochain article les critères à comparer avant de s'engager auprès d'un établissement de microfinance.</p>",
                    'views' => 18,
                ],
            ],
            'Santé & prévention' => [
                [
                    'title' => "Stupéfiants : ce que révèlent les derniers chiffres au Cameroun",
                    'content' => "<p>Les autorités sanitaires alertent régulièrement sur la consommation de substances psychoactives chez les jeunes, en particulier en milieu urbain. Les données disponibles restent partielles, mais les structures de prise en charge signalent une hausse des demandes d'accompagnement.</p><p>Cet article sera mis à jour au fur et à mesure que des données officielles complémentaires seront publiées. Notre traitement de ce sujet privilégie les sources vérifiées et un ton factuel.</p>",
                    'views' => 29,
                ],
                [
                    'title' => "Paludisme : les gestes de prévention à connaître avant la saison des pluies",
                    'content' => "<p>Avec l'arrivée de la saison des pluies, le risque de paludisme augmente sensiblement. Les moustiquaires imprégnées, l'élimination des eaux stagnantes autour des habitations et le dépistage rapide en cas de fièvre restent les mesures les plus efficaces.</p><p>Les centres de santé communautaires rappellent l'importance de consulter rapidement en cas de symptômes, plutôt que de recourir à l'automédication.</p>",
                    'views' => 40,
                ],
            ],
            'Politique & faits divers' => [
                [
                    'title' => "Ce qu'il faut retenir de la dernière session parlementaire",
                    'content' => "<p>La dernière session parlementaire a permis l'examen de plusieurs textes concernant le budget et les infrastructures locales. Les débats ont notamment porté sur la répartition des investissements entre régions.</p><p>Nous reviendrons prochainement en détail sur les textes adoptés et leurs implications concrètes pour les citoyens.</p>",
                    'views' => 15,
                ],
                [
                    'title' => "Sécurité routière à Yaoundé : les autorités annoncent de nouvelles mesures",
                    'content' => "<p>Face à la recrudescence des accidents de la route dans la capitale, les autorités municipales ont annoncé un renforcement des contrôles ainsi que des campagnes de sensibilisation auprès des conducteurs de moto-taxis.</p><p>Ces mesures s'inscrivent dans un plan plus large de sécurisation des axes urbains les plus fréquentés.</p>",
                    'views' => 23,
                ],
            ],
        ];

        foreach ($articles as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->where('language', $language)->first();

            if (! $category) {
                continue;
            }

            foreach ($items as $item) {
                if (News::where('title', $item['title'])->exists()) {
                    continue;
                }

                $news = new News();
                $news->language = $language;
                $news->category_id = $category->id;
                $news->auther_id = $autherId;
                $news->image = '';
                $news->title = $item['title'];
                $news->slug = \Str::slug($item['title']);
                $news->content = $item['content'];
                $news->meta_title = $item['title'];
                $news->meta_description = \Str::limit(strip_tags(explode('</p>', $item['content'])[0]), 250);
                $news->is_breaking_news = 0;
                $news->show_at_slider = 0;
                $news->show_at_popular = 1;
                $news->is_approved = 1;
                $news->status = 1;
                $news->views = $item['views'];
                $news->save();
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Seeder;

class InternationalSportNewsSeeder extends Seeder
{
    public function run()
    {
        $autherId = 1;
        $language = 'fr';

        $articles = [
            'International' => [
                [
                    'title' => "Sommet régional : les chefs d'État d'Afrique centrale se réunissent à Yaoundé",
                    'content' => "<p>Les dirigeants de la sous-région se sont retrouvés cette semaine pour discuter d'intégration économique et de coopération sécuritaire. Les discussions ont porté notamment sur la libre circulation des biens et des personnes au sein de la CEMAC.</p><p>Un communiqué final est attendu à l'issue des travaux, avec des engagements chiffrés sur les infrastructures transfrontalières.</p>",
                    'views' => 12,
                ],
                [
                    'title' => "La diaspora camerounaise se mobilise pour soutenir des projets locaux",
                    'content' => "<p>De plus en plus de Camerounais vivant à l'étranger investissent dans des projets communautaires au pays : écoles, centres de santé, initiatives agricoles. Cette dynamique s'appuie souvent sur des associations de ressortissants organisées par région d'origine.</p><p>Plusieurs de ces initiatives misent désormais sur le transfert d'argent mobile pour simplifier les contributions depuis l'étranger.</p>",
                    'views' => 9,
                ],
            ],
            'Sport' => [
                [
                    'title' => "Les Lions Indomptables préparent les prochaines échéances continentales",
                    'content' => "<p>La sélection nationale a repris l'entraînement en vue des prochaines rencontres officielles. Le staff technique a convoqué plusieurs joueurs évoluant à l'étranger, aux côtés de talents du championnat local.</p><p>L'objectif affiché est clair : consolider les acquis et préparer sereinement les qualifications à venir.</p>",
                    'views' => 21,
                ],
                [
                    'title' => "Basketball : le championnat national féminin prend de l'ampleur",
                    'content' => "<p>La ligue féminine de basketball attire un public grandissant dans plusieurs grandes villes du pays. Les clubs investissent davantage dans la formation des jeunes joueuses, avec l'appui de quelques sponsors locaux.</p><p>Plusieurs observateurs y voient un vivier prometteur pour les compétitions régionales à venir.</p>",
                    'views' => 15,
                ],
            ],
        ];

        $imageIndex = 1;

        foreach ($articles as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->where('language', $language)->first();

            if (! $category) {
                continue;
            }

            foreach ($items as $item) {
                if (News::where('title', $item['title'])->exists()) {
                    $imageIndex++;
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

                $imageIndex++;
            }
        }
    }
}

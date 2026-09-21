<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\NewspaperEditionBuilder;
use Dompdf\Dompdf;
use Dompdf\Options;

class NewspaperController extends Controller
{
    /**
     * Generate and download a classic-newspaper-style PDF: a cover ("Une")
     * followed by one inner page per theme grouping, each with a feature
     * story and a small grid of secondary stories — real photos, real
     * excerpts, no invented stats/quotes/checklists (unlike a generic
     * template mockup, we don't fabricate data the underlying article
     * doesn't actually contain). See NewspaperEditionBuilder for how
     * articles are picked.
     *
     * Deliberately does NOT call Gemini here: writing a clean print excerpt
     * for up to 8 articles can take well over a web request's execution
     * time limit. That happens ahead of time instead, via the
     * news:generate-print-excerpts scheduled command (see
     * GeneratePrintExcerpts) — this method only ever reads whatever's
     * already cached on News::print_excerpt, falling back to the raw
     * article body when it isn't there yet (see newspaper.blade.php's
     * $printText), so a download is always fast.
     */
    public function download(NewspaperEditionBuilder $editionBuilder)
    {
        $edition = $editionBuilder->build();
        $lead = $edition['lead'];
        $teasers = $edition['teasers'];
        $pages = $edition['pages'];

        $settings = [
            'site_name' => config('app.name'),
        ];

        $html = view('frontend.newspaper', compact('lead', 'teasers', 'pages', 'settings'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Georgia');
        // dompdf refuses to read local files outside its chroot by default; allow the
        // storage/public disk so article images can be embedded in the PDF.
        $options->set('chroot', [public_path(), storage_path()]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        // Neither A4 (842pt) nor A3: both are taller than this layout
        // actually needs, leaving visible blank space at the bottom of
        // every page. dompdf applies one page size to the whole document
        // (no per-page CSS sizing), so this is one height that fits every
        // page type — the cover (masthead + lead + teasers + banner) is the
        // tallest, and every inner page has exactly one category section
        // (see NewspaperEditionBuilder's $pageGroups) so none of them need
        // more room than that.
        // 760pt found empirically: rendered and measured at several
        // heights (700-820pt) until the cover stopped spilling onto a
        // second physical page, then kept a small safety margin above that
        // threshold (750pt still overflowed) rather than shaving it razor
        //-thin — a slightly longer article some day shouldn't silently
        // break the layout again. Width stays A4's own (595pt / 210mm)
        // since the column layout is tuned to it.
        $dompdf->setPaper([0, 0, 595, 760], 'portrait');
        $dompdf->render();

        return $dompdf->stream('journal-' . now()->format('Y-m-d') . '.pdf', [
            'Attachment' => true,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

/**
 * PDFGeneratorService
 *
 * Renders the listing flyer template, then hands it to whichever PDF
 * engine is installed (preferred order: barryvdh/laravel-dompdf → raw
 * dompdf/dompdf). When neither is available the service falls back
 * to returning the printable HTML directly — that way the feature
 * still works on a fresh clone and the operator just gets a "Save as
 * PDF" experience from their browser until they `composer require`
 * a PDF backend.
 *
 * Themes: orange, blue, green, red, purple, gold.
 * Orientations: portrait, landscape.
 *
 * Each flyer embeds a QR code pointing at the listing URL — generated
 * via the publicly-fetchable goqr.me image API (no PHP dependency
 * required), with a graceful "no image" placeholder if the network
 * is unavailable when dompdf fetches it.
 */
class PDFGeneratorService
{
    public const THEMES = ['orange', 'blue', 'green', 'red', 'purple', 'gold'];
    public const ORIENTATIONS = ['portrait', 'landscape'];

    public function generateListingFlyer(
        Listing $listing,
        ?string $theme = null,
        ?string $orientation = null,
    ): Response {
        $theme = in_array($theme, self::THEMES, true)
            ? $theme
            : (string) get_setting('flyer_default_theme', 'orange');

        $orientation = in_array($orientation, self::ORIENTATIONS, true)
            ? $orientation
            : (string) get_setting('flyer_default_orientation', 'portrait');

        $html = View::make('flyers.listing', [
            'listing'     => $listing,
            'theme'       => $theme,
            'orientation' => $orientation,
            'qrUrl'       => $this->qrUrlFor(route('listings.show', $listing->slug)),
            'colors'      => $this->themeColors($theme),
            'siteName'    => trim((string) get_setting('site_title', config('app.name'))),
            'contactPhone'=> trim((string) get_setting('contact_phone', '')),
            'contactEmail'=> trim((string) get_setting('contact_email', '')),
        ])->render();

        $filename = 'flyer-' . preg_replace('/[^A-Za-z0-9_-]/', '_', $listing->slug ?? $listing->id) . '.pdf';

        // --- 1) barryvdh/laravel-dompdf (preferred)
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', $orientation);
            return $pdf->download($filename);
        }

        // --- 2) raw dompdf/dompdf
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', $orientation);
            $dompdf->render();
            return response($dompdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // --- 3) HTML fallback (browser → Print → Save as PDF)
        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * QR code via goqr.me — no PHP dependency required. dompdf fetches
     * it as a remote image when isRemoteEnabled is true.
     */
    public function qrUrlFor(string $payload): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&format=png&data='
            . urlencode($payload);
    }

    /**
     * Resolve the active theme to a 3-colour palette the Blade
     * template can splat into inline styles. Hex values chosen for
     * print contrast on white paper.
     */
    public function themeColors(string $theme): array
    {
        return match ($theme) {
            'blue'   => ['primary' => '#1D4ED8', 'dark' => '#1E3A8A', 'accent' => '#DBEAFE'],
            'green'  => ['primary' => '#1E6F5C', 'dark' => '#13493E', 'accent' => '#D1FAE5'],
            'red'    => ['primary' => '#DC2626', 'dark' => '#991B1B', 'accent' => '#FEE2E2'],
            'purple' => ['primary' => '#7C3AED', 'dark' => '#5B21B6', 'accent' => '#EDE9FE'],
            'gold'   => ['primary' => '#B45309', 'dark' => '#78350F', 'accent' => '#FEF3C7'],
            default  => ['primary' => '#EA580C', 'dark' => '#9A3412', 'accent' => '#FFEDD5'], // orange
        };
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Response;

/**
 * Turkish real-estate portal XML feed publisher.
 *
 * Portals (Sahibinden, Emlakjet, Hurriyet Emlak, Hepsihome, Zingat …)
 * accept an XML feed URL from corporate members. The portal's
 * indexer crawls the URL on a schedule (typically hourly) and
 * mirrors the listings into their own system. Fields and format
 * differ between portals — this controller emits per-portal XML
 * shapes so a single admin can register the same base URL with all
 * of them.
 *
 * Public routes:
 *   GET /feed/emlakjet.xml    → Emlakjet flavour
 *   GET /feed/sahibinden.xml  → Sahibinden flavour (approx; each
 *                                merchant gets a bespoke schema, so
 *                                verify with your corporate contact)
 *   GET /feed/hurriyet.xml    → Hurriyet Emlak flavour
 *   GET /feed/generic.xml     → Generic RETS-like fallback for other
 *                                portals (Hepsihome, Zingat, İhale)
 *
 * SECURITY: All feeds are unauthenticated (portals crawl anonymously)
 * but include ONLY approved + active + non-expired listings. No
 * personal data or internal notes are emitted.
 */
class PortalFeedController extends Controller
{
    /** Emit an Emlakjet-flavoured XML feed. */
    public function emlakjet(): Response
    {
        return $this->xml('feeds.emlakjet', ['listings' => $this->publishableListings()]);
    }

    /** Emit a Sahibinden-flavoured XML feed. */
    public function sahibinden(): Response
    {
        return $this->xml('feeds.sahibinden', ['listings' => $this->publishableListings()]);
    }

    /** Emit a Hurriyet Emlak flavoured XML feed. */
    public function hurriyet(): Response
    {
        return $this->xml('feeds.hurriyet', ['listings' => $this->publishableListings()]);
    }

    /** Generic RETS-like fallback — Hepsihome / Zingat / İhale. */
    public function generic(): Response
    {
        return $this->xml('feeds.generic', ['listings' => $this->publishableListings()]);
    }

    /**
     * Active + approved listings with the relations the templates use.
     * `limit` caps at 50k to keep XML render under portal timeouts;
     * split into multiple feed URLs if you exceed this.
     */
    protected function publishableListings()
    {
        return Listing::with(['category.descriptions', 'city', 'district', 'neighborhood', 'images'])
            ->where('status', 'active')
            ->where('is_approved', true)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('updated_at')
            ->limit(50000)
            ->get();
    }

    protected function xml(string $view, array $data): Response
    {
        $xml = view($view, $data)->render();
        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}

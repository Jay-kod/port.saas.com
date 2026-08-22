<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use App\Models\Profile;
use App\Services\CurrentProfileResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase 3 & Phase 6 (docs/agents/04-THEMING-DOMAINS.md):
 * Resolves the tenant profile either from a verified custom domain Host header
 * or from the {slug} route parameter in SAAS_MODE=true.
 */
class ResolveTenantFromSlug
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower(trim($request->getHost()));

        // 1. Attempt resolution via verified custom domain
        $domain = Domain::query()
            ->with(['profile.account', 'profile.theme'])
            ->where('domain', $host)
            ->whereNotNull('verified_at')
            ->first();

        if ($domain) {
            $profile = $domain->profile;
            abort_unless($profile && $profile->is_published, 404);

            app(CurrentProfileResolver::class)->setResolved($profile);
            URL::defaults(['slug' => $profile->slug]);

            return $next($request);
        }

        // 2. Fall back to {slug} route parameter on platform domain
        $slug = $request->route('slug');

        if ($slug) {
            $profile = Profile::query()
                ->with(['account', 'theme'])
                ->where('slug', $slug)
                ->where('is_published', true)
                ->first();

            abort_unless($profile, 404);

            app(CurrentProfileResolver::class)->setResolved($profile);
            URL::defaults(['slug' => $profile->slug]);

            return $next($request);
        }

        // If neither custom domain nor slug matched on a tenant-scoped route, abort 404
        abort(404);
    }
}

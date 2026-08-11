<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\Project;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $content = Cache::remember('seo:robots', now()->addHours(6), function () {
            $sitemapUrl = url('/sitemap.xml');

            return implode("\n", [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin',
                'Disallow: /login',
                'Disallow: /register',
                'Disallow: /password',
                'Disallow: /email',
                '',
                "Sitemap: {$sitemapUrl}",
                '',
            ]);
        });

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function sitemap(): Response
    {
        $xml = Cache::remember('seo:sitemap', now()->addHours(6), function () {
            $static = [
                ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
                ['loc' => url('/about'), 'priority' => '0.8', 'changefreq' => 'monthly'],
                ['loc' => url('/services'), 'priority' => '0.8', 'changefreq' => 'monthly'],
                ['loc' => url('/projects'), 'priority' => '0.9', 'changefreq' => 'weekly'],
                ['loc' => url('/career'), 'priority' => '0.8', 'changefreq' => 'monthly'],
                ['loc' => url('/contact'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ];

            $projectUrls = Project::query()
                ->latest('updated_at')
                ->get(['slug', 'updated_at'])
                ->map(function (Project $p) {
                    return [
                        'loc' => url('/projects/' . $p->slug),
                        'lastmod' => $p->updated_at ? $p->updated_at->toAtomString() : now()->toAtomString(),
                        'priority' => '0.7',
                        'changefreq' => 'monthly',
                    ];
                });

            $serviceUrls = \App\Models\Service::query()
                ->latest('updated_at')
                ->get(['slug', 'updated_at'])
                ->map(function ($s) {
                    return [
                        'loc' => url('/services/' . $s->slug),
                        'lastmod' => $s->updated_at ? $s->updated_at->toAtomString() : now()->toAtomString(),
                        'priority' => '0.7',
                        'changefreq' => 'monthly',
                    ];
                });

            $careerUrls = \App\Models\Career::query()
                ->where('is_active', true)
                ->latest('updated_at')
                ->get(['slug', 'updated_at'])
                ->map(function ($c) {
                    return [
                        'loc' => url('/career/' . $c->slug),
                        'lastmod' => $c->updated_at ? $c->updated_at->toAtomString() : now()->toAtomString(),
                        'priority' => '0.6',
                        'changefreq' => 'monthly',
                    ];
                });

            $dynamicUrls = $projectUrls->merge($serviceUrls)->merge($careerUrls);

            $items = collect($static)->map(function (array $row) {
                $loc = e($row['loc']);
                $lastmod = now()->toAtomString();

                return implode('', [
                    '<url>',
                    "<loc>{$loc}</loc>",
                    "<lastmod>{$lastmod}</lastmod>",
                    "<changefreq>{$row['changefreq']}</changefreq>",
                    "<priority>{$row['priority']}</priority>",
                    '</url>',
                ]);
            })->merge($dynamicUrls->map(function (array $row) {
                $loc = e($row['loc']);
                $lastmod = e($row['lastmod']);

                return implode('', [
                    '<url>',
                    "<loc>{$loc}</loc>",
                    "<lastmod>{$lastmod}</lastmod>",
                    "<changefreq>{$row['changefreq']}</changefreq>",
                    "<priority>{$row['priority']}</priority>",
                    '</url>',
                ]);
            }))->implode('');

            return implode('', [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
                $items,
                '</urlset>',
            ]);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Support\PublicSeo;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function page(Request $request)
    {
        $seo = PublicSeo::page($request->path());

        return view('welcome', PublicSeo::fallbackViewData($seo));
    }

    public function subject(Subject $subject)
    {
        $seo = PublicSeo::subjectPage($subject);

        return view('welcome', PublicSeo::fallbackViewData($seo));
    }

    public function robots()
    {
        return response(
            file_get_contents(public_path('robots.txt')),
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    public function sitemap()
    {
        return response()
            ->view('sitemap', ['entries' => PublicSeo::sitemapEntries()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function spaFallback(Request $request)
    {
        $seo = PublicSeo::page($request->path());

        return view('welcome', PublicSeo::fallbackViewData($seo));
    }
}

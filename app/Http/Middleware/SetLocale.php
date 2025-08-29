<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Closure): (\Illuminate\Http\Response|\Illuminate\Http\Response)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and has language preference
        if (Auth::check() && Auth::user()->language) {
            $locale = Auth::user()->language;
        }
        // Check if language is set in session
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
        }
        // Check browser language
        else {
            $browserLanguage = substr($request->header('Accept-Language', 'en'), 0, 2);
            
            // Map browser languages to supported languages
            $languageMap = [
                'tr' => 'tr',
                'en' => 'en',
                'de' => 'en', // German -> English
                'fr' => 'en', // French -> English
                'es' => 'en', // Spanish -> English
                'it' => 'en', // Italian -> English
                'pt' => 'en', // Portuguese -> English
                'ru' => 'en', // Russian -> English
                'ar' => 'en', // Arabic -> English
                'zh' => 'en', // Chinese -> English
                'ja' => 'en', // Japanese -> English
                'ko' => 'en', // Korean -> English
            ];
            
            $locale = $languageMap[$browserLanguage] ?? 'en';
            
            // Store in session for future requests
            Session::put('locale', $locale);
        }
        
        // Validate locale
        $allowedLocales = ['tr', 'en'];
        if (!in_array($locale, $allowedLocales)) {
            $locale = 'en';
        }
        
        // Set application locale
        App::setLocale($locale);
        
        return $next($request);
    }
}

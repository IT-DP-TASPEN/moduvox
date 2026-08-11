<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageSettingController extends Controller
{
    private const FILE_KEYS = [
        'home' => ['home_hero_video', 'home_hero_video_poster'],
        'project' => [],
        'about' => ['about_org_structure', 'about_hero_image', 'about_facility_office', 'about_facility_workshop', 'about_facility_activity'],
        'services' => [],
        'contact' => ['contact_image', 'contact_hero_image', 'contact_hero_video', 'contact_facility_images'],
        'career' => ['career_hero_image'],
    ];

    private const RULES = [
        'home' => [
            'home_hero_video' => 'nullable|file|mimes:mp4,webm|max:20480',
            'home_hero_video_poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'home_hero_title' => 'nullable|string|max:255',
            'home_hero_subtitle' => 'nullable|string|max:255',
            'home_about_title' => 'nullable|string|max:255',
            'home_about_desc' => 'nullable|string|max:5000',
            'home_vision' => 'nullable|string|max:5000',
            'home_mission' => 'nullable|string|max:5000',
            'home_stats_years' => 'nullable|string|max:20',
            'home_stats_projects' => 'nullable|string|max:20',
            'home_stats_clients' => 'nullable|string|max:20',
            'home_stats_regions' => 'nullable|string|max:20',
        ],
        'project' => [
            'project_hero_title' => 'nullable|string|max:255',
            'project_hero_desc' => 'nullable|string|max:5000',
            'project_cta_title' => 'nullable|string|max:255',
            'project_cta_desc' => 'nullable|string|max:5000',
        ],
        'about' => [
            'about_hero_title' => 'nullable|string|max:255',
            'about_hero_desc' => 'nullable|string|max:5000',
            'about_story_title' => 'nullable|string|max:255',
            'about_story_desc' => 'nullable|string|max:8000',
            'about_vision' => 'nullable|string|max:5000',
            'about_mission' => 'nullable|string|max:8000',
            'about_journey' => 'nullable|string|max:8000',
            'about_org_structure' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'about_hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'about_facility_office' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'about_facility_workshop' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'about_facility_activity' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ],
        'services' => [
            'services_hero_title' => 'nullable|string|max:255',
            'services_hero_desc' => 'nullable|string|max:5000',
            'services_cta_title' => 'nullable|string|max:255',
            'services_cta_desc' => 'nullable|string|max:5000',
        ],
        'contact' => [
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_whatsapp' => 'nullable|string|max:255',
            'contact_address' => 'nullable|string|max:5000',
            'contact_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'contact_map' => 'nullable|string|max:5000',
            'contact_hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'contact_hero_video' => 'nullable|file|mimes:mp4,webm|max:20480',
            'contact_facility_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ],
        'career' => [
            'career_hero_title' => 'nullable|string|max:255',
            'career_hero_desc' => 'nullable|string|max:1000',
            'career_hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ],
    ];

    public function home()
    {
        $settings = PageSetting::where('page', 'home')->pluck('value', 'key')->toArray();
        return view('admin.pages.home', compact('settings'));
    }

    public function project()
    {
        $settings = PageSetting::where('page', 'project')->pluck('value', 'key')->toArray();
        return view('admin.pages.project', compact('settings'));
    }

    public function about()
    {
        $settings = PageSetting::where('page', 'about')->pluck('value', 'key')->toArray();
        return view('admin.pages.about', compact('settings'));
    }

    public function services()
    {
        $settings = PageSetting::where('page', 'services')->pluck('value', 'key')->toArray();
        return view('admin.pages.services', compact('settings'));
    }

    public function contact()
    {
        $settings = PageSetting::where('page', 'contact')->pluck('value', 'key')->toArray();
        return view('admin.pages.contact', compact('settings'));
    }

    public function career()
    {
        $settings = PageSetting::where('page', 'career')->pluck('value', 'key')->toArray();
        return view('admin.pages.career', compact('settings'));
    }

    public function store(Request $request, $page)
    {
        if (!array_key_exists($page, self::RULES)) {
            abort(404);
        }

        $validated = $request->validate(self::RULES[$page]);
        $fileKeys = self::FILE_KEYS[$page] ?? [];

        foreach (array_keys(self::RULES[$page]) as $key) {
            if (in_array($key, $fileKeys, true)) {
                if (!$request->hasFile($key)) {
                    continue;
                }

                $existing = PageSetting::where('page', $page)->where('key', $key)->first();
                if ($existing && is_string($existing->value) && Str::startsWith($existing->value, "settings/{$page}/")) {
                    Storage::disk('public')->delete($existing->value);
                }

                $storedPath = $request->file($key)->store("settings/{$page}", 'public');
                PageSetting::updateOrCreate(
                    ['page' => $page, 'key' => $key],
                    ['value' => $storedPath]
                );

                cache()->forget("pagesetting:{$page}:{$key}");

                continue;
            }

            if (!array_key_exists($key, $validated)) {
                continue;
            }

            $value = $validated[$key] ?? '';

            PageSetting::updateOrCreate(
                ['page' => $page, 'key' => $key],
                ['value' => $value]
            );

            cache()->forget("pagesetting:{$page}:{$key}");
        }

        // Handle Multi-Image for Facility Showcase (Contact Page)
        if ($page === 'contact' && $request->hasFile('contact_facility_images')) {
            $existingImagesSetting = PageSetting::where('page', $page)->where('key', 'contact_facility_images')->first();
            $images = $existingImagesSetting ? json_decode($existingImagesSetting->value, true) : [];
            if (!is_array($images)) $images = [];
            
            foreach ($request->file('contact_facility_images') as $file) {
                $path = $file->store("settings/contact/facilities", 'public');
                $images[] = $path;
            }
            
            PageSetting::updateOrCreate(
                ['page' => $page, 'key' => 'contact_facility_images'],
                ['value' => json_encode($images)]
            );
            cache()->forget("pagesetting:{$page}:contact_facility_images");
        }

        return back()->with('success', 'Perubahan pengaturan halaman berhasil disimpan.');
    }

    public function destroySetting(Request $request, $page, $key)
    {
        if (!array_key_exists($page, self::RULES)) {
            abort(404);
        }

        $existing = PageSetting::where('page', $page)->where('key', $key)->first();
        
        if ($existing) {
            // Special handling for Facility Images (JSON array)
            if ($key === 'contact_facility_images' && $request->has('image_path')) {
                $images = json_decode($existing->value, true) ?: [];
                $target = $request->image_path;
                
                if (($index = array_search($target, $images)) !== false) {
                    Storage::disk('public')->delete($target);
                    unset($images[$index]);
                    $existing->update(['value' => json_encode(array_values($images))]);
                }
            } else {
                // Single file deletion
                if (is_string($existing->value) && Storage::disk('public')->exists($existing->value)) {
                    Storage::disk('public')->delete($existing->value);
                }
                $existing->update(['value' => '']);
            }
            
            cache()->forget("pagesetting:{$page}:{$key}");
        }

        return back()->with('success', 'Aset berhasil dihapus.');
    }
}

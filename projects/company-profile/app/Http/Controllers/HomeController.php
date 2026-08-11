<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use App\Models\Client;
use App\Models\Career;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // Disable cache for debugging 500 error
        $projects = Project::query()
            ->latest('updated_at')
            ->take(6)
            ->get(['id', 'title', 'slug', 'category', 'description', 'image', 'is_featured', 'created_at', 'location', 'year', 'client_name']);

        $services = Service::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'icon', 'image']);

        $clients = Client::query()
            ->orderBy('name')
            ->get(['id', 'name', 'logo']);

        return view('frontend.home', compact('projects', 'services', 'clients'));
    }

    public function about()
    {
        return view('frontend.about');
    }

    public function services()
    {
        $services = Service::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'icon', 'image']);

        return view('frontend.services', compact('services'));
    }

    public function serviceShow($slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();
        $related = Service::where('id', '!=', $service->id)->take(3)->get(['id', 'name', 'slug', 'icon']);
        return view('frontend.service-show', compact('service', 'related'));
    }

    public function projects()
    {
        $projects = Project::query()
            ->latest('updated_at')
            ->select(['id', 'title', 'slug', 'category', 'description', 'image', 'is_featured', 'created_at', 'location', 'year', 'client_name'])
            ->paginate(9);

        $categories = Project::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('frontend.projects', compact('projects', 'categories'));
    }

    public function contact()
    {
        $services = Service::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'icon', 'image']);

        return view('frontend.contact', compact('services'));
    }

    public function projectShow(Project $project)
    {
        $related = Project::query()
            ->where('id', '!=', $project->id)
            ->where('category', $project->category)
            ->latest()
            ->take(6)
            ->get(['id', 'title', 'slug', 'category', 'description', 'image', 'created_at', 'location', 'year', 'client_name']);

        return view('frontend.project-show', compact('project', 'related'));
    }

    public function career()
    {
        $careers = Career::where('is_active', true)->latest()->get();
        return view('frontend.career', compact('careers'));
    }

    public function careerShow($slug)
    {
        $career = Career::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('frontend.career-show', compact('career'));
    }
}

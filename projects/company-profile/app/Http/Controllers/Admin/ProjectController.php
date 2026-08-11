<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::latest()->paginate(10);
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'client_name'   => 'nullable|string|max:255',
            'location'      => 'nullable|string|max:255',
            'year'          => 'nullable|string|max:20',
            'category'      => 'required|string|max:255',
            'description'   => 'nullable|string',
            'project_scope' => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery'       => 'nullable|array',
            'gallery.*'     => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_featured'   => 'nullable',
        ]);

        $data = $request->only('title', 'client_name', 'location', 'year', 'category', 'description', 'project_scope');
        $data['is_featured'] = $request->has('is_featured');
        $data['slug'] = $this->makeUniqueSlug((string) $data['title']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('projects', 'public');
        }

        $project = Project::create($data);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $path = $image->store('projects/gallery', 'public');
                $project->images()->create(['image_path' => $path]);
            }
        }
        Cache::forget('home:projects:latest6:v2');
        Cache::forget('projects:categories');
        Cache::forget('seo:sitemap');
        return redirect()->route('admin.projects.index')->with('success', 'Data proyek berhasil disimpan.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'client_name'   => 'nullable|string|max:255',
            'location'      => 'nullable|string|max:255',
            'year'          => 'nullable|string|max:20',
            'category'      => 'required|string|max:255',
            'description'   => 'nullable|string',
            'project_scope' => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery'       => 'nullable|array',
            'gallery.*'     => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_featured'   => 'nullable',
        ]);

        $data = $request->only('title', 'client_name', 'location', 'year', 'category', 'description', 'project_scope');
        $data['is_featured'] = $request->has('is_featured');

        if ((string) $project->title !== (string) $data['title']) {
            $data['slug'] = $this->makeUniqueSlug((string) $data['title'], $project->id);
        }

        if ($request->hasFile('image')) {
            if ($project->image) Storage::disk('public')->delete($project->image);
            $data['image'] = $request->file('image')->store('projects', 'public');
        }

        $project->update($data);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $path = $image->store('projects/gallery', 'public');
                $project->images()->create(['image_path' => $path]);
            }
        }
        Cache::forget('home:projects:latest6:v2');
        Cache::forget('projects:categories');
        Cache::forget('seo:sitemap');
        Cache::forget("project:related:{$project->id}");
        return redirect()->route('admin.projects.index')->with('success', 'Perubahan data proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        if ($project->image) Storage::disk('public')->delete($project->image);
        
        foreach ($project->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        
        $project->delete();
        Cache::forget('home:projects:latest6:v2');
        Cache::forget('projects:categories');
        Cache::forget('seo:sitemap');
        Cache::forget("project:related:{$project->id}");
        return redirect()->route('admin.projects.index')->with('success', 'Data proyek berhasil dihapus.');
    }

    public function deleteGalleryImage(ProjectImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        return back()->with('success', 'Gambar galeri berhasil dihapus.');
    }

    private function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'project';
        }

        $slug = $base;
        $i = 2;
        while (
            Project::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}

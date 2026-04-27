<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $country = (string) $request->query('country', 'all');
        if (! in_array($country, ['all', 'kz', 'ru', 'by'], true)) {
            $country = 'all';
        }

        $projects = collect(config('realbrick-projects.projects', []))->values();
        if ($country !== 'all') {
            $projects = $projects->where('country', $country)->values();
        }

        $perPage = max(1, (int) config('realbrick-projects.per_page', 4));
        $currentPage = max(1, (int) $request->query('page', 1));
        $totalPages = max(1, (int) ceil($projects->count() / $perPage));
        $currentPage = min($currentPage, $totalPages);

        return view('real-brick.projects', [
            'country' => $country,
            'page' => $currentPage,
            'totalPages' => $totalPages,
            'projects' => $projects->forPage($currentPage, $perPage)->values()->all(),
            'tabs' => config('realbrick-projects.tabs', []),
        ]);
    }

    public function show(string $slug)
    {
        $project = collect(config('realbrick-projects.projects', []))
            ->firstWhere('slug', $slug);

        abort_unless($project, 404);

        return view('real-brick.project-show', [
            'project' => $project,
        ]);
    }
}

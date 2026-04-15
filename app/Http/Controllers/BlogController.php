<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $topic = trim((string) $request->query('topic', ''));
        $page = max((int) $request->query('page', 1), 1);

        $baseQuery = BlogPost::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->orderBy('sort_order');

        $allTopics = BlogPost::query()
            ->where('is_published', true)
            ->whereNotNull('topic')
            ->where('topic', '!=', '')
            ->distinct()
            ->orderBy('topic')
            ->pluck('topic')
            ->values()
            ->all();

        if ($topic !== '') {
            $baseQuery->where('topic', $topic);
        }

        $featuredPost = (clone $baseQuery)->first();

        $cardsQuery = clone $baseQuery;
        if ($featuredPost) {
            $cardsQuery->where('id', '!=', $featuredPost->id);
        }

        $posts = $cardsQuery
            ->paginate(6, ['*'], 'page', $page)
            ->withQueryString();

        return view('real-brick.index', [
            'page' => 'blog',
            'featuredPost' => $featuredPost,
            'blogPosts' => $posts,
            'blogTopics' => $allTopics,
            'activeTopic' => $topic,
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::query()
            ->where('is_published', true)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('real-brick.index', [
            'page' => 'blog-post',
            'blogPost' => $post,
        ]);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $collection = trim((string) $request->query('collection', ''));
        $color = trim((string) $request->query('color', ''));

        $baseQuery = GalleryPhoto::query()
            ->where('is_active', true);

        if ($collection !== '') {
            $baseQuery->where('collection_type', $collection);
        }
        if ($color !== '') {
            $baseQuery->where('color', $color);
        }

        $collections = GalleryPhoto::query()
            ->where('is_active', true)
            ->distinct()
            ->orderBy('collection_type')
            ->pluck('collection_type')
            ->values();
        
        $colors = GalleryPhoto::query()
            ->where('is_active', true)
            ->distinct()
            ->orderBy('color')
            ->pluck('color')
            ->values();

        $featuredPhoto = (clone $baseQuery)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->first();
            
        if (! $featuredPhoto) {
            $featuredPhoto = (clone $baseQuery)
                ->orderBy('sort_order')
                ->first();
        }

        $cardsQuery = clone $baseQuery;
        if ($featuredPhoto) {
            $cardsQuery->where('id', '!=', $featuredPhoto->id);
        }

        $galleryCards = $cardsQuery
            ->orderBy('sort_order')
            ->paginate(6)
            ->withQueryString();

        return view('real-brick.gallery.index', [
            'featuredPhoto' => $featuredPhoto,
            'galleryCards' => $galleryCards,
            'collections' => $collections,
            'colors' => $colors,
            'activeCollection' => $collection,
            'activeColor' => $color,
        ]);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\KnowledgePost;

class KnowledgeController extends Controller
{
    public function index()
    {
        $posts = KnowledgePost::where('is_published', true)->orderBy('sort_order')->latest('published_at')->paginate(12);

        return view('knowledge.index', compact('posts'));
    }

    public function show(KnowledgePost $knowledgePost)
    {
        abort_unless($knowledgePost->is_published || auth()->user()?->isAdmin(), 404);

        return view('knowledge.show', compact('knowledgePost'));
    }
}

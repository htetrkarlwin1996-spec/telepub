<?php

namespace App\Http\Controllers;

use App\Models\KnowledgePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminKnowledgeController extends Controller
{
    public function index()
    {
        return view('admin.knowledge.index', ['posts' => KnowledgePost::orderBy('sort_order')->latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.knowledge.form', ['post' => new KnowledgePost]);
    }

    public function store(Request $request)
    {
        $post = new KnowledgePost;
        $this->save($request, $post);

        return redirect()->route('admin.knowledge.index')->with('success', 'Knowledge post created.');
    }

    public function edit(KnowledgePost $knowledgePost)
    {
        return view('admin.knowledge.form', ['post' => $knowledgePost]);
    }

    public function update(Request $request, KnowledgePost $knowledgePost)
    {
        $this->save($request, $knowledgePost);

        return redirect()->route('admin.knowledge.index')->with('success', 'Knowledge post updated.');
    }

    public function destroy(KnowledgePost $knowledgePost)
    {
        if ($knowledgePost->cover_image && ! str_starts_with($knowledgePost->cover_image, 'images/knowledge/')) {
            Storage::disk('public')->delete($knowledgePost->cover_image);
        }
        $knowledgePost->delete();

        return back()->with('success', 'Knowledge post deleted.');
    }

    private function save(Request $request, KnowledgePost $post): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'], 'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'is_published' => ['nullable', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        if ($request->hasFile('cover_image')) {
            if ($post->cover_image && ! str_starts_with($post->cover_image, 'images/knowledge/')) Storage::disk('public')->delete($post->cover_image);
            $validated['cover_image'] = $request->file('cover_image')->store('knowledge', 'public');
        } else {
            unset($validated['cover_image']);
        }
        $validated['is_published'] = (bool) ($validated['is_published'] ?? false);
        $validated['slug'] = $this->uniqueSlug($validated['title'], $post->id);
        $validated['author_id'] = $request->user()->id;
        $validated['published_at'] = $validated['is_published'] ? ($post->published_at ?? now()) : null;
        $post->fill($validated)->save();
    }

    private function uniqueSlug(string $title, ?int $ignoreId): string
    {
        $base = Str::slug($title) ?: 'knowledge-post'; $slug = $base; $counter = 2;
        while (KnowledgePost::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) $slug = $base.'-'.$counter++;
        return $slug;
    }
}

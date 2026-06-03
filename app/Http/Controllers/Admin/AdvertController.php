<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdvertController extends Controller
{
    public function index(): Response
    {
        $adverts = Advert::latest()->paginate(15);

        return Inertia::render('Admin/Adverts/Index', [
            'adverts' => $adverts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('adverts', 'public');
        }

        Advert::create($validated);

        return back()->with('success', 'Advertisement created successfully.');
    }

    public function update(Request $request, Advert $advert)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('adverts', 'public');
        }

        $advert->update($validated);

        return back()->with('success', 'Advertisement updated successfully.');
    }

    public function destroy(Advert $advert)
    {
        $advert->delete();
        return back()->with('success', 'Advertisement deleted successfully.');
    }

    public function toggle(Advert $advert)
    {
        $advert->update(['is_active' => !$advert->is_active]);
        return back()->with('success', 'Advertisement status updated.');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertController extends Controller
{
    /**
     * Get all active adverts for users
     */
    public function index()
    {
        $adverts = Advert::active()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($adverts);
    }

    /**
     * Get all adverts for admin (including inactive/expired)
     */
    public function adminIndex()
    {
        $adverts = Advert::orderBy('created_at', 'desc')->get();
        return response()->json($adverts);
    }

    /**
     * Store a new advert
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['title', 'description', 'start_date', 'end_date', 'is_active']);
        $data['tenant_id'] = $request->user()->tenant_id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('adverts', 'public');
            $data['image_path'] = $path;
        }

        $advert = Advert::create($data);

        return response()->json([
            'message' => 'Advert created successfully',
            'advert' => $advert,
        ], 201);
    }

    /**
     * Update an advert
     */
    public function update(Request $request, $id)
    {
        $advert = Advert::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['title', 'description', 'start_date', 'end_date', 'is_active']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($advert->image_path) {
                Storage::disk('public')->delete($advert->image_path);
            }
            $path = $request->file('image')->store('adverts', 'public');
            $data['image_path'] = $path;
        }

        $advert->update($data);

        return response()->json([
            'message' => 'Advert updated successfully',
            'advert' => $advert,
        ]);
    }

    /**
     * Delete an advert
     */
    public function destroy($id)
    {
        $advert = Advert::findOrFail($id);

        // Delete image if exists
        if ($advert->image_path) {
            Storage::disk('public')->delete($advert->image_path);
        }

        $advert->delete();

        return response()->json([
            'message' => 'Advert deleted successfully',
        ]);
    }
}

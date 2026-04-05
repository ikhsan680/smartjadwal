<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MapelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $mapel = Mapel::with('guru')->get();
            return response()->json([
                'success' => true,
                'data' => $mapel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255|unique:mapel,nama',
            ]);

            $mapel = Mapel::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil ditambahkan',
                'data' => $mapel
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $mapel = Mapel::with('guru')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $mapel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mata pelajaran tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $mapel = Mapel::findOrFail($id);

            $validated = $request->validate([
                'nama' => 'required|string|max:255|unique:mapel,nama,' . $id,
            ]);

            $mapel->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil diperbarui',
                'data' => $mapel
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $mapel = Mapel::findOrFail($id);
            $mapel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Attach guru to mapel
     */
    public function attachGuru($mapelId, $guruId): JsonResponse
    {
        try {
            $mapel = Mapel::findOrFail($mapelId);
            $guru = Guru::findOrFail($guruId);

            $mapel->guru()->attach($guru->id);

            return response()->json([
                'success' => true,
                'message' => 'Guru berhasil ditambahkan ke mata pelajaran',
                'data' => $mapel->load('guru')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detach guru from mapel
     */
    public function detachGuru($mapelId, $guruId): JsonResponse
    {
        try {
            $mapel = Mapel::findOrFail($mapelId);
            $guru = Guru::findOrFail($guruId);

            $mapel->guru()->detach($guru->id);

            return response()->json([
                'success' => true,
                'message' => 'Guru berhasil dihapus dari mata pelajaran',
                'data' => $mapel->load('guru')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

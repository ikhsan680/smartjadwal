<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $kelas = Kelas::with('waliGuru')->get();
            return response()->json([
                'success' => true,
                'data' => $kelas
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
                'nama' => 'required|string|max:255',
                'jurusan' => 'required|string|max:255',
                'wali_kelas' => 'nullable|string|max:255',
                'wali_guru_id' => 'nullable|exists:guru,id',
            ]);

            if (!empty($validated['wali_guru_id'])) {
                $guruWali = Guru::find($validated['wali_guru_id']);
                $validated['wali_kelas'] = $guruWali?->nama;
            }

            $kelas = Kelas::create($validated);
            $kelas->load('waliGuru');

            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil ditambahkan',
                'data' => $kelas
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
            $kelas = Kelas::with('waliGuru')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $kelas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $kelas = Kelas::findOrFail($id);

            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'jurusan' => 'required|string|max:255',
                'wali_kelas' => 'nullable|string|max:255',
                'wali_guru_id' => 'nullable|exists:guru,id',
            ]);

            if (!empty($validated['wali_guru_id'])) {
                $guruWali = Guru::find($validated['wali_guru_id']);
                $validated['wali_kelas'] = $guruWali?->nama;
            } elseif (array_key_exists('wali_guru_id', $validated) && empty($validated['wali_guru_id'])) {
                $validated['wali_kelas'] = null;
            }

            $kelas->update($validated);
            $kelas->load('waliGuru');

            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil diperbarui',
                'data' => $kelas
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
            $kelas = Kelas::findOrFail($id);
            $kelas->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

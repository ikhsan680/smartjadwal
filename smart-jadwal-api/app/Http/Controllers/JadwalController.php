<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $jadwal = Jadwal::with('kelas', 'mapel', 'guru', 'kegiatan')->get();
            return response()->json([
                'success' => true,
                'data' => $jadwal
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get jadwal by kelas ID
     */
    public function getByKelas($kelasId): JsonResponse
    {
        try {
            $jadwal = Jadwal::with('kelas', 'mapel', 'guru', 'kegiatan')
                ->where('kelas_id', $kelasId)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $jadwal
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
                'kelas_id' => 'required|exists:kelas,id',
                'mapel_id' => 'nullable|exists:mapel,id',
                'guru_id' => 'nullable|exists:guru,id',
                'kegiatan_id' => 'nullable|exists:kegiatan,id',
                'hari' => 'required|string|max:255',
                'jam_mulai' => 'required|string',
                'jam_selesai' => 'required|string',
            ]);

            // Ensure either mapel_id or kegiatan_id is provided
            if (empty($validated['mapel_id'] ?? null) && empty($validated['kegiatan_id'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal harus memiliki mapel atau kegiatan'
                ], 422);
            }

            // Check for duplicate jadwal at the same time
            $conflictingJadwal = Jadwal::where('kelas_id', $validated['kelas_id'])
                ->where('hari', $validated['hari'])
                ->where('jam_mulai', $validated['jam_mulai'])
                ->first();

            if ($conflictingJadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal sudah ada di jam yang sama untuk hari dan kelas ini'
                ], 422);
            }

            // Ensure null values for optional fields that weren't provided
            $data = [
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'] ?? null,
                'guru_id' => $validated['guru_id'] ?? null,
                'kegiatan_id' => $validated['kegiatan_id'] ?? null,
                'hari' => $validated['hari'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'],
            ];

            $jadwal = Jadwal::create($data);
            $jadwal->load('kelas', 'mapel', 'guru', 'kegiatan');

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil ditambahkan',
                'data' => $jadwal
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
            $jadwal = Jadwal::with('kelas', 'mapel', 'guru', 'kegiatan')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $jadwal
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $jadwal = Jadwal::findOrFail($id);

            $validated = $request->validate([
                'kelas_id' => 'required|exists:kelas,id',
                'mapel_id' => 'nullable|exists:mapel,id',
                'guru_id' => 'nullable|exists:guru,id',
                'kegiatan_id' => 'nullable|exists:kegiatan,id',
                'hari' => 'required|string|max:255',
                'jam_mulai' => 'required|string',
                'jam_selesai' => 'required|string',
            ]);

            // Ensure either mapel_id or kegiatan_id is provided
            if (empty($validated['mapel_id'] ?? null) && empty($validated['kegiatan_id'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal harus memiliki mapel atau kegiatan'
                ], 422);
            }

            // Check for duplicate jadwal at the same time (exclude current jadwal)
            $conflictingJadwal = Jadwal::where('kelas_id', $validated['kelas_id'])
                ->where('hari', $validated['hari'])
                ->where('jam_mulai', $validated['jam_mulai'])
                ->where('id', '!=', $id)
                ->first();

            if ($conflictingJadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal sudah ada di jam yang sama untuk hari dan kelas ini'
                ], 422);
            }

            // Ensure null values for optional fields that weren't provided
            $data = [
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'] ?? null,
                'guru_id' => $validated['guru_id'] ?? null,
                'kegiatan_id' => $validated['kegiatan_id'] ?? null,
                'hari' => $validated['hari'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'],
            ];

            $jadwal->update($data);
            $jadwal->load('kelas', 'mapel', 'guru', 'kegiatan');

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil diperbarui',
                'data' => $jadwal
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
            $jadwal = Jadwal::findOrFail($id);
            $jadwal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

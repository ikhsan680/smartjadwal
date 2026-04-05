<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index()
    {
        $guru = Guru::all();
        return response()->json([
            'success' => true,
            'data' => $guru
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|unique:guru,nama',
        ]);

        $guru = Guru::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Guru berhasil dibuat',
            'data' => $guru
        ], 201);
    }

    public function show(Guru $guru)
    {
        return response()->json([
            'success' => true,
            'data' => $guru
        ]);
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => 'required|string|unique:guru,nama,' . $guru->id,
        ]);

        $guru->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Guru berhasil diperbarui',
            'data' => $guru
        ]);
    }

    public function destroy(Guru $guru)
    {
        $guru->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guru berhasil dihapus'
        ]);
    }
}

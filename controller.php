<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\BonWarkah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BerkasController extends Controller
{
    // Menambah berkas
    public function store(Request $request, $bonWarkahId)
    {
        $validator = Validator::make($request->all(), [
            'berkas.*' => 'required|mimes:pdf|max:10240'
        ], [
            'berkas.*.required' => 'Berkas harus diunggah',
            'berkas.*.mimes' => 'Berkas harus dalam format PDF',
            'berkas.*.max' => 'Ukuran berkas maksimal 10MB'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bonWarkah = BonWarkah::findOrFail($bonWarkahId);
        $savedBerkas = [];

        if ($request->hasFile('berkas')) {
            foreach ($request->file('berkas') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = "berkas/{$bonWarkahId}";
                
                $storedPath = $file->storeAs($filePath, $fileName, 'public');

                $berkas = Berkas::create([
                    'bon_warkah_id' => $bonWarkahId,
                    'nama_berkas' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'file_size' => $file->getSize()
                ]);

                $savedBerkas[] = $berkas;
            }
        }

        return response()->json([
            'message' => 'Berkas berhasil diunggah',
            'data' => $savedBerkas
        ], 201);
    }

    // Update berkas
    public function update(Request $request, $berkasId)
    {
        $berkas = Berkas::findOrFail($berkasId);

        $validator = Validator::make($request->all(), [
            'berkas' => 'required|mimes:pdf|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('berkas')) {
            // Hapus file lama
            if (Storage::disk('public')->exists($berkas->file_path)) {
                Storage::disk('public')->delete($berkas->file_path);
            }

            $file = $request->file('berkas');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = "berkas/{$berkas->bon_warkah_id}";
            
            $storedPath = $file->storeAs($filePath, $fileName, 'public');

            $berkas->update([
                'nama_berkas' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_size' => $file->getSize()
            ]);
        }

        return response()->json([
            'message' => 'Berkas berhasil diperbarui',
            'data' => $berkas
        ]);
    }

    // Menghapus berkas
    public function destroy($berkasId)
    {
        $berkas = Berkas::findOrFail($berkasId);

        if (Storage::disk('public')->exists($berkas->file_path)) {
            Storage::disk('public')->delete($berkas->file_path);
        }

        $berkas->delete();

        return response()->json(['message' => 'Berkas berhasil dihapus']);
    }

    // Mendapatkan berkas berdasarkan bon_warkah_id
    public function getByBonWarkah($bonWarkahId)
    {
        $berkas = Berkas::where('bon_warkah_id', $bonWarkahId)->get();
        return response()->json($berkas);
    }

    // Download berkas
    public function download($berkasId)
    {
        $berkas = Berkas::findOrFail($berkasId);
        return Storage::disk('public')->download($berkas->file_path, $berkas->nama_berkas);
    }
}
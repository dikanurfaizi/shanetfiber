<?php

namespace App\Http\Controllers;

use App\Models\ApiUrl;
use Illuminate\Http\Request;

class ApiUrlController extends Controller
{
    // Menyimpan URL
    public function store(Request $request)
    {
        // Validasi data yang diterima
        $request->validate([
            'name' => 'required|string|max:255', // Validasi nama
            'url' => 'required|url|unique:api_urls,url', // Validasi URL
        ]);

        // Membuat entri baru untuk URL
        $apiUrl = ApiUrl::create([
            'name' => $request->name,
            'url' => $request->url
        ]);

        return response()->json(['message' => 'URL saved successfully', 'id' => $apiUrl->id], 201);
    }

    // Mengambil URL berdasarkan ID
    public function show($id)
    {
        $apiUrl = ApiUrl::find($id); // Mencari URL berdasarkan ID

        if ($apiUrl) {
            return response()->json(['url' => $apiUrl->url, 'name' => $apiUrl->name], 200);
        } else {
            return response()->json(['message' => 'URL not found'], 404);
        }
    }

    // Mengambil semua URL
    public function index()
    {
        $apiUrls = ApiUrl::all(); // Mengambil semua URL

        return response()->json($apiUrls, 200);
    }

    // Mengupdate URL
    public function update(Request $request, $id)
    {
        $request->validate([
            'url' => 'required|url|unique:api_urls,url,' . $id, // Validasi dengan pengecualian untuk ID ini
        ]);

        $apiUrl = ApiUrl::find($id);

        if ($apiUrl) {
            $apiUrl->update(['url' => $request->url]);
            return response()->json(['message' => 'URL updated successfully'], 200);
        } else {
            return response()->json(['message' => 'URL not found'], 404);
        }
    }

    // Menghapus URL
    public function destroy($id)
    {
        $apiUrl = ApiUrl::find($id);

        if ($apiUrl) {
            $apiUrl->delete();
            return response()->json(['message' => 'URL deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'URL not found'], 404);
        }
    }
}

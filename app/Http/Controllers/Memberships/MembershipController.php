<?php

namespace App\Http\Controllers\Memberships;

use Illuminate\Http\Request;
use App\Models\Membership\Membership;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class MembershipController extends Controller
{
    public function index()
    {
        $members = Membership::paginate(10); // Misal 10 data per halaman

        $message = $members->isEmpty() ? "Saat ini belum ada member list yang dibuat" : null;

        $i = ($members->currentPage() - 1) * $members->perPage();

        return view('members.index', compact('members', 'message', 'i'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
    {
        // VALIDASI
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'discount_type' => 'required|in:none,percent,amount',
            'status' => 'required|boolean',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|between:0,100',
        ]);

        // NORMALISASI DISKON
        switch ($validated['discount_type']) {

            case 'none':
                $discount = 0;
                $discountValue = 0;
                break;

            case 'percent':
                // misal user isi 10 → berarti 10%
                $discount = (float) ($validated['discount_percent'] ?? 0);
                $discountValue = $discount;
                break;

            case 'amount':
                // bersihkan titik ribuan (JS sudah bersihkan, tapi kita pastikan)
                $rawAmount = str_replace('.', '', $validated['discount_value'] ?? 0);
                $discount = (float) $rawAmount;
                $discountValue = $discount;
                break;
        }

        // DATA YANG DISIMPAN
        $memberData = [
            'name' => $validated['name'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $discountValue, // untuk keperluan internal
            'is_active' => $validated['status'],
            'slug' => str($validated['name'] . '-' . Str::random(6))->slug(),
        ];

        Membership::create($memberData);

        return redirect()->route('members.index')
            ->with('success', 'Member created successfully');
    }


    public function update(Request $request, Membership $member)
    {
        try {
            // Toggle Status
            if ($request->has('update_status')) {
                $newStatus = $member->is_active ? 0 : 1;
                $member->update(['is_active' => $newStatus]);

                return response()->json([
                    'success' => true,
                    'message' => $newStatus ? 'Member activated successfully.' : 'Member deactivated successfully.'
                ]);
            }

            // VALIDASI
            $validated = $request->validate([
                'name' => 'required|string',
                'discount_type' => 'required|in:none,percent,amount',
                'status' => 'required|boolean',
                'discount_value' => 'nullable|numeric|min:0',
                'discount_percent' => 'nullable|numeric|between:0,100',
            ]);

            // NORMALISASI DISKON
            switch ($validated['discount_type']) {
                case 'none':
                    $discount = 0;
                    $validated['discount_value'] = 0;
                    break;

                case 'percent':
                    $discount = (float) $validated['discount_percent']; // simpan 10 untuk 10%
                    $validated['discount_value'] = $discount; // simpan juga sebagai unified value
                    break;

                case 'amount':
                    // pastikan pemisah ribuan sudah hilang (JS harus bersih)
                    $raw = str_replace('.', '', $validated['discount_value']);
                    $discount = (float) $raw;
                    $validated['discount_value'] = $discount;
                    break;
            }

            // UPDATE
            $member->update([
                'name' => $validated['name'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'], // untuk nilai raw
                'discount' => $discount,                       // field unified
                'is_active' => $validated['status'],
            ]);

            return redirect()->route('members.index')
                ->with('success', 'Member updated successfully.');
        } catch (\Exception $e) {
            Log::error($e);

            return back()->withErrors('An error occurred. Please try again later.');
        }
    }

    public function edit(Membership $member)
    {
        // dd($member);
        return view('members.edit', compact('member'));
    }

    public function activate(Membership $member)
    {
        $member->update(['is_active' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Member activated successfully.'
        ]);
    }

    public function deactivate(Membership $member)
    {
        $member->update(['is_active' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Member deactivated successfully.'
        ]);
    }
}

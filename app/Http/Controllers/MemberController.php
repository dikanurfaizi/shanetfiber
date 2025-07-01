<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::paginate(10); // Misal 10 data per halaman

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
        $request->validate([
            'type' => 'required|string|max:50',
            'discount' => 'required|numeric|min:0',
        ]);

        // dd($request);

        $memberData = [
            'type' => $request->type,
            'discount' => $request->discount,
            'slug' => str($request->type . '-' . Str::random(6))->slug(),
            'status' => $request->status,
        ];
        // dd($memberData);

        Member::create($memberData);
        return redirect()->route('members.index')->with('success', 'Member created successfully');
    }

    public function update(Request $request, Member $member)
    {
        try {
            if ($request->has('update_status')) {
                $newStatus = $member->status === 1 ? 0 : 1;
                $member->update(['status' => $newStatus]);

                return response()->json([
                    'success' => true,
                    'message' => $newStatus === 1 ? 'Member activated successfully.' : 'Member deactivated successfully.'
                ]);
            }

            // Validasi dan update dari form biasa
            $validated = $request->validate([
                'type' => 'required|string',
                'discount' => 'nullable|numeric',
                'status' => 'required|boolean'
            ]);

            $member->update($validated);

            return redirect()->route('members.index')->with('success', 'Member updated successfully.');
        } catch (\Exception $e) {
            Log::error($e);

            return $request->has('update_status')
                ? response()->json(['success' => false, 'message' => 'An error occurred. Please try again later.'], 500)
                : back()->withErrors('An error occurred. Please try again later.');
        }
    }

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }
}

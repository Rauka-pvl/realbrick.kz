<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LeadSubmittedMail;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $lead = Lead::create($validated);

        Mail::to('mr.redle3@gmail.com')->send(new LeadSubmittedMail($lead));

        return response()->json([
            'success' => true,
        ], 201);
    }
}


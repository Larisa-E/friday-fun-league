<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    // add participant form submission
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:participants,name', // name is required and must not already exist in the table  
            'avatar_emoji' => 'nullable|max:10', 
        ]);

        Participant::create([
            'name' => $request->name,
            'avatar_emoji' => $request->avatar_emoji,
        ]);

        return redirect()->route('dashboard')->with('success', 'Participant ' . $request->name . ' added successfully!');
    }
}

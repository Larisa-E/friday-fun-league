<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Services\LeagueStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function update(Request $request, Participant $participant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:participants,name,' . $participant->id,
            'avatar_emoji' => 'nullable|max:10',
        ]);

        $participant->update($validated);

        return redirect()->route('dashboard')->with('success', 'Participant updated successfully!');
    }

    public function destroy(Participant $participant, LeagueStatsService $leagueStats)
    {
        DB::transaction(function () use ($participant, $leagueStats): void {
            $participant->delete();
            $leagueStats->recalculateParticipantStats();
        });

        return redirect()->route('dashboard')->with('success', 'Participant deleted successfully!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Services\LeagueStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ParticipantController extends Controller
{
    // Create a new participant from the dashboard form.
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:participants,name',
            'avatar_emoji' => 'nullable|max:10',
        ]);

        Participant::create([
            'name' => $request->name,
            'avatar_emoji' => $request->avatar_emoji,
        ]);

        return redirect()->route('dashboard')->with('success', 'Participant ' . $request->name . ' added successfully!');
    }

    // Update one participant and send validation errors back to the correct edit modal.
    public function update(Request $request, Participant $participant)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:participants,name,' . $participant->id,
            'avatar_emoji' => 'nullable|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('dashboard')
                ->withErrors($validator, 'participantUpdate.' . $participant->id)
                ->withInput()
                ->with('openModal', 'editParticipantModal' . $participant->id);
        }

        $validated = $validator->validated();

        $participant->update($validated);

        return redirect()->route('dashboard')->with('success', 'Participant updated successfully!');
    }

    // Delete the participant and recalculate standings so the rank list stays correct.
    public function destroy(Participant $participant, LeagueStatsService $leagueStats)
    {
        DB::transaction(function () use ($participant, $leagueStats): void {
            $participant->delete();
            $leagueStats->recalculateParticipantStats();
        });

        return redirect()->route('dashboard')->with('success', 'Participant deleted successfully!');
    }
}

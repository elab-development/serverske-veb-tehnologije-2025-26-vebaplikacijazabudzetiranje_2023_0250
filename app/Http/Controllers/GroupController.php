<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Group::with('creator', 'members')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
   
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $group = Group::create([
        'name' => $validated['name'],
        'created_by' => $request->user()->id,
    ]);

    return response()->json($group, 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
$group = Group::with('creator', 'members', 'expenses')->find($id);

    if (!$group) {
        return response()->json(['message' => 'Grupa nije pronađena'], 404);
    }

    return response()->json($group);
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    $group = Group::find($id);

    if (!$group) {
        return response()->json(['message' => 'Grupa nije pronađena'], 404);
    }

    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
    ]);

    $group->update($validated);

    return response()->json($group);
}

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(string $id)
{
    $group = Group::find($id);

    if (!$group) {
        return response()->json(['message' => 'Grupa nije pronađena'], 404);
    }

    $group->delete();

    return response()->json(['message' => 'Grupa je uspešno obrisana']);
}
}

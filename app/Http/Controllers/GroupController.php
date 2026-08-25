<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Http\Resources\GroupResource;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    $query = Group::with('creator', 'members');

    if ($request->has('name')) {
        $query->where('name', 'like', '%' . $request->input('name') . '%');
    }

    return GroupResource::collection($query->paginate(5));
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

    //automatski dodajemo kreatora grupe kao njenog prvog zvaničnog člana
    // (bez ovoga, group_user tabela ostaje prazna dok neko ručno ne pozove addMember)
    $group->members()->attach($request->user()->id);

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
public function destroy(Request $request, string $id)
{
    $group = Group::find($id);

    if (!$group) {
        return response()->json(['message' => 'Grupa nije pronađena'], 404);
    }

    $user = $request->user();

    if ($user->role === 'authenticated_user') {
        return response()->json(['message' => 'Nemate dozvolu za brisanje grupa'], 403);
    }

    if ($user->role !== 'admin' && $group->created_by !== $user->id) {
        return response()->json(['message' => 'Možete brisati samo svoje grupe'], 403);
    }

    $group->delete();

    return response()->json(['message' => 'Grupa je uspešno obrisana']);
}
public function expenses(string $id)
{
    $group = Group::find($id);

    if (!$group) {
        return response()->json(['message' => 'Grupa nije pronađena'], 404);
    }

    return response()->json($group->expenses()->with('payer')->get());
}

public function addMember(Request $request, string $id)
{
    $group = Group::find($id);

    if (!$group) {
        return response()->json(['message' => 'Grupa nije pronađena'], 404);
    }

    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    $group->members()->attach($validated['user_id']);

    return response()->json(['message' => 'Korisnik je uspešno dodat u grupu']);

    }

    //Vraća ukupan iznos koji je svaki član grupe platio za troškove, sortirano od najvišeg ka najnižem.
    // JOIN preko 3 tabele (expenses, users, group_user), agregacija (SUM), grupisanje (GROUP BY).

public function balanceSummary($id)
{
    // Proveravamo da grupa postoji
    $group = Group::findOrFail($id);

    $summary = DB::table('expenses')
        // JOIN sa users - da dobijemo ime osobe koja je platila
        ->join('users', 'expenses.paid_by', '=', 'users.id')
        // JOIN sa group_user - da potvrdimo da je ta osoba stvarno član grupe
        ->join('group_user', function ($joinClause) {
            $joinClause->on('group_user.user_id', '=', 'users.id')
                       ->on('group_user.group_id', '=', 'expenses.group_id');
        })
        ->where('expenses.group_id', $id)
        ->select(
            'users.id as user_id',
            'users.name',
            DB::raw('SUM(expenses.amount) as total_paid'),
            DB::raw('COUNT(expenses.id) as broj_troskova')
        )
        ->groupBy('users.id', 'users.name')
        ->orderByDesc('total_paid')
        ->get();

    return response()->json([
        'group' => $group->name,
        'balance_summary' => $summary,
    ], 200);
    }

}


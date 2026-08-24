<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Expense::with('group', 'payer')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
    'description' => 'required|string|max:255',
    'amount' => 'required|numeric|min:0',
    'group_id' => 'required|exists:groups,id',
]);

$expense = Expense::create([
    'description' => $validated['description'],
    'amount' => $validated['amount'],
    'group_id' => $validated['group_id'],
    'paid_by' => $request->user()->id,
]);

return response()->json($expense, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $expense = Expense::with('group', 'payer')->find($id);

if (!$expense) {
    return response()->json(['message' => 'Trošak nije pronađen'], 404);
}

return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $expense = Expense::find($id);

if (!$expense) {
    return response()->json(['message' => 'Trošak nije pronađen'], 404);
}

$validated = $request->validate([
    'description' => 'sometimes|required|string|max:255',
    'amount' => 'sometimes|required|numeric|min:0',
]);

$expense->update($validated);

return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = Expense::find($id);

if (!$expense) {
    return response()->json(['message' => 'Trošak nije pronađen'], 404);
}

$expense->delete();

return response()->json(['message' => 'Trošak je uspešno obrisan']);
    }
}

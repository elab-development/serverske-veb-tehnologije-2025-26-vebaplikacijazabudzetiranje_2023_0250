<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Http\Resources\ExpenseResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    $query = Expense::with('group', 'payer');

    if ($request->has('min_amount')) {
        $query->where('amount', '>=', $request->input('min_amount'));
    }

    if ($request->has('max_amount')) {
        $query->where('amount', '<=', $request->input('max_amount'));
    }

    return ExpenseResource::collection($query->paginate(5));
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

    return new ExpenseResource($expense);
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

// Provera vlasništva (IDOR fix)
$user = $request->user();
if ($user->role !== 'admin' && $expense->paid_by !== $user->id) {
    return response()->json(['message' => 'Možete menjati samo svoje troškove'], 403);
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

// Provera vlasništva (IDOR fix)
$user = $request->user();
if ($user->role !== 'admin' && $expense->paid_by !== $user->id) {
    return response()->json(['message' => 'Možete brisati samo svoje troškove'], 403);
}

$expense->delete();

return response()->json(['message' => 'Trošak je uspešno obrisan']);
    }
    public function exchangeRate(string $currency)
{
    $currency = strtoupper($currency);

    // Kesiramo kurs na 60 minuta - ne dovlacimo isti podatak sa spoljnog servisa svaki put
    $data = Cache::remember("exchange-rate-{$currency}", now()->addMinutes(60), function () use ($currency) {
        $response = Http::withOptions(['verify' => false])->get("https://api.frankfurter.app/latest", [
            'from' => 'EUR',
            'to' => $currency,
        ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    });

    if ($data === null) {
        return response()->json(['message' => 'Greška prilikom pribavljanja kursa'], 500);
    }

    return response()->json($data);
}

 // Drugi javni REST servis - restcountries.com (podaci o drzavi: valuta, glavni grad, region)
    public function countryInfo(string $name)
{
    $cacheKey = 'country-info-' . strtolower($name);

    $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($name) {
        $response = Http::withOptions(['verify' => false])->get("https://restcountries.com/v3.1/name/{$name}", [
            'fields' => 'name,currencies,capital,region',
        ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    });

    if ($data === null) {
        return response()->json(['message' => 'Greška prilikom pribavljanja podataka o drzavi'], 500);
    }

    return response()->json($data);
}
}



<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Dozvoljene vrednosti za kolonu "category" (mora se poklapati sa
     * enum-om definisanim u change_category_column_in_expenses_table migraciji).
     */
    private const CATEGORIES = ['food', 'transport', 'housing', 'entertainment', 'health', 'other'];

    /**
     * GET /api/expenses
     *
     * Query parametri:
     *  - per_page  : broj stavki po strani (paginacija)
     *  - group_id  : filtriranje troškova po grupi
     *  - category  : filtriranje po kategoriji
     *  - search    : pretraga po nazivu troška (LIKE %search%)
     */
    public function index(Request $request)
    {
        $query = Expense::with(['group', 'payer']);

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $expenses = $query->latest('expense_date')
            ->paginate($request->integer('per_page', 10));

        return response()->json($expenses);
    }

    /**
     * POST /api/expenses
     * Ulogovani korisnik (request->user()) automatski postaje "paid_by".
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'title' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense = Expense::create([
            'group_id' => $request->group_id,
            'paid_by' => $request->user()->id,
            'title' => $request->title,
            'amount' => $request->amount,
            'category' => $request->category,
            'description' => $request->description,
            'expense_date' => $request->expense_date,
        ]);

        return response()->json([
            'message' => 'Trošak je uspešno dodat.',
            'expense' => $expense->load('group', 'payer'),
        ], 201);
    }

    /**
     * GET /api/expenses/{expense}
     */
    public function show(Expense $expense)
    {
        return response()->json($expense->load('group', 'payer'));
    }

    /**
     * PUT/PATCH /api/expenses/{expense}
     */
    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:150',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'category' => 'sometimes|required|in:' . implode(',', self::CATEGORIES),
            'description' => 'nullable|string',
            'expense_date' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense->update($request->only(
            'title', 'amount', 'category', 'description', 'expense_date'
        ));

        return response()->json([
            'message' => 'Trošak je uspešno izmenjen.',
            'expense' => $expense,
        ]);
    }

    /**
     * DELETE /api/expenses/{expense}
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return response()->json(['message' => 'Trošak je uspešno obrisan.']);
    }
}

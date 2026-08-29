<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Group;
use App\Http\Resources\GroupResource;
use App\Mail\DebtNotification;

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

    //automatski dodajemo kreatora grupe kao njenog prvog zvanicnog clana
    // (bez ovoga, group_user tabela ostaje prazna dok neko rucno ne pozove addMember)
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

    $user = $request->user();

    // Provera vlasnistva (IDOR fix) - samo kreator grupe ili admin sme da menja
    if ($user->role !== 'admin' && $group->created_by !== $user->id) {
        return response()->json(['message' => 'Možete menjati samo svoje grupe'], 403);
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

    //Vraca ukupan iznos koji je svaki član grupe platio za troškove, sortirano od najviseg ka najnizem.
    // JOIN preko 3 tabele (expenses, users, group_user), agregacija (SUM), grupisanje (GROUP BY).

public function balanceSummary($id)
{
    // Proveravamo da grupa postoji
    $group = Group::findOrFail($id);

    $summary = DB::table('expenses')
        // JOIN sa users - da dobijemo ime osobe koja je platila
        ->join('users', 'expenses.paid_by', '=', 'users.id')
        // JOIN sa group_user - da potvrdimo da je ta osoba stvarno clan grupe
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

    /**
     * Računa ko kome duguje (refundacija) u grupi.
     * Pretpostavka: svaki trošak se deli ravnomerno na sve trenutne članove grupe
     * (isti princip kao Splitwise "equal split").
     * Pozitivan balans = clan je platio vise nego sto je trebalo -> POTRAZUJE.
     * Negativan balans = clan je platio manje nego sto je trebalo -> DUGUJE (treba da refundira).
     */
    private function calculateSettlement(Group $group)
    {
        $members = $group->members;
        $memberCount = $members->count();

        if ($memberCount === 0) {
            return null;
        }

        $totalExpenses = (float) $group->expenses()->sum('amount');
        $fairShare = round($totalExpenses / $memberCount, 2);

        $settlement = $members->map(function ($member) use ($group, $fairShare) {
            $paid = (float) $group->expenses()->where('paid_by', $member->id)->sum('amount');
            $balance = round($paid - $fairShare, 2);

            if ($balance > 0) {
                $status = 'potražuje';
            } elseif ($balance < 0) {
                $status = 'duguje';
            } else {
                $status = 'izmireno';
            }

            return [
                'user_id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'paid' => round($paid, 2),
                'fair_share' => $fairShare,
                'balance' => $balance,
                'status' => $status,
            ];
        });

        return [
            'total_expenses' => round($totalExpenses, 2),
            'fair_share_per_member' => $fairShare,
            'settlement' => $settlement,
        ];
    }

    public function settlement($id)
    {
        $group = Group::with('members')->find($id);

        if (!$group) {
            return response()->json(['message' => 'Grupa nije pronađena'], 404);
        }

        $data = $this->calculateSettlement($group);

        if ($data === null) {
            return response()->json(['message' => 'Grupa nema članova'], 400);
        }

        return response()->json(array_merge(['group' => $group->name], $data));
    }

    /**
     * Salje mail svakom clanu grupe koji trenutno duguje novac (balance < 0),
     * na osnovu istog obracuna kao settlement(). Koristi Laravel Mail
     * (driver se podesava u .env - MAIL_MAILER=log za testiranje bez pravog slanja).
     */
    public function notifyDebts($id)
    {
        $group = Group::with('members')->find($id);

        if (!$group) {
            return response()->json(['message' => 'Grupa nije pronađena'], 404);
        }

        $data = $this->calculateSettlement($group);

        if ($data === null) {
            return response()->json(['message' => 'Grupa nema članova'], 400);
        }

        $notified = [];

        foreach ($data['settlement'] as $member) {
            if ($member['balance'] < 0 && !empty($member['email'])) {
                Mail::to($member['email'])->send(new DebtNotification(
                    $member['name'],
                    $group->name,
                    abs($member['balance'])
                ));

                $notified[] = [
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'amount' => abs($member['balance']),
                ];
            }
        }

        return response()->json([
            'message' => 'Mejlovi poslati clanovima koji duguju.',
            'notified' => $notified,
        ]);
    }

    // Export troškova grupe u CSV fajl
    public function exportExpensesCsv($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Grupa nije pronađena'], 404);
        }

        $expenses = $group->expenses()->with('payer')->get();

        $fileName = 'troskovi-grupe-' . $group->id . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($expenses) {
            $handle = fopen('php://output', 'w');

            // BOM da bi Excel ispravno prikazao dijakritike (c, c, s...)
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Opis', 'Iznos', 'Platio', 'Datum']);

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->description,
                    $expense->amount,
                    $expense->payer->name ?? 'Nepoznato',
                    $expense->created_at->format('d.m.Y H:i'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * REGISTRACIJA
     */
    public function register(Request $request)
    {
        // Validator je Laravel-ov ugrađen sistem za proveru podataka
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // "unique:users" - automatski proverava da email nije zauzet
            'password' => 'required|string|min:6',
        ]);

        // Ako validacija ne prođe, vrati grešku sa opisom šta nije u redu
        if ($validator->fails()) {
            // status 422 = "Unprocessable Entity" - standardan Laravel kod za greške validacije
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kreiramo korisnika 
        // Laravel AUTOMATSKI hash-uje ovo polje čim ga postavimo
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // automatski se hash-uje
            'role' => 'user', // svaki nov korisnik je podrazumevano "user"
        ]);

        return response()->json([
            'message' => 'Uspešna registracija.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tražimo korisnika po emailu
        $user = User::where('email', $request->email)->first();

        // Hash::check() proverava da li se uneta lozinka poklapa sa hash-om iz baze
        if (!$user || !Hash::check($request->password, $user->password)) {
            // status 401 = "Unauthorized" - namerno ista poruka za oba slučaja
            // (pogrešan email ILI pogrešna lozinka) iz sigurnosnih razloga
            return response()->json(['error' => 'Pogrešan email ili lozinka.'], 401);
        }

        // createToken() je Sanctum metoda (dobijena preko HasApiTokens trait-a)
        // 'plainTextToken' je STVARNI token koji šaljemo korisniku - vidi se SAMO jednom, ovde
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Uspešan login.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 200);
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        // $request->user() vraća trenutno ulogovanog korisnika
        // (Sanctum middleware ga automatski "kači" na request kad prepozna validan token)
        // currentAccessToken()->delete() briše BAŠ TAJ token iz baze - korisnik ga više ne može koristiti
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Uspešno ste odjavljeni.'], 200);
    }
}
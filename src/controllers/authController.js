"use strict";

// Uvozimo model User da bismo mogli da čitamo/pišemo u tabelu users
const { User } = require("../models");

// bcrypt koristimo da HASH-UJEMO lozinku (nikad je ne čuvamo kao čist tekst)
const bcrypt = require("bcrypt");

// jsonwebtoken koristimo da napravimo JWT token nakon uspešnog login-a
const jwt = require("jsonwebtoken");

// ============================================
// REGISTRACIJA
// ============================================
async function register(req, res) {
  try {
    // req.body sadrži podatke koje je korisnik poslao (jer smo u app.js dodale express.json())
    const { name, email, password } = req.body;

    // Osnovna validacija - proveravamo da su sva polja poslata
    if (!name || !email || !password) {
      // status 400 = "Bad Request", greška je do korisnika (nešto fali u zahtevu)
      return res.status(400).json({ error: "Sva polja su obavezna (name, email, password)." });
    }

    // Proveravamo da li email već postoji u bazi (email mora biti unique)
    const postojeciKorisnik = await User.findOne({ where: { email } });
    if (postojeciKorisnik) {
      // status 409 = "Conflict" - resurs već postoji
      return res.status(409).json({ error: "Korisnik sa ovim emailom već postoji." });
    }

    // HASH-OVANJE lozinke - NIKAD ne čuvamo pravu lozinku u bazi
    // bcrypt.hash(lozinka, brojKrugova) - drugi parametar je "salt rounds",
    // veći broj = sigurnije ali sporije. 10 je standardna vrednost.
    const password_hash = await bcrypt.hash(password, 10);

    // Kreiramo novog korisnika u bazi
    const noviKorisnik = await User.create({
      name,
      email,
      password_hash,
      role: "user", // svaki novoregistrovan korisnik po difoltu je obican "user"
    });

    // status 201 = "Created" - uspešno napravljen novi resurs
    // VAŽNO: nikad ne vraćamo password_hash nazad u odgovoru, čak ni hash-ovan
    return res.status(201).json({
      message: "Uspešna registracija.",
      user: {
        id: noviKorisnik.id,
        name: noviKorisnik.name,
        email: noviKorisnik.email,
        role: noviKorisnik.role,
      },
    });
  } catch (error) {
    // status 500 = "Internal Server Error" - greška na našoj strani (bag u kodu, baza pukla i sl.)
    console.error("Greška prilikom registracije:", error);
    return res.status(500).json({ error: "Greška na serveru." });
  }
}

// ============================================
// LOGIN
// ============================================
async function login(req, res) {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({ error: "Email i lozinka su obavezni." });
    }

    // Tražimo korisnika po emailu
    const korisnik = await User.findOne({ where: { email } });
    if (!korisnik) {
      // status 401 = "Unauthorized" - namerno ne kažemo "email ne postoji"
      // (iz sigurnosnih razloga - da napadač ne sazna koji emailovi postoje u bazi)
      return res.status(401).json({ error: "Pogrešan email ili lozinka." });
    }

    // bcrypt.compare proverava da li se uneta lozinka poklapa sa hash-om iz baze
    // (bcrypt sam zna kako da "otključa" hash i uporedi, mi to ne radimo ručno)
    const lozinkaTacna = await bcrypt.compare(password, korisnik.password_hash);
    if (!lozinkaTacna) {
      return res.status(401).json({ error: "Pogrešan email ili lozinka." });
    }

    // Kreiramo JWT token - ovo je "propusnica" koju korisnik dobija i šalje
    // uz svaki sledeći zahtev da dokaže da je ulogovan
    const token = jwt.sign(
      {
        id: korisnik.id,       // ubacujemo id i role u token da ih kasnije lako pročitamo
        role: korisnik.role,
      },
      process.env.JWT_SECRET,  // tajni ključ iz .env - NIKO drugi ne sme da ga zna
      { expiresIn: "24h" }     // token važi 24h, posle toga se korisnik mora ponovo ulogovati
    );

    return res.status(200).json({
      message: "Uspešan login.",
      token,
      user: {
        id: korisnik.id,
        name: korisnik.name,
        email: korisnik.email,
        role: korisnik.role,
      },
    });
  } catch (error) {
    console.error("Greška prilikom login-a:", error);
    return res.status(500).json({ error: "Greška na serveru." });
  }
}

module.exports = { register, login };
"use strict";

const jwt = require("jsonwebtoken");

// Middleware je funkcija koja se izvrši IZMEĐU zahteva i kontrolera
// Ima treći parametar "next" - kad ga pozoveš, kažeš Express-u "sve OK, pusti dalje ka kontroleru"
// Ako ga NE pozoveš (nego vratiš res.status(...).json(...)), zahtev se tu zaustavlja

function proveriToken(req, res, next) {
  // JWT token se šalje u Authorization headeru, u formatu: "Bearer <token>"
  const authHeader = req.headers["authorization"];

  if (!authHeader) {
    // status 401 = "Unauthorized" - nema tokena, znači korisnik nije poslao dokaz da je ulogovan
    return res.status(401).json({ error: "Niste ulogovani. Token nedostaje." });
  }

  // authHeader izgleda kao "Bearer eyJhbGc..." - mi hoćemo samo deo posle "Bearer "
  const token = authHeader.split(" ")[1];

  if (!token) {
    return res.status(401).json({ error: "Token nije u ispravnom formatu." });
  }

  try {
    // jwt.verify proverava DA LI je token validan (nije falsifikovan, nije istekao)
    // koristi isti JWT_SECRET kojim je token napravljen prilikom login-a
    const podaciIzTokena = jwt.verify(token, process.env.JWT_SECRET);

    // Ako je token validan, podatke (id, role) kačimo na req objekat
    // da bi ih SLEDEĆI kontroler (npr. kreiranje troška) mogao da koristi
    // - npr. da zna KO pravi trošak, bez da korisnik to ponovo šalje u body-ju
    req.user = podaciIzTokena;

    next(); // sve OK, pusti zahtev dalje ka pravom kontroleru
  } catch (error) {
    // jwt.verify baca grešku ako je token istekao ili je neispravan/falsifikovan
    return res.status(401).json({ error: "Token je nevažeći ili je istekao." });
  }
}


// dodatni middleware za proveru uloge korisnika (npr. admin, user, guest) - koristi se za zaštitu ruta
// poziva se sa listom dozvoljenih uloga
// i VRAĆA middleware funkciju prilagođenu tim ulogama
function proveriUlogu(...dozvoljeneUloge) {
  return function (req, res, next) {
    // req.user postoji samo ako je PRE ovoga prošao proveriToken middleware
    // (zato ova dva middleware-a UVEK idu zajedno, proveriToken pa proveriUlogu)
    if (!dozvoljeneUloge.includes(req.user.role)) {
      // status 403 = "Forbidden" - razlika od 401: korisnik JESTE ulogovan,
      // ali njegova uloga nema dozvolu za ovu akciju
      return res.status(403).json({ error: "Nemate dozvolu za ovu akciju." });
    }
    next();
  };
}

module.exports = { proveriToken, proveriUlogu }; //izvoz iz fajla
"use strict";

const express = require("express");
const router = express.Router(); // Router = "mini aplikacija" za grupu srodnih ruta

const { register, login, logout } = require("../controllers/authController");
const { proveriToken } = require("../middleware/authMiddleware"); // NOVO - uvozimo middleware za proveru tokena

// Kad stigne POST zahtev na /register, izvršava se funkcija register iz kontrolera
router.post("/register", register);

// Kad stigne POST zahtev na /login, izvršava se funkcija login
router.post("/login", login);

// Kad stigne POST zahtev na /logout, PRVO se izvrši proveriToken middleware,
// pa tek ako on pozove next() (znači token je validan), izvršava se logout funkcija
// logično je da samo neko ko JESTE ulogovan (ima validan token) uopšte može da se "odjavi"
router.post("/logout", proveriToken, logout);

module.exports = router;
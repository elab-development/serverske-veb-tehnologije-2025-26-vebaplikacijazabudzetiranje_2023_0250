"use strict";

const express = require("express");
const router = express.Router(); // Router = "mini aplikacija" za grupu srodnih ruta

const { register, login } = require("../controllers/authController");

// Kad stigne POST zahtev na /register, izvršava se funkcija register iz kontrolera
router.post("/register", register);

// Kad stigne POST zahtev na /login, izvršava se funkcija login
router.post("/login", login);

module.exports = router;
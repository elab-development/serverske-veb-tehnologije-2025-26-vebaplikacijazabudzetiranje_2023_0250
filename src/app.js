const express = require("express"); //ucitavanje biblioteke
const dotenv = require("dotenv");

const testRoutes = require("./routes/testRoutes");
const authRoutes = require("./routes/authRoutes");

dotenv.config();

const app = express(); //kreiranje express aplikacije

app.use(express.json()); //prihvati json podatke

app.use("/", testRoutes);
app.use("/api/auth", authRoutes); //sve rute iz authRoutes dobijaju prefiks /api/auth

module.exports = app; //omogucava da server koristi aplikaciju


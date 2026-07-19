const express = require("express"); //ucitavanje biblioteke
const dotenv = require("dotenv");

const testRoutes = require("./routes/testRoutes");

dotenv.config();

const app = express(); //kreiranje express aplikacije

app.use(express.json()); //prihvati json podatke

app.use("/", testRoutes);

module.exports = app; //omogucava da server koristi aplikaciju


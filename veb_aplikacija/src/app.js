const express = require("express"); //ucitavanje biblioteke

const app = express(); //kreiranje express aplikacije

app.use(express.json()); //prihvati json podatke

module.exports = app; //omogucava da server koristi aplikaciju
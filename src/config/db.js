// konekcija koju koristi app.js
// Ovaj fajl kreira Sequelize instancu koju će koristiti SVI modeli u aplikaciji
// Razlika u odnosu na config.js: ovaj fajl se importuje direktno u kodu aplikacije

const { Sequelize } = require("sequelize");
require("dotenv").config();

// Kreiramo novu Sequelize instancu - ovo je objekat koji predstavlja konekciju na bazu
const sequelize = new Sequelize(
  process.env.DB_NAME,     // ime baze
  process.env.DB_USER,     // korisničko ime
  process.env.DB_PASSWORD, // lozinka
  {
    host: process.env.DB_HOST,
    dialect: "mysql",
    logging: false, // false = ne ispisuje svaki SQL upit u konzoli (postavi na console.log ako želiš da vidiš upite radi debug-a)
  }
);

module.exports = sequelize;
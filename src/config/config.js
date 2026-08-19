// Ovaj fajl definiše podešavanja konekcije za SVAKO okruženje (development, test, production)
// Sequelize CLI ovaj fajl koristi kada pokrećeš migracije iz terminala

require("dotenv").config(); // učitava vrednosti iz .env fajla u process.env

module.exports = {
  development: {
    username: process.env.DB_USER,
    password: process.env.DB_PASSWORD || null, // ako je prazno u .env, šalje null
    database: process.env.DB_NAME,
    host: process.env.DB_HOST,
    dialect: "mysql", // govori Sequelize-u koji tip baze koristimo
  },
  test: {
    username: process.env.DB_USER,
    password: process.env.DB_PASSWORD || null,
    database: process.env.DB_NAME + "_test",
    host: process.env.DB_HOST,
    dialect: "mysql",
  },
  production: {
    username: process.env.DB_USER,
    password: process.env.DB_PASSWORD || null,
    database: process.env.DB_NAME,
    host: process.env.DB_HOST,
    dialect: "mysql",
  },
};
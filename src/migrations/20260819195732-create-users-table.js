"use strict";

// Migracija UVEK ima dve funkcije: "up" i "down"
// up   = šta se desi kad PRIMENIŠ migraciju (napravi tabelu)
// down = šta se desi kad je PONIŠTIŠ (obriši tabelu) - korisno ako pogrešiš

module.exports = {
  async up(queryInterface, Sequelize) {
    // queryInterface je "alat" preko kog Sequelize izvršava sirove komande nad bazom
    await queryInterface.createTable("Users", {
      id: {
        type: Sequelize.INTEGER,
        primaryKey: true,      // ovo je jedinstveni identifikator svakog reda
        autoIncrement: true,   // baza sama uvećava broj za svakog novog korisnika (1, 2, 3...)
        allowNull: false,
      },
      name: {
        type: Sequelize.STRING, // STRING = kratak tekst (do ~255 karaktera)
        allowNull: false,       // ne sme biti prazno - svaki korisnik mora imati ime
      },
      email: {
        type: Sequelize.STRING,
        allowNull: false,
        unique: true,           // dva korisnika ne mogu imati isti email
      },
      password_hash: {
        type: Sequelize.STRING,
        allowNull: false,       // ovde se čuva HASH-OVANA lozinka, nikad prava lozinka!
      },
      role: {
        type: Sequelize.ENUM("admin", "authenticated_user", "user"),
        // ENUM = kolona sme da ima SAMO jednu od ovih tačno navedenih vrednosti
        // ovo ti pokriva zahtev za 3 korisničke uloge
        allowNull: false,
        defaultValue: "user",  // ako ne navedeš ulogu prilikom kreiranja, automatski je "user"
      },
      createdAt: {
        // Sequelize po konvenciji sam dodaje i upravlja ovom kolonom
        // beleži KADA je red (korisnik) napravljen
        allowNull: false,
        type: Sequelize.DATE,
      },
      updatedAt: {
        // beleži KADA je red poslednji put izmenjen - Sequelize je sam ažurira
        allowNull: false,
        type: Sequelize.DATE,
      },
    });
  },

  async down(queryInterface, Sequelize) {
    // Ako ikad želiš da poništiš ovu migraciju, ovo jednostavno briše celu tabelu
    await queryInterface.dropTable("Users");
  },
};
"use strict";
const { Model } = require("sequelize");

// Model je JavaScript klasa koja "predstavlja" tabelu Users u kodu
module.exports = (sequelize, DataTypes) => {
  class User extends Model {
    // Ovde kasnije mogu ići "asocijacije" - npr. "jedan User ima više Expenses"
    // To dodajemo kad napravimo i ostale modele (Group, Expense...)
    static associate(models) {
      // primer za kasnije:
      // User.hasMany(models.Expense, { foreignKey: "paid_by" });
    }
  }

  // Ovde definišemo iste kolone kao u migraciji - migracija pravi tabelu u bazi,
  // a ovo govori Sequelize-u kako da tu tabelu "vidi" iz koda
  User.init(
    {
      name: {
        type: DataTypes.STRING,
        allowNull: false,
      },
      email: {
        type: DataTypes.STRING,
        allowNull: false,
        unique: true,
        validate: {
          isEmail: true, // Sequelize sam proveri da li je uneti tekst validan email format
        },
      },
      password_hash: {
        type: DataTypes.STRING,
        allowNull: false,
      },
      role: {
        type: DataTypes.ENUM("admin", "authenticated_user", "user"),
        allowNull: false,
        defaultValue: "user",
      },
    },
    {
      sequelize,       // prosleđujemo konekciju
      modelName: "User", // ime modela u kodu (koristićeš ga kao User.create(), User.findAll()...)
    }
  );

  return User;
};
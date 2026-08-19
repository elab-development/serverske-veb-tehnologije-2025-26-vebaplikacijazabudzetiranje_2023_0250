# Expense Sharing App

Veb aplikacija za deljenje i praćenje troškova, razvijena u okviru predmeta **Serverske veb tehnologije 2025/26**.

Aplikacija omogućava korisnicima da registruju nalog, prijave se, kreiraju i dele troškove, kao i da prate dugovanja između korisnika.

## Tehnologije

* Node.js
* Express.js
* MySQL
* Sequelize
* Sequelize CLI (migracije)
* JWT
* bcrypt
* dotenv
* Nodemon
* REST API

## Preuzimanje projekta

Klonirati repozitorijum:

```bash
git clone https://github.com/elab-development/serverske-veb-tehnologije-2025-26-vebaplikacijazabudzetiranje_2023_0250.git
```

Ući u folder projekta:

```bash
cd serverske-veb-tehnologije-2025-26-vebaplikacijazabudzetiranje_2023_0250
```

## Instalacija biblioteka

Nakon preuzimanja projekta potrebno je instalirati sve potrebne biblioteke:

```bash
npm install
```

Nije potrebno ručno instalirati svaku biblioteku pojedinačno. Komanda `npm install` automatski instalira sve dependencies i devDependencies navedene u `package.json` fajlu.

## Podešavanje `.env` fajla

`.env` fajl nije deo GitHub repozitorijuma zbog bezbednosti.

Potrebno je napraviti novi fajl pod nazivom:

```text
.env
```

u glavnom folderu projekta.

U njega je potrebno uneti konfiguraciju:

```env
PORT=3000

DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=expense_app

JWT_SECRET=nekaTajnaSifra
```

Vrednosti `DB_USER`, `DB_PASSWORD` i ostalih parametara potrebno je prilagoditi lokalnoj MySQL konfiguraciji.

## Baza podataka

Pre pokretanja aplikacije potrebno je imati instaliran i pokrenut MySQL server.

Podešavanja baze se nalaze u `.env` fajlu.

Bazu je moguće kreirati ručno:

```sql
CREATE DATABASE expense_app;
```

ili preko Sequelize CLI-ja:

```bash
npx sequelize-cli db:create
```

Nakon toga je potrebno primeniti migracije kako bi se napravile tabele u bazi:

```bash
npx sequelize-cli db:migrate
```

## Pokretanje aplikacije

Za pokretanje aplikacije u razvojnom režimu koristiti:

```bash
npm run dev
```

Aplikacija će biti dostupna na:

```text
http://localhost:3000
```

Za standardno pokretanje koristiti:

```bash
npm start
```

## Testiranje REST API-ja

REST API se testira pomoću alata **Postman**.

Primer test zahteva:

```text
GET http://localhost:3000/
```

## Autentifikacija

Aplikacija trenutno ima implementiranu registraciju, prijavu i odjavu korisnika preko JWT tokena.

| Ruta | Metoda | Opis | Telo zahteva |
| --- | --- | --- | --- |
| `/api/auth/register` | POST | Registracija novog korisnika (uloga `user` po difoltu) | `{ "name": "", "email": "", "password": "" }` |
| `/api/auth/login` | POST | Prijava korisnika, vraća JWT token | `{ "email": "", "password": "" }` |
| `/api/auth/logout` | POST | Odjava korisnika (zahteva token) | - |

Za pristup zaštićenim rutama (npr. `/api/auth/logout`) potrebno je poslati JWT token dobijen prilikom login-a u `Authorization` header-u:

```text
Authorization: Bearer <token>
```

## Struktura projekta

```text
expense-sharing-app/
│
├── src/
│   ├── controllers/
│   ├── routes/
│   ├── middleware/
│   ├── models/
│   ├── migrations/
│   ├── config/
│   └── app.js
│
├── server.js
├── .sequelizerc
├── package.json
├── package-lock.json
├── .env
├── .gitignore
└── README.md
```

## Važne napomene

* `node_modules` se ne čuva u GitHub repozitorijumu. Nakon preuzimanja projekta kreira se pomoću `npm install`.
* `.env` se ne čuva u GitHub repozitorijumu i svaki član tima ga kreira lokalno.
* `package.json` i `package-lock.json` su deo repozitorijuma.
* Aplikacija se trenutno testira preko Postman-a i nema frontend deo.

# Expense Sharing App

Veb aplikacija za deljenje i praćenje troškova, razvijena u okviru predmeta **Serverske veb tehnologije 2025/26**.

Aplikacija omogućava korisnicima da registruju nalog, prijave se, kreiraju i dele troškove, kao i da prate dugovanja između korisnika.

Projekat je originalno razvijen u Node.js/Express-u, a zatim je u potpunosti prebačen na **Laravel**.

## Tehnologije

* PHP 8.2+
* Laravel 12
* MySQL
* Eloquent ORM (Laravel migracije)
* Laravel Sanctum (autentifikacija preko API tokena)
* Composer
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

Potrebno je imati instaliran **PHP 8.2+** i **Composer**.

Nakon preuzimanja projekta potrebno je instalirati sve potrebne pakete:

```bash
composer install
```

Nije potrebno ručno instalirati svaki paket pojedinačno. Komanda `composer install` automatski instalira sve zavisnosti navedene u `composer.json` fajlu (Laravel framework, Sanctum i ostale pakete).

## Podešavanje `.env` fajla

`.env` fajl nije deo GitHub repozitorijuma zbog bezbednosti.

Nakon `composer install`, iskopirati primer konfiguracije:

```bash
cp .env.example .env
```

Zatim generisati aplikacioni ključ (Laravel ga koristi za enkripciju):

```bash
php artisan key:generate
```

U `.env` fajlu podesiti konekciju ka bazi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_app_laravel
DB_USERNAME=root
DB_PASSWORD=
```

Vrednosti `DB_USERNAME`, `DB_PASSWORD` i ostalih parametara potrebno je prilagoditi lokalnoj MySQL konfiguraciji.

## Baza podataka

Pre pokretanja aplikacije potrebno je imati instaliran i pokrenut MySQL server (npr. preko XAMPP-a).

Bazu je moguće napraviti ručno:

```sql
CREATE DATABASE expense_app_laravel;
```

Nakon toga primeniti migracije kako bi se napravile tabele u bazi:

```bash
php artisan migrate
```

Migracije prave sledeće tabele: `users` (sa `role` kolonom: `admin`, `authenticated_user`, `user`), `personal_access_tokens` (za Sanctum tokene), `cache` i `jobs`.

## Pokretanje aplikacije

Za pokretanje aplikacije u razvojnom režimu koristiti:

```bash
php artisan serve
```

Aplikacija će biti dostupna na:

```text
http://127.0.0.1:8000
```

## Testiranje REST API-ja

REST API se testira pomoću alata **Thunder Client** ili `curl`.

Primer test zahteva:

```text
POST http://127.0.0.1:8000/api/register
```

## Autentifikacija

Aplikacija ima implementiranu registraciju, prijavu i odjavu korisnika preko **Laravel Sanctum** API tokena.

| Ruta | Metoda | Opis | Telo zahteva |
| --- | --- | --- | --- |
| `/api/register` | POST | Registracija novog korisnika (uloga `user` po difoltu) | `{ "name": "", "email": "", "password": "" }` |
| `/api/login` | POST | Prijava korisnika, vraća Sanctum token | `{ "email": "", "password": "" }` |
| `/api/logout` | POST | Odjava korisnika (zahteva token, briše ga iz baze) | - |

Za pristup zaštićenim rutama (npr. `/api/logout`) potrebno je poslati token dobijen prilikom login-a u `Authorization` header-u:

```text
Authorization: Bearer <token>
```

## Struktura projekta

```text
expense-sharing-app/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── AuthController.php
│   └── Models/
│       └── User.php
│
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│
├── routes/
│   └── api.php
│
├── config/
├── public/
├── composer.json
├── .env
└── README.md
```

## Važne napomene

* `vendor/` se ne čuva u GitHub repozitorijumu. Nakon preuzimanja projekta kreira se pomoću `composer install`.
* `.env` se ne čuva u GitHub repozitorijumu i svaki član tima ga kreira lokalno na osnovu `.env.example`.
* `composer.json` i `composer.lock` su deo repozitorijuma.
* Aplikacija se trenutno testira preko Thunder Client-a i nema frontend deo.

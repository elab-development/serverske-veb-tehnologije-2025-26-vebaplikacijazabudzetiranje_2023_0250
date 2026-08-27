# Expense Sharing App

Veb aplikacija za deljenje i praćenje troškova, razvijena u okviru predmeta **Serverske veb tehnologije 2025/26**.

Aplikacija omogućava korisnicima da registruju nalog, prijave se, prave grupe, dodaju članove u grupe, kreiraju i dele troškove unutar grupe, i prate ko je koliko platio.

Projekat je razvijen u **Laravel-u**.

## Tehnologije

* PHP 8.2+
* Laravel 12
* MySQL
* Eloquent ORM (Laravel migracije, factory-ji, seederi)
* Laravel Sanctum (autentifikacija preko API tokena)
* Composer
* REST API (uključujući pozive dva spoljna javna servisa — [frankfurter.app](https://frankfurter.app) za kursnu listu i [date.nager.at](https://date.nager.at) za državne praznike)

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

Komanda automatski instalira sve zavisnosti iz `composer.json` (Laravel framework, Sanctum, Doctrine DBAL za izmenu kolona, i ostalo).

## Podešavanje `.env` fajla

`.env` fajl nije deo GitHub repozitorijuma zbog bezbednosti.

Iskopirati primer konfiguracije i generisati aplikacioni ključ:

```bash
cp .env.example .env
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

Opciono, napuniti bazu test podacima (10 grupa, 20 troškova sa nasumičnim podacima):

```bash
php artisan db:seed
```

ili oboje odjednom (briše i ponovo pravi sve tabele):

```bash
php artisan migrate:fresh --seed
```

### Tabele u bazi

| Tabela | Opis |
| --- | --- |
| `users` | Korisnici, sa `role` kolonom (`admin`, `authenticated_user`, `user`) |
| `groups` | Grupe za deljenje troškova, svaka ima kreatora (`created_by`) |
| `group_user` | Pivot tabela — članstvo korisnika u grupama (many-to-many) |
| `expenses` | Troškovi, vezani za grupu (`group_id`) i platioca (`paid_by`) |
| `personal_access_tokens` | Sanctum API tokeni |

## Pokretanje aplikacije
Za pokretanje aplikacije u razvojnom režimu koristiti:
```bash
php artisan serve
```

Aplikacija je dostupna na 
http://127.0.0.1:8000.

## Testiranje REST API-ja

Sve rute se testiraju kroz **Postman**. Rute za grupe i troškove zahtevaju autentifikaciju (`Authorization: Bearer <token>` dobijen kroz `/api/login`).

### Koraci za testiranje kroz Postman

U Postman-u, za svaku zaštićenu rutu, u tabu **Authorization** izabrati tip **Bearer Token** i nalepiti token (bez reči "Bearer" ispred, Postman je sam dodaje).

**1. Registracija**
- `POST http://127.0.0.1:8000/api/register`
- Body → raw → JSON: `{ "name": "Ime Prezime", "email": "test@example.com", "password": "lozinka123" }`
- Token: ne treba

**2. Login**
- `POST http://127.0.0.1:8000/api/login`
- Body → raw → JSON: `{ "email": "test@example.com", "password": "lozinka123" }`
- Token: ne treba
- Iz odgovora kopirati vrednost polja `"token"` — koristi se u svim narednim koracima

**3. Kreiranje grupe**
- `POST http://127.0.0.1:8000/api/groups`
- Body: `{ "name": "Moja test grupa" }`
- Token: ✅ obavezan
- Ulogovani korisnik automatski postaje i kreator i član grupe. Zapamtiti `"id"` grupe iz odgovora.

**4. Dodavanje člana u grupu** (opciono, ako testiraš sa više korisnika)
- `POST http://127.0.0.1:8000/api/groups/{id}/members`
- Body: `{ "user_id": 2 }`
- Token: ✅ obavezan

**5. Kreiranje troška**
- `POST http://127.0.0.1:8000/api/expenses`
- Body: `{ "description": "Racun za struju", "amount": 150, "group_id": 1 }` (zameniti `group_id` sa ID-jem iz koraka 3)
- Token: ✅ obavezan

**6. Lista grupa / troškova (paginacija i filter)**
- `GET http://127.0.0.1:8000/api/groups?name=test`
- `GET http://127.0.0.1:8000/api/expenses?min_amount=50&max_amount=500`
- Token: ✅ obavezan

**7. Balans po grupi**
- `GET http://127.0.0.1:8000/api/groups/{id}/balance-summary` (zameniti `{id}` ID-jem grupe)
- Token: ✅ obavezan
- Vraća, za svakog člana grupe, ukupan iznos koji je platio i broj troškova, sortirano od najvišeg ka najnižem

**8. Kurs valute (spoljni servis)**
- `GET http://127.0.0.1:8000/api/exchange-rate/USD`
- Token: ✅ obavezan

**9. Brisanje grupe (provera uloga)**
- `DELETE http://127.0.0.1:8000/api/groups/{id}`
- Token: ✅ obavezan
- `user` može obrisati samo svoju grupu, `authenticated_user` ne može nijednu, `admin` može svaku — testirati sa različitim ulogama za razliku u odgovoru (200 vs 403)

**10. Logout**
- `POST http://127.0.0.1:8000/api/logout`
- Token: ✅ obavezan (nakon logout-a isti token više ne radi — sledeći zahtev s njim vraća `401`)

## Modeli i relacije

* **User** — `hasMany` Expense (kao platilac), `belongsToMany` Group (preko `group_user`)
* **Group** — `belongsTo` User (kreator), `belongsToMany` User (članovi), `hasMany` Expense
* **Expense** — `belongsTo` Group, `belongsTo` User (platilac)

## Autentifikacija
| Ruta | Metoda | Opis | Telo zahteva |
| --- | --- | --- | --- |
| `/api/register` | POST | Registracija novog korisnika (uloga `user` po difoltu) | `{ "name": "", "email": "", "password": "" }` |
| `/api/login` | POST | Prijava korisnika, vraća Sanctum token | `{ "email": "", "password": "" }` |
| `/api/logout` | POST 🔒 | Odjava korisnika, briše token | - |

## Grupe

Sve rute zahtevaju autentifikaciju (`auth:sanctum`).

| Ruta | Metoda | Opis |
| --- | --- | --- |
| `/api/groups` | GET | Lista grupa (paginacija po 5, filter `?name=`) |
| `/api/groups` | POST | Kreiranje grupe (ulogovani korisnik postaje kreator) |
| `/api/groups/{id}` | GET | Detalji grupe (sa kreatorom, članovima, troškovima) |
| `/api/groups/{id}` | PUT/PATCH | Izmena grupe |
| `/api/groups/{id}` | DELETE | Brisanje grupe — **ograničeno po ulozi**: `admin` briše svaku, `user` samo svoju, `authenticated_user` ne sme nijednu |
| `/api/groups/{id}/expenses` | GET | Svi troškovi jedne grupe (ugnježdena ruta) |
| `/api/groups/{id}/members` | POST | Dodavanje člana u grupu (`{ "user_id": 1 }`) |
| `/api/groups/{id}/balance-summary` | GET | Ukupan iznos koji je svaki član grupe platio, sortirano opadajuće (JOIN + agregacija) |
| `/api/groups/{id}/export-csv` | GET | Export svih troškova grupe u CSV fajl |

## Troškovi

| Ruta | Metoda | Opis |
| --- | --- | --- |
| `/api/expenses` | GET | Lista troškova (paginacija po 5, filter `?min_amount=`, `?max_amount=`) |
| `/api/expenses` | POST | Kreiranje troška (ulogovani korisnik postaje platilac) |
| `/api/expenses/{id}` | GET | Detalji troška |
| `/api/expenses/{id}` | PUT/PATCH | Izmena troška — **ograničeno po vlasništvu**: `admin` menja svaki, ostali samo trošak koji su sami platili |
| `/api/expenses/{id}` | DELETE | Brisanje troška — **ograničeno po vlasništvu**: `admin` briše svaki, ostali samo trošak koji su sami platili |

## Spoljni servisi

Oba spoljna poziva su keširana (`Cache::remember`) da se ne pozivaju ponovo pri svakom zahtevu — kursna lista na 60 minuta, praznici na 24h.

| Ruta | Metoda | Opis |
| --- | --- | --- |
| `/api/exchange-rate/{currency}` | GET | Trenutni kurs EUR → zadata valuta, preko [frankfurter.app](https://frankfurter.app) |
| `/api/public-holidays/{countryCode}` | GET | Državni praznici za tekuću godinu, preko [date.nager.at](https://date.nager.at) (kod drzave, npr. `RS`, `US`, `DE`) |

## Korisničke uloge

Tri uloge, sa različitim ovlašćenjima:

* **`user`** — može da kreira grupe/troškove, briše samo svoje grupe
* **`authenticated_user`** — ulogovan, ali bez prava brisanja grupa
* **`admin`** — puna kontrola, briše bilo koju grupu

Isto pravilo (vlasništvo, admin sme sve) važi i za izmenu/brisanje troškova — vidi tabelu Troškovi.

## Struktura projekta

```text
expense-sharing-app/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── GroupController.php
│   │   │   └── ExpenseController.php
│   │   └── Resources/
│   │       ├── GroupResource.php
│   │       └── ExpenseResource.php
│   └── Models/
│       ├── User.php
│       ├── Group.php
│       └── Expense.php
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
* Aplikacija nema frontend deo, testira se isključivo preko Postman-a.

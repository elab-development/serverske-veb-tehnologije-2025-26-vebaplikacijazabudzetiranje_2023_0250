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
* REST API (uključujući poziv spoljnih javnih servisa — [frankfurter.app](https://frankfurter.app) za kursnu listu i [date.nager.at](https://date.nager.at) za državne praznike)
* Keširanje odgovora spoljnih servisa (Laravel `Cache`)
* Laravel Mail (obaveštenje mejlom kad neko duguje novac u grupi)

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

Za slanje mejlova (reset lozinke, obaveštenje o dugu) bez prave email konfiguracije, dodati:

```env
MAIL_MAILER=log
```

Mejlovi se tada ne šalju stvarno, već se upisuju u `storage/logs/laravel.log` — dovoljno za testiranje.

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

Opciono, napuniti bazu test podacima (10 korisnika — od čega 3 sa fiksnim nalozima po ulozi radi lakšeg testiranja, 10 grupa, 20 troškova sa nasumičnim podacima):

```bash
php artisan db:seed
```

ili oboje odjednom (briše i ponovo pravi sve tabele):

```bash
php artisan migrate:fresh --seed
```

Seeder pravi i 3 fiksna naloga, po jedan za svaku ulogu (lozinka za sve: `password`, **samo za lokalni razvoj/testiranje — ne koristiti u produkciji i promeniti lozinke po potrebi**) — korisno za testiranje razlika u ovlašćenjima:

| Email | Rola |
| --- | --- |
| `admin@example.com` | `admin` |
| `authenticated@example.com` | `authenticated_user` |
| `user@example.com` | `user` |

### Tabele u bazi

| Tabela | Opis |
| --- | --- |
| `users` | Korisnici, sa `role` kolonom (`admin`, `authenticated_user`, `user`) |
| `groups` | Grupe za deljenje troškova, svaka ima kreatora (`created_by`) |
| `group_user` | Pivot tabela — članstvo korisnika u grupama (many-to-many) |
| `expenses` | Troškovi, vezani za grupu (`group_id`) i platioca (`paid_by`), sa kategorijom (`category`) i datumom plaćanja (`paid_at`) |
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
- Body: `{ "description": "Racun za struju", "category": "Režije", "amount": 150, "group_id": 1, "paid_at": "2026-08-15" }` (zameniti `group_id` sa ID-jem iz koraka 3; `category` je obavezno polje; `paid_at` je opciono — ako se izostavi uzima se današnji datum)
- Token: ✅ obavezan

**6. Lista grupa / troškova (paginacija i filter)**
- `GET http://127.0.0.1:8000/api/groups?name=test`
- `GET http://127.0.0.1:8000/api/expenses?min_amount=50&max_amount=500`
- Token: ✅ obavezan

**7. Balans po grupi**
- `GET http://127.0.0.1:8000/api/groups/{id}/balance-summary` (zameniti `{id}` ID-jem grupe)
- Token: ✅ obavezan
- Vraća, za svakog člana grupe, ukupan iznos koji je platio i broj troškova, sortirano od najvišeg ka najnižem

**8. Kurs valute (spoljni servis, keširano)**
- `GET http://127.0.0.1:8000/api/exchange-rate/USD`
- Token: ✅ obavezan
- Prvi poziv ide na frankfurter.app, svaki naredni u narednih 60 min vraća se iz keša (brži odgovor, isti podatak)

**9. Državni praznici (drugi spoljni servis, keširano)**
- `GET http://127.0.0.1:8000/api/public-holidays/RS`
- Token: ✅ obavezan
- Poziva date.nager.at, keširano 24h; kod države mora biti ISO format (npr. `RS`, `US`, `DE`)

**10. Export troškova grupe u CSV**
- `GET http://127.0.0.1:8000/api/groups/{id}/export-csv`
- Token: ✅ obavezan
- Vraća CSV fajl za preuzimanje sa svim troškovima grupe (opis, iznos, ko je platio, datum)

**11. Ko duguje kome u grupi (settlement)**
- `GET http://127.0.0.1:8000/api/groups/{id}/settlement`
- Token: ✅ obavezan
- Pretpostavka: svaki trošak grupe se deli ravnomerno na sve trenutne članove (kao Splitwise). Vraća za svakog člana koliko je platio, koliko je "fer" trebalo da plati, i status: `duguje` / `potražuje` / `izmireno`

**12. Obaveštenje mejlom o dugu**
- `POST http://127.0.0.1:8000/api/groups/{id}/notify-debts`
- Token: ✅ obavezan
- Šalje mejl (upisuje u `storage/logs/laravel.log` ako je `MAIL_MAILER=log`) svakom članu grupe koji trenutno duguje novac, na osnovu iste logike kao settlement

**13. Brisanje grupe (provera uloga)**
- `DELETE http://127.0.0.1:8000/api/groups/{id}`
- Token: ✅ obavezan
- `user` može obrisati samo svoju grupu, `authenticated_user` ne može nijednu, `admin` može svaku — testirati prijavom preko fiksnih seed naloga (vidi tabelu u sekciji Baza podataka) za razliku u odgovoru (200 vs 403)

**14. Izmena/brisanje tuđeg troška ili grupe (provera vlasništva — IDOR zaštita)**
- `PUT` ili `DELETE http://127.0.0.1:8000/api/expenses/{id}`, ili `PUT http://127.0.0.1:8000/api/groups/{id}`
- Token: ✅ obavezan
- Ako trošak/grupu nije kreirao ulogovani korisnik (i korisnik nije `admin`), vraća `403` — testirati prijavom kao drugi korisnik na tuđ trošak/grupu

**15. Logout**
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
| `/api/groups/{id}` | PUT/PATCH | Izmena grupe — **samo kreator grupe ili admin** (IDOR zaštita) |
| `/api/groups/{id}` | DELETE | Brisanje grupe — **ograničeno po ulozi**: `admin` briše svaku, `user` samo svoju, `authenticated_user` ne sme nijednu |
| `/api/groups/{id}/expenses` | GET | Svi troškovi jedne grupe (ugnježdena ruta) |
| `/api/groups/{id}/members` | POST | Dodavanje člana u grupu (`{ "user_id": 1 }`) |
| `/api/groups/{id}/balance-summary` | GET | Ukupan iznos koji je svaki član grupe platio, sortirano opadajuće (JOIN + agregacija) |
| `/api/groups/{id}/export-csv` | GET | Export svih troškova grupe u CSV fajl za preuzimanje |
| `/api/groups/{id}/settlement` | GET | Ko duguje kome u grupi (ravnomerna podela troškova, kao Splitwise) |
| `/api/groups/{id}/notify-debts` | POST | Šalje mejl svakom članu grupe koji trenutno duguje novac |

## Troškovi

| Ruta | Metoda | Opis |
| --- | --- | --- |
| `/api/expenses` | GET | Lista troškova (paginacija po 5, filter `?min_amount=`, `?max_amount=`) |
| `/api/expenses` | POST | Kreiranje troška (ulogovani korisnik postaje platilac; `category` je obavezno polje; `paid_at` opciono — datum plaćanja, podrazumevano današnji) |
| `/api/expenses/{id}` | GET | Detalji troška |
| `/api/expenses/{id}` | PUT/PATCH | Izmena troška — **samo vlasnik troška ili admin** (IDOR zaštita) |
| `/api/expenses/{id}` | DELETE | Brisanje troška — **samo vlasnik troška ili admin** (IDOR zaštita) |

## Spoljni servisi

Odgovori oba servisa se keširaju (`Cache::remember`) da se ne poziva spoljni API pri svakom zahtevu.

| Ruta | Metoda | Opis |
| --- | --- | --- |
| `/api/exchange-rate/{currency}` | GET | Trenutni kurs EUR → zadata valuta, preko [frankfurter.app](https://frankfurter.app) (keš 60 min) |
| `/api/public-holidays/{countryCode}` | GET | Državni praznici za tekuću godinu, preko [date.nager.at](https://date.nager.at) (keš 24h) |

## Korisničke uloge

Tri uloge, sa različitim ovlašćenjima (seeder pravi fiksan nalog za svaku — vidi sekciju Baza podataka):

* **`user`** — može da kreira grupe/troškove, briše/menja samo svoje grupe i troškove
* **`authenticated_user`** — ulogovan, ali bez prava brisanja grupa
* **`admin`** — puna kontrola, briše/menja bilo čiju grupu ili trošak

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
│   ├── Mail/
│   │   └── DebtNotification.php
│   └── Models/
│       ├── User.php
│       ├── Group.php
│       └── Expense.php
│
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│       ├── UserSeeder.php
│       ├── GroupSeeder.php
│       └── ExpenseSeeder.php
│
├── resources/
│   └── views/
│       └── emails/
│           └── debt-notification.blade.php
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

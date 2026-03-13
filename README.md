# Library API

REST API do zarządzania biblioteką zbudowane w Laravel 12, PHP 8.3 i MySQL 8.

---

## Wymagania

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Git](https://git-scm.com/)
- Make (opcjonalnie, ale zalecane)

---

## Uruchomienie projektu

### 1. Sklonuj repozytorium

```bash
git clone https://github.com/Dawid1992/library-api.git
cd library-api
```

### 2. Skopiuj plik konfiguracyjny

```bash
cp .env.example .env
```

### 3. Zbuduj i uruchom kontenery

```bash
docker-compose up -d --build
```

> Pierwsze uruchomienie może potrwać kilka minut — Docker pobiera obrazy i instaluje zależności PHP.

### 4. Wygeneruj klucz aplikacji

```bash
docker-compose exec app php artisan key:generate
```

### 5. Uruchom migracje

```bash
docker-compose exec app php artisan migrate
```

### 6. Uruchom worker kolejki

W osobnym terminalu:

```bash
docker-compose exec app php artisan queue:work
```

> Worker musi być uruchomiony, aby job `UpdateAuthorLastAddedBook` działał. Bez niego książki będą się dodawać poprawnie, ale pole `last_added_book_id` u autorów nie zostanie zaktualizowane.

### 7. Gotowe

API dostępne pod adresem: **http://localhost:8100**

---

## Autoryzacja (Laravel Sanctum)

Projekt używa **Laravel Sanctum** do autoryzacji opartej na tokenach. Endpoint `POST /api/books` wymaga uwierzytelnienia.

### Tworzenie użytkownika

Utwórz użytkownika przez Laravel Tinker:

```bash
docker-compose exec app php artisan tinker --execute="
\App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@test.com',
    'password' => bcrypt('password123')
]);
"
```

### Logowanie i pobieranie tokenu

```bash
curl -s -X POST http://localhost:8100/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@test.com","password":"password123"}'
```

Odpowiedź:

```json
{
  "token": "1|abc123xyz..."
}
```

### Używanie tokenu

Dołącz token do żądań wymagających autoryzacji w nagłówku `Authorization`:

```bash
curl -s -X POST http://localhost:8100/api/books \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TWOJ_TOKEN" \
  -d '{"title":"Diuna","isbn":"978-83-7839-461-0","published_year":1965}'
```

> **Ważne:** Zawsze dodawaj nagłówek `Accept: application/json` do żądań API. Bez niego Laravel może zwrócić odpowiedź HTML zamiast JSON (np. przy błędach walidacji lub braku autoryzacji).

### Które endpointy wymagają autoryzacji?

| Metoda | Endpoint | Autoryzacja |
|--------|----------|-------------|
| GET | `/api/books` | nie |
| GET | `/api/books/{id}` | nie |
| **POST** | **`/api/books`** | **tak** |
| PUT | `/api/books/{id}` | nie |
| DELETE | `/api/books/{id}` | nie |
| GET | `/api/authors` | nie |
| GET | `/api/authors/{id}` | nie |
| POST | `/api/authors` | nie |
| PUT | `/api/authors/{id}` | nie |
| DELETE | `/api/authors/{id}` | nie |
| POST | `/api/login` | nie |

---

## Kolejka (Queue)

Projekt używa kolejki bazodanowej do asynchronicznego aktualizowania pola `last_added_book_id` w tabeli `authors`.

**Jak to działa:**

1. Tworzysz nową książkę przez `POST /api/books`
2. Książka zostaje zapisana w bazie, autorzy zsynchronizowani
3. Job `UpdateAuthorLastAddedBook` trafia do tabeli `jobs` w bazie danych
4. Worker (`queue:work`) pobiera job i aktualizuje `last_added_book_id` u wszystkich powiązanych autorów

**Uruchomienie workera:**

```bash
docker-compose exec app php artisan queue:work
```

**Podgląd oczekujących jobów** (w osobnym terminalu lub phpMyAdmin):

```bash
docker-compose exec app php artisan queue:monitor
```

> W środowisku produkcyjnym worker zarządzany jest przez **Supervisor**, który automatycznie go restartuje w razie awarii.

---

## Testy

Projekt zawiera dwa rodzaje testów.

**Feature testy** (`tests/Feature/`) — testują całe endpointy HTTP z bazą danych:

```bash
docker-compose exec app php artisan test --filter BookApiTest
```

```bash
docker-compose exec app php artisan test --filter AuthorSearchTest
```

**Unit testy** (`tests/Unit/`) — testują logikę biznesową w izolacji, bez bazy danych (z mockami):

```bash
docker-compose exec app php artisan test --filter BookLogicTest
```

**Wszystkie testy naraz:**

```bash
docker-compose exec app php artisan test
```

### Pokrycie testów

| Klasa | Typ | Przypadki |
|---|---|---|
| `BookApiTest` | Feature | tworzenie książki (z autorami i bez), walidacja tytułu, duplikat ISBN, nieistniejący autor, rok z przyszłości, usuwanie książki, usuwanie relacji, 404 przy usuwaniu |
| `BookLogicTest` | Unit | dispatch joba po create, sync autorów, odfiltrowywanie `author_ids`, usuwanie, 404 przy braku książki |
| `AuthorSearchTest` | Feature | filtrowanie autorów po tytule książki, zwracanie wszystkich autorów bez parametru `search` |

---

## Kontenery

| Kontener     | Opis              | Port                        |
|--------------|-------------------|-----------------------------|
| `app`        | PHP 8.3-FPM       | —                           |
| `nginx`      | Web server        | http://localhost:8100       |
| `db`         | MySQL 8.0         | localhost:3306              |
| `phpmyadmin` | Panel bazy danych | http://localhost:8101       |

### phpMyAdmin

Zaloguj się na http://localhost:8101 używając:

- **Użytkownik:** `library_user`
- **Hasło:** `secret`

---

## Przydatne komendy

Jeśli masz zainstalowany `make`:

```bash
make up          # uruchom kontenery
make down        # zatrzymaj kontenery
make build       # przebuduj kontenery
make shell       # wejdź do kontenera app (bash)
make migrate     # uruchom migracje
make fresh       # reset bazy + seedery
make tinker      # Laravel Tinker (REPL)
make logs        # logi kontenera app
```

Bez `make` — odpowiedniki z docker-compose:

```bash
docker-compose up -d
docker-compose down
docker-compose exec app bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose logs -f app
```

---

### Tworzenie autora przez konsolę

Projekt zawiera Artisan Command `author:create`, który umożliwia interaktywne dodawanie nowych autorów.

**Uruchomienie komendy:**

```bash
docker-compose exec app php artisan author:create

---

## Struktura projektu

```
library-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthorController.php
│   │   │   └── BookController.php
│   │   └── Requests/
│   │       ├── StoreAuthorRequest.php
│   │       ├── UpdateAuthorRequest.php
│   │       ├── StoreBookRequest.php
│   │       └── UpdateBookRequest.php
│   ├── Jobs/
│   │   └── UpdateAuthorLastAddedBook.php
│   ├── Logic/
│   │   ├── IAuthorLogic.php
│   │   ├── AuthorLogic.php
│   │   ├── IBookLogic.php
│   │   └── BookLogic.php
│   ├── Models/
│   │   ├── Author.php
│   │   └── Book.php
│   └── Repositories/
│       ├── IAuthorRepository.php
│       ├── AuthorRepository.php
│       ├── IBookRepository.php
│       └── BookRepository.php
├── database/
│   ├── factories/
│   │   ├── AuthorFactory.php
│   │   └── BookFactory.php
│   └── migrations/
│       ├── ..._create_authors_table.php
│       ├── ..._create_books_table.php
│       ├── ..._create_author_book_table.php
│       └── ..._add_last_added_book_id_to_authors_table.php
├── docker/
│   └── nginx/
│       └── default.conf
├── routes/
│   └── api.php
├── tests/
│   ├── Feature/
│   │   ├── BookApiTest.php
│   │   └── AuthorSearchTest.php
│   └── Unit/
│       └── BookLogicTest.php
├── docker-compose.yml
├── Dockerfile
└── .env.example
```

---

## Schemat bazy danych

```
authors                  author_book               books
─────────────────────    ────────────────          ──────────────────────
id                       author_id (FK) ───────── id
first_name               book_id (FK)  ─────────  title
last_name                                          isbn
bio                                                published_year
last_added_book_id (FK) ─────────────────────────  description
created_at                                         created_at
updated_at                                         updated_at
```

Relacja **many-to-many**: jedna książka może mieć wielu autorów, jeden autor może mieć wiele książek.

---

## Rozwiązywanie problemów

**Port zajęty?**
Zmień porty w `docker-compose.yml` na wolne u siebie.

**Błąd uprawnień (storage)?**
```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

**Baza danych nie odpowiada?**
Poczekaj chwilę po `docker-compose up` — MySQL potrzebuje kilku sekund na inicjalizację, następnie ponów `php artisan migrate`.

**Job nie aktualizuje autorów?**
Upewnij się że worker jest uruchomiony: `docker-compose exec app php artisan queue:work`

**API zwraca HTML zamiast JSON?**
Dodaj nagłówek `Accept: application/json` do każdego żądania.

**Błąd 401 Unauthenticated przy POST /api/books?**
Zaloguj się przez `POST /api/login`, pobierz token i dołącz go do żądania jako `Authorization: Bearer TWOJ_TOKEN`.

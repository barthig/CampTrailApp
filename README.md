# Camptrail

## Przegląd projektu

Camptrail to dedykowana aplikacja webowa do zarządzania wyprawami kempingowymi. Umożliwia:

* rejestrację i logowanie użytkowników,
* definiowanie tras i przypisywanie kamperów do tras,
* zarządzanie miejscami docelowymi,
* obsługę powiadomień i kontaktów alarmowych.

Projekt zrealizowano w czystym PHP 8+, z wykorzystaniem PostgreSQL, konteneryzacją w Dockerze oraz serwerem Nginx.

## Kluczowe funkcje

* **Autoryzacja i uwierzytelnianie**: rejestracja, logowanie, resetowanie hasła.
* **RBAC**: role i uprawnienia weryfikowane w czasie rzeczywistym.
* **CRUD modułów**:

  * Kampery (Campers) – zarządzanie pojazdami kempingowymi,
  * Miejsca docelowe (Destinations),
  * Trasy (Routes),
  * Powiadomienia (Notifications),
  * Kontakty alarmowe (Emergency Contacts).
* **Dashboard statystyk**: podsumowania i wykresy aktywności i tras.
* **Upload plików**: zdjęcia awatarów i dokumenty.
* **Responsywny interfejs**: dostosowany do urządzeń mobilnych i desktop.

## Zabezpieczenia

* **Hashowanie haseł**: bcrypt z saltingiem w `src/Controllers/ProfileController.php` i `src/Controllers/SecurityController.php`.
* **Ochrona przed SQL Injection**: parametryzowane zapytania.
* **Zarządzanie sesją**: `SessionManager.php` regeneruje identyfikator, ustawia timeout i zabezpieczenia ciasteczek.
* **HTTPS**: wymuszanie SSL w `docker/nginx/nginx.conf`.
* **Limity uploadu**: `upload_max_filesize` w PHP-FPM.
* **Content Security Policy (CSP)**: nagłówki w konfiguracji Nginx.

## Scenariusze użycia

1. **Planowanie wyprawy**: tworzenie trasy i przypisywanie kamperów.
2. **Zarządzanie kamperami**: dodawanie, edycja, usuwanie pojazdów.
3. **Monitorowanie tras**: przegląd szczegółów i powiadomień.
4. **Obsługa alarmów**: szybki dostęp do kontaktów alarmowych.
5. **Historia tras**: raporty dystansu i czasu zakończonych tras.
6. **Przypomnienia**: automatyczne alerty o przeglądach technicznych i polisach.

## Typy modułów

* **Miejsca docelowe** (Destinations): definicja punktów początkowych i końcowych trasy.
* **Trasy** (Routes): historia przejazdów kamperami z metrykami dystansu i czasu.
* **Kampery** (Campers): dane pojazdów, terminy przeglądów i polis.
* **Powiadomienia** (Notifications): przypomnienia i alerty systemowe.
* **Statystyki** (Dashboard): metryki użytkownika (liczba tras, łączny dystans).
* **Profil** (User Profile): widok i edycja danych użytkownika.
* **Pomoc i FAQ**: często zadawane pytania i odpowiedzi.

## Struktura bazy danych

# Tabele

```
roles
permissions
role_permissions
users
campers
camper_images
destinations
amenities
destination_amenity
destination_images
routes
insurance
technical_inspection
notifications
emergency_contact
```

### Funkcje SQL

* `set_emergency_contact_updated_at()` – automatycznie aktualizuje kolumnę `updated_at`.
* `set_updated_timestamp()` – aktualizuje `updated_at` w tabelach `campers`, `destinations`, `routes`.
* `fn_add_notification(_user_id INTEGER, _message TEXT)` – dodaje wpis do `notifications`.

### Wyzwalacze (Triggers)

* `trg_emergency_contact_updated_at`: BEFORE UPDATE na `emergency_contact`.
* `trg_update_timestamp`: BEFORE UPDATE na `campers`, `destinations`, `routes`.
* `destinations_after_insert`: AFTER INSERT na `destinations` -> notyfikacja.
* `inspection_before_upsert`: BEFORE INSERT/UPDATE na `technical_inspection` -> notyfikacja.
* `policy_before_upsert`: BEFORE INSERT/UPDATE na `insurance` -> notyfikacja.
* `users_after_insert_welcome`: AFTER INSERT na `users` -> powitanie.
* `routes_after_insert_notify`: AFTER INSERT na `routes` -> notyfikacja.

### Widoki

* `camper_insurance_view` – dane polis kamperów.
* `camper_inspections_view` – historia przeglądów technicznych.
* `camper_view` – podstawowe dane pojazdu.
* `destination_full_details_view` – kompletne informacje o destynacji.
* `route_overview` – lista tras z najważniejszymi metrykami.
* `route_stats_monthly` – miesięczne statystyki tras.
* `route_stats_yearly` – roczne statystyki tras.
* `vw_user_credentials` – dane logowania użytkownika.
* `vw_user_profile` – profil użytkownika (avatar, preferencje).

### Przykładowe dane

* **Role**: `admin`, `user`.
* **Permissions**: `manage_users`, `manage_campers`, etc.
* **Użytkownicy**: konta testowe z rolami.
* **Kampery, destynacje, trasy**: przykładowe rekordy.

## Struktura projektu

```
Camptrail/
├─ docker/             # Dockerfile'y i konfiguracje
├─ public/             # Nginx-served
│  ├─ css/             # Style
│  ├─ js/              # Fetch API scripts
│  ├─ img/             # Grafiki
│  ├─ uploads/         # Pliki użytkowników
│  └─ views/           # Szablony PHP
├─ src/                # MVC (Model, View, Controller)
│  ├─ Core/            # Bazowe klasy (DB, Sesje, CSRF)
│  ├─ Controllers/     # Obsługa żądań
│  ├─ Models/          # Encje
│  ├─ Repositories/    # Dostęp do danych
│  └─ RouterConfig.php # Definicja ścieżek
├─ sql/                # Skrypty SQL (schemat, dane)
├─ init.sql            # Inicjalizacja bazy
├─ docker-compose.yml  # Orkiestracja usług
├─ index.php           # Front controller
├─ routing.php         # Routing
└─ src/config.php      # Ustawienia (PDO, sesje)
```

## Skrypty JavaScript i Fetch API

* `public/js/auth.js`: logowanie, rejestracja, reset hasła.
* `public/js/camper.js`: CRUD kamperów (`/api/campers`).
* `public/js/destination.js`: CRUD destynacji (`/api/destinations`).
* `public/js/route.js`: zarządzanie trasami (`/api/routes`).
* `public/js/notifications.js`: pobieranie i oznaczanie powiadomień.
* `public/js/profile.js`: edycja profilu i awatara.
* `public/js/faq.js`: wczytywanie sekcji FAQ.

## Eksport danych z konta administratora

Administrator (`role = admin`) może pobrać zrzut bazy danych:

1. Zaloguj się jako administrator.
2. Przejdź do **Profil → Eksport bazy danych**.
3. Kliknij **Eksportuj** — plik `db_export_<YYYY-MM-DD_HH-MM-SS>.sql` zostanie pobrany.

Implementacja: `ProfileController::exportDb()` uruchamia `pg_dump --no-owner --no-privileges`.

### Wyjaśnienie opcji `pg_dump`

* `--no-owner`  – pomija polecenia przypisania właścicieli obiektów.
* `--no-privileges` (alias `--no-acl`)  – pomija instrukcje GRANT/REVOKE (ACL).

## Instrukcje uruchomienia

1. Zainstaluj Docker i Docker Compose.
2. Sklonuj repozytorium:

   ```bash
   git clone <repo-url>
   cd Camptrail
   ```
3. Zbuduj i uruchom kontenery:

   ```bash
   docker-compose up --build
   ```
4. (opcjonalnie) Załaduj `init.sql` do bazy:

   ```bash
   type .\init.sql | docker exec -i kamper_db psql -U postgres -d kamper_app
   ```
## Schemat bazy danych


![erd](images/ERD.png)

# Zrzuty ekranu aplikacji CampTrail

## 1. Autentykacja

Logowanie
![Ekran logowania](images/Logowanie.png )

Rejestracja
![Ekran rejestracji](images/Rejestracja.png "CampTrail – Rejestracja")

Reset hasła
![Reset hasła](images/Reset_Hasła.png "CampTrail – Reset hasła")

## 2. Trasy

Strona główna
![Strona główna CampTrail](images\Strona_Główna.png "CampTrail – Strona główna")

Dashboard
![Główny pulpit użytkownika](images/Dashboard.png "CampTrail – Dashboard")

Twoje trasy
![Lista tras użytkownika](images/Twoje_Trasy.png "CampTrail – Twoje trasy")

Dodawanie trasy
![Formularz nowej trasy](images/Nowa_trasa.png "CampTrail – Nowa trasa")

Edycja trasy
![Edycja istniejącej trasy](images/Edytuj_trasę.png "CampTrail – Edytuj trasę")



## 3. Pojazdy

Przegląd pojazdów
![Przegląd pojazdów](images/Pojazdy.png "CampTrail – Pojazdy")

Dodawanie kampera
![Formularz nowego kampera](images/Nowy_kamper.png "CampTrail – Nowy kamper")

Edycja kampera
![Edycja danych kampera](images/Edytuj_kamper.png "CampTrail – Edytuj kamper")

Szczegóły pojazdu
![Szczegóły wybranego pojazdu](images/Szczegóły_pojazdu.png "CampTrail – Szczegóły pojazdu")

## 4. Destynacje

Lista destynacji
![Lista destynacji](images/Destynacje.png "CampTrail – Destynacje")

Widok destynacji
![Mountain View – destynacja](images/dest.png "CampTrail – Widok destynacji")

Dodawanie destynacji
![Formularz nowej destynacji](images/nowa_destynacja.png "CampTrail – Nowa destynacja")

## 5. Profil

Mój profil
![Podgląd profilu użytkownika](images/Mój_Profil.png "CampTrail – Mój profil")

Edycja profilu
![Ekran edycji profilu](images/edit_prof.png "CampTrail – Edytuj profil")

Mój profil admin
![Ponowny podgląd profilu](images/admin_profil.png "CampTrail – Mój profil")

## 6. Inne

Powiadomienia![Panel powiadomień](images/Powiadomienia.png "CampTrail – Powiadomienia")

Panel administratora![Panel administratora](images/Panel.png "CampTrail – Panel administratora")
## Autor

* Bartosz Filipak


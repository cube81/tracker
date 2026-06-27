# Tracker — Aplikacja do rozliczania prac na projektach

Prosta, wygodna aplikacja do śledzenia czasu pracy na projektach i generowania raportów. Wzorowana na Clockify, zbudowana na PHP 8 + MySQL.

**URL:** https://projekty.pgms.pl

---

## Instalacja

### 1. Wgraj pliki na serwer home.pl

Skopiuj zawartość tego katalogu na hosting. Document root powinien wskazywać na folder `public/`.

```
/public          ← document root
/src
/config
/migrations
/vendor          ← Composer dependencies
/.env            ← Dane dostępu do bazy
```

### 2. Zainstaluj Composer dependencies

```bash
composer install
```

### 3. Skonfiguruj .env

Skopiuj `.env.example` na `.env` i uzupełnij dane dostępu do bazy:

```env
DB_HOST=serwer2452403.home.pl:3380
DB_USER=38860350_projekty
DB_PASS=qUYCAw35qvSCz4fTb
DB_NAME=38860350_projekty
```

### 4. Zainstaluj bazę danych

Uruchom setup lokalnie lub bezpośrednio na serwerze:

```bash
php setup.php
```

Lub import SQL manualnie:
```bash
mysql -h serwer2452403.home.pl -P 3380 -u 38860350_projekty -p migrations/001_initial.sql
```

---

## Logowanie

**Email:** `admin@tracker.local`  
**Hasło:** `admin123`

Zmień hasło w profilu lub edytuj użytkownika w zarządzaniu.

---

## Użycie

### 1. **Time Tracker** (`/tracker`)
- Dodaj opis, wybierz projekt, ustaw czas
- Wpisz godziny (np. `1700` → `17:00`)
- Czas trwania oblicza się automatycznie
- Lista dzisiejszych aktywności poniżej formularza

### 2. **Raporty** (`/reports`)
- Filtry: data od-do, status (wszystkie/rozliczone/nierozliczone), projekt
- Widok: łączny czas, breakdown by project
- Eksport do PDF (mPDF)
- Edytuj aktywności bezpośrednio z raportu

### 3. **Zarządzanie** (Admin)
- **Zleceniodawcy** (`/clients`) — CRUD
- **Projekty** (`/projects`) — CRUD + przypisywanie zespołu
- **Użytkownicy** (`/users`) — zarządzanie rolami

### 4. **Zespoły** (`/projects/{id}/team`)
- Przypisz użytkowników do projektów
- Ustaw role: Developer lub Project Manager

---

## Struktura bazy danych

```sql
users              # Użytkownicy + role (admin/pm/developer)
clients            # Zleceniodawcy
projects           # Projekty
project_members    # Członkowie zespołu w projektach
activities         # Wpisy czasu pracy
invoice_markers    # Znaczniki rozliczenia (milestones)
password_resets    # Tokeny do resetowania hasła
```

---

## Role i uprawnienia

| Akcja | Admin | PM | Developer |
|-------|-------|----|-----------|
| Zarządzanie użytkownikami | ✓ | — | — |
| Zarządzanie klientami | ✓ | — | — |
| Zarządzanie projektami | ✓ | ✓ | — |
| Dodawanie aktywności | ✓ | ✓ | ✓ |
| Raporty wszystkich | ✓ | ✓ | — |
| Własne raporty | ✓ | ✓ | ✓ |

---

## Konfiguracja poczty (opcjonalnie)

Aby włączyć resetowanie hasła mailem:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=twoj_email@gmail.com
SMTP_PASS=twoje_haslo
MAIL_FROM=noreply@pgms.pl
```

---

## API

### GET `/api/descriptions?q=<query>`
Zwraca listę poprzednich opisów aktywności dla autouzupełniania.

```json
["Aktualizacja wtyczek", "Spotkanie z klientem", "...]
```

---

## Technologia

- **Backend:** PHP 8.0+
- **Frontend:** HTML, CSS, Vanilla JS
- **Baza danych:** MySQL 5.7+
- **Biblioteki:**
  - mPDF (generowanie PDF)
  - PHPMailer (obsługa poczty)

---

## Plany przyszłościowe

- [ ] Invoice markers (widoczne na kalendarzu)
- [ ] Kalendarz z aktywościami
- [ ] Statystyki zaawansowane
- [ ] API REST
- [ ] Integracja z Git (auto-log commits)

---

## Pytania?

Jeśli masz pytania lub napotkasz błędy, zmodyfikuj kod lub skontaktuj się z deweloperem.

Powodzenia! 🚀

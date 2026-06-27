# Deployment na home.pl

## Krok 1: Przygotowanie plików

1. Sklonuj/pobierz projekt
2. Zainstaluj Composer dependencies:
   ```bash
   composer install --no-dev
   ```

## Krok 2: FTP na home.pl

Wgraj całą zawartość projektu za pośrednictwem FTP do katalogatu głównego domeny.

Struktura powinno wyglądać tak:
```
/public_html/
├── public/              ← ✓ SET AS DOCUMENT ROOT
│   ├── .htaccess
│   ├── index.php
│   ├── assets/
│   └── ...
├── src/
├── config/
├── migrations/
├── vendor/
├── .env                 ← ✓ SKONFIGURUJ
└── setup.php            ← DO URUCHOMIENIA RAZ
```

## Krok 3: Ustawiania hosta (cPanel)

1. Zaloguj się do cPanel domeny
2. Przejdź do **Addon Domains** lub **Domains**
3. Dla domeny `projekty.pgms.pl` ustaw:
   - **Document Root:** `/public/`

## Krok 4: Konfiguracja .env

1. FTP → pobierz lub utwórz `.env`
2. Wypełnij dane bazy z maila od home.pl:
   ```env
   DB_HOST=serwer2452403.home.pl:3380
   DB_USER=38860350_projekty
   DB_PASS=qUYCAw35qvSCz4fTb
   DB_NAME=38860350_projekty
   ```
3. FTP → upload `.env`

## Krok 5: Instalacja bazy danych

### Opcja A: PHPMyAdmin (najprostsze)
1. cPanel → PHPMyAdmin
2. Otwórz SQL
3. Copy-paste zawartość `migrations/001_initial.sql`
4. Execute

### Opcja B: Przez SSH (jeśli dostępne)
```bash
php setup.php
```

### Opcja C: Przez FTP (jeśli SSH niedostępny)
1. FTP → upload `setup.php` do katalogu głównego
2. Przejdź w przeglądarce do: `https://projekty.pgms.pl/setup.php`
3. Powinno wyświetlić: "✓ Database setup complete!"
4. Usuń `setup.php` z serwera

## Krok 6: Weryfikacja

1. Przejdź do https://projekty.pgms.pl/login
2. Zaloguj się:
   - Email: `admin@tracker.local`
   - Hasło: `admin123`
3. Zmień hasło w profilu

## Troubleshooting

### "404 Not Found" po zalogowaniu
- Upewnij się, że `.htaccess` jest w folderze `/public/`
- Sprawdź czy mod_rewrite jest włączony (cPanel → Apache Modules)

### "Database connection failed"
- Sprawdź połączenie z bazą w `.env`
- Host: `serwer2452403.home.pl:3380` (z portem!)
- Sprawdź czy hasło nie zawiera znaków specjalnych niezakodowanych

### "Class not found"
- Upewnij się że folder `/vendor/` jest wgrany
- Jeśli nie: `composer install` lokalnie i wgraj `/vendor/`

### Maile nie wysyłają się
- SMTP jest opcjonalne — aplikacja działa bez maila
- Aby włączyć, skonfiguruj SMTP_HOST w `.env`

---

**Gotowe!** Aplikacja powinna być dostępna pod https://projekty.pgms.pl 🚀

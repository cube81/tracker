# 🚀 Quick Start

## Wdrażanie na home.pl

### 1. Przygotuj projekt (lokalnie)
```bash
# Zainstaluj dependencies
composer install --no-dev

# Gotowe!
```

### 2. Wgraj na serwer (FTP)
- Wgraj wszystkie pliki do katalogu głównego domeny
- **Ważne:** ustawić Document Root na `/public/`

### 3. Skonfiguruj bazę (cPanel → PHPMyAdmin)
```sql
-- Skopiuj zawartość migrations/001_initial.sql
-- Wklej do SQL w PHPMyAdmin
-- Execute
```

### 4. Utwórz `.env`
```env
DB_HOST=serwer2452403.home.pl:3380
DB_USER=38860350_projekty
DB_PASS=qUYCAw35qvSCz4fTb
DB_NAME=38860350_projekty
```

### 5. Gotowe! 🎉
```
Login: admin@tracker.local
Pass:  admin123
```

---

## Struktura katalogów

```
public/              ← Document root
├── index.php        ← Front controller
├── .htaccess        ← URL rewriting
└── assets/          ← CSS, JS

src/                 ← Kod aplikacji
├── Controllers/     ← Logika biznesowa
├── Models/          ← Modele danych
├── Views/           ← Szablony HTML
└── Core/            ← Framework (Router, Auth, DB...)

config/
├── config.php       ← Ustawienia aplikacji
└── .env             ← Zmienne środowiska (git ignore!)

migrations/
└── 001_initial.sql  ← Schemat bazy

vendor/              ← Composer dependencies
└── mpdf/, phpmailer/, ...
```

---

## Kluczowe cechy

✅ **Time Tracker** — dodaj aktywności z auto-formatem czasu  
✅ **Raporty** — filtry, summary, eksport PDF  
✅ **Zarządzanie** — użytkownicy, projekty, zespoły  
✅ **Rola-based** — admin, PM, developer  
✅ **Responsive** — działa na mobilu  

---

## Pytania?

📖 Czytaj: `README.md` (pełna dokumentacja)  
🚀 Wdrażanie: `DEPLOYMENT.md` (krok po kroku)  

Powodzenia! 🎯

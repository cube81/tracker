# CLAUDE.md — Notatki dla przyszłych iteracji

## Architektura aplikacji

### Frontend
- **CSS:** Clockify-style UI w `/public/assets/css/app.css`
- **JS:** Vanilla JS (brak frameworka) w `/public/assets/js/app.js` i inline w views
- **Layout:** `/src/Views/layout/base.php` (sidebar + main)
- **Views:** Po katalogach (auth/, dashboard/, activities/, projects/, clients/, users/, teams/, reports/)

### Backend
- **Router:** `/src/Core/Router.php` — pattern matching `/users/{id}`
- **Auth:** `/src/Core/Auth.php` — session-based, role checking
- **Database:** `/src/Core/Database.php` — PDO singleton
- **View:** `/src/Core/View.php` — render views + layout
- **Mailer:** `/src/Core/Mailer.php` — PHPMailer wrapper
- **Routing:** `/public/index.php` — definiuje wszystkie routes

### Konwencje

#### Models
- Plik: `/src/Models/User.php` itd.
- Statyczne metody: `find($id)`, `all()`, `where($col, $val)`, `hydrate($row)`
- Instancjowe: `save()`, `delete()`
- Relacje: `->user()`, `->project()` (lazy load)

#### Controllers
- Plik: `/src/Controllers/UserController.php`
- Metody public bez parametrów: `index()`, `show()`, `create()`, `edit()`, `delete()`
- Dostęp do GET/POST: `$_GET`, `$_POST` bezpośrednio
- Renderowanie: `echo view('users.index', $data);`
- Auth guard: `Auth::guard('admin');` — redirect do loginu + 403

#### Views
- Ścieżka: `/src/Views/users/index.php` (dot notation: `users.index`)
- Layout automatycznie dodawany (chyba że `auth.*`)
- Zmienne dostępne bezpośrednio: `$user`, `$projects`, itp. (extract())
- Escape output: `htmlspecialchars($var)`

#### Routing
```php
$router->get('/path', [ControllerClass::class, 'method']);
$router->post('/path', [ControllerClass::class, 'method']);
$router->match(['GET', 'POST'], '/path', [...]);
$router->get('/users/{id}', [...]);  // $_GET['id'] dostępny
```

## Wspólne problemy

### "Class not found"
- Sprawdź namespace: `namespace App\Controllers;`
- Sprawdź autoloader w `public/index.php`
- Composer: `composer dump-autoload`

### "Database connection failed"
- `.env` musi mieć `DB_HOST=serwer2452403.home.pl:3380` (z portem!)
- Sprawdź czy `config/config.php` czyta `.env`
- Test: `php -r "require 'setup.php';"`

### 404 po zalogowaniu
- `.htaccess` musi być w `/public/`
- mod_rewrite musi być włączony
- Router matching: `/users/{id}` to pattern

### Session nie działa
- `session_start()` w `Auth::start()` — jest wywoływane w index.php
- HTTPS: cookies httponly + secure + samesite

## Dodawanie nowej funkcjonalności

### 1. Nowy model
```php
class Invoice {
    public int $id;
    public int $project_id;
    // ... properties
    
    public static function find($id) { ... }
    public function save() { ... }
}
```

### 2. Nowy controller
```php
class InvoiceController {
    public function index() {
        Auth::guard('admin');  // access control
        $invoices = Invoice::all();
        echo view('invoices.index', ['invoices' => $invoices]);
    }
}
```

### 3. Routing
```php
$router->get('/invoices', [InvoiceController::class, 'index']);
```

### 4. View
```php
<?php foreach ($invoices as $inv): ?>
    <tr>
        <td><?= $inv->id ?></td>
    </tr>
<?php endforeach; ?>
```

## Database migration

Edytuj `migrations/001_initial.sql`, zaraz potem:
```bash
php setup.php  # Tworzy nowe tabele
```

Lub manual w PHPMyAdmin:
```sql
ALTER TABLE activities ADD COLUMN status ENUM(...);
```

## Frontend — Time Tracker auto-format

W `tracker.php` JS:
```javascript
timeFromInput.addEventListener('blur', () => {
    let val = timeFromInput.value.replace(/\D/g, '');
    if (val.length === 4) {
        timeFromInput.value = val.substring(0, 2) + ':' + val.substring(2);
    }
});
```

Kalkulacja duracji:
```javascript
function calculateDuration() {
    const [fH, fM] = timeFromInput.value.split(':').map(Number);
    const [tH, tM] = timeToInput.value.split(':').map(Number);
    let duration = (tH * 60 + tM) - (fH * 60 + fM);
    durationDisplay.textContent = String(Math.floor(duration / 60)).padStart(2, '0') + ':' + ...;
}
```

## Raporty — PDF export

```php
$html = '... HTML string ...';
$mpdf = new Mpdf();
$mpdf->WriteHTML($html);
header('Content-Type: application/pdf');
$mpdf->Output();
```

## Bezpieczeństwo — Checklist

- ✅ PDO prepared statements (SQL injection safe)
- ✅ htmlspecialchars() na output (XSS safe)
- ✅ password_hash(PASSWORD_BCRYPT) (secure)
- ✅ Session security (httponly, secure, samesite)
- ✅ Role guards (Auth::guard())
- ✅ CSRF tokens (jeśli będziesz dodawać)

## Deployment — home.pl

1. Wgraj `/public` + `/src` + `/vendor` + `/config` (FTP)
2. `.env` z danymi bazy
3. Document root: `/public/`
4. `php setup.php` (raz na deployment)
5. `.htaccess` w `/public/`

## Performance

- Indeksy: `date`, `is_billed`, `user_id`, `project_id`
- Prepared statements (no string concat)
- Lazy loading w modelach (relacje)
- Brak N+1 queries (use joins jeśli třeba)

## TODO na przyszłość

- [ ] Invoice markers — widoczne na kalendarzu raportów
- [ ] CSV export (oprócz PDF)
- [ ] Bulk actions w raportach (zaznacz wszystkie jako rozliczone)
- [ ] Email notificationsy po rozliczeniu
- [ ] Webhook integracja z innym systemem
- [ ] API REST (JSON endpoints)

---

**Ostatnia aktualizacja:** 2026-06-26  
**Wersja aplikacji:** 1.0.0 — Production Ready  
**Hosting:** home.pl (serwer2452403)

<h1>Nowy zleceniodawca</h1>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Nazwa</label>
            <input type="text" name="name" required autofocus>
        </div>

        <div class="form-group">
            <label>Opis</label>
            <textarea name="description"></textarea>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Utwórz</button>
            <a href="/clients" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

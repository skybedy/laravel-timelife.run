# Deployment Guide - Po Git Cleanup

**Datum:** 2025-12-25
**Status:** Čeká na deployment na produkci

---

## ✅ Co už je hotové (na produkčním serveru)

1. ✅ **Git větve vyčištěny**
   - Main branch je aktualizovaný (204 nových commitů)
   - Všechny staré development větve smazány
   - GitHub má jen `main` větev

2. ✅ **Composer dependencies** - aktualizovány
3. ✅ **NPM dependencies** - nainstalovány
4. ✅ **Assets buildnuté** - `npm run build` hotovo

---

## ⚠️ CO ZBÝVÁ DODĚLAT NA PRODUKCI

### 1. Vyřešit duplicitní záznamy v results tabulce

**Problém:**
Migrace `2025_12_25_114159_add_unique_constraint_to_results_table` selhala, protože v databázi jsou duplicitní záznamy.

**Chybová hláška:**
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '3-2024-06-24-0'
for key 'results_unique_constraint'
```

**Řešení - PŘED spuštěním migrace:**

```sql
-- 1. Připoj se do databáze
mysql -u [user] -p [database_name]

-- 2. Zkontroluj duplicity
SELECT registration_id, finish_time_date, finish_time_sec, COUNT(*) as count
FROM results
GROUP BY registration_id, finish_time_date, finish_time_sec
HAVING count > 1;

-- 3. Smaž duplicitní záznamy (nechá jen nejnovější)
DELETE r1 FROM results r1
INNER JOIN results r2
WHERE r1.id < r2.id
  AND r1.registration_id = r2.registration_id
  AND r1.finish_time_date = r2.finish_time_date
  AND r1.finish_time_sec = r2.finish_time_sec;

-- 4. Zkontroluj že duplicity zmizely
SELECT registration_id, finish_time_date, finish_time_sec, COUNT(*) as count
FROM results
GROUP BY registration_id, finish_time_date, finish_time_sec
HAVING count > 1;
```

### 2. Spusť migrace

```bash
cd /var/www/laravel-timelife.run
php artisan migrate --force
```

### 3. Vyčisti cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
```

### 4. Restart služeb (pokud používáš)

```bash
# Pokud máš queue workers
php artisan queue:restart

# Pokud máš supervisor
sudo supervisorctl restart all

# Pokud používáš PHP-FPM
sudo service php8.3-fpm restart
# nebo
sudo systemctl restart php8.3-fpm
```

---

## 🔍 Kontrola po deploymetu

```bash
# Zkontroluj že jsi na main
git branch --show-current
# Mělo by vypsat: main

# Zkontroluj poslední commit
git log -1 --oneline
# Mělo by být: e96e7e9 - docs: add Git branch cleanup guide for localhost work

# Zkontroluj že migrace proběhly
php artisan migrate:status

# Zkontroluj že cache je vyčištěná
php artisan config:show app.name
```

---

## 📝 Poznámky

### Co obsahuje aktuální main:
- ✅ Laravel 12
- ✅ Stripe payment integration
- ✅ Strava webhook fixes (3 hlavní bugy opraveny)
- ✅ Unique constraint pro results (prevence duplicit)
- ✅ Facebook sharing implementace
- ✅ OG image generation

### Smazané větve:
- facebook-js-sdk-implementation (merged do main)
- facebook-share-without-api-key
- jitka-dev
- dev
- castecne-fungujici-facebook
- feature/facebook-share-jitka-results
- stripe-elements-implementation
- Guest-layout
- A další (celkem 15+ větví)

---

## 🆘 Pokud něco selže

### Rollback migrace:
```bash
php artisan migrate:rollback --step=1
```

### Rollback na předchozí stav gitu:
```bash
# Vrátit se na starý main (pokud je problém)
git checkout backup-main-before-reset
# Tato větev neexistuje na remote, jen lokálně byla
```

### Zkontrolovat databázové připojení:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

---

**Vytvořeno:** 2025-12-25 pomocí Claude Code
**Pro:** Deployment po git cleanup na produkci

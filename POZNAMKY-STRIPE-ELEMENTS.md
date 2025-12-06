# Poznámky k implementaci Stripe Elements a QR plateb

**Datum posledních změn:** 2025-12-05
**Branch:** `stripe-elements-implementation`

## Co je hotové ✅

### 1. Stripe Elements implementace
- ✅ Nahrazena Stripe Checkout za Stripe Elements
- ✅ Vytvořen dvoustránkový donation flow:
  - Stránka 1: Homepage s polem pro částku (min 50 Kč) + tlačítko "Darovat"
  - Stránka 2: Výběr platební metody + formulář pro dárce (jméno, email - volitelné)
  - Stránka 3: Tři samostatné stránky pro každou platební metodu

### 2. Platební metody
- ✅ **Platba kartou** (`/donation/pay-card`)
  - Stripe Elements Payment Element
  - Payment Intent API s 3D Secure

- ✅ **Google Pay** (`/donation/pay-googlepay`)
  - Payment Request Button API
  - Detekce dostupnosti v prohlížeči

- ✅ **QR kód** (`/donation/pay-qr`)
  - Server-side generování pomocí `chillerlan/php-qrcode`
  - Czech SPD formát verze 1.0
  - IBAN: `CZ6420100000002101782768`
  - Zpráva: `Jitka [částka] pulmaratonu`

### 3. Soubory které byly změněny
- `routes/web.php` - přidány routy pro donation flow
- `app/Http/Controllers/RegistrationController.php` - nové metody:
  - `paymentSelection()` - výběr platební metody
  - `payWithCard()` - stránka pro platbu kartou
  - `payWithGooglePay()` - stránka pro Google Pay
  - `payWithQR()` - generování QR kódu
  - `createPaymentIntent()` - vytvoření Payment Intent
  - `createPaymentFromIntent()` - webhook handler pro payment_intent.succeeded

- `resources/views/index/index.blade.php` - zjednodušen na minimální donation form
- `resources/views/donations/payment-selection.blade.php` - NOVÝ - výběr platby
- `resources/views/donations/pay-card.blade.php` - NOVÝ - platba kartou
- `resources/views/donations/pay-googlepay.blade.php` - NOVÝ - Google Pay
- `resources/views/donations/pay-qr.blade.php` - NOVÝ - QR kód

- `composer.json` - přidán balíček `chillerlan/php-qrcode`

## Co je potřeba ještě dodělat 🔧

### 1. QR kód - validace délky zprávy
**POZOR:** Český SPD standard má limit **60 znaků** pro pole MSG (zpráva pro příjemce)

Aktuální formát: `Jitka [částka] pulmaratonu`
- Pro částku 100 Kč: "Jitka 100 pulmaratonu" = 21 znaků ✅
- Pro částku 1000000 Kč: "Jitka 1000000 pulmaratonu" = 26 znaků ✅

**Je to zatím v pohodě**, ale mohli bychom:
1. Přidat validaci pro případ budoucích změn
2. Zkrátit formát na něco jako "Jitka 100 Kc" nebo "Dar Jitka 100"

**Soubor k úpravě:** `app/Http/Controllers/RegistrationController.php:559`

```php
// Současný kód (řádek 559):
$message = 'Jitka ' . $amount . ' pulmaratonu';

// Případně ošetřit na max 60 znaků:
$message = mb_substr('Jitka ' . $amount . ' pulmaratonu', 0, 60);
```

### 2. Otestovat QR kód v české bance
- Otestovat naskenování QR kódu v aplikaci české banky (George, SmartBanking, atd.)
- Ověřit, že se správně vyplní:
  - Číslo účtu/IBAN
  - Částka
  - Zpráva pro příjemce

### 3. Statement descriptor v Stripe Dashboard
**Poznámka:** V kódu je nastaveno `LIFERUN.CZ JDVORACKOVA` (22 znaků), ale na kartě se zobrazuje "TENELIFE JITKA"

**Řešení:** Je potřeba zkontrolovat nastavení v Stripe Dashboard:
- Settings → Business settings → Public business information → Statement descriptor
- Toto je account-level nastavení, které přebíjí kód

## Technické detaily

### SPD formát pro QR kód
```
SPD*1.0*ACC:CZ6420100000002101782768*AM:100.00*CC:CZK*MSG:Jitka 100 pulmaratonu
```

**Struktura:**
- `SPD*1.0` - verze standardu
- `ACC:` - IBAN účtu příjemce
- `AM:` - částka s dvěma desetinnými místy
- `CC:` - měna (CZK)
- `MSG:` - zpráva pro příjemce (max 60 znaků)

### Hardcoded hodnoty v kódu
- `event_id: 10` - kampaň pro Jitku
- `payment_recipient_id: 3` - Dům pro Julii
- Účet: `2101782768/2010` (IBAN: `CZ6420100000002101782768`)

## Příkazy pro restart serveru
```bash
composer dump-autoload
php artisan serve --host=0.0.0.0 --port=8000
```

## Jak pokračovat zítra

1. **Otestuj QR kód:**
   - Otevři http://localhost:8000
   - Zadej částku (např. 100 Kč)
   - Zvol QR kód
   - Naskenuj v mobilní bance a zkontroluj, jestli se správně vyplní údaje

2. **Případně uprav formát zprávy:**
   - Pokud chceš jiný formát než "Jitka 100 pulmaratonu"
   - Soubor: `app/Http/Controllers/RegistrationController.php:559`

3. **Otestuj Stripe platby:**
   - Platba kartou
   - Google Pay (pokud máš Google účet a podporovaný prohlížeč)

4. **Zkontroluj Stripe Dashboard:**
   - Statement descriptor nastavení
   - Webhook pro `payment_intent.succeeded`

## Server status
Server běží na `http://localhost:8000`

Background procesy:
- Bash 4377cd - php artisan serve
- Bash 5b9645 - php artisan serve (backup)

# Poznámky k implementaci Stripe Elements

**Datum posledních změn:** 2025-12-05
**Branch:** `stripe-elements-implementation`
**Branch s QR kódem:** `stripe-elements-with-qr-code` (záloha)

## Co je hotové ✅

### 1. Stripe Elements implementace
- ✅ Nahrazena Stripe Checkout za Stripe Elements
- ✅ Vytvořen dvoustránkový donation flow:
  - Stránka 1: Homepage s polem pro částku (min 50 Kč) + tlačítko "Darovat"
  - Stránka 2: Výběr platební metody + formulář pro dárce (jméno, email - volitelné)
  - Stránka 3: Dvě samostatné stránky pro každou platební metodu

### 2. Platební metody
- ✅ **Platba kartou** (`/donation/pay-card`)
  - Stripe Elements Payment Element
  - Payment Intent API s 3D Secure
  - Všechny platby sledovatelné v Stripe Dashboard

- ✅ **Google Pay** (`/donation/pay-googlepay`)
  - Payment Request Button API
  - Detekce dostupnosti v prohlížeči
  - Rychlá platba pro mobilní zařízení

## ⚠️ QR kód byl odstraněn
**Důvod:** QR platby jdou přímo na bankovní účet bez možnosti sledování
- Žádné webhooky
- Žádné záznamy v databázi
- Nemožnost automatického párování plateb
- **Záloha řešení:** Branch `stripe-elements-with-qr-code` obsahuje plně funkční QR implementaci

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

## Co je potřeba ještě dodělat 🔧

### 1. Statement descriptor v Stripe Dashboard
**Poznámka:** V kódu je nastaveno `LIFERUN.CZ JDVORACKOVA` (22 znaků), ale na kartě se zobrazuje "TENELIFE JITKA"

**Řešení:** Je potřeba zkontrolovat nastavení v Stripe Dashboard:
- Settings → Business settings → Public business information → Statement descriptor
- Toto je account-level nastavení, které přebíjí kód

## Technické detaily

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

1. **Otestuj Stripe platby:**
   - Otevři http://localhost:8000
   - Zadej částku (např. 100 Kč)
   - Vyzkoušej platbu kartou (použij Stripe testovací kartu)
   - Vyzkoušej Google Pay (pokud máš Google účet a podporovaný prohlížeč)

2. **Zkontroluj Stripe Dashboard:**
   - Ověř, že platby přicházejí správně
   - Statement descriptor nastavení
   - Webhook pro `payment_intent.succeeded`

3. **Pokud bude potřeba QR kód v budoucnu:**
   - Přepni se na branch `stripe-elements-with-qr-code`
   - Zvažte použití platební brány s QR podporou (GoPay, Comgate)

## Server status
Server běží na `http://localhost:8000`

Background procesy:
- Bash 4377cd - php artisan serve
- Bash 5b9645 - php artisan serve (backup)

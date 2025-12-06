# Prompt pro pokračování práce na produkci

## Kontext
Implementovali jsme Stripe Elements payment flow pro donation systém Jitky Dvořáčkové. Kód je připravený v branch `stripe-elements-implementation` a pushnutý na GitHub.

## Aktuální stav

### Branch k deploynutí
```bash
git checkout stripe-elements-implementation
git pull origin stripe-elements-implementation
```

### Co je implementováno ✅

1. **Třístránkový donation flow:**
   - Stránka 1: Homepage s input pro částku (min 50 Kč)
   - Stránka 2: Payment selection - formulář pro donor info (jméno, email - volitelné) + výběr platební metody
   - Stránka 3a: `/donation/pay-card` - Stripe Payment Element pro karty
   - Stránka 3b: `/donation/pay-googlepay` - Stripe Payment Request Button pro Google Pay

2. **Klíčové soubory:**
   - `routes/web.php` - přidány donation routes (řádky 48-53)
   - `app/Http/Controllers/RegistrationController.php` - metody:
     - `paymentSelection()` - payment selection page
     - `payWithCard()` - card payment page
     - `payWithGooglePay()` - Google Pay page
     - `createPaymentIntent()` - Payment Intent API endpoint
   - `resources/views/donations/payment-selection.blade.php` - výběr platby
   - `resources/views/donations/pay-card.blade.php` - platba kartou
   - `resources/views/donations/pay-googlepay.blade.php` - Google Pay platba

3. **Důležité změny:**
   - ✅ CSRF token fix - používá `document.querySelector('meta[name="csrf-token"]').getAttribute('content')`
   - ✅ `@stack('scripts')` přidán do `resources/views/layouts/app.blade.php:107`
   - ✅ Statement descriptor suffix: `JDVORACKOVA` (22 znaků limit)
   - ✅ Webhook handler podporuje `payment_intent.succeeded` event
   - ✅ Debug logging v konzoli (připraveno k odstranění po testování)

4. **Hardcoded hodnoty v kódu:**
   - `event_id: 10` - kampaň pro Jitku
   - `payment_recipient_id: 3` - Dům pro Julii (Stripe Connected Account)
   - Test data: `donor_name: "Test Testovič"`, `donor_email: "test@example.com"`

## Známé problémy na localhostu

### ❌ Problem 1: "Failed to fetch" na localhostu
- **Chyba:** Payment Intent API vrací "Failed to fetch"
- **Kde:** Platební stránky při inicializaci Stripe Elements
- **Pravděpodobná příčina:** `php artisan serve` má omezení se sessions/CSRF
- **Očekávané řešení:** Na produkci s nginx/Apache by to mělo fungovat správně
- **Test:** Po deployi zkus: http://your-domain.com/donation/payment-selection?amount=200

### ⚠️ Google Pay tlačítko zobrazení
- **Stav:** Google Pay tlačítko se renderuje jako "svislice" (vertikální čára)
- **Kde:** `/donation/pay-googlepay`
- **Možné řešení:** Přidat CSS styling pro `#google-pay-button`, nebo zvážit přechod na unified Payment Element (branch `stripe-elements-auto-googlepay`)

## Deployment checklist

### 1. Deploy kódu
```bash
cd /path/to/production
git checkout stripe-elements-implementation
git pull origin stripe-elements-implementation
```

### 2. Clear cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 3. Test platby
- [ ] Test card payment s kartou `4242 4242 4242 4242`
- [ ] Test Google Pay (pokud je k dispozici na HTTPS)
- [ ] Zkontroluj konzoli na chyby

### 4. Zkontroluj Stripe Dashboard
- [ ] Webhook URL správně nastavena (https://your-domain.com/webhook/stripe)
- [ ] Webhook events obsahují `payment_intent.succeeded`
- [ ] Test mode aktivní (použij test API klíče)
- [ ] Statement descriptor suffix nastavení (Settings → Public business info)

## Alternativní řešení (pokud Google Pay nefunguje)

Máme připravenou branch `stripe-elements-auto-googlepay`, která:
- Odstraňuje samostatnou Google Pay stránku
- Používá unified Payment Element, který automaticky detekuje a zobrazí Google Pay
- Jednodušší flow: payment selection → jedna platební stránka (karty + Google Pay)

**Pro přepnutí:**
```bash
git checkout stripe-elements-auto-googlepay
git pull origin stripe-elements-auto-googlepay
```

## Debugging tipy

### Zkontroluj konzoli prohlížeče
- Měly by tam být `[DEBUG]` logy od inicializace až po mounting Payment Element
- Zkontroluj `Payment Intent response status` - mělo by být 200

### Pokud Payment Intent selže
1. Zkontroluj CSRF token v meta tagu: `document.querySelector('meta[name="csrf-token"]')`
2. Zkontroluj network tab - měl by být POST request na `/registration/create/payment-intent`
3. Zkontroluj response - pokud je 419, problém s CSRF

### Laravel 12 specifické
- Endpoint `/registration/create/payment-intent` JE v `routes/web.php` (má CSRF ochranu)
- Webhooky jsou v `routes/webhooks.php` (BEZ CSRF ochrany)
- Session driver v `config/session.php` je nastaven správně

## Dotazy k zodpovězení

1. **Funguje Payment Intent API na produkci?**
   - Otevři devtools → Network tab
   - Klikni na "Pokračovat k platbě"
   - Měl by být úspěšný POST request s response obsahující `clientSecret`

2. **Zobrazuje se Google Pay button správně?**
   - Pokud ne, zvážit přechod na `stripe-elements-auto-googlepay` branch
   - Nebo přidat CSS styling

3. **Je potřeba odstranit debug logging?**
   - Ano, po úspěšném testování odstraň všechny `console.log('[DEBUG] ...')` z pay-card.blade.php a pay-googlepay.blade.php

4. **Je potřeba odstranit testovací data?**
   - Ano, v `payment-selection.blade.php` odstraň `value="Test Testovič"` a `value="test@example.com"`

## Co dělat pokud...

### Platby nefungují vůbec
1. Zkontroluj `.env` - `STRIPE_KEY` a `STRIPE_SECRET` jsou nastaveny
2. Zkontroluj logs: `tail -f storage/logs/laravel.log`
3. Zkontroluj Stripe Dashboard → Logs

### Google Pay se nezobrazuje
- To je OK - Payment Request API vyžaduje splnění podmínek (HTTPS, saved cards, Google account)
- Zvážit přechod na unified Payment Element approach

### Statement descriptor je stále špatně
- Jdi do Stripe Dashboard → Settings → Business settings → Public business information
- Změň "Statement descriptor" na account level
- Kód má `statement_descriptor_suffix: 'JDVORACKOVA'`

## Další kroky po úspěšném testu

1. Odstranit debug logging
2. Odstranit testovací default hodnoty z formuláře
3. Otestovat webhook (simulovat platbu až do konce)
4. Přepnout na produkční Stripe klíče (až bude ready)
5. Zvážit merge do main branch

## Kontakt na předchozího vývojáře
- Branch: `stripe-elements-implementation`
- Poslední commit: `8ecc9e9` - "Fix CSRF token retrieval in payment forms"
- Alternativní branch: `stripe-elements-auto-googlepay` (jednodušší Google Pay)
- Záložní branch s QR kódem: `stripe-elements-with-qr-code`

---

**TL;DR:** Deploy `stripe-elements-implementation`, clear cache, test payment flow. Pokud "Failed to fetch" - zkontroluj network tab a CSRF token. Pokud Google Pay divně vypadá - zvážit `stripe-elements-auto-googlepay` branch.

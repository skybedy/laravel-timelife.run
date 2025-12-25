# Git Branch Cleanup & Main Synchronization Guide

**Datum:** 2025-12-25
**Status:** Main branch je 5 měsíců zastaralý (poslední commit 5.8.2024)
**Problém:** Produkce běží s kódem, který není v main branch

---

## 🚨 Aktuální Situace

### Main Branch
- **Poslední commit:** 8ecce98 "050824" (5. srpna 2024)
- **Status:** VELMI ZASTARALÝ - neodpovídá produkci

### Vývojové Větve (všechny ahead of main)

| Větev | Commits ahead | Poslední aktivita | Poznámka |
|-------|--------------|-------------------|----------|
| `facebook-js-sdk-implementation` | 198 | 6 dní | ✅ **AKTIVNÍ** - právě dokončené Strava webhook opravy |
| `facebook-share-without-api-key` | 198 | 6 dní | ⚠️ Možná duplicita |
| `stripe-elements-implementation` | 185 | 2 týdny | Stripe platby |
| `castecne-fungujici-facebook` | 189 | 2 týdny | ⚠️ Experimentální FB share |
| `feature/facebook-share-jitka-results` | 188 | 2 týdny | FB share pro Jitka results |
| `jitka-dev` | 130 | 3 týdny | Možná zastaralé |
| `dev` | 11 | 4 týdny | ❓ Účel neznámý |

---

## 🎯 Cíl

1. Synchronizovat `main` branch s aktuálním produkčním stavem
2. Smazat zastaralé/duplicitní větve
3. Konsolidovat 3 Facebook větve do jedné
4. Zajistit, aby `main` byl single source of truth

---

## 📋 Krok za Krokem (na localhost)

### KROK 1: Zjisti, co běží v produkci

```bash
# Na produkčním serveru
cd /var/www/laravel-timelife.run
git log -1 --oneline
git status
```

**Zjisti:**
- Jaký je poslední commit hash v produkci?
- Je produkce na nějaké větvi nebo detached HEAD?

---

### KROK 2: Pull všechno na localhost

```bash
# Na localhost
git clone git@github.com:skybedy/laravel-timelife.run.git
cd laravel-timelife.run

# Fetch všechny větve
git fetch --all

# Checkout všechny remote větve lokálně
git checkout -b facebook-js-sdk-implementation origin/facebook-js-sdk-implementation
git checkout -b facebook-share-without-api-key origin/facebook-share-without-api-key
git checkout -b castecne-fungujici-facebook origin/castecne-fungujici-facebook
git checkout -b feature/facebook-share-jitka-results origin/feature/facebook-share-jitka-results
git checkout -b stripe-elements-implementation origin/stripe-elements-implementation
git checkout -b jitka-dev origin/jitka-dev
git checkout -b dev origin/dev

git checkout main
```

---

### KROK 3: Porovnej větve s produkcí

Zjisti, která větev je nejblíž produkčnímu stavu:

```bash
# Pokud víš commit hash z produkce (např. abc123):
git branch --contains abc123

# Nebo porovnej obsah souborů:
git diff main facebook-js-sdk-implementation -- app/ routes/ database/migrations/
```

---

### KROK 4: Analyzuj Facebook větve

Zjisti, jaké jsou rozdíly mezi 3 Facebook větvemi:

```bash
# Porovnej commity
git log --oneline --graph --decorate \
  facebook-js-sdk-implementation \
  facebook-share-without-api-key \
  castecne-fungujici-facebook \
  feature/facebook-share-jitka-results

# Porovnej soubory
git diff facebook-js-sdk-implementation facebook-share-without-api-key
git diff facebook-js-sdk-implementation castecne-fungujici-facebook
git diff facebook-js-sdk-implementation feature/facebook-share-jitka-results
```

**Otázky k zodpovězení:**
- Obsahuje `facebook-js-sdk-implementation` všechnu funkcionalitu z ostatních větví?
- Jsou tam nějaké unikátní features v jiných větvích?

---

### KROK 5: Rozhodnutí - Která větev do main?

**Doporučení:**

Pokud produkce běží s `facebook-js-sdk-implementation` (nejpravděpodobnější):

```bash
git checkout main
git merge facebook-js-sdk-implementation --no-ff -m "sync: merge production state into main (facebook-js-sdk-implementation)"

# Řeš případné konflikty
git status

# Zkontroluj, že merge je OK
git log --oneline -10

# Push do main
git push origin main
```

---

### KROK 6: Smaž zastaralé větve

**Pravděpodobně ke smazání:**

```bash
# NEJDŘÍV LOKÁLNĚ - zkontroluj, že nic neztrácíš!

# Smaž lokální větve
git branch -d castecne-fungujici-facebook
git branch -d feature/facebook-share-jitka-results
git branch -d facebook-share-without-api-key  # Pokud je duplicita
git branch -d jitka-dev  # Pokud je zastaralé

# Pak smaž remote větve (OPATRNĚ!)
git push origin --delete castecne-fungujici-facebook
git push origin --delete feature/facebook-share-jitka-results
git push origin --delete facebook-share-without-api-key
git push origin --delete jitka-dev
```

**⚠️ POZOR:** Před smazáním VŽDY zkontroluj:
```bash
# Zjisti, co je v větvi navíc oproti main
git log main..castecne-fungujici-facebook --oneline

# Pokud tam jsou důležité commity, které nejsou v main, NEMAZAT!
```

---

### KROK 7: Co dělat s `dev` větví?

```bash
# Zjisti, co je v dev
git checkout dev
git log main..dev --oneline

# Pokud je to staging větev:
# - Nech ji, ale synchronizuj s main
git merge main

# Pokud je zastaralé:
git branch -d dev
git push origin --delete dev
```

---

### KROK 8: Co dělat s `stripe-elements-implementation`?

```bash
# Zjisti, jestli jsou Stripe features už v main
git log main..stripe-elements-implementation --oneline

# Pokud NE a chceš to:
git checkout main
git merge stripe-elements-implementation

# Pokud ANO nebo je zastaralé:
git branch -d stripe-elements-implementation
git push origin --delete stripe-elements-implementation
```

---

## 🔍 Kontrolní Checklist

Po cleanup:

- [ ] `main` branch obsahuje aktuální produkční kód
- [ ] Všechny důležité features jsou v `main` nebo v aktivních feature branches
- [ ] Žádné duplicitní větve (3x Facebook sharing)
- [ ] Žádné větve starší než 1 měsíc (pokud nejsou aktivní)
- [ ] `main` branch je pushnutý na GitHub
- [ ] Produkce může být kdykoliv nasazena z `main`

---

## 📊 Doporučená Finální Struktura

**Main branch:**
- `main` - produkční kód (VŽDY aktuální)

**Aktivní feature branches (pokud je potřeba):**
- `dev` - staging/development větev
- Jednotlivé feature branches pro nové funkce (krátká životnost)

**Smazané větve:**
- ~~`castecne-fungujici-facebook`~~ - experimentální
- ~~`feature/facebook-share-jitka-results`~~ - merged do main
- ~~`facebook-share-without-api-key`~~ - merged do main
- ~~`jitka-dev`~~ - zastaralé

---

## 🚀 Best Practices do Budoucna

1. **Main = Production**
   - `main` branch vždy odpovídá produkci
   - Každý deployment = merge do main

2. **Feature Branches**
   - Krátká životnost (max 2-4 týdny)
   - Po mergi SMAZAT

3. **Staging Branch**
   - Pokud potřebuješ staging: `dev` nebo `staging`
   - Pravidelně sync s `main`

4. **Naming Convention**
   ```
   feature/nazev-funkce
   fix/nazev-opravy
   hotfix/kriticky-bug
   ```

5. **Po každém deployment:**
   ```bash
   git checkout main
   git merge feature/xyz
   git push origin main
   git branch -d feature/xyz
   git push origin --delete feature/xyz
   ```

---

## 🆘 Co dělat při problémech

### Pokud merge selže s konflikty:
```bash
# Zjisti, které soubory mají konflikty
git status

# Otevři v editoru, vyřeš konflikty
# (hledej <<<<<<, ======, >>>>>> markery)

# Po vyřešení:
git add .
git commit -m "fix: resolve merge conflicts"
```

### Pokud něco pokazíš:
```bash
# Vrať se zpět před merge
git merge --abort

# Nebo reset na předchozí stav
git reset --hard HEAD~1
```

### Pokud smažeš větev omylem:
```bash
# Zjisti hash posledního commitu (z git log nebo GitHub)
git checkout -b obnovena-vetev abc123

# Nebo restore z remote
git checkout -b obnovena-vetev origin/smazana-vetev
```

---

## 📝 Poznámky

### Co bylo v `facebook-js-sdk-implementation` (poslední commit):

**b183dd5** - "fix: comprehensive Strava webhook fixes and duplicate result prevention"
- Oprava Strava webhook (3 hlavní bugy)
- Přidání unique constraint do `results` tabulky
- Oprava `registrationExists()` volání
- Přidání výpočtu `pace_mile`
- Prevence duplicitních výsledků

**Tato větev by měla jít do main!**

---

## ✅ Doporučený Akční Plán

1. ✅ **Zjisti produkční stav** (commit hash)
2. ✅ **Pull vše na localhost**
3. ✅ **Merge `facebook-js-sdk-implementation` do main**
4. ✅ **Smaž duplicitní Facebook větve**
5. ⚠️ **Rozhodně o `stripe-elements-implementation`** (má důležité Stripe features?)
6. ⚠️ **Rozhodně o `dev`** (staging nebo smazat?)
7. ✅ **Push main na GitHub**
8. ✅ **Nasaď main na produkci**

---

**Vytvořeno:** 2025-12-25 pomocí Claude Code
**Pro:** Cleanup git větví laravel-timelife.run projektu

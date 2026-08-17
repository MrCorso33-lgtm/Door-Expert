# _shelf — polica za sklonjene elemente

Ovdje čuvamo dijelove sajta koje smo **uklonili sa sajta**, ali ih možda vraćamo nekad
u budućnosti. Ništa iz ovog foldera se ne renderuje niti učitava — sve je van teme.

## Pravila

- **Svaki sklonjeni element = jedan fajl** u ovom folderu (`{naziv}.md`).
- Fajl počinje **zaglavljem** koje sadrži:
  - **Naziv** — šta je element.
  - **Vraća se u** — tačan fajl + sekcija gdje se vraća (npr. `front-page.php`, hero slide 1).
  - **CSS/JS** — gdje su pripadajući stilovi/skripte (da se vrate zajedno).
  - **Šta predstavlja** — kratak opis + zašto je sklonjeno.
  - **Datum sklanjanja**.
- Ispod zaglavlja stoji **kompletan kod** (HTML/PHP + CSS + JS ako ima), spreman za copy-paste nazad.
- Kad nešto stavimo ovdje, **uklonimo ga sa sajta** (iz template-a i iz CSS/JS).

## Sadržaj police

| Fajl | Element | Vraća se u |
|---|---|---|
| [home-hero-category-strip.md](home-hero-category-strip.md) | Hero traka sa 4 kategorije (naslovna) | `front-page.php` — hero slide 1 |

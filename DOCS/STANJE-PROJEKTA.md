# Stanje projekta

Pregled dokle smo stigli i šta slijedi. Namjerno kratko — detalji su u git istoriji,
a rollback reference po featureu u [`WORKING_COMMITS.md`](WORKING_COMMITS.md).

> Ažurirati kad se **završi stranica**, **donese odluka** ili **portuje komponenta**.
> Ne voditi kao changelog.

---

## 1. Konverzija prototipa → WP tema

Prototipi su `.html` u repo root-u (izvor istine).

### Gotovo
| Prototip | WP |
|---|---|
| `index.html` | `front-page.php` |
| `header-demo.html` | `header.php` |
| `prodavnica.html` | `archive-product.php` (+ `inc/shop.php`, `template-parts/shop/`) |
| `product.html` | `single-product.php` (+ `template-parts/product/`, `inc/product.php`) |
| `korpa.html` | `template-parts/page/korpa.php` (+ `inc/quote-cart.php`) |
| `hvala.html` | `template-parts/page/hvala.php` |
| `404.html` | `404.php` |
| `o-nama`, `kontakt`, `akcije`, `montaza`, `brendovi` | `template-parts/page/*.php` |
| 5 brend stranica (`new-tiles`, `tau-ceramica`, `arcana-ceramica`, `ribesalbes`, `bathco`) | `template-parts/page/*.php` |

### Ostalo
| Prototip | Treba | Napomena |
|---|---|---|
| `b2b.html` | `template-parts/page/b2b.php` | "Za investitore" — linkovan sa 404 stranice, pa dok ne postoji vodi na 404 |
| `inspiracija.html` | `template-parts/page/inspiracija.php` | CSS/JS već ožičeni, fali samo template-part — linkovan sa 404 stranice |
| `kolekcije.html` | arhiva kolekcija | nova Manus stranica |
| `kolekcija-travertino.html` | pojedinačna kolekcija | nova Manus stranica |

**Ne konvertuje se:** `footer-variants.html`, `header-variants.html` (dizajn varijante za izbor, nisu stranice).

### Kategorije (poseban slučaj)
Arhitektura radi (`taxonomy-product_cat.php` + `subcategory.php` + `parts/`), ali sadržaj u
`inc/category-content.php` je popunjen **samo za `podne-plocice`**. Preostalih 17 kategorija
ima prototipe ali se renderuje šturo. To je **popunjavanje sadržaja**, ne konverzija.

---

## 2. Podešeno u wp-adminu

| Stavka | Stanje |
|---|---|
| `product_cat` | 18 kategorija (4 roditelja + 14 djece) |
| `product_brand` | 5 brendova (Tau, Arcana, Ribesalbes, New Tiles, Bathco) — **pravi podaci** |
| `pa_boja` | atribut postoji, **bez termova** (vrijednosti se dodaju uz proizvode) |
| `pa_dimenzije-vrata` | atribut postoji, bez termova |
| `pa_dimenzije-plocica` | atribut postoji, bez termova |
| Shop page | Prodavnica |
| Cart page | Korpa (preimenovana sa "Cart") |
| Hvala | stranica napravljena (slug `hvala`) |
| Checkout / My account | postoje ali se ne koriste (quote model); `/checkout/` redirektuje na korpu |
| Proizvodi | **unose se ručno**; za sad samo test proizvod |

Setup skripte (jednokratne, gitignored u `_setup/`): `setup-categories.php`, `setup-shop-taxonomies.php`.
Obrisati sa servera poslije pokretanja.

---

## 3. Odluke na čekanju

| Pitanje | Stanje |
|---|---|
| **Varijacije: Simple ili Variable?** | Odloženo ("ne znam za sad"). PDP je v1: Simple proizvodi, varijante read-only iz atributa. Nadogradnja je izolovana u jednoj sekciji |
| **Prave vrijednosti boja / dimenzija** | Nisu definisane. Prototipske su Manus placeholder. Swatch mapa slug→hex u `filters.php` pokriva par boja, ostalo pada na neutralnu sivu |
| **Wishlist** | Nije portovan → zato su tabovi "Korpa / Sačuvano" izostavljeni iz korpe |
| **Cijena po m² za keramiku** | PDP prikazuje cijenu kako je unijeta. Korekcija u korpi nije portovana (vidi red portovanja) |
| **Pretraga** | Ne postoji nigdje. Search UI na 404 stoji ali je neaktivan; header šalje na nepostojeći `/pretraga/`. Logika se eksportuje iz Saye naknadno |
| **Thumbnail-i kategorija** | Nisu postavljeni → kartice na 404 prikazuju WooCommerce placeholder. Čim se postave u wp-adminu, slike se pojave same (bez izmjene koda) |

---

## 4. Red portovanja iz Saya paketa

Izvor: [`FOR DOOR EXPERT/`](FOR%20DOOR%20EXPERT/). Kod u tim dokumentima je sintaksno
provjeren ali **nikad pokrenut ovdje** — nacrti, ne testiran kod.

| # | Komponenta | Dokument | Stanje |
|---|---|---|---|
| 1 | Quote cart | `02-PORT-quote-cart.md` | ✅ **Portovano** (uz izmjene: forma u korpi, n8n primaran) |
| 2 | Varijacije (matching engine + server-side add-to-cart) | `03-PORT-variations.md` | Čeka odluku Simple/Variable |
| 3 | Texture swatches | `11-UI-SWATCHES.md` | Ide zajedno sa varijacijama |
| 4 | PhotoSwipe lightbox | `04-PORT-gallery-lightbox.md` | Nije početo (~2h, vidljivo na svakoj stranici) |
| 5 | Trust & delivery blok | `13-UI-PDP-AND-PROJECTS.md` §1 | Nije početo (brz dobitak) |
| 6 | Per-m² korekcija cijene | `05-PORT-tile-calculator.md` | Nije početo — **revenue bug** ako se prodaje po m² |
| 7 | Ambient-first kartica + zamjena slike po boji | `12-UI-PRODUCT-CARD.md` | Nije početo |
| 8 | SEO noindex/canonical za filtrirane URL-ove | `01-AUDIT-REPORT.md` §5 (bonus) | **Relevantno** — naši filteri prave mnogo GET kombinacija |
| 9 | Pretraga (six passes) | `01-AUDIT-REPORT.md` §16 | **Kod nije izvučen** — treba eksportovati iz Saye (vidi ispod) |

**Ne portovati:** filtere (imamo svoje), product card (naš postoji).

### Pretraga — poseban slučaj

Pretraga **nigdje ne radi**: nema `search.php`, a `header.php:494` šalje na nepostojeći
`/pretraga/` sa `name="q"` umjesto WP-ovog `name="s"`. Search UI na 404 stranici je namjerno
neaktivan dok se ovo ne riješi.

Saya ima šestoprolaznu pretragu (`ADAPT (heavy)`, ~nedelju dana), ali u paketu postoji **samo
opis, bez koda**. Treba eksportovati iz Saye: `functions.php:1630-2133` (REST autocomplete),
`:2134-2417` (prikupljanje ID-eva), `:2418-2547` (podudaranje kategorija), `search.php`,
`js/search-page.js` + njihov `DOCS/BITNE FUNKCIONALNOSTI/PRETRAGA.md`.

Prolazi: *kategorija → naslov + kratki opis → brend → brend + ostatak → atribut → SKU*.
Naslijediti odluku da se **dugi opis ne pretražuje** (cross-sell rečenice davale su pogrešne
pogotke). Ograničenja: bez fuzzy matchinga, sinonima i ćirilice.

Kad pretraga proradi, u istom prolazu: žičiti 404 search box, vratiti akciju u `assets/js/404.js`,
**popraviti pretragu u headeru**, i tek onda vratiti kolonu "Popularne pretrage" na 404 — sa
stvarnim top upitima iz logovanja, ne izmišljenim.

---

## 5. Poznate zamke

- **`?ver` se ne skida** sa tema aseta (blueprint 1.2). PHP se ne bustuje → Purge poslije PHP izmjena.
- **Slug razlika:** Saya ima `pa_dimenzije-plocice`, mi `pa_dimenzije-plocica`.
- **n8n:** ako webhook padne, stiže mejl sa prefiksom `[WEBHOOK PAO]` — to je namjerni alarm.
- **Kvadratni crop + `srcset`:** pejzažna slika u kvadratnom `object-fit: cover` okviru je mutna
  na desktopu. Ako uvedemo kvadratne okvire, vidi `12-UI-PRODUCT-CARD.md` §2.

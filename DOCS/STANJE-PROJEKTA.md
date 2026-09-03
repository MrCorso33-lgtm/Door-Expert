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
| `o-nama`, `kontakt`, `akcije`, `montaza`, `brendovi` | `template-parts/page/*.php` |
| 5 brend stranica (`new-tiles`, `tau-ceramica`, `arcana-ceramica`, `ribesalbes`, `bathco`) | `template-parts/page/*.php` |

### Ostalo
| Prototip | Treba | Napomena |
|---|---|---|
| `404.html` | `404.php` | **Fali sasvim** — trenutno pada na `index.php`. CSS/JS su već ožičeni |
| `b2b.html` | `template-parts/page/b2b.php` | "Za investitore" |
| `inspiracija.html` | `template-parts/page/inspiracija.php` | CSS/JS već ožičeni, fali samo template-part |
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

**Ne portovati:** filtere (imamo svoje), product card (naš postoji), search (nedelju dana posla).

---

## 5. Poznate zamke

- **`?ver` se ne skida** sa tema aseta (blueprint 1.2). PHP se ne bustuje → Purge poslije PHP izmjena.
- **Slug razlika:** Saya ima `pa_dimenzije-plocice`, mi `pa_dimenzije-plocica`.
- **n8n:** ako webhook padne, stiže mejl sa prefiksom `[WEBHOOK PAO]` — to je namjerni alarm.
- **Kvadratni crop + `srcset`:** pejzažna slika u kvadratnom `object-fit: cover` okviru je mutna
  na desktopu. Ako uvedemo kvadratne okvire, vidi `12-UI-PRODUCT-CARD.md` §2.

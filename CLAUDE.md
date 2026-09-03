# CLAUDE.md — Pravila za rad na Door Expert (custom WP tema)

> Custom WordPress tema, bez page buildera. Konverzija HTML prototipa (Manus) u WP temu.
> Sajt: Door Expert, Podgorica (CG) — vrata, španske keramičke pločice, dekorativni umivaonici.
> Model: "quote cart" (bez online plaćanja; ~70% konverzija ide preko telefona).
>
> **Glavna dev referenca:** `_NOVI-PROJEKTI/WP_CUSTOM_DEV_BLUEPRINT.md` (lokalni kit, gitignore-ovan).
> **Prototip (izvor istine):** `.html` fajlovi u **repo root-u** (npr. `o-nama.html`, `podne-plocice.html`).
> **Projektni docs:** `DOCS/` — `DEPLOY.md`, `Category Hierarchy.md`, `CRO/`, `ISTRAZIVACKA OSNOVA/`.

## 0. Gde šta stoji (ovaj projekat)

| Šta | Gde |
|---|---|
| HTML prototip (izvor istine) | repo root: `*.html` (35 stranica, GitHub Pages) |
| Tema | `wp-content/themes/door-expert/` |
| CSS / JS | `assets/css/` + `assets/js/` **(svesno odstupanje od blueprint `css/`+`js/` — vidi §2)** |
| Template-parts | `template-parts/{category,page}/…` **(ugnježdeno, ne flat — kompleksan sajt)** |
| Content provideri | `inc/category-content.php`, `inc/page-content.php` (odvojen sadržaj od prikaza) |
| Blueprint + kit | `_NOVI-PROJEKTI/` (lokalno, van git-a) |
| Setup skripte | `_setup/` (jednokratno u browseru, `manage_options` guard, obrisati posle) |

Jezik sadržaja: **ijekavica** (porijeklo, rješenje, prijedlog, dio). Podgorica / Crna Gora.

## 1. Striktno poštovanje HTML prototipa

Pre implementacije bilo koje stranice/sekcije, **obavezno pročitaj odgovarajući `.html`
prototip u repo root-u** pre pisanja ili izmene koda.

- CSS klase, padding, margine, boje, font-size, border-radius — **identično prototipu**
- Inline `<style>`/`<script>` iz prototipa prebaci u odgovarajući `assets/css/*.css` / `assets/js/*.js`
- Tekstualni sadržaj (naslovi, labele, opisi) — **identičan prototipu**
- Struktura HTML elemenata i redosled sekcija — kao u prototipu
- Ne izmišljaj klase, vrednosti ili strukturu. Prototip je jedini izvor istine.
- Manus greške u interlinkovanju/URL-ovima **NE prenositi** — linkove sklapamo dinamički
  (`get_term_link()` / `door_expert_cat_url()` / `home_url()`), nikad hardkodovan URL.

## 2. Arhitektura — bez page buildera

- **Nema Elementora, page buildera, Contact Form 7, Jet frontend widgeta, JetSmartFilters.**
- **Dozvoljeni pluginovi:**
  - **WooCommerce** — proizvodi + `product_cat` taksonomija (korpa = quote model, bez plaćanja).
  - **JetEngine** — SAMO kao data sloj (meta boxovi na `product_cat`/CPT, `get_term_meta()`).
    Bez ijednog Jet frontend widgeta/listinga/filtera. Podaci da, prikaz ne.
  - **Rank Math** — SEO (title/meta/schema/sitemap).
- Sve je custom PHP template + custom CSS/JS. Struktura teme i `functions.php` prate blueprint (§2–3).

### Obrasci koje projekat KORISTI (drži se njih)
- **Kategorije** (`product_cat`): `taxonomy-product_cat.php` = ROUTER →
  `door_expert_cat_content($slug)` (sadržaj) + `template-parts/category/…` (prikaz).
  Roditelj sa bespoke sekcijama → `template-parts/category/parent/{slug}.php`;
  ostalo → `template-parts/category/subcategory.php`.
- **Stranice** (WP Page): `page.php` = ROUTER →
  `door_expert_page_content($slug)` (sadržaj) + `template-parts/page/{slug}.php` (prikaz);
  fallback na `the_content()` ako per-slug part ne postoji.
- **Deljene sekcije** (hero, faq, cta, breadcrumb, mobile-phone-bar): u `parts/` pod-folderu,
  primaju podatke preko `get_template_part( …, null, $args )`. Reuse između category i page.
- **URL kategorija:** NIKAD hardkodovana baza (`/c/…`). WooCommerce sam sklapa; koristi
  `door_expert_cat_url($slug)` ili `get_term_link()`.

### Content ↔ prikaz razdvojen (zbog JetEngine migracije)
- Faza A (sad, za prezentaciju klijentu): sadržaj hardkodovan u `door_expert_cat_content()` /
  `door_expert_page_content()` (PHP niz).
- Produkcija: menja se **samo unutrašnjost tih funkcija** (`get_term_meta()` / JetEngine Meta Box),
  šabloni i template-parts ostaju netaknuti.

### Odstupanja od blueprinta (svesna, dokumentovana — ne "popravljati" unazad)
1. **`assets/css/` + `assets/js/`** umesto blueprint `css/` + `js/`. Prati strukturu prototipa,
   grupiše asete. Ne poravnavamo unazad (previše churn-a + rizik od stale keša). Novi CSS/JS ide u `assets/`.
2. **Ugnježden `template-parts/`** (`category/`, `page/`, `*/parts/`) umesto flat. Bolje za obiman sajt.
3. **`taxonomy-product_cat.php`** kao primarni kategorijski šablon (blueprint pominje `archive-product.php`;
   za WC kategorije `taxonomy-product_cat.php` je specifičniji i ispravan).

Ako nešto u planu/blueprintu nije tačno ili je kontra dogovorenog — **odmah upozori korisnika**
uz objašnjenje i predlog. Nikad tiho odstupati.

## 3. Performanse (blueprint §1, §7)

- Aseti teme se verzionišu preko **`filemtime()`** (`door_expert_ver()`) — auto cache-busting.
- **NIKAD ne skidati `?ver`** sa tema aseta (blueprint 1.2 — "Saya lekcija", stale-JS mesecima).
- Page-specific CSS/JS učitavaj **samo gde treba** (kondicionalni enqueue u `door_expert_enqueue_assets`).
  `tokens.css` uvek prvi (`:root` varijable = zavisnost za sve ostalo).
- `loading="lazy"` na slikama osim LCP hero; LCP = `eager` + `fetchpriority="high"`.
- Fontovi: prototip koristi Google Fonts CDN; pre produkcije self-host woff2 + preload (TODO u functions.php).

### Mobile-first (OBAVEZNO)
- Sav CSS se piše **mobile-first**: bazni stilovi su za mobilni, `@media (min-width: …)` override-uje naviše
  za tablet/desktop. NIKAD desktop-first (`max-width` override naniže).
- Prototip fidelity (§1) = **isti vizuelni rezultat, klase i vrijednosti** (padding, boje, font-size),
  ali CSS **strukturiran mobile-first** — ne kopiramo `max-width` smjer prototipa.
- Zatečenih 22 desktop-first CSS fajlova (Manus zaostavština) migrirati postupno (kad se fajl dira) +
  završni mobilni QA: hamburger meni radi, tap-mete ≥44px, bez horizontalnog overflow-a, čitljiv tekst, slike se skaliraju.

## 4. Security i kodni standardi (blueprint §4–5) — OBAVEZNO

Prati [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

### Input — sanitizuj na UPISU
- Untrusted: `$_POST`, `$_GET`, `$_REQUEST`, `$_COOKIE`, `$_SERVER`.
- Redosled: **`wp_unslash()` PA `sanitize_*()`** →
  `$x = sanitize_text_field( wp_unslash( $_POST['x'] ?? '' ) );`
  (`absint()` za ID, `sanitize_email()`, `sanitize_key()` za slug, `wp_kses_post()` za rich HTML).

### Output — escape na ISPISU (late escaping)
| Kontekst | Funkcija |
|---|---|
| HTML body tekst | `esc_html()` |
| HTML atribut | `esc_attr()` |
| URL (href/src/action) | `esc_url()` |
| Inline CSS vrednost | `esc_attr()` |
| JS podaci | `wp_json_encode()` |
| Rich/trusted HTML | `wp_kses_post()` |

Unutar atributa (`title=""`, `aria-label=""`) koristi `echo esc_attr( get_the_title() )` /
`echo esc_url( get_permalink() )`, ne `the_title()`/`the_permalink()`.

### Nonce, capabilities, baza
- Svaka forma i AJAX: nonce (`wp_nonce_field` / `check_ajax_referer`), bail rano ako padne.
  **Kontakt forma = custom AJAX handler** (nonce + sanitizacija + rate limit + `wp_mail()`), ne CF7.
- Pre svake privilegovane akcije: `current_user_can( … )`.
- Svi `$wpdb` upiti kroz **`$wpdb->prepare()`**.
- Setup skripte: `current_user_can('manage_options')`, obrisati po završetku. **`wp-config.php` nikad u git.**

### Zabranjeni patterni (nikad)
`eval()`, `extract()`, `@` (suppression), `stripslashes()`→`wp_unslash()`,
`json_encode()`→`wp_json_encode()`, raw `mail()`→`wp_mail()`, direktan `mysqli_*`/PDO→`$wpdb`,
hardkodirani ključevi→`wp-config.php` konstante.

### Higijena fajlova
- Svaki ne-template PHP fajl počinje: `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- PHP-only fajlovi (`functions.php`, klase, `inc/*`) **bez** zatvarajućeg `?>`.
- **Bez definicija funkcija u template fajlovima** — sve u `functions.php` ili `/inc/`.
- Sve custom funkcije/hookovi prefiksovani `door_expert_`.
- Aseti samo preko `wp_enqueue_*` u `wp_enqueue_scripts` — nikad inline `<script>`/`<link>`.

### Stil (odabran za ovaj projekat — drži dosledno, ne mešaj)
- Pun `<?php` tag (nikad `<?`/`<?=`), razmaci posle `if (`/`foreach (`, uvek vitičaste zagrade.
- **Tabovi** za PHP indentaciju; **`array()`** (ne `[]`); **Yoda uslovi** (`0 === (int) $x`).
- U HTML delovima template-parts: 2 razmaka (kao postojeći parts). Ne reformatiraj zatečen kod.
- Imenovanje: `lowercase_with_underscores` (funkcije/var), `UpperCamelCase` (klase), `UPPER_CASE` (konst).

## 5. SEO (blueprint §6)

- Tačno jedan `<h1>` po stranici; navigacija/mega meni nikad `<h1>`–`<h6>`.
- Alt text na slikama, canonical, schema gde ima smisla (Rank Math + ručni JSON-LD gde treba).
- Paginacija generiše `/page/N/` URL-ove (ne `?paged=N`).

## 6. Radni proces

- **Bez em dasha (—) na frontendu** — ni kao karakter ni kao `&mdash;`.
- Posle svake završene celine: mali, čitljivi commit + push (kad korisnik traži).
- **Vodi `DOCS/WORKING_COMMITS.md`** — po završenom/verifikovanom featureu upiši stabilan
  commit, fajlove, šta radi i "ako pukne, šta proveriti". To je rollback referenca
  (`git checkout <hash> -- fajl`), ne changelog. Ažuriraj hash kad feature dobije popravku.
- Posle deploya PHP/template izmena: **Purge keš**; JS/CSS se auto-bustuje preko `?ver`.
- Kad nešto "ne radi" na živom sajtu: **prvo isključi keš iz jednačine**, pa traži bug.
- Windows okruženje: PowerShell primaran; UTF-8 bez BOM pri pisanju fajlova.

# Door Expert — deploy na novi hosting + kasniji cutover na pravi domen

## Kontekst

`doorexpert.me` trenutno pokazuje na **stari hosting** (stari sajt živ dok se novi radi).
Novi sajt gradimo na **novom shared-IP hostingu**, kojem se pristupa preko
`http://<shared_IP>/~doorexpe/` (Apache **mod_userdir** — radi jer `/~user/` ne zavisi
od domena/Host header-a). WP instalacija je već pokrenuta na toj adresi.

**Ključna posledica izabranog metoda:** URL sa kog instaliraš postaje WP `siteurl`/`home`
u bazi. Znači `siteurl = http://<shared_IP>/~doorexpe`. Zato cutover na pravi domen
**zahteva search-replace URL-ova kroz bazu**. To je bezbedno i standardno — tema nema
nijedan hardkodiran domen (sve ide preko `home_url()`, provereno), pa je zamena nizak rizik.

DNS za `doorexpert.me` se kontroliše **u Zone Editoru starog hostinga** → cutover je
promena `A` recorda tamo, bez diranja nameservera (email/MX ostaje netaknut).

---

## Faza 1 — Završi WP instalaciju (na `IP/~doorexpe`)

1. cPanel → **MySQL Databases**: napravi bazu + korisnika + dodeli sve privilegije.
2. Vrati se na installer (`setup-config.php`), unesi te podatke, završi instalaciju.
3. Zapamti `siteurl` je sad `http://<shared_IP>/~doorexpe`. To je OK za build fazu.
4. `wp-config.php` — uključi debug za prvi test (isključi pre produkcije):
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   ```
5. Sigurnost: instalacija ide preko `http://` (Nije bezbedno). Admin lozinku koju sad
   postaviš tretiraj kao privremenu; posle cutovera + HTTPS-a je promeni.

## Faza 2 — Tema + stranice + test

1. Preko FTP/WinSCP upload **samo** `wp-content/themes/door-expert/` u
   `.../wp-content/themes/door-expert/`. (Prototip `.html`, `_NOVI-PROJEKTI`, root `assets/`
   ostaju lokalno — tema ima svoju kopiju asseta.)
2. **Appearance → Themes → Activate** "Door Expert".
3. **Settings → Permalinks → "Post name"** i sačuvaj (upisuje `.htaccess` RewriteBase).
4. Napravi sadržaj — **dva tipa** (detalji u sekciji "SEO URL + WooCommerce" niže):
   - **WP stranice** (info/brend, ravan slug): `o-nama, kontakt, b2b, montaza,
     akcije, inspiracija, brendovi, new-tiles, tau-ceramica, arcana-ceramica,
     ribesalbes, bathco`.
   - **WooCommerce kategorije** (proizvodi + hijerarhija): glavne kategorije i
     potkategorije — **NE praviti ih kao WP stranice** (slug bi se sudario sa WC kategorijom).
   - **korpa** → WooCommerce Cart stranica (WC je pravi pri setup-u).
5. **Settings → Reading → Homepage displays → A static page → Homepage** = odgovarajuća
   stranica (`front-page.php` se automatski koristi za naslovnu).
6. **Test:** otvori naslovnu i par unutrašnjih linkova.
   - Ako unutrašnje stranice daju **404** → to je `RewriteBase /~doorexpe/` problem pod
     subpath-om; ponovo sačuvaj Permalinks, ili ručno dodaj `RewriteBase /~doorexpe/` u
     `.htaccess`. Nestaje sam kad pređeš na pravi domen (root).
   - Layout radi ali sa Unsplash slikama i hardkodiranim menijem → očekivano stanje.

## Faza 3 — Cutover na `doorexpert.me`

Kad je sajt spreman:

1. **Backup baze** (cPanel → phpMyAdmin → Export, ili Backup Wizard). Obavezno pre replace-a.
2. **Snizi TTL** `A` recorda u Zone Editoru starog hostinga na 300s **dan ranije** (brža propagacija).
3. **Search-replace URL-ova** (serialization-safe — NIKAD raw SQL `REPLACE`):
   - WP-CLI (ako host ima SSH/Terminal):
     ```
     wp search-replace 'http://<shared_IP>/~doorexpe' 'https://doorexpert.me' \
       --all-tables --precise --recurse-objects --dry-run
     ```
     Proveri broj pogodaka, pa pusti bez `--dry-run`.
   - Ili plugin **Better Search Replace** (isti efekat iz wp-admina): prvo "dry run".
4. **Promeni glavni `A` record** u Zone Editoru starog hostinga:
   `@` i `www` → `<shared_IP novog servera>`. **Ne diraj nameservere ni MX** (email ostaje gde je).
5. Sačekaj propagaciju (proveri `nslookup doorexpert.me` / whatsmydns).
6. **AutoSSL / Let's Encrypt** za `doorexpert.me` u novom cPanel-u (sad radi jer DNS pokazuje ovamo).
7. Posle SSL-a: potvrdi da su `siteurl`/`home` = `https://doorexpert.me` (search-replace ih je
   već promenio), pa **Settings → Permalinks → Save** (flush) i dodaj http→https redirect.
8. Isključi `WP_DEBUG`.

---

## Verifikacija (end-to-end)

- Preview (Faza 2): naslovna + 3-4 unutrašnja linka se otvaraju bez 404 na `IP/~doorexpe`.
- Posle search-replace (dry-run): broj pogodaka > 0 i deluje razumno; nema greške o
  serijalizaciji.
- Posle cutovera: `https://doorexpert.me` učitava novu naslovnu; klik kroz meni radi;
  slike/CSS se učitavaju sa `doorexpert.me` (ne sa starog `IP/~doorexpe`); admin login radi;
  email na `@doorexpert.me` i dalje stiže (MX netaknut).

## Trenutni status konverzije (za referencu)

**Napravljeno** — `wp-content/themes/door-expert/`:
- `style.css` (samo theme header), `functions.php` (filemtime enqueue + security + bloat),
  `header.php` (+ otvara `<main>`), `footer.php` (+ zatvara `<main>`), `front-page.php`
  (naslovna), `index.php` (fallback), `assets/` (CSS/JS/logo) + novi `assets/css/base.css`.
- Provereno: 0 zaostalih `.html` linkova, svi asseti preko `get_template_directory_uri()`,
  `<main>` balansiran, nema aktivnih inline skripti.

**TODO (upisano kao komentari u samim fajlovima):**
- Slike (mega meni + naslovna) su **Unsplash placeholderi** → zameniti WP medijom.
- Navigacija **hardkodirana** (verna prototipu) → kasnije `wp_nav_menu()` ili ACF ako
  klijent treba sam da menja meni.
- Korpa/ponuda linkovi → `wc_get_cart_url()` kad WooCommerce bude aktivan.
- `pre-footer` se ponavlja na više stranica → kandidat za `template-parts/pre-footer.php`.
- `style.css` Theme URI je `doorexpert.rs` (pogrešan TLD) → sitna kozmetička ispravka na `.me`.
- **Preostalo ~30 stranica** za konverziju (product, kategorije, kontakt, o-nama, korpa…);
  mapiranje uslova za enqueue je već stubovano u `functions.php`.
- Tema još **nije parsirana PHP-om ni renderovana WP-om** — prvi load na hostingu je pravi test.

## SEO URL + WooCommerce (odluka 2026-07-26)

SEO stručnjak traži **ugnježdene** URL-ove za kategorije. Odlučeno: kategorije se prave
kao **WooCommerce product kategorije** (`product_cat`), NE kao WP stranice.

**Hijerarhija kategorija:**
- `sobna-vrata` → `klizna`, `staklena-vrata`
- `sigurnosna-vrata` → `za-stan`, `za-kucu`
- `keramicke-plocice` → `podne`, `zidne`, `za-kupatilo`, `za-kuhinju`, `spoljne`, `za-bazen`, `gaziste-za-stepenice`
- `umivaonici` → `kameni`, `samostojeci`, `nadgradni`

**URL baza = `c`** (SEO kolega; stabilnije od prazne `.`): Settings → Permalinks →
"Product category base" = `c` → Save. Stvarni URL: `/c/sobna-vrata/klizna/`.
- Glavne kategorije NE smeju postojati i kao WP stranice — slug bi se sudario.

**✅ URADJENO — linkovi u temi prebačeni na `get_term_link()`.** Umesto hardkodiranih
`home_url('/.../')`, svi kategorijski linkovi (header mega meni, mobilni, footer, naslovna)
sad idu kroz helper `door_expert_cat_url( 'slug' )` u `functions.php` → WooCommerce sam
sklapa pun URL sa bazom (`/c/...`). Ako se baza promeni, tema automatski prati — ništa se
ne dira. Info stranice (o-nama, kontakt…) i korpa ostaju `home_url`.

**✅ 5 potkategorija** (`podne, zidne, za-kuhinju, spoljne, samostojeci`) sad vode na SVOJU
kategoriju (ne roditelja), na sva mesta gde se pojavljuju.

**Šabloni:** kategorijske landing stranice (keramicke-plocice, sigurnosna-vrata...) postaju
WC šabloni (`taxonomy-product_cat.php` / `woocommerce.php`), NE WP stranice — konverzija
prototip landing → WC arhiva je poseban korak (single proizvod = `single-product.php`).

## Poznate zamke

- **RewriteBase pod `/~user/`** — pretty permalinci mogu 404 tokom preview faze; nestaje na root domenu.
- **Search-replace je obavezan** za ovaj metod (jer siteurl = IP/~user). Uvek dry-run + backup pre.
- **Menjaj samo `A` record, ne nameservere** — čuva email/MX na starom mestu.
- **Media URL-ovi** — slike uploadovane tokom builda dobijaju `IP/~doorexpe` u bazi; search-replace
  ih hvata (zato ide `--all-tables`).
- **Alternativa bez migracije baze** (za ubuduće, ne sad): instalacija direktno kao `doorexpert.me` +
  lokalni hosts fajl → 0 izmena u bazi na cutoveru. Trenutni metod je već započet, pa idemo search-replace putem.

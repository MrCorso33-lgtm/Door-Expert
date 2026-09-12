# Stabilni commitovi po featureu

Referenca: ako se feature pokvari, vrati se na ovaj commit za taj fajl.

Kako da vratiš jedan fajl na stanje iz commita:
```
git checkout <commit-hash> -- putanja/do/fajla.php
```

> **Pre svake "kod ne radi" panike — isključi keš iz jednačine.**
> Vidi `WP_CUSTOM_DEV_BLUEPRINT.md` sekciju 14 (Caching & deploy) i dijagnostiku curl-om.
>
> Podsjetnik: CSS/JS se auto-bustuju preko `filemtime()` `?ver`. **PHP se ne bustuje** —
> poslije PHP/template izmjena uvijek Purge keš.

---

## Prodavnica — WooCommerce shop arhiva

**Commit:** `53b211b`
**Fajl(ovi):** `archive-product.php`, `inc/shop.php`, `template-parts/shop/product-card.php`, `template-parts/shop/filters.php`, `assets/css/prodavnica.css`, `assets/js/prodavnica.js`, `functions.php`

**Šta radi:**
- Prava WooCommerce arhiva proizvoda sa dizajnom iz prototipa `prodavnica.html`
- Hero pilule (Sve / Vrata / Keramika / Umivaonici) sa dinamičkim brojačima
- Server-side filteri kroz `woocommerce_product_query` (kategorija, brend, boja, dimenzije, cijena, dostupnost) — **bez JetSmartFilters**
- Sort + paginacija `/page/N/`, GET forme čuvaju stanje jedna drugoj preko hidden inputa

> **Zastarjelo od `162d28d`:** sidebar više ne gradi `template-parts/shop/filters.php` sam,
> nego ga renderuje plugin. Za filtere gledaj sekciju "Filter sidebar" niže; za grid,
> hero pilule, sort i paginaciju ovaj commit i dalje važi.

### Kada se pokvari — šta proveriti
1. **Keš** — da li je novi kod na serveru (`curl ... | grep`), pa Purge + incognito
2. **Shop page** — WooCommerce → Settings → Products → Shop page = Prodavnica; pa Settings → Permalinks → Save
3. **Kartice bez stilova** — `category.css` se učitava kao zavisnost prije `prodavnica.css`; provjeri `page_assets` mapu u `functions.php`
4. **Grid prazan** — nema proizvoda, ili su svi izvan izabranih filtera (očisti filtere linkom "Očisti sve")

---

## Filter sidebar — plugin WC Filter Configurator

**Commit:** `162d28d`
**Fajl(ovi):** `inc/filters.php` (novi), `inc/shop.php`, `template-parts/shop/filters.php`,
`template-parts/category/parent/keramicke-plocice.php`, `assets/css/prodavnica.css`,
`assets/js/prodavnica.js`, `functions.php`, plus plugin u `wp-plugins/wc-filter-configurator/`

**Šta radi:**
- Sidebar renderuje plugin, a podešava se iz admina (Settings → Filter Configurator):
  koje grupe, kojim redom, koja labela, otvoreno/zatvoreno, po kategoriji
- Termovi i brojevi su **ograničeni na proizvode te kategorije** (ranije globalni
  `get_terms()`, pa su se keramički brendovi nudili na sobnim vratima)
- Varijabilni proizvod ne nudi term za koji nema objavljene varijacije
- Upit ostaje naš (`inc/shop.php`), plugin ga ne dira

**Podjela odgovornosti (ne miješati):**
| Sloj | Fajl | Vlasti |
|---|---|---|
| Sidebar | plugin | grupe, redosljed, termovi, brojevi, keš |
| Most | `inc/filters.php` | konfiguracija, paleta, cijena, dorada markupa, Dostupnost |
| Upit | `inc/shop.php` | GET → `WP_Query`, sort, hidden inputi |

**URL parametri:** `product_brand[]`, `pa_boja[]`, `pa_dimenzije-vrata[]` … (naziv taksonomije),
plus `f_cat[]`, `f_stock[]`, `min_price`, `max_price`, `orderby`.
Stari `f_brand` / `f_boja` / `f_dim_*` **više ne rade**.

### Kada se pokvari — šta proveriti
1. **Keš** — PHP se ne bustuje; Purge poslije svake izmjene. Plugin ima i svoj
   **Flush filter cache** (Settings → Filter Configurator) za brojeve koji kasne
2. **Sidebar prazan, a plugin aktivan** — admin je snimio neki kontekst pa `default`
   ne pokriva ovu kategoriju. Provjeri tab te kategorije; `door_expert_filter_configs_fallback()`
   popunjava `default` samo kad ga uopšte **nema** u opciji
3. **Piše "Plugin nije aktivan"** (vidi samo admin) — plugin nije u `wp-content/plugins/`
   ili nije aktiviran. Vidi `DEPLOY.md` korak 1a
4. **Drag & drop u adminu ne radi** — `assets/vendor/Sortable.min.js` nije stigao na server
   (konzola: `Sortable is not defined`)
5. **Filter ne vraća ništa** — provjeri slug atributa. Naši su `pa_dimenzije-vrata` i
   `pa_dimenzije-plocica` (NE `-plocice`, to je Saya slug)
6. **Filter se vidi ali ne filtrira** — atribut nije u whitelisti; ona dolazi iz
   `wc_get_attribute_taxonomies()`, dakle atribut mora biti **globalni** WC atribut,
   ne per-proizvod
7. **Cijena se ne šalje** — grupu renderuje `door_expert_filter_price_group()`; bez
   `name` atributa slider ne šalje ništa. Provjeri da nije neko vratio plugin markup
8. **Multi-select gubi izbor** — `door_expert_filter_fix_checkboxes()` dodaje `[]` i
   `checked`; ako je plugin promijenio markup checkboxa, regex tamo više ne hvata

### Još nije provjereno na živom sajtu
Napisano bez WP instance lokalno. Prije nego što se ovaj commit proglasi stabilnim:
otvori `/c/sobna-vrata/` i potvrdi da nema keramičkih brendova; broj u zagradi mora
da se poklopi sa brojem rezultata poslije klika; sort mora da sačuva filtere;
"Očisti sve" mora da vrati čist URL.

---

## PDP — WooCommerce single product

**Commit:** `da7962a`
**Fajl(ovi):** `single-product.php`, `template-parts/product/single.php`, `inc/product.php`, `assets/js/product.js`, `assets/css/product.css`

**Šta radi:**
- Bespoke prikaz proizvoda iz `WC_Product` (galerija + lightbox, cijena sa uštedom, dostupnost, atributi read-only, količina, "Dodaj u ponudu", specifikacije iz atributa, opis, FAQ po grupi, slični proizvodi, mobilna sticky traka)
- m² kalkulator samo za keramiku (grupa se određuje po top-level kategoriji)
- v1: **Simple** proizvodi; varijante su read-only (bez menjanja cijene)

### Kada se pokvari — šta proveriti
1. **Keš** — Purge poslije svake PHP izmjene
2. **Default WooCommerce izgled** — `single-product.php` nije na serveru ili je u pogrešnom folderu (mora u root teme)
3. **Desna kolona odsječena** — stara verzija `product.css`. Fix je u `da7962a`: `.product-decision` NEMA `max-height`/`overflow-y`/`position:sticky` (sticky je prebačen na galeriju)
4. **Kalkulator se ne pojavljuje** — proizvod nije u keramika grupi; provjeri `door_expert_product_group()` i top-level kategoriju proizvoda
5. **Specifikacije prazne** — proizvod nema dodijeljene atribute (tabela se puni iz WC atributa, nema placeholdera)
6. **Galerija bez thumbova** — proizvod ima samo jednu sliku (traka se crta od 2 slike naviše)

---

## Korpa — quote cart (WooCommerce bez plaćanja)

**Commit:** `b96017a`
**Fajl(ovi):** `inc/quote-cart.php`, `template-parts/page/korpa.php`, `assets/js/korpa.js`, `assets/js/header.js`, `header.php`, `page.php`, `functions.php`

**Šta radi:**
- Korpa radi normalno, ali umjesto plaćanja kupac šalje upit → pravi se **`WC_Order` sa statusom on-hold**
- Forma upita je U KORPI; AJAX količina/uklanjanje bez reload-a
- Dokaz saglasnosti (tekst + vrijeme + IP) na narudžbi, honeypot, rate limit 5/h po IP
- Notifikacija: **n8n webhook primaran**, `wp_mail` samo fallback ako webhook padne
- Proizvodi sa cijenom 0 ostaju kupljivi ("cijena na upit")
- Badž korpe se hidratira sa servera (full-page keš zamrzava server-rendered broj)
- Direktan pristup `/checkout/` vraća na korpu

### Kada se pokvari — šta proveriti
1. **Keš** — Purge; badž je posebno osjetljiv na full-page keš
2. **Cart page** — WooCommerce → Settings → Advanced → Cart page mora biti postavljena (prikaz ide preko `is_cart()`, pa naziv/slug stranice nije bitan)
3. **Upit ne stiže nikome** — provjeri `DOOR_EXPERT_WEBHOOK` u `wp-config.php`. Ako je n8n pao, stiže mejl sa prefiksom **`[WEBHOOK PAO]`** — to je namjerni alarm, ne bug
4. **Duple notifikacije** — regresija: `wp_mail` smije ići SAMO kad webhook nije podešen ili je vratio ne-2xx (vidi `door_expert_notify_inquiry()`)
5. **Cijene pogrešne u mejlu/narudžbi** — `WC()->cart->calculate_totals()` mora ići PRIJE čitanja cijena (u admin-ajax zahtjevu `woocommerce_before_calculate_totals` još nije odrađen)
6. **HTTP 429 pri slanju** — rate limit (5 upita/sat po IP). Transient `de_rl_inquiry_<md5(ip)>`; obriši ga za test
7. **Redirect poslije slanja ne radi** — ne postoji stranica sa slugom `hvala`
8. **Link korpe 404** — negdje je ostao hardkodovan URL; mora `door_expert_cart_url()`

---

## Hvala — potvrda poslatog upita

**Commit:** `f8bf492`
**Fajl(ovi):** `template-parts/page/hvala.php`, `assets/css/hvala.css`

**Šta radi:**
- Thank-you stranica na koju korpa redirektuje: `/hvala/?upit=<broj narudžbe>`
- Prikazuje broj upita, 4 koraka procesa, info blok, akcije, cross-sell
- CSS preveden u mobile-first (prototip je bio desktop-first)

### Kada se pokvari — šta proveriti
1. **Keš**
2. **Stranica ne postoji** — mora WP Page sa slugom tačno `hvala` (ako WP doda `hvala-2`, isprazni Trash)
3. **Bez stilova** — nedostaje unos `'hvala'` u `page_assets` mapi u `functions.php`
4. **Broj upita se ne prikazuje** — normalno ako se stranica otvori direktno, bez `?upit=` parametra

---

## 404 — stranica nije pronađena

**Commit:** `6ec51ad`
**Fajl(ovi):** `404.php`, `assets/js/404.js`, `assets/css/404.css`

**Šta radi:**
- Brendirana 404 umjesto golog `index.php`: hero, search UI, 4 kategorijske kartice, brzi linkovi, kontakt kartica, trust traka, mobilna sticky traka
- Kartice vuku naziv i sliku iz `product_cat` terma; ako thumbnail nije postavljen ide WooCommerce placeholder, ako term ne postoji kartica se preskače
- Telefon i radno vrijeme iz `door_expert_company_info()`
- **Search UI je namjerno neaktivan** – pretraga u temi ne postoji (žiči se uz Saya komponentu #16)

### Kada se pokvari — šta proveriti
1. **Keš** — Purge; PHP se ne bustuje preko `?ver`
2. **Vidi se goli `index.php`** — `404.php` nije na serveru ili nije u root-u teme
3. **HTTP status je 200 umjesto 404** — provjeri `curl -I <nepostojeci-url> | head -1`. Uzrok je obično plugin za redirekcije ili keš koji servira 200; bitno za SEO
4. **Prazna rupa desno u sekciji linkova** — nedostaje `--2col` modifikator na `.e404-links__inner` (grid je bazno `1fr 1fr 320px`, a srednja kolona je izbačena)
5. **Kartica bez slike** — kategoriji nije postavljen thumbnail u wp-adminu (Products → Categories). To je očekivano, ne bug; prikazuje se WC placeholder
6. **Kartica nedostaje** — `product_cat` term sa tim slug-om ne postoji
7. **Klik na "Pretraži" vodi negdje** — regresija: vratio se stari `doSearch()` simulator iz `404.js`. Dugme mora biti inertno dok pretraga ne postoji
8. **Brzi linkovi na Inspiraciju / Za investitore vode na 404** — očekivano dok te stranice nisu konvertovane

---

## Montaža — stranica "Šta je uključeno u cijenu"

**Commit:** `3176576`
**Fajl(ovi):** `template-parts/page/montaza.php`, `assets/css/montaza.css`, `functions.php`

**Šta radi:**
- Konverzija prototipa `montaza.html`: hero, uključeno/nije uključeno, 6 koraka, majstori, cjenovna tabela, FAQ, recenzije, CTA
- Majstori i cijene su placeholder (demo)

### Kada se pokvari — šta proveriti
1. **Keš**
2. **Stranica ne koristi template** — slug mora biti `montaza` (router `page.php` mapira slug → template-part)
3. **Bez stilova** — unos `'montaza'` u `page_assets` mapi

---

<!--
Šablon za novi unos (kopiraj iznad ove linije):

## Naziv featurea
**Commit:** `hash`
**Fajl(ovi):** `...`
**Šta radi:**
- ...
### Kada se pokvari — šta proveriti
1. ...
-->

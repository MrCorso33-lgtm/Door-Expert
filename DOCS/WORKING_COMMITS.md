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
0. **Sidebar ignoriše ono što si sačuvao u adminu** i pokazuje grupe kojih nema u
   konfiguraciji (npr. "Brend" sa brendovima iz cijele baze i globalnim brojevima)
   → **tema na serveru je stara**. Plugin i tema se deployuju odvojeno i lako je
   poslati samo jedno. Provjeri da na serveru postoji `inc/filters.php`; ako njega
   nema, sve ostalo je takođe staro. Pošalji cijeli folder teme i Purge
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

### Status verifikacije (13.09.2026, staging)
Potvrđeno na `staging/`: sidebar se renderuje iz konfiguracije sačuvane u adminu,
opseg po kategoriji radi (grupa bez termova među proizvodima te kategorije se ne
prikazuje), keramički brendovi više ne iskaču na sobnim vratima.

**Ograničenje te provjere:** katalog je u tom trenutku imao jedan test proizvod sa
jednim atributom. Ponašanje sa više filtera istovremeno (OR unutar grupe, AND između
grupa, poklapanje brojeva sa rezultatom, čuvanje filtera pri promjeni sorta) nije
moglo biti smisleno provjereno. Ponovi kad katalog naraste.

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

## PDP varijacije — Variable proizvodi, pilule, zaliha

**Commit:** `69c63a1` (osnova) → `8eae453` → `389e5be` → `9470ace` → `f65e32d` → `e85da35`
**Fajl(ovi):** `template-parts/product/single.php`, `assets/js/product.js`, `assets/css/product.css`,
`inc/product.php`, `inc/product-variations.php` (novi), `inc/quote-cart.php`,
`template-parts/page/korpa.php`, `functions.php`

> Ovih šest commita se **ne vraćaju pojedinačno** — testirani su zajedno i međusobno
> zavise. Za rollback uzmi `e85da35` (posljednji verifikovan) za sve fajlove iz spiska.

**Šta radi:**
- Varijacije rade preko WC-ove `variations_form`: skriveni `<select>`-ovi su izvor istine,
  pilule iz prototipa su vizuelni sloj nad njima (`product.js`). Matching kombinacija,
  cijenu, stanje i `variation_id` radi `wc-add-to-cart-variation.js`, **ne naš kod**
- Pilule se prikazuju SAMO za atribute označene "Used for variations". Filter-atributi
  (boja, prostorija, tip vrata) nisu izbor i idu u tabelu Specifikacije
- Auto-izbor: kad u drugom redu ostane tačno jedna moguća opcija, bira se sama
- Zamjena glavne slike po varijaciji, m² kalkulator prati cijenu izabrane varijacije
- Vrata su naručljiva i van lagera (quote model) — upit prolazi i kad je zaliha 0
- Blok dostupnosti prati **izabranu varijaciju**, u tri stanja (na stanju / po narudžbi /
  nije na zalihama), plus link na `/montaza/`
- Korpa i mejl upita ispisuju ime terma (`Širina vrata: 80 cm`), ne slug (`80-cm`)

**Tri stvari koje se lako slome (sve tri su nas već ugrizle):**

| Zamka | Posljedica |
|---|---|
| Obrisani `wp.template` blokovi u `single.php` | `wp.template()` dobije `undefined` → `TypeError` → varijacije tiho prestanu | 
| `is_in_stock()` korišten za **prikaz** zalihe | Uvijek piše "Na stanju", jer ga `product-variations.php` filtrira. Za prikaz ide `get_stock_status()` |
| Djelimičan upload (npr. `functions.php` bez `inc/product.php`) | Fatal kroz `wp_head` → bijela stranica na **svakom** proizvodu |

### Kada se pokvari — šta proveriti
1. **Keš** — PHP se ne bustuje preko `?ver`; Purge + incognito
2. **Bijela stranica na proizvodu** — pogledaj `error_log` / `debug.log` u root-u. Ako je
   `Call to undefined function door_expert_*`, nedostaje fajl iz `inc/` na serveru
3. **Pilule se uopšte ne vide** — proizvod nije tipa **Variable**, ili atribut nema
   čekiran "Used for variations", ili varijacije nisu generisane
4. **Pilule se vide ali ne reaguju na klik** — u izvoru stranice traži
   `id="tmpl-variation-template"`; ako ga nema, `single.php` je star. Zatim provjeri da
   li je `wc-add-to-cart-variation.js` uopšte učitan (enqueue je u `functions.php`,
   uslovljen `is_type('variable')`)
5. **Dostupnost gore ne prati izbor dimenzije** — `product.js` star, ili u JSON-u nema
   `door_expert_stock_status` (filter `woocommerce_available_variation` u
   `inc/product-variations.php`)
6. **Upit za rasprodata vrata odbijen** — `inc/product-variations.php` nije na serveru.
   Napomena: `woocommerce_add_to_cart_validation` tu **ne pomaže**, jer `WC_Cart::add_to_cart()`
   baci izuzetak na `is_in_stock()` prije njega
7. **Korpa pokazuje `80-cm` umjesto `80 cm`** — stari `korpa.php` ili `quote-cart.php`

**Nije pokriveno (poznato):** ako *sve* varijacije jednog proizvoda odu na nulu, WC i
roditelju postavi `outofstock`; nije provjereno da li tada filter i dalje pušta upit.

---

## PLP kartica — cijela slika proizvoda + naslov bez underline-a

**Commit:** `f352a0b`
**Fajl(ovi):** `assets/css/category.css`, `template-parts/shop/product-card.php`

**Šta radi:**
- Kartica prikazuje **cio proizvod**, bez kropovanja. Bila su tri uzroka u lancu:
  1. `woocommerce_thumbnail` je **hard-crop** veličina (1:1 po Customizer podešavanju),
     pa je sam fajl bio odsječen → sada `woocommerce_single` (skalirano samo po širini)
  2. `<a>` omotač oko slike (prototip ga nije imao, mi ga dodajemo radi linka) nije
     dobijao visinu, pa `object-fit` nije imao na šta da se osloni → anchor je sada
     `position: absolute; inset: 0` + flex centriranje
  3. slika ide na `max-width/max-height: 100%` uz `width/height: auto` umjesto
     `width/height: 100%` — ne zavisi od razrješavanja procentualne visine
- Pravilo gađa i `.prod-card__img-wrap img`, ne samo `.prod-card__img` (zaštita za
  slučaj da klasa ne prođe kroz `wp_get_attachment_image`)
- Okvir slike `3/4` → `2/3` — fotografije vrata su ~1:2,3, pa viši okvir znači krupniji
  prikaz uz `contain`. **Odnos je namjerno isti za sve kategorije** da bi kartice u istom
  redu grida bile poravnate (različit odnos po `data-cat` razbija poravnanje naslova/cijene)
- `.prod-card__name a` dobio `color: inherit` + `text-decoration: none` (+ `:visited`),
  hover u jantar — naslov je link od kad ga renderuje `product-card.php`, a prototip
  nije imao pravilo za njega pa je pokazivao browser default (podvučeno, ljubičasto)

**Svjesno odstupanje od prototipa (§1):** prototip koristi `object-fit: cover`.
Prešli smo na `contain` na izričit zahtjev klijenta — cio proizvod je važniji od
popunjenog okvira. Posljedica: uske fotke (vrata) imaju prazan prostor lijevo i desno,
popunjen bojom `--color-alabaster`.

**Važi i za kategorijske listinge**, ne samo Prodavnicu — `category.css` je zajednički
izvor `.prod-card` stilova za oba. Homepage ima svoj `.prod-card` u `featured.css`
sa statičnim slikama iz prototipa i **nije** dirán.

> **Nastavak u `3cdf477` — PDP galerija i cross-sell.** Ova popravka je pokrivala samo
> karticu; PDP je ostao na prototipskom `cover`. Sada isti dogovor važi i za
> `assets/css/product.css` + `template-parts/product/single.php`:
> - glavna slika `contain`, okvir `3/4` → `2/3`, sličice `woocommerce_thumbnail` → `medium`
> - cross-sell „Možda će vas zanimati“ (isti trostruki problem) → `contain` + `2/3` +
>   `woocommerce_single`
>
> **Nauk koji vrijedi i za druge okvire:** na desktopu je `max-height: 680px` bio
> stvarni regulator, ne `aspect-ratio`. Kolona galerije je ~752px, okvir `3/4` je
> tražio 1003px visine, dobio 680 i ispao **pejzažni** (752×680). Uz `contain` **visina
> okvira je visina proizvoda** — širina ne mijenja ništa. Zato je `max-height` sad
> `min(860px, 100vh - 160px)`, vezan za viewport jer je kolona sticky.
>
> Još nije potvrđeno uživo na staging-u.

### Kada se pokvari — šta proveriti
1. **Slika i dalje isječena** → u DevTools klikni na sliku: da li `.prod-card__img-wrap > a`
   ima `position: absolute` i `inset: 0`. Ako nema — stari `category.css` je u kešu.
   Sam `object-fit: contain` na slici **nije dokaz** da je novi CSS aktivan, on je bio
   prisutan i dok je slika bila kropovana
2. **Slika mutna** → `woocommerce_single` je podrazumijevano 600px širine; za slike
   uploadovane prije aktivacije WooCommerce-a ta veličina možda ne postoji
   → WooCommerce → Status → Tools → Regenerate shop thumbnails
3. **Previše praznog prostora oko proizvoda** → to je priroda `contain` uz fotku čiji
   se odnos strana ne poklapa sa okvirom 2/3. Rješenje nije u CSS-u nego u fotografiji
   (enterijer ili kadar iz ugla popunjava okvir prirodno). Dodavanje bijele pozadine
   u fotku ne pomaže — proizvod time postaje manji, ne veći
4. **Naslov opet podvučen/ljubičast** → `category.css` nije na serveru; provjeri i da
   neki noviji CSS ne gazi `.prod-card__name a`

---

## Sticky na cijelom sajtu + sticky filter sidebar

**Commit:** `3733bb4`
**Fajl(ovi):** `assets/css/base.css`, `assets/css/subcat.css`, `assets/css/prodavnica.css`,
`assets/css/korpa.css`, `assets/css/akcije.css`

**Šta radi:**
- **Uzrok:** `html, body { overflow-x: hidden }` (iz prototipa `header-demo.html`, gdje je
  označeno kao "Demo page scaffolding only"). Kad je `hidden` na OBA elementa, `body`
  postaje scroll kontejner koji se nikad ne skroluje (skroluje viewport), pa se svaki
  sticky element "zakači" za njega i stoji u mjestu. DevTools i dalje pokazuje
  `position: sticky` — pravilo JESTE primijenjeno, samo nema efekta
- **Popravka:** `overflow-x: clip` — sprečava horizontalni skrol kao `hidden`, ali ne pravi
  scroll kontejner. `hidden` ostaje linija iznad kao fallback (Safari < 16)
- Isto pravilo je dupliran u `subcat.css`, koji se učitava POSLE `base.css` na kategorijama
  — mora biti ispravljen i tamo, inače vrati bug
- **Filter sidebar (desktop ≥1025px):** sticky ispod headera, scroll iznutra kad je viši
  od ekrana; scrollbar providan dok miš nije nad sidebarom (`:hover`/`:focus-within`),
  `overscroll-behavior-y: contain` (skrol ne propada na stranicu), `scrollbar-gutter: stable`
  (nema poskakivanja), `align-self: start` (grid ćelija se ne rasteže po visini liste)
- `top` za sidebar, korpu i akcije računat iz tokena: `--header-height` (80) +
  `--header-top-height` (36) = 116px. Ranije hardkodovano 80 / 100 / 72px — dok sticky nije
  radio to se nije vidjelo, a posle popravke bi vrh tih elemenata bio ispod headera

**Posljedica:** header je sada sticky na svim stranicama. To je izvorna namjera dizajna
(`header.css` ima `position: sticky`, `header.js` dodaje `.scrolled` blur + sjenku), ali je
vidljiva promjena. Ako se ne želi: `.site-header { position: relative; }`.

### Kada se pokvari — šta proveriti
1. **Sticky opet ne radi** → u DevTools provjeri Computed za `html` i `body`: `overflow-x`
   mora biti `clip`. Ako je `hidden`, neki CSS učitan posle `base.css` ga gazi (prvi
   osumnjičeni: `subcat.css` ili novi Manus fajl sa istim resetom — grep `overflow-x`)
2. **Sticky ne radi samo na jednom elementu** → neki predak ima `overflow: hidden/auto`
   (svaki takav predak "hvata" sticky). DevTools: idi uz stablo i traži `overflow`
3. **Vrh elementa ispod headera** → `top` je hardkodovan umjesto
   `calc(var(--header-height) + var(--header-top-height) + …)`
4. **Horizontalni skrol na mobilnom** → Safari < 16 ne zna `clip`; tamo radi fallback
   `hidden` (i tamo sticky ne radi — poznato, prihvaćeno)
5. **Sidebar sa malo filtera razvučen do dna liste** → nedostaje `align-self: start`

---

## Filteri — živi faceting + SEO filtriranih arhiva

**Commit:** `fd919d5`
**Fajl(ovi):** `inc/filters.php`, `inc/filters-seo.php` (**nov**), `functions.php`,
`assets/css/prodavnica.css`

Izvor: `DOCS/FOR DOOR EXPERT/08-PARITY-faceting-seo-ajax.md`, tačke 1 i 2.
**Tačka 3 (AJAX) NIJE rađena** — čeka odluku da li ostaje dugme "Primijeni filtere".

**Šta radi — faceting (sekcija 7 u `inc/filters.php`):**
- Brojevi u sidebaru prate tekući izbor. Računicu radi plugin (`wcfc_compute_facets`),
  tema je do sad **nije ni pozivala**, pa su brojevi opisivali nefiltriranu kategoriju:
  štikliraš Hrast, vidiš "Bijela (7)", štikliraš i nju i dobiješ prazan grid.
- Nula rezultata se **sivi i onemogućava, ali ostaje vidljiva**. Već štiklirana opcija
  se nikad ne onemogućava, inače se ne bi mogla odštiklirati.
- Prepravka ide nad cijelom `<label>` (broj živi u susjednom `<small class="wcfc-count">`),
  a stari input-pass ostaje ispod kao sigurnosna mreža: ako plugin promijeni markup
  labele, gube se samo brojevi, a NE `[]` i `checked` od kojih zavisi multi-select.
- Radi bez AJAX-a: forma ide GET-om, računa se server-side na svakom reload-u.

**Šta radi — SEO (`inc/filters-seo.php`):**
- Filter URL i `?orderby` → `noindex, nofollow` + canonical na čist listing.
- `/page/N/` ostaje `index, follow` sa self-canonical-om (nije duplikat nego nastavak).
- Radi bez SEO plugina i sa Rank Math-om, bez dupliranog canonical-a.

**Svjesna odstupanja od dokumenta (ne "popravljati" nazad):**
1. Canonical na brend/atribut arhivama vodi na **taj term**, ne na prodavnicu.
   `door_expert_listing_base_url()` zna samo za `product_cat` i za sve ostalo vraća
   prodavnicu — po dokumentu bi brend stranica dobila canonical na `/prodavnica/`.
2. Faceti se računaju za grupe koje sidebar **stvarno renderuje**
   (`wcfc_attrs_for_context( wcfc_current_context() )`), ne za kontekst izabrane
   kategorije. Bez toga na prodavnici sa pilulom "Keramika" grupa "Dimenzije vrata"
   zadrži globalne brojeve i odvede kupca u prazan grid.
3. Robots ide kroz `wp_robots` API (jedan tag), ne ručnim `echo` (bio bi drugi tag).
4. Canonical za `/page/N/` bez query stringa, da `utm_*` ne ulazi u njega.

**Provjereno na staging-u:** faceting radi; noindex i canonical ispravni;
`/prodavnica/?product_brand[]=<slug>` daje shop arhivu (nema sudara sa WooCommerce
brend taksonomijom, alias nije potreban).

**Poznata ograničenja (nisu bugovi, pisana i u kodu):**
- Grupa "Kategorija" i cijena nemaju facete (plugin ih preskače, `wcfc_special_attrs`).
- "Dostupnost" (`f_stock`) nije taksonomija, pa je faceti ignorišu: sa aktivnim filterom
  dostupnosti brojevi mogu biti veći od broja prikazanih proizvoda.
- Prodavnica bez hero pilule ili sa više njih ("Vrata" = sobna + sigurnosna) nema jedan
  opseg za brojanje, pa ostaju plugin-ovi brojevi.

### Kada se pokvari — šta proveriti
1. **Brojevi se ne mijenjaju** → plugin nije aktivan, ili `wcfc_compute_facets` ne postoji
   (stara verzija plugina — plugin i tema se deployuju odvojeno), ili si na prodavnici
   bez pilule/sa "Vrata" pilulom, gdje faceting namjerno ne radi
2. **Svi brojevi (0), sve posivilo** → `door_expert_filter_facet_term()` je pogodio pogrešnu
   kategoriju, ili je `wcfc_get_cat_product_ids()` prazan (keš plugina: `wcfc_bump_cache_version`)
3. **Brojevi zaostaju za izmjenom proizvoda** → plugin kešira facete 10 min (transient
   `wcfc_facets_v…`), a `wcfc_cache_version` se diže na izmjenu proizvoda/kategorija
4. **Multi-select prestao da radi (`[]` nestalo)** → prvi regex je promašio I sigurnosna
   mreža je promašila; provjeri markup opcije u `wcfc_render_term_filter` (`render.php`)
5. **Dva `rel=canonical` u izvoru** → `door_expert_seo_canonical_handled()` nije prepoznao
   SEO plugin. Provjeri: `curl -s "<URL>" | grep -c 'rel="canonical"'` mora biti 1
6. **Čiste kategorije otišle u noindex** → to NIJE ovaj kod (on na čistoj strani 1 propušta
   vrijednost SEO plugina). Rank Math → Titles & Meta → Product Categories → Robots Meta
   mora biti `index, follow`
7. **Fatalna greška posle deploya** → `functions.php` je otišao bez `inc/filters-seo.php`.
   Ta dva fajla idu na server zajedno

---

## Hero pilule — brojevi uz kategorijske prekidače

**Commit:** `d75bb60`
**Fajl(ovi):** `inc/shop.php` (`door_expert_shop_group_count`), `archive-product.php`

**Simptom:** pilula "Vrata" je pokazivala **4** nad katalogom od **2** proizvoda, dok je
toolbar ispod pisao "Prikazano 2 proizvoda". Podgrupa je izgledala veća od cjeline.

**Uzrok:** brojač je sabirao `term->count` roditelja i **svake** potkategorije. Proizvod
koji je u "Sobna vrata" i u nekoj njenoj potkategoriji ima term relacije na oba, pa se
brojao dvaput. Uz to je pilula "Sve" koristila `wp_count_posts()`, dakle treći način
računanja od ostale tri.

**Popravka:** jedan brojač za sve četiri pilule. Broji **različite** proizvode preko
`WP_Query` sa istim uslovima koje klik na pilulu proizvede (`include_children` +
`product_visibility`). Prazan niz slugova = svi proizvodi. Rezultat se kešira u okviru
jednog učitavanja stranice (četiri lagana `COUNT` upita).

---

### 🔁 PRAVILO ZA SVAKI SLIČAN UI (brojevi uz filtere, pilule, kategorije)

Ovo se ponavlja svaki put kad uz neki prekidač stoji broj. Dvije odvojene odluke:

**1. Kako se broji — nikad zbir `term->count` kroz stablo.**
`term->count` je broj relacija, ne broj proizvoda. Čim je proizvod u više kategorija
istog stabla (a to je normalno), zbir laže naviše. Uvijek izbroj **različite proizvode
upitom koji odgovara onome što klik stvarno uradi** — isti `tax_query`, isti
`include_children`, ista pravila vidljivosti. Test u jednoj rečenici:
*klikni na prekidač i uporedi njegov broj sa brojem rezultata; moraju biti isti.*

**2. Šta se broji — prekidač ili facet?** Dva različita ponašanja, lako ih je pomiješati:

| | Hero pilula (prekidač) | Filter u sidebaru (facet) |
|---|---|---|
| Šta radi klik | mijenja kontekst (kategoriju) | sužava tekući izbor |
| Broj pokazuje | koliko ta kategorija ima **ukupno** | koliko ostaje **uz tekući izbor** |
| Prati druge filtere | **ne** | **da** (`door_expert_filter_facets`) |
| Nula | ostaje klikabilna | sivi se i onemogućava |

Ako pilula počne da prati filtere, kupac gubi orijentaciju (kategorije "nestaju" jer je
štiklirao boju). Ako facet **ne** prati filtere, kupac klikne broj različit od nule i
dobije prazan grid — to je tačno bug koji je riješen u commitu `fd919d5` iznad.

### Kada se pokvari — šta proveriti
1. **Broj na piluli ≠ "Prikazano N proizvoda"** → upit u brojaču se razišao sa upitom
   arhive. Uporedi `door_expert_shop_group_count()` sa `door_expert_shop_tax_query()`:
   `include_children` i `product_visibility` moraju biti isti u oba
2. **"Sve" veće od zbira ostalih pilula** → normalno je ako neki proizvod nije ni u jednoj
   od grupa iz `$de_groups` (npr. nova kategorija koja nema svoju pilulu)
3. **Broj uključuje sakrivene proizvode** → `wc_get_product_visibility_term_ids()` nije
   dostupna (WooCommerce nije učitan u tom trenutku), pa je klauzula preskočena
4. **Sporo učitavanje prodavnice** → keš je po zahtjevu, ne trajni. Ako katalog naraste na
   hiljade proizvoda, staviti transient sa invalidacijom na `save_post_product`

---

## AJAX filtriranje listinga (bez dugmeta "Primijeni")

**Commit:** `d6d85d9`
**Fajl(ovi):** `inc/shop-ajax.php` (**nov**), `inc/shop.php`, `inc/filters.php`,
`functions.php`, `archive-product.php`,
`template-parts/category/parts/product-grid.php`, `assets/js/prodavnica.js`,
`assets/css/prodavnica.css`

Izvor: `DOCS/FOR DOOR EXPERT/08-PARITY-faceting-seo-ajax.md`, tačka 3.
**Odluka vlasnika (nije tehnička):** trenutno filtriranje svuda, dugme "Primijeni
filtere" se sklanja kad JS radi. Alternativa je bila zadržati dugme — ako se ikad
predomisliš, dovoljno je ukloniti `form.classList.add( 'is-live' )` iz `prodavnica.js`.

**Šta radi:**
- Promjena filtera, sortiranje i paginacija mijenjaju grid bez ponovnog učitavanja.
  URL prati stanje, pa se može podijeliti i osvježiti.
- Faceti i posivljene opcije se osvježavaju u istom odgovoru.

**Jedan izvor istine — ovo je suština i ne smije da se razgradi:**
1. **Upit:** handler NE gradi svoj `tax_query`/`meta_query`. Hidratiše `$_GET` iz
   poslatog query stringa i zove `door_expert_shop_tax_query()` /
   `door_expert_shop_meta_query()`, a sortiranje prepušta WooCommerce-u. To su iste
   funkcije koje rade i pri običnom učitavanju. (Saya ovdje ima dva graditelja upita
   koji se razilaze — dokument izričito kaže da se to ne kopira.)
2. **Markup:** `door_expert_shop_results()` renderuje grid + paginaciju + prazno stanje.
   Koriste je **oba šablona i AJAX**. Da AJAX renderuje svoju karticu, prije ili kasnije
   bi izgubio `srcset`, `loading="lazy"` ili schema podatke, i to niko ne bi primijetio.
3. **Vidljivost:** `door_expert_shop_visibility_clause()` je izdvojena i dijeljena.
   `WC_Query::get_tax_query()` se namjerno **ne** koristi: ta metoda na kraju sama
   primijeni `woocommerce_product_query_tax_query`, pa bi naši filteri ušli dvaput.

**Detalji koji rješavaju tipične AJAX probleme:**
- Redni broj zahtjeva (`seq`): spor odgovor ne može da pregazi noviji.
- Debounce 250ms (slider 150ms, jer okida tek na otpuštanje).
- `history.replaceState`, ne `pushState`: štikliranje ne puni dugme Nazad.
- Greška (npr. istekao nonce zbog keša stranice) => puno učitavanje sa istim
  filterima. Korisnik svakako dobije tačan rezultat, samo sporije.
- `door_expert_filter_facets( $context )` prima kontekst: u AJAX-u
  `is_product_category()` nije tačno, pa bi faceti pali na `'default'` i grupa
  specifična za kategoriju ostala bi sa zastarjelim brojevima.
- Paginacija: na serverskoj putanji bazu i dalje daje `get_pagenum_link` (dokazano
  ponašanje), eksplicitna baza se koristi samo u AJAX-u gdje glavnog upita nema.

**Usput riješen zaseban bug (stariji od AJAX-a):** mobilni panel filtera bio je
JS-only — `.shop-filters` je `display: none`, a otvarala ga je JS klasa `is-open`.
Telefon bez JavaScripta nije imao **nijedan** filter. Toggle je sada sakriven
checkbox + `<label>`; otvaranje, zatvaranje i natpis radi CSS preko `:checked`,
a JS dodaje samo `aria-expanded`.

> **Pouka za svaki sličan panel:** ako otvaranje/zatvaranje nosi JS klasa, a element je
> `display: none` u polaznom stanju, bez JS-a taj sadržaj ne postoji. Checkbox + label
> daje isto ponašanje bez ijedne linije JS-a.

**Provjereno na staging-u:** filtriranje, osvježavanje stranice, povratak sa PDP-a na
listing, kategorijske stranice, sortiranje, sakriveno dugme, i rad bez JavaScripta na
desktopu i na telefonu. Paginacija NIJE provjerena uživo (katalog ima 2 proizvoda,
paginacija se pojavljuje od 13).

### Kada se pokvari — šta proveriti
1. **AJAX daje druge rezultate nego osvježavanje stranice** → neko je u handleru počeo
   da gradi upit ručno. Handler smije samo da napuni `$_GET` i pozove funkcije iz
   `inc/shop.php` (vidi "Jedan izvor istine" gore)
2. **Filtriranje se ne dešava, stranica se puno učitava** → `wp_localize_script` nije
   prošao (`doorExpertShop` nije definisan). Skripta se kači na
   `door-expert-prodavnica-js`, pa ako se ime handle-a promijeni u `functions.php`,
   `inc/shop-ajax.php` to mora da isprati
3. **403 / stalno puno učitavanje** → istekao nonce zbog keša stranice. Isključi keš za
   prodavnicu i kategorije, ili skrati TTL
4. **Grid se zamijeni ali brojevi u sidebaru ostanu stari** → `facets` u odgovoru je
   prazan: `door_expert_filter_facet_term()` nije našao kategoriju (na prodavnici bez
   hero pilule faceting namjerno ne radi)
5. **Kartice izgledaju drugačije posle filtriranja** → neko je zaobišao
   `door_expert_shop_results()` i renderuje karticu na drugom mjestu
6. **Fatalna greška posle deploya** → `functions.php` je otišao bez `inc/shop-ajax.php`.
   Ta dva fajla idu zajedno
7. **Na telefonu nema filtera** → `.shop-filters-switch` checkbox nije u markupu ili je
   CSS stari; provjeri `.shop-filters-switch:checked ~ .shop-filters`

---

## Istaknuti atributi — PDP traka + čipovi na kartici

**Commit:** `191a380` (prva verzija: `dbf8945`)
**Fajl(ovi):** `inc/product-highlights.php`, `inc/product-highlights-admin.php`,
`template-parts/product/parts/highlights.php`, `template-parts/shop/product-card.php`,
`assets/css/admin-highlights.css` (**nov**), `assets/js/admin-highlights.js` (**nov**),
`assets/css/category.css`, `assets/css/product.css`, `functions.php`

**Šta radi:**
- Jedan izbor u adminu (Proizvodi → Istaknuti atributi), dva prikaza: traka sa
  ikonicama na PDP-u i čipovi „Labela: vrijednost“ ispod naziva na kartici.
- Vrijednosti se **ne unose nigdje ponovo** — čitaju se sa proizvoda. Iz admina se
  bira samo koji izvor se ističe, kojom ikonom i pod kojom labelom.
- Konfiguracija po kategoriji, sa nasljeđivanjem: potkategorija bez svojih redova
  uzima od najbližeg pretka, pa od `default`.
- Izvori: WC atributi (`pa_*`), brend, šifra, dostupnost i statičan tekst.
  Rezervni tekst po redu pokriva proizvod kojem vrijednost fali.
- Ulaze u Rank Math Product schemu kao `additionalProperty`.

**Odluke koje se ne vraćaju unazad:**
1. **Kontekst se računa iz kategorija samog proizvoda**, ne iz kategorije koja se
   gleda — ista kartica nosi iste čipove u prodavnici, na kategoriji, u pretrazi,
   u cross-sellu i u AJAX osvježenom listingu.
2. **Čip prikazuje sve vrijednosti atributa** („Širina: 70 cm, 80 cm, 90 cm“), ne
   prvu. Prva vrijednost je kod varijacija tiho krila ostale širine.
3. **Čip odstupa od prototipa** (tamo je samo vrijednost, bez labele) — traženo.
4. **Vrijednost na PDP-u je običan tekst, ne link.** Linkovi ka filtriranom listingu
   su bili napravljeni pa uklonjeni: filtrirane arhive su `noindex, nofollow`
   (`inc/filters-seo.php`), pa SEO koristi nema, a PDP je stranica gdje se
   konvertuje. Ako se ikad vraća — kao opcija po redu u adminu, ne globalno.
5. **Ključ konteksta je term ID, ne slug** — slug se mijenja pri preimenovanju
   kategorije i tiho bi obrisao njeno podešavanje. Isti obrazac kao filter plugin.

**Tri stanja kategorije (vidi se i u lijevoj koloni admina):**
| Stanje | Oznaka | Znači |
|---|---|---|
| svoje | `•` | ima svoje redove |
| naslijeđeno | bez oznake | uzima od pretka ili `default` |
| prazno | `∅` | namjerno bez trake, **ne** nasljeđuje |

**Provjereno na staging-u:** admin ekran, nasljeđivanje, pokrivenost, čipovi na
kartici i traka na PDP-u. Statičan tekst, rezervni tekst i schema **nisu** provjereni
uživo (nema podešenog reda koji ih koristi).

### Kada se pokvari — šta proveriti
1. **Admin izgleda „raspadnuto“ ili nema ▲▼ / pregleda ikone** → `admin-highlights.css`
   ili `admin-highlights.js` nije stigao na server. Idu zajedno sa
   `inc/product-highlights-admin.php`
2. **Pokrivenost stoji na „računam…“** → AJAX pada; provjeri nonce (keš admina) i da
   `wp_ajax_door_expert_highlights_coverage` postoji. Bez brojača forma i dalje radi
3. **Kategorija pokazuje tuđe atribute** → proizvod je u više kategorija, a redoslijed
   bira `door_expert_highlights_order_terms()`. Postavi Rank Math primarnu kategoriju
4. **Sačuvao a ništa se ne mijenja** → red je ispao u sanitizaciji: izvor ne postoji
   (obrisan atribut), duplikat je, ili je „Statičan tekst“ bez teksta
5. **Kartica pokazuje dimenzije i boju iako je podešeno drugo** → radi fallback u
   `product-card.php`, znači nijedan podešen red nema vrijednost na tom proizvodu
6. **Traka nestala svuda** → neka kategorija je čekirana kao „ne prikazuj“, ili je
   `default` ostao bez ijednog reda
7. **Promjena u adminu se ne vidi na sajtu** → PHP se ne bustuje preko `?ver`; Purge keš

---

## Mobilna sticky traka — dodavanje u korpu sa PDP-a

**Commit:** `8287283`
**Fajl(ovi):** `footer.php`, `template-parts/product/single.php`,
`inc/product-variations.php`, `assets/js/product.js`, `assets/css/product.css`

**Šta radi:**
- Sticky dugme na telefonu stvarno dodaje proizvod u upit, sa izabranom varijacijom
  i izabranom količinom. Ranije je bio link koji za varijabilni proizvod vodi na
  stranicu na kojoj kupac već jeste.
- Radi i bez JavaScripta: `<button type="submit" form="product-cta-form">`, ne JS proxy.
- Na stranici proizvoda postoji **samo jedna** traka; `footer.php` svoju preskače.

**Tri kvara koja su bila u lancu (ako se nešto od ovoga vrati, tu je uzrok):**
1. `add_to_cart_url()` za `WC_Product_Variable` vraća **permalink**, jer ta klasa ne
   prepisuje baznu metodu. Kod simple proizvoda vraća `?add-to-cart=ID` i ignoriše
   polje za količinu.
2. Dvije `position: fixed; bottom: 0; z-index: 900` trake su se crtale jedna preko
   druge. Globalna (footer, ispod 768px) je pobjeđivala jer je kasnije u DOM-u.
3. Bez JS-a `variation_id` ostaje 0, a WooCommerce-ov
   `find_matching_product_variation()` na ovoj instalaciji vraća 0 iako je atribut
   poslat ispravno → „Please choose product options". Ta pretraga ide kroz
   `get_posts()`, pa je može poremetiti plugin koji filtrira upite (JetEngine je aktivan).

**Odluke koje se ne vraćaju unazad:**
- `door_expert_resolve_posted_variation()` varijaciju nalazi **iteracijom po djeci
  roditelja, bez upita nad bazom**. Namjerno — upit je ono što je i puklo. Kači se na
  `wp_loaded` prioritet **19**, jer `WC_Form_Handler::add_to_cart_action()` je na 20.
  Kad JS radi, funkcija odmah izlazi (`variation_id` je već popunjen).
- Prazna vrijednost atributa varijacije je WC-ov džoker („Bilo koja"), ne vrijednost —
  zato se preskače pri poređenju.
- Na PDP-u je **„Dodaj u ponudu" primarno (amber), poziv sekundarno** — obrnuto od
  ostatka sajta. Po `DOCS/CRO/CRO - product.md`: dodavanje u upit je primarna svrha
  stranice, poziv je sekundarni cilj, a Mobile-Specific CRO izričito traži sticky
  „Dodaj u upit". Isti dokument pod A/B prijedlozima ostavlja ovo kao otvoreno za test.

**Provjereno na staging-u:** POST-om (`curl`) i u browseru. Sa izabranom širinom i bez
JS-a proizvod ulazi u korpu; bez izbora ne ulazi ništa. **Nije provjereno:** simple
proizvod (katalog ga još nema).

**Poznata rupa, nije uzrokovana ovim radom:** šablon PDP-a nigdje ne ispisuje
WooCommerce notice-e (`woocommerce_output_all_notices()`), pa bez JS-a klik bez izbora
prođe **bez ijedne poruke**. Sa JS-om se ne vidi jer je dugme ugašeno.

### Kada se pokvari — šta proveriti
1. **Sticky dugme ne radi ništa** → u DevTools provjeri da je `<button>` a ne `<a>`, i
   da `form="product-cta-form"` pogađa postojeći `id` forme
2. **Dvije trake / vidi se pogrešna** → `footer.php` na serveru je star; provjeri
   `is_product()` uslov oko `.mobile-sticky-cta`
3. **„Please choose product options" bez JS-a** → `inc/product-variations.php` je star
   ili se `door_expert_resolve_posted_variation()` ne izvršava. Testiraj direktno:
   `curl -X POST <pdp> -d "add-to-cart=ID&variation_id=0&attribute_pa_x=slug&quantity=1"`
   pa traži tu poruku u odgovoru
4. **Dodaje pogrešnu varijaciju** → džoker logika; provjeri da se prazna vrijednost
   preskače, a ne poredi
5. **Sticky dugme ostaje blijedo iako je sve izabrano** → `product.js` ne prati klasu
   `disabled` na `#btn-add-to-cart` (MutationObserver), ili je WC promijenio ime klase

---

## Cijena na upit — proizvod bez unesene cijene

**Commit:** `07b3f1c`
**Fajl(ovi):** `inc/quote-cart.php`, `template-parts/page/korpa.php`,
`assets/js/korpa.js`, `assets/js/product.js`, `functions.php`

**Šta radi:**
- Proizvod bez cijene piše **„Cijena na upit"**, nikad „0,00 €" — na kartici u
  listingu, na PDP-u prije i poslije izbora, u korpi i u pregledu ponude.
- Kod varijabilnog proizvoda se iz raspona izbacuju varijacije bez cijene.
  „0,00 € – 355,00 €" je bio najgori ishod: sugeriše da nešto košta nula.
- Pregled ponude u korpi se osvježava kroz AJAX, bez ponovnog učitavanja.

**Gdje su tri odvojena puta do iste greške (ako se vrati, provjeri sva tri):**
1. **Cijena proizvoda** → filter `woocommerce_get_price_html`.
2. **Zbir stavke u korpi** → `door_expert_cart_line_total()`. Filter iznad ovdje
   **ne stiže** — to je zaseban račun (`wc_price( line_total )`).
3. **AJAX pri promjeni količine** → isti helper. Bez njega se tekst vrati u
   „0,00 €" čim kupac klikne plus ili minus. Ovo je najlakše promašiti.

**Odluke koje se ne vraćaju unazad:**
- Tekst živi u `door_expert_price_on_request()`, jednom mjestu. U JS stiže kroz
  `wp_localize_script` (`doorExpertPrice.onRequest`), ne kao drugi literal.
- **Natpis dugmeta se ne mijenja po cijeni** — radnja je ista, pa ostaje „Dodaj u
  ponudu". Porting dokument `03` predlaže „Zatražite cijenu"; odbijeno svjesno, po
  `DOCS/CRO/CRO - product.md` i odluci vlasnika.
- Kad **sve** varijacije imaju cijenu, zatečeni WooCommerce ispis se ne dira.
- Pregled ponude crta `door_expert_quote_summary_html()` — **jedan renderer za
  šablon i za oba AJAX odgovora**, isti obrazac kao `door_expert_shop_results()`.
  Šablon nema svoju kopiju tog markupa.
- Taj markup se ispisuje **bez `wp_kses_post()`**: funkcija escape-uje svaki podatak
  iznutra, a kses bi skinuo `data-cart-total`, na kojem stoji osvježavanje iznosa.
- Napomena „Stavke sa oznakom Cijena na upit nisu uračunate" ide u **postojeću**
  napomenu ispod iznosa, samo kad takva stavka stvarno postoji u korpi.

**Provjereno na staging-u:** listing, PDP prije i poslije izbora, korpa, promjena
količine, uklanjanje stavke, pregled ponude. **Nije provjereno:** tekst u mejlu upita.

### Kada se pokvari — šta proveriti
1. **„0,00 €" negdje izlazi** → nađi koji od tri puta gore renderuje to mjesto
2. **Tekst se vrati u „0,00 €" na +/−** → AJAX handler ne koristi
   `door_expert_cart_line_total()`
3. **Pregled desno kasni za tabelom** → odgovor nema `summary_html`, ili u markupu
   fali `data-cart-summary`
4. **Iznos se ne osvježava poslije izmjene** → `data-cart-total` je pojeden; provjeri
   da nad `door_expert_quote_summary_html()` niko nije vratio `wp_kses_post()`
5. **Raspon opet počinje od nule** → filter ne radi; provjeri da proizvod jeste
   `variable` i da `get_variation_prices()` vraća nulu za tu varijaciju

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

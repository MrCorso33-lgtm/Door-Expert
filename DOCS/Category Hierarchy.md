# Category Hierarchy — Door Expert

WooCommerce `product_cat` struktura, 1:1 sa SEO tabelom. Referenca za pravljenje
kategorija i za linkove u temi.

**Tri kolone koje se ne mešaju:**
- **Naziv** = display ime (vidi se kao H1 kategorije, u meniju, breadcrumb). Pun, opisni naziv.
- **Slug** = segment u URL-u (mala slova, bez dijakritike).
- **URL** = finalni, ugnježdeni (posle uklanjanja `/product-category/` baze).

> Bitno: potkategorija ima **pun naziv** ("Sigurnosna vrata za stan"), ali **kratak slug**
> (`za-stan`) jer roditelj već daje kontekst u URL-u (`/sigurnosna-vrata/za-stan/`).

> ⚠️ **Baza kategorije = `c`** (SEO kolega). Stvarni URL zato ima `/c/` prefiks:
> `/c/sobna-vrata/klizna/`. URL-ovi u tabeli ispod su bez prefiksa radi preglednosti —
> dodaj `/c/` na početak za pravi URL. **Tema ne hardkoduje ovo:** koristi
> `get_term_link()` (`door_expert_cat_url()` helper), pa WooCommerce sam sklapa pun URL
> sa trenutnom bazom. Ako se baza promeni, tema automatski prati.

---

## Stablo (vizuelno)

```
Sobna vrata                          /sobna-vrata/
├─ Klizna vrata                      /sobna-vrata/klizna/
└─ Staklena vrata                    /sobna-vrata/staklena-vrata/

Sigurnosna vrata                     /sigurnosna-vrata/
├─ Sigurnosna vrata za stan          /sigurnosna-vrata/za-stan/
└─ Sigurnosna vrata za kuću          /sigurnosna-vrata/za-kucu/

Keramičke pločice                    /keramicke-plocice/
├─ Podne pločice                     /keramicke-plocice/podne/
├─ Zidne pločice                     /keramicke-plocice/zidne/
├─ Pločice za kupatilo               /keramicke-plocice/za-kupatilo/
├─ Pločice za kuhinju                /keramicke-plocice/za-kuhinju/
├─ Spoljne pločice                   /keramicke-plocice/spoljne/
├─ Pločice za bazen                  /keramicke-plocice/za-bazen/
└─ Gazište za stepenice              /keramicke-plocice/gaziste-za-stepenice/

Dekorativni umivaonici               /umivaonici/
├─ Kameni umivaonik                  /umivaonici/kameni/
├─ Samostojeći umivaonik             /umivaonici/samostojeci/
└─ Nadgradni umivaonik               /umivaonici/nadgradni/
```

**4 roditelja + 14 potkategorija = 18 kategorija.**

---

## Puna tabela

| # | Nivo | Naziv (display) | Parent | Slug | URL |
|---|------|-----------------|--------|------|-----|
| 1 | I | Sobna vrata | — | `sobna-vrata` | `/sobna-vrata/` |
| 2 | II | Klizna vrata | Sobna vrata | `klizna` | `/sobna-vrata/klizna/` |
| 3 | II | Staklena vrata | Sobna vrata | `staklena-vrata` | `/sobna-vrata/staklena-vrata/` |
| 4 | I | Sigurnosna vrata | — | `sigurnosna-vrata` | `/sigurnosna-vrata/` |
| 5 | II | Sigurnosna vrata za stan | Sigurnosna vrata | `za-stan` | `/sigurnosna-vrata/za-stan/` |
| 6 | II | Sigurnosna vrata za kuću | Sigurnosna vrata | `za-kucu` | `/sigurnosna-vrata/za-kucu/` |
| 7 | I | Keramičke pločice | — | `keramicke-plocice` | `/keramicke-plocice/` |
| 8 | II | Podne pločice | Keramičke pločice | `podne` | `/keramicke-plocice/podne/` |
| 9 | II | Zidne pločice | Keramičke pločice | `zidne` | `/keramicke-plocice/zidne/` |
| 10 | II | Pločice za kupatilo | Keramičke pločice | `za-kupatilo` | `/keramicke-plocice/za-kupatilo/` |
| 11 | II | Pločice za kuhinju | Keramičke pločice | `za-kuhinju` | `/keramicke-plocice/za-kuhinju/` |
| 12 | II | Spoljne pločice | Keramičke pločice | `spoljne` | `/keramicke-plocice/spoljne/` |
| 13 | II | Pločice za bazen | Keramičke pločice | `za-bazen` | `/keramicke-plocice/za-bazen/` |
| 14 | II | Gazište za stepenice | Keramičke pločice | `gaziste-za-stepenice` | `/keramicke-plocice/gaziste-za-stepenice/` |
| 15 | I | Dekorativni umivaonici | — | `umivaonici` | `/umivaonici/` |
| 16 | II | Kameni umivaonik | Dekorativni umivaonici | `kameni` | `/umivaonici/kameni/` |
| 17 | II | Samostojeći umivaonik | Dekorativni umivaonici | `samostojeci` | `/umivaonici/samostojeci/` |
| 18 | II | Nadgradni umivaonik | Dekorativni umivaonici | `nadgradni` | `/umivaonici/nadgradni/` |

---

## Kako napraviti u WooCommerce

1. **Product category base = `c`** (Settings → Permalinks) → Save. (Odabrano umesto `.`
   jer je stabilnije; daje `/c/sobna-vrata/klizna/`.)
2. **Products → Categories** — pravi **roditelje prvo**, pa decu (dete bira Parent iz padajućeg).
   Za svaku unesi **Name** (iz kolone "Naziv") i **Slug** (iz kolone "Slug") tačno kako piše.
3. **Flush:** Settings → Permalinks → Save (samo klik).
4. **Test:** otvori npr. `/sobna-vrata/klizna/` — treba da učita (prazna) kategorija, NE 404.

---

## Napomene

- **Prazne kategorije su OK za sad** — URL radi i bez proizvoda. Proizvodi dolaze kasnije.
- **Naziv ≠ slug:** naziv se vidi (pun, opisni), slug je u URL-u (kratak). Nemoj stavljati
  pun naziv u slug — pokvarilo bi URL strukturu.
- **Roditelj "Sigurnosna vrata":** SEO tabela ga negde zove "Sigurnosna (blind) vrata".
  Za display naziv koristimo "Sigurnosna vrata" (kao u temi/prototipu); ako SEO baš traži
  "(blind)" varijantu, to je stvar naziva, ne slug-a — URL ostaje `/sigurnosna-vrata/`.
- **Ne praviti WP stranice sa istim slug-ovima** (sobna-vrata, keramicke-plocice…) — sudarilo
  bi se sa WC kategorijom pošto je baza uklonjena.
- **Izgled kategorije** će biti default WooCommerce dok ne napravimo `taxonomy-product_cat.php`
  šablon iz dizajniranih landing stranica (poseban korak).
- **Linkovi u temi** (ravno → ugnježdeno) se menjaju ZAJEDNO sa ovim — vidi `DEPLOY.md`,
  sekcija "SEO URL + WooCommerce".

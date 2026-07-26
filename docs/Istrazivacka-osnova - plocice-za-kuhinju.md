# Istraživačka osnova — Pločice za kuhinju
## Zašto je ova stranica izgrađena ovako — sekcija po sekcija

### 1. Hero sekcija — "Pločice za kuhinju — otporne i elegantne"

**Šta je izgrađeno:** Hero sa naslovom koji naglašava funkcionalnost (otpornost), cijenama od 16 €/m², i badge-om "Otporne na masnoću i toplotu".

**Istraživačka osnova:**
> "Korisnici koji dolaze na stranicu pločica imaju visoku namjeru kupovine — traže konkretne dimenzije, boje i primjenu" — Website UX Research

> "Španski brendovi su ključna diferenciacija — naglasiti porijeklo na svakom koraku" — Competitive Landscape

**Zašto ovo rješenje:** Korisnik koji traži "kuhinjske pločice" ima specifičnu namjeru — želi pločice koje su otporne na masnoću i lako se čiste. Hero odmah potvrđuje da je na pravom mjestu i komunicira ključnu prednost.

### 2. Trust bar — Španska keramika, odmah dostupno

**Šta je izgrađeno:** Horizontalni trust bar sa 4 elementa: "Španski brendovi", "Na stanju odmah", "Besplatna konsultacija", "Direktan uvoz".

**Istraživačka osnova:**
> "Zalihe odmah dostupne — jedina kompanija u CG koja to komunicira online" — Business Facts

> "Trust signali su kritični — certifikati, porijeklo, fizički salon" — Trust Signals DoorExpert

**Zašto ovo rješenje:** Tržište Crne Gore je skeptično prema online kupovini. Trust bar odmah ispod heroa gradi povjerenje prije nego korisnik vidi cijene.

### 3. Benefits sekcija — 6 kartica prednosti

**Šta je izgrađeno:** 6 kartica: otpornost na masnoću, lako čišćenje, otpornost na toplotu, koordinirani pod+zid, 4 brenda, odmah dostupno.

**Istraživačka osnova:**
> "Korisnici traže tehničke specifikacije (dimenzije, anti-slip klasa, otpornost na mraz) prije kupovine" — Website UX Research

**Zašto ovo rješenje:** Kuhinjske pločice imaju specifične tehničke zahtjeve koje korisnik mora razumjeti. Kartice sa ikonama komuniciraju tehničke prednosti vizuelno, bez teksta koji se ne čita.

### 4. Zones sekcija — Pod, Splashback, Zid, Mozaik

**Šta je izgrađeno:** 4 kartice koje objašnjavaju tehničke zahtjeve svake zone kuhinje sa preporukama.

**Istraživačka osnova:**
> "Cross-sell između kategorija je prirodan u kupatilskom prostoru" — Website Quote Cart Model

> "Tipična kupovina: ~5 unutrašnjih vrata po stanu, od kojih je obično 1 stakleno i 1 kupatilsko" — Business Facts (analogija za cross-zone kupovinu)

**Zašto ovo rješenje:** Korisnik koji kupuje kuhinjske pločice često ne zna da splashback zahtijeva drugačije pločice od poda. Zones sekcija edukuje i povećava prosječnu vrijednost narudžbe jer korisnik shvata da treba i pod i splashback.

### 5. Product grid sa filterima

**Šta je izgrađeno:** Grid sa 8 proizvoda i filterima: zona primjene, brend, finish, dimenzije, boja, cijena, dostupnost.

**Istraživačka osnova:**
> "Vidljivi popusti su obavezni — tržište ne kupuje bez vidljivog popusta" — Conversion Strategy

> "Category-specific filters: tiles (application, dimensions, color)" — Project Instructions

**Zašto ovo rješenje:** Filter po zoni primjene (pod/splashback/zid) je jedinstven za kuhinjske pločice i direktno odgovara na mentalni model korisnika koji razmišlja po zonama, ne po tehničkim karakteristikama.

### 6. FAQ sekcija

**Šta je izgrađeno:** 7 pitanja specifičnih za kuhinjske pločice: dimenzije splashbacka, otpornost na masnoću, razlika pod vs zid, održavanje, koordinirani setovi.

**Istraživačka osnova:**
> "Solve this PROACTIVELY: a dedicated 'how installation works / what's included' page" — Project Instructions (analogija za proaktivnu edukaciju)

**Zašto ovo rješenje:** Kuhinjska renovacija je kompleksna i korisnici imaju mnogo tehničkih pitanja. Proaktivni FAQ smanjuje potrebu za pozivom i ubrzava odluku o kupovini.

### 7. Cross-sell sekcija

**Šta je izgrađeno:** 3 cross-sell kartice: Podne pločice, Zidne pločice, Pločice za kupatilo.

**Istraživačka osnova:**
> "Doors vs. ceramics/bathroom are two distinct shopping 'worlds' that only naturally overlap via interior doors + the bathroom space. Cross-sell at logical points, not forced everywhere." — Project Instructions

**Zašto ovo rješenje:** Korisnik koji kupuje kuhinjske pločice prirodno treba i podne pločice za kuhinju i zidne pločice za ostatak kuhinje. Cross-sell je logičan, ne forsiran.

## Elementi koji su svjesno izostavljeni
- **Online kalkulator za m²** — nije implementiran u prototipu jer zahtijeva JavaScript logiku; preporučuje se za WordPress fazu
- **Video ugradnje** — nije dostupan materijal; placeholder za buduću fazu
- **Recenzije kupaca** — politika sadržaja zabranjuje fabrikovane recenzije; čekati prave recenzije

## Ključne pretpostavke za WordPress fazu
- Filter "Koordinirani set" (pod+zid iz iste kolekcije) zahtijeva custom taxonomiju u WooCommerce
- Zones sekcija treba biti dinamična — svaka pločica treba imati atribut "zona primjene"
- Splashback dimenzije u FAQ trebaju biti ažurirane prema stvarnim dimenzijama iz asortimana

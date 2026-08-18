# CRO dokument: Stranica "Sve Kolekcije" (kolekcije.html)

## Uloga stranice u konverzijskom toku

Ova stranica je **srednji dio funela** (mid-funnel). Korisnik je vec zainteresovan za keramiku (dosao je sa pocetne, brand stranice, ili direktno sa Google pretrage "tau ceramica kolekcije crna gora") i sada bira koju kolekciju da detaljnije istrazi. Cilj stranice nije direktna konverzija (dodavanje u ponudu), vec **usmjeravanje ka stranici kolekcije** gdje ce se desiti dublja interakcija i eventualno dodavanje proizvoda u quote cart.

---

## Primarni konverzijski cilj

**Klik na "Pogledaj kolekciju"** - korisnik prelazi na pojedinacnu stranicu kolekcije.

## Sekundarni konverzijski ciljevi

1. Klik na "Zakazite posjetu" (CTA strip na dnu)
2. Koriscenje filtera/pretrage (signal angazovanosti)
3. Povratak na pocetnu ili brand stranicu (navigacija, ne napustanje sajta)

---

## CRO elementi na stranici

| Element | CRO funkcija | Mehanizam |
|---------|-------------|-----------|
| Statistike u hero-u (12+, 4, 1) | Social proof / obim ponude | Brojevi komuniciraju "ovdje ima dovoljno izbora" i smanjuju strah od ogranicene ponude |
| "Spanija" badge na karticama | Trust signal / kvalitet | Ponavlja kljucni prodajni argument na svakoj kartici bez da korisnik mora da cita opis |
| "NOVO" badge | Urgency / freshness | Signalizira da se ponuda azurira, motivise ponovne posjete |
| Sticky filter bar | Smanjenje frikcije | Korisnik ne mora scrollati nazad da promijeni filter, sto smanjuje napustanje |
| Masonry layout sa featured | Vizuelna hijerarhija | Usmjerava paznju na kolekcije koje klijent zeli da promovise |
| "Pogledaj kolekciju" link sa strelicom | Micro-interaction / affordance | Strelica se pomjera na hover, signalizirajuci interaktivnost |
| CTA strip "Ne mozete da se odlucite?" | Objection handling | Adresira nesigurnost kupca i nudi alternativni put (fizicka posjeta) |
| Hover zoom na slikama | Engagement signal | Suptilni zoom komunicira da je kartica klikabilna i poziva na interakciju |

---

## Potencijalni friction points i rjesenja

| Friction point | Rizik | Rjesenje |
|---------------|-------|---------|
| Previse kolekcija na jednoj stranici | Kognitivno opterecenje, paraliza izbora | Filter po brendu + pretraga smanjuju vidljivi set na relevantne |
| Korisnik ne zna koji brend je "bolji" | Nesigurnost, napustanje | Featured kartice (vece) signaliziraju preporuku klijenta |
| Nema cijene na karticama | Korisnik ne zna da li je u budzetu | Namjerno: cijena se prikazuje na stranici kolekcije, ovdje je fokus na inspiraciji |
| Slike se sporo ucitavaju | Bounce rate raste | Lazy loading + optimizovane slike (WebP, max 600px sirina za kartice) |
| Korisnik dosao sa mobilnog (Instagram) | Mali ekran, tezak grid | Responsive: 1 kolona na mobilnom, featured kartice gube span |

---

## Metrike za pracenje (KPI)

| Metrika | Cilj | Alat |
|---------|------|------|
| CTR na "Pogledaj kolekciju" | > 35% posjetilaca stranice | GA4 event tracking |
| Scroll depth | > 60% korisnika vidi bar 6 kartica | GA4 scroll tracking |
| Filter usage rate | > 20% koristi bar jedan filter | Custom event |
| Bounce rate | < 40% | GA4 |
| CTA strip CTR ("Zakazite posjetu") | > 5% | GA4 event |
| Prosjecno vrijeme na stranici | > 45 sekundi | GA4 |

---

## A/B test ideje

1. **Grid layout:** Uniformni 3-kolona vs masonry sa featured karticama - mjeriti CTR na kolekcije
2. **Badge pozicija:** "Spanija" badge na slici vs u body tekstu - mjeriti percepciju kvaliteta (survey)
3. **CTA strip copy:** "Ne mozete da se odlucite?" vs "Zelite da ih vidite uzivo?" - mjeriti CTR
4. **Broj prikazanih kolekcija:** Sve odjednom vs "Ucitaj jos" (load more) - mjeriti scroll depth i engagement
5. **Pretraga prominentnost:** Mala u filteru vs velika iznad grida - mjeriti usage rate

---

## Preporuke za dalji razvoj

1. Dodati "Najpopularnije" tab u filter bar (na osnovu analytics podataka o najposjecenijim kolekcijama)
2. Implementirati "quick view" modal na hover/klik koji prikazuje 3-4 slike iz kolekcije bez napustanja arhive
3. Dodati breadcrumb navigaciju (Pocetna > Keramicke plocice > Kolekcije) za SEO i orijentaciju
4. Razmotriti "Compare" funkcionalnost gdje korisnik moze uporediti 2-3 kolekcije side-by-side


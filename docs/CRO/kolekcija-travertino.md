# CRO dokument: Stranica kolekcije "Travertino" (kolekcija-travertino.html)

## Uloga stranice u konverzijskom toku

Ova stranica je **donji dio funela** (bottom-funnel). Korisnik je vec izabrao kolekciju koja ga zanima (dosao sa arhive kolekcija ili brand stranice) i sada zeli da vidi detalje, slike u kontekstu, i konkretne proizvode. Cilj stranice je da ga dovede do **dodavanja proizvoda u quote cart** ili **telefonskog poziva**.

---

## Primarni konverzijski cilj

**Klik na "Zatrazite ponudu"** (CTA strip na dnu) ili dodavanje proizvoda iz grida u quote cart.

## Sekundarni konverzijski ciljevi

1. Klik na "Pozovite nas" (click-to-call)
2. Klik na pojedinacni proizvod u gridu (prelazak na product page)
3. Scroll do product grida (signal visoke namjere)

---

## CRO elementi na stranici

| Element | CRO funkcija | Mehanizam |
|---------|-------------|-----------|
| Fullscreen hero sa ambijentnom slikom | Emocionalni hook | Kupac se vizuelno "smjesta" u prostor sa tom keramicom, sto aktivira zamisljanje |
| "Tau Ceramica · Spanija" eyebrow | Trust signal | Porijeklo i brend su prva informacija, grade povjerenje prije nego sto kupac vidi cijenu |
| Horizontalni scroll reveal | Engagement / dwell time | Interaktivni scroll zadrzava korisnika na stranici duze, povecavajuci vjerovatnocu konverzije |
| Sticky tekst sa specifikacijama | Informirana odluka | Kupac uvijek vidi tehnicke detalje dok gleda slike, smanjujuci potrebu za "nazad" navigacijom |
| Spec badge-ovi (60x120, Mat, Frost-proof) | Quick-scan trust | Tehnicke info u kompaktnom formatu za kupce koji znaju sta traze |
| Galerija sa fade-in | Progressive disclosure | Slike se otkrivaju postepeno, odrzavajuci paznju i sprecavajuci "wall of content" osjecaj |
| Product grid sa filterima | Smanjenje frikcije | Kupac moze filtrirati po dimenziji/boji bez napustanja stranice |
| Cijena "28 EUR/m2 sa PDV-om" | Transparentnost | Jasna cijena smanjuje nesigurnost i eliminise potrebu za pozivom samo radi cijene |
| CTA strip sa dva dugmeta | Dual-path konverzija | Primarni (ponuda) i sekundarni (poziv) put su jasno razdvojeni |
| "Na stanju" u CTA eyebrow-u | Urgency / differentiator | Ponavlja kljucnu prednost (odmah dostupno) u momentu odluke |

---

## Konverzijski tok korisnika na stranici

```
Hero (emocionalni hook, 3-5 sec)
    |
    v
Horizontal Reveal (engagement, 10-15 sec scroll)
    |
    v
Story Split (informiranje, citanje opisa + galerija)
    |
    v
Product Grid (izbor, filtriranje, poredjenje cijena)
    |
    v
CTA Strip (akcija: ponuda ili poziv)
```

Prosjecno ocekivano vrijeme na stranici: **90-120 sekundi** (duze od standardne product page jer ukljucuje cinematic intro).

---

## Potencijalni friction points i rjesenja

| Friction point | Rizik | Rjesenje |
|---------------|-------|---------|
| Cinematic intro je predugacak | Nestrpljivi korisnici napustaju prije product grida | Horizontal reveal je 300vh (3 ekrana scrolla), dovoljno kratko da ne frustrira |
| Nema "skip to products" opcije | Power users zele odmah grid | Dodati anchor link u hero "Pogledaj proizvode ↓" |
| Slike se sporo ucitavaju (hero je velika) | Bounce na sporom mobilnom | Hero slika optimizovana (WebP), ostale lazy-loaded |
| Korisnik ne zna koliko kosta montaza | Nesigurnost oko ukupne cijene | Dodati napomenu "Cijena ne ukljucuje montazu" sa linkom na montaza.html |
| Nema "dodaj u ponudu" na karticama | Korisnik mora ici na product page da bi dodao | Razmotriti quick-add dugme na hover (buduci razvoj) |
| Mobilni korisnik ne vidi horizontalni scroll | Efekat se gubi na malom ekranu | Responsive: na mobilnom se prebacuje na vertikalni fade reveal |

---

## Metrike za pracenje (KPI)

| Metrika | Cilj | Alat |
|---------|------|------|
| Scroll depth do product grida | > 60% posjetilaca | GA4 scroll tracking |
| CTR na CTA "Zatrazite ponudu" | > 8% | GA4 event |
| CTR na "Pozovite nas" | > 3% | GA4 event |
| Klik na proizvod u gridu | > 25% | GA4 event |
| Prosjecno vrijeme na stranici | > 90 sekundi | GA4 |
| Bounce rate | < 35% | GA4 |
| Filter usage | > 15% | Custom event |

---

## A/B test ideje

1. **Sa vs bez cinematic intro:** Direktan product grid vs fullscreen hero + reveal - mjeriti konverziju i dwell time
2. **CTA pozicija:** Samo na dnu vs floating sticky CTA koji se pojavljuje nakon scrolla do grida
3. **Cijena format:** "28 EUR/m2" vs "od 28 EUR/m2" vs "28-32 EUR/m2 (zavisno od dimenzije)" - mjeriti CTR na ponudu
4. **Gallery format:** Vertikalna galerija vs horizontalni carousel sa strelicama - mjeriti engagement
5. **Hero tekst:** Sa tagline-om vs samo ime kolekcije - mjeriti percepciju premium-a (survey)

---

## Preporuke za dalji razvoj

1. Dodati "Dodaj u ponudu" dugme direktno na kartice u product gridu (quick-add bez odlaska na product page)
2. Implementirati "Slicne kolekcije" sekciju ispod CTA-a (cross-sell ka drugim Tau kolekcijama)
3. Dodati "Kalkulator kolicine" widget (unesi m2 prostora, dobij kolicinu i okvirnu cijenu)
4. Razmotriti video element u galeriji (30-sec clip postavljanja plocice) za dodatni trust
5. Dodati "Preuzmi specifikaciju" PDF link za B2B kupce (investitore, arhitekte)


# Istrazivacka osnova: Stranica kolekcije "Travertino" (kolekcija-travertino.html)

## Pregled

Ova stranica je cinematic studio prezentacija jedne keramicke kolekcije (Tau Ceramica Travertino). Njena uloga je da kupcu pruzi imerzivno iskustvo kolekcije prije nego sto predje na konkretne proizvode i dodavanje u ponudu. Stranica kombinuje scroll-driven animacije, editorijalni sadrzaj i product grid.

---

## Sekcija 1: Fullscreen Hero (100vh)

**Dizajnerska odluka:** Atmosferska fotografija kupatila sa travertin plocicama, bez teksta na slici osim naziva kolekcije u donjem lijevom uglu. Minimalni header sa logom i "nazad" linkom.

**Istrazivacka osnova:**

Iz "Visual Research for Door Expert": "Keramika treba JEDNAKU vizuelnu prominentnost kao vrata." Fullscreen hero daje kolekciji maksimalnu vizuelnu tezinu i komunicira da je ovo premium proizvod koji zasluzuje sopstvenu "izlozbu", ne samo red u katalogu.

Iz "Website UX Research" (sekcija o prvom utisku): "Ono sto je iznad prevoja utice na 84% korisnickog iskustva." Hero koji zauzima cijeli ekran osigurava da je prvi utisak iskljucivo vizuelan i emocionalan, bez distrakcija.

Iz poslovnih cinjenica: "Spanish origin is the single biggest selling advantage." Eyebrow tekst "Tau Ceramica · Spanija" je pozicioniran iznad naziva kolekcije da bi porijeklo bilo prva informacija koju korisnik registruje.

Scroll indikator ("Scroll" + animirana linija) signalizira da ima vise sadrzaja ispod, sto sprecava da korisnik pomisli da je stranica samo jedna slika.

---

## Sekcija 2: Horizontalni scroll reveal

**Dizajnerska odluka:** Scroll-driven horizontalni pokret sa velikim outline tipografskim rijecima ("Travertino", "Elegancija", "Prirodnog", "Kamena") i slikama koje se otkrivaju izmedju rijeci.

**Istrazivacka osnova:**

Iz analize Saya Group kolekcija (kolekcije-single.js): Saya koristi GSAP ScrollTrigger sa vertikalnim pin-om i blur efektom za ime kolekcije. Za Door Expert sam namjerno izabrao **horizontalni** scroll umjesto vertikalnog pina da bi se vizuelno razlikovao, ali zadrzao isti princip: scroll-driven otkrivanje sadrzaja koje kupca "uvlaci" u pricu.

Iz "Website UX Research": Suptilne animacije komuniciraju premium osjecaj. Horizontalni parallax sa velikom tipografijom stvara osjecaj "setnje kroz izlozbu" koji je konzistentan sa pozicioniranjem Door Expert-a kao premium showroom-a.

Outline tipografija (stroke bez fill-a) za rijeci "Elegancija" i "Prirodnog" stvara vizuelni kontrast sa filled rijecima "Travertino" i "Kamena", naglasavajuci ime kolekcije dok prateece rijeci sluze kao atmosferski kontekst.

---

## Sekcija 3: Story Split (sticky tekst + galerija)

**Dizajnerska odluka:** Dvostubacni layout: lijevo sticky tekst sa opisom kolekcije i specifikacijama (dimenzije, povrsina, frost-proof), desno vertikalna galerija sa 3 slike koje se fade-in-uju na scroll.

**Istrazivacka osnova:**

Iz "Website Conversion Strategy" (sekcija o stranicama proizvoda): "Kljucni elementi (slika, naziv, cijena, kratki opis, dostupnost) trebaju biti vidljivi." Sticky tekst osigurava da su specifikacije uvijek vidljive dok korisnik scrolluje kroz galeriju, bez potrebe da se vraca gore.

Iz "Trust Signals DoorExpert": Tehnicke specifikacije (dimenzije, tip povrsine, frost-proof) su trust signali koji komuniciraju profesionalnost i pomazu kupcu da donese informisanu odluku. Prikazane su kao badge-ovi (kompaktni, skenirajuci) umjesto dugog teksta.

Iz "Competitive Landscape": Konkurenti prikazuju keramiku u generickim katalozima bez konteksta. Galerija sa ambijentnim fotografijama (plocica u prostoru, ne samo swatch) pomaze kupcu da vizualizuje kako ce kolekcija izgledati u njegovom domu.

Prelaz sa tamne pozadine (hero + reveal) na svijetlu (alabaster) signalizira tranziciju iz "emocionalnog" dijela u "informativni" dio stranice.

---

## Sekcija 4: Product Grid

**Dizajnerska odluka:** 3-kolona grid sa karticama proizvoda (slika, ime, meta badge-ovi za dimenziju/povrsinu/primjenu, cijena u EUR/m2). Filter tabovi po dimenziji i boji.

**Istrazivacka osnova:**

Iz "Website UX Research": "Filtri specificni za kategoriju: plocice (primjena, dimenzije, boja)." Filteri na product gridu su prilagodjeni keramici: dimenzija (60x120, 30x60) i boja (Beige, Ivory, Noce).

Iz poslovnih cinjenica: "Each door model comes in ~3 standard dimensions - show as VARIANTS on a single product page, not separate products." Isti princip je primijenjen na keramiku: jedna kolekcija, vise dimenzija/boja kao varijante u istom gridu.

Iz "Website Conversion Strategy": Cijena mora biti vidljiva. Prikazana je kao "28 EUR/m2 sa PDV-om" jer je to format koji kupci keramike ocekuju (cijena po kvadratu, ne po komadu).

Staggered entrance animacija (GSAP) na karticama daje osjecaj "otkrivanja" proizvoda umjesto staticnog ucitavanja.

---

## Sekcija 5: CTA Strip

**Dizajnerska odluka:** Tamna sekcija sa dva CTA dugmeta: "Zatrazite ponudu" (primarno) i "Pozovite nas" (sekundarno).

**Istrazivacka osnova:**

Iz "Website Conversion Strategy" (sekcija 1): "Konverzijski ciljevi po prioritetu: (1) quote cart inquiry, (2) telefonski pozivi, (3) posjete salonu." Oba primarna cilja su pokrivena: dugme za ponudu vodi ka korpi (quote cart), a dugme za poziv je click-to-call.

Iz "Website Conversion Strategy" (sekcija 2): "Sticky pozivni dio moze povecati broj poziva do 46%." CTA strip na dnu stranice kolekcije je ekvivalent sticky CTA-a jer je to posljednje sto korisnik vidi nakon sto je pregledao sve proizvode.

Tekst "Na stanju" u eyebrow-u iznad CTA-a ponavlja kljucnu prednost Door Expert-a (odmah dostupno, konkurencija ceka 45 dana).

---

## Zakljucak

Stranica kolekcije "Travertino" je dizajnirana kao trodijelno iskustvo: emocionalni uvod (hero + reveal), informativni srednji dio (story + galerija), i akcioni zavrsni dio (product grid + CTA). Ovaj tok prati prirodni put kupca od inspiracije do odluke, sto je konzistentno sa istrazivanjima o ponasanju kupaca premium proizvoda.

---

## Reference

- Website Conversion Strategy.md (sekcije 1, 2, 3)
- Trust Signals DoorExpert.md
- Website UX Research.md
- Visual Research for Door Expert.md
- Competitive_Landscape_UNIFIED.md
- Analiza Saya Group kolekcije (kolekcije-single.js)
- Project Instructions (poslovne cinjenice)

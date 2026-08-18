# Istrazivacka osnova: Stranica "Sve Kolekcije" (kolekcije.html)

## Pregled

Ova stranica sluzi kao centralna arhiva svih keramickih kolekcija koje Door Expert nudi. Njen cilj je da kupcu pruzi pregled kompletne ponude kolekcija na jednom mjestu, sa mogucnoscu filtriranja po brendu i pretrage, te da ga usmjeri ka pojedinacnoj stranici kolekcije gdje ce vidjeti studio prezentaciju i proizvode.

---

## Sekcija 1: Hero sa statistikama

**Dizajnerska odluka:** Centriran hero sa eyebrow tekstom, velikim italic naslovom i tri statistike (12+ kolekcija, 4 brenda, 1 zemlja porijekla).

**Istrazivacka osnova:**

Iz dokumenta "Website Conversion Strategy" (sekcija o trust signalima): "Vizuelni signali povjerenja i konkretni brojevi grade kredibilitet." Statistike na vrhu stranice (broj kolekcija, brendova, zemlja porijekla) sluze kao instant trust signal koji komunicira obim ponude i ekskluzivnost (samo spanska keramika).

Iz dokumenta "Trust Signals DoorExpert": Spansko porijeklo keramike je identificirano kao najjaci prodajni argument. Zato je "1 zemlja porijekla" istaknuta kao statistika, a eyebrow tekst eksplicitno navodi "Spanska keramika".

Iz "Competitive Landscape": Nijedan konkurent u Crnoj Gori ne komunicira kolekcije kao kurirani dizajnerski izbor. Vecina prikazuje proizvode u generickom katalogu. Pristup "dizajnerske kolekcije" pozicionira Door Expert kao premium, editorijalni brend.

---

## Sekcija 2: Sticky filter bar

**Dizajnerska odluka:** Fiksirana traka sa tabovima po brendu (Tau Ceramica, Arcana, Ribesalbes, New Tiles) i poljem za pretragu.

**Istrazivacka osnova:**

Iz "Website UX Research": "Site search je OBAVEZAN (stari sajt ga nema)." Polje za pretragu na arhivskoj stranici omogucava brzo pronalazenje kolekcije po imenu, sto je kriticno za korisnike koji vec znaju sta traze (npr. dosli su sa Instagrama gdje su vidjeli "Travertino").

Iz "UX Research for Navigation": "Filtri specifični za kategoriju" su identificirani kao kljucni zahtjev. Na stranici kolekcija, filtriranje po brendu je najprirodniji nacin segmentacije jer su brendovi vec poznati kupcima (Tau Ceramica, Arcana, itd.) i svaki ima prepoznatljiv stil.

Sticky pozicioniranje filtera osigurava da korisnik uvijek ima pristup filtriranju bez scrollanja nazad na vrh, sto je posebno vazno na mobilnim uredjajima gdje je stranica dugacka.

---

## Sekcija 3: Masonry grid sa karticama kolekcija

**Dizajnerska odluka:** Asimetricni grid sa featured karticama (2 kolone) i standardnim (1 kolona). Svaka kartica ima: atmosfersku sliku, badge kategorije, badge "Spanija", ime kolekcije u italic serif fontu, kratki tagline, i CTA link.

**Istrazivacka osnova:**

Iz "Visual Research for Door Expert": "Keramika i umivaonici trebaju JEDNAKU vizuelnu prominentnost kao vrata." Masonry grid sa velikim featured karticama daje kolekcijama vizuelnu tezinu i editorijalni osjecaj koji komunicira premium pozicioniranje.

Iz poslovnih cinjenica (project instructions): "Ceramic brands ARE highlighted" i "Spanish origin is the single biggest selling advantage and must be prominent." Zato svaka kartica ima vidljiv badge "Spanija" i ime brenda iznad naziva kolekcije.

Iz "Website Conversion Strategy": "Korisnici koji dolaze sa Instagrama na mobilnim uredjajima" trebaju brz vizuelni pregled. Kartice sa velikom slikom i minimalnim tekstom omogucavaju brzo skeniranje i odlucivanje bez citanja dugih opisa.

Asimetricni grid (featured vs standard) stvara vizuelnu hijerarhiju koja usmjerava paznju na kljucne kolekcije koje klijent zeli da promovise, bez da sve izgleda uniformno i "katalosko".

---

## Sekcija 4: CTA strip

**Dizajnerska odluka:** Tamna sekcija sa pozivom na posjetu salonu i dugmetom "Zakazite posjetu".

**Istrazivacka osnova:**

Iz "Website Conversion Strategy" (sekcija 1): Konverzijski ciljevi po prioritetu su: (1) quote cart inquiry, (2) telefonski pozivi, (3) posjete salonu. Na arhivskoj stranici kolekcija, korisnik je u fazi istrazivanja i mozda jos nije spreman za konkretnu ponudu. Zato je CTA usmjeren na posjetu salonu ("Ne mozete da se odlucite? Posjetite nas salon") jer je to prirodni sledeci korak za kupca koji pregleda kolekcije ali zeli da ih fizicki vidi i dodirne.

Iz poslovnih cinjenica: "~70% ljudi koji pozovu na kraju kupe." Dugme za zakazivanje posjete vodi ka kontakt stranici gdje je telefon prominentan, cime se indirektno povecava vjerovatnoca telefonskog kontakta.

---

## Sekcija 5: Entrance animacije (staggered)

**Dizajnerska odluka:** Kartice se pojavljuju sa blagim fade-in i translateY efektom, svaka sa malim kasnjenjem (stagger).

**Istrazivacka osnova:**

Iz "Website UX Research" (sekcija o percepciji kvaliteta): Suptilne animacije komuniciraju premium osjecaj i paznju na detalj. Stagger efekat (svaka kartica se pojavljuje 80ms nakon prethodne) stvara osjecaj "otkrivanja" sadrzaja umjesto staticnog ucitavanja, sto je konzistentno sa editorijanim pristupom cijelog sajta.

Animacije su namjerno kratke (500ms) i koriste ease-out krivulju da bi bile brze i ne-blokirajuce za korisnika koji zeli da brzo scrolluje.

---

## Zakljucak

Stranica "Sve Kolekcije" je dizajnirana kao most izmedju brand stranica (koje prodaju pricu o brendu) i pojedinacnih stranica kolekcija (koje pruzaju studio prezentaciju i proizvode). Njena uloga je navigaciona i inspirativna: pomoci kupcu da brzo pronadje kolekciju koja mu odgovara i da ga usmjeri dublje u sajt.

---

## Reference

- Website Conversion Strategy.md (sekcije 1, 2, 3)
- Trust Signals DoorExpert.md
- Website UX Research.md
- UX Research for Navigation.md
- Visual Research for Door Expert.md
- Competitive_Landscape_UNIFIED.md
- Project Instructions (poslovne cinjenice)

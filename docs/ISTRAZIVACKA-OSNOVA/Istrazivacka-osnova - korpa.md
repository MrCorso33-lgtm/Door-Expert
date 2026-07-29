# Istraživačka osnova — Korpa za ponudu

## Zašto je ova stranica izgrađena ovako — sekcija po sekcija

### 1. Pregled ponude

**Šta je izgrađeno:** Tabela ili lista koja prikazuje sve dodate proizvode sa slikama, nazivima, cijenama, poljima za izmjenu količine i dugmadima za brisanje. Uz svaki proizvod je jasno istaknuta oznaka "Odmah na stanju". Prikazan je i ukupni procijenjeni iznos.

**Istraživačka osnova:**
> "Kupci u Crnoj Gori su izuzetno osjetljivi na rokove isporuke. Informacija da je roba odmah dostupna u magacinu u Podgorici je presudan faktor za donošenje odluke o kupovini." — [Business Facts]
> "Model korpe za ponudu smanjuje anksioznost kod korisnika jer ne zahtijeva trenutno plaćanje, već otvara prostor za pregovore o cijeni i uslovima isporuke." — [Website Quote Cart Model]

**Zašto ovo rješenje:** Prikaz oznake "Odmah na stanju" direktno u korpi eliminiše glavni strah kupaca (kašnjenje radova). Transparentan prikaz proizvoda i količina omogućava korisniku da provjeri svoju narudžbu prije slanja, dok jasan ukupni iznos služi kao osnova za dalju komunikaciju i eventualne pregovore o popustu.

### 2. Forma za upit

**Šta je izgrađeno:** Jednostavna kontakt forma sa poljima za ime, email, broj telefona i opcionu poruku. Iznad forme se nalazi jasan tekst koji objašnjava da ovo nije obavezujuća kupovina. Ispod dugmeta za slanje nalazi se mikro-kopija o brzom odgovoru.

**Istraživačka osnova:**
> "Telefonski pozivi konvertuju sa stopom od oko 70%. Prikupljanje tačnog broja telefona kroz formu je kritično za prodajni tim kako bi brzo reagovali i zatvorili prodaju." — [Conversion Strategy]
> "Korisnici često napuštaju proces ako misle da moraju unijeti podatke sa kreditne kartice. Jasna komunikacija da se radi o upitu za predračun značajno povećava stopu konverzije." — [UX Research]

**Zašto ovo rješenje:** Forma je namjerno svedena na minimum kako bi se smanjilo trenje. Broj telefona je obavezno polje jer je to primarni kanal za uspješno zatvaranje prodaje. Napomena o neobavezujućem upitu otklanja strah od online plaćanja, što je ključno za crnogorsko tržište.

### 3. Korpa je prazna

**Šta je izgrađeno:** Ekran koji se prikazuje kada nema proizvoda u korpi, sa prijateljskom porukom i istaknutim CTA dugmadima koja vode ka glavnim kategorijama (Španska keramika, Bathco umivaonici).

**Istraživačka osnova:**
> "Prazne stranice bez jasnih putokaza (dead ends) su jedan od glavnih razloga za napuštanje sajta. Korisnicima se mora ponuditi jasan sljedeći korak." — [UX Research]
> "Špansko porijeklo keramike i brendovi poput Tau Ceramica i Bathco su glavni diferencijatori Door Expert-a u odnosu na konkurenciju." — [Competitive Landscape]

**Zašto ovo rješenje:** Umjesto generičke poruke "Vaša korpa je prazna", ova sekcija se koristi kao prilika za usmjeravanje korisnika ka najprofitabilnijim i najatraktivnijim kategorijama proizvoda, čime se produžava njihovo zadržavanje na sajtu.

### 4. Upit je uspješno primljen

**Šta je izgrađeno:** Stranica ili modalni prozor koji potvrđuje uspješno slanje upita, objašnjava naredne korake (slanje predračuna na email) i nudi broj telefona za hitne slučajeve.

**Istraživačka osnova:**
> "Nedostatak povratne informacije nakon slanja forme stvara frustraciju i nesigurnost kod korisnika. Jasna potvrda sa očekivanim vremenom odgovora gradi povjerenje." — [Trust Signals DoorExpert]
> "S obzirom na visoku stopu konverzije putem telefona, svaki korak u lijevku treba da nudi mogućnost direktnog poziva." — [Conversion Strategy]

**Zašto ovo rješenje:** Ova sekcija upravlja očekivanjima korisnika i smiruje eventualnu anksioznost. Pružanje broja telefona za hitne slučajeve kapitalizuje na činjenici da su neki kupci u žurbi i preferiraju trenutni odgovor, što direktno vodi ka najefikasnijem kanalu prodaje.

### 5. Posjetite nas ili zatražite ponudu (Pre-footer)

**Šta je izgrađeno:** Sekcija sa detaljnim kontakt podacima, adresom izložbenog salona u Podgorici, radnim vremenom i velikim, klikabilnim brojem telefona.

**Istraživačka osnova:**
> "Fizički izložbeni salon je ključni trust signal. Kupci keramike i sanitarija često žele da vide i opipaju proizvod prije konačne odluke." — [Trust Signals DoorExpert]
> "Konverzijski prioriteti su: 1. upit kroz korpu, 2. telefonski poziv, 3. posjeta salonu. Sajt mora jasno komunicirati sve tri opcije." — [Business Facts]

**Zašto ovo rješenje:** Ova sekcija služi kao sigurnosna mreža za korisnike koji nisu spremni da pošalju upit online. Isticanje fizičke lokacije u Podgorici i mogućnosti pregleda španske keramike uživo dodatno gradi povjerenje i podstiče offline konverzije.

## Elementi koji su svjesno izostavljeni
- **Integracija za online plaćanje (Payment Gateway):** Izostavljeno jer Door Expert posluje po modelu "Quote cart" (upit za ponudu). Tržište preferira plaćanje preko računa ili u salonu, a online plaćanje bi stvorilo nepotrebno trenje i smanjilo broj upita.
- **Polje za unos kupona/promotivnog koda:** Izostavljeno jer se popusti komuniciraju direktno kroz pregovore nakon slanja upita, posebno za veće količine. Prisustvo ovog polja bi moglo frustrirati korisnike koji nemaju kod.
- **Zahtjev za kreiranje korisničkog naloga:** Izostavljeno kako bi se maksimalno ubrzao proces slanja upita. Prisiljavanje korisnika da se registruju drastično smanjuje stopu konverzije.
- **Detaljne specifikacije proizvoda u samoj korpi:** Izostavljeno radi preglednosti. U korpi su prikazani samo osnovni podaci (slika, naziv, količina, cijena), dok su detalji dostupni na pojedinačnim stranicama proizvoda.

## Ključne pretpostavke za WordPress fazu
- **WooCommerce u "Catalog" modu:** Sistem mora biti konfigurisan tako da funkcioniše kao katalog sa opcijom zatraživanja ponude (YITH Request a Quote ili sličan plugin), a ne kao standardna e-commerce prodavnica.
- **Automatsko generisanje email obavještenja:** WordPress mora biti podešen da automatski šalje detaljan email prodajnom timu sa svim podacima iz korpe i kontakt podacima korisnika, kao i potvrdni email samom korisniku.
- **Responzivnost tabele proizvoda:** Prikaz korpe mora biti tehnički riješen tako da na mobilnim uređajima prelazi u format kartica, izbjegavajući horizontalno skrolovanje koje narušava korisničko iskustvo.
- **Click-to-call funkcionalnost:** Svi brojevi telefona na stranici moraju biti pravilno formatirani sa `tel:` linkovima kako bi omogućili direktno pozivanje sa mobilnih uređaja.
- **Brzina učitavanja:** S obzirom na to da korpa može sadržati više slika proizvoda, neophodna je optimizacija slika i keširanje kako bi se stranica učitavala brzo i bez zastoja.

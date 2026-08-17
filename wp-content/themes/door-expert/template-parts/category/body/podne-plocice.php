<?php
/**
 * Bespoke srednji deo: Podne pločice – subcat-rooms (primjena po prostoriji) + antislip-guide.
 *
 * Ovo je layout-teški, jednokratni sadržaj (ne struktuiran kao hero/faq), pa stoji kao markup.
 * Migracija na JetEngine: ovakvi bespoke delovi idu u "flexible content" ili ostaju kod.
 *
 * Slike su Unsplash placeholderi iz prototipa – zameniti WP medijom pre produkcije.
 *
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="subcat-rooms">
  <div class="subcat-rooms__inner">
    <div class="subcat-rooms__header">
      <p class="subcat-rooms__eyebrow">Vodič po primjeni</p>
      <h2 class="subcat-rooms__title">Prava pločica za svaki prostor</h2>
      <p class="subcat-rooms__subtitle">Različite prostorije zahtijevaju različite tehničke karakteristike – odaberite po namjeni.</p>
    </div>
    <div class="rooms-grid">
      <div class="room-tile-card">
        <img class="room-tile-card__img" src="https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=600&q=80" alt="Podne pločice za dnevnu sobu" loading="lazy" />
        <div class="room-tile-card__body">
          <h3 class="room-tile-card__name">Dnevna soba</h3>
          <p class="room-tile-card__desc">Veliki formati (60×60, 60×120) u mat ili poliranom finishu. Neutralne boje – bež, siva, bijela – za bezvremenski izgled koji podnosi svaki stil namještaja.</p>
          <div class="room-tile-card__specs">
            <span class="room-tile-card__spec">Format: 60×60 / 60×120</span>
            <span class="room-tile-card__spec">Klasa: R9</span>
            <span class="room-tile-card__spec">Mat ili poliran</span>
          </div>
          <p class="room-tile-card__price">Od 22 €/m²</p>
        </div>
      </div>
      <div class="room-tile-card">
        <img class="room-tile-card__img" src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=600&q=80" alt="Podne pločice za kupatilo" loading="lazy" />
        <div class="room-tile-card__body">
          <h3 class="room-tile-card__name">Kupatilo</h3>
          <p class="room-tile-card__desc">Anti-slip R10 klasa je minimum za kupatilski pod. Manji formati (30×30, 30×60) bolje prate nagib prema slivniku. R11 za tuševe i mokre zone bez kabine.</p>
          <div class="room-tile-card__specs">
            <span class="room-tile-card__spec">Format: 30×30 / 30×60</span>
            <span class="room-tile-card__spec">Klasa: R10 / R11</span>
            <span class="room-tile-card__spec">Vodootporne</span>
          </div>
          <p class="room-tile-card__price">Od 18 €/m²</p>
        </div>
      </div>
      <div class="room-tile-card">
        <img class="room-tile-card__img" src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&q=80" alt="Podne pločice za hodnik" loading="lazy" />
        <div class="room-tile-card__body">
          <h3 class="room-tile-card__name">Hodnik i ulaz</h3>
          <p class="room-tile-card__desc">Hodnik prima najveće habanje – bira se tvrda granitna keramika (PEI klasa 4-5). Tamnije boje i teksturirani finish skrivaju tragove i prašinu između čišćenja.</p>
          <div class="room-tile-card__specs">
            <span class="room-tile-card__spec">Format: 60×60 / 30×90</span>
            <span class="room-tile-card__spec">PEI klasa: 4–5</span>
            <span class="room-tile-card__spec">Visoka otpornost</span>
          </div>
          <p class="room-tile-card__price">Od 25 €/m²</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="antislip-guide">
  <div class="antislip-guide__inner">
    <div class="antislip-guide__header">
      <p class="antislip-guide__eyebrow">Tehnički vodič</p>
      <h2 class="antislip-guide__title">Koja anti-slip klasa vam treba?</h2>
      <p class="antislip-guide__subtitle">R-klasa (DIN 51130) mjeri otpornost na klizanje – viši broj znači veću hrapavost i sigurnost u mokrim uslovima.</p>
    </div>
    <div class="antislip-grid">
      <div class="antislip-card">
        <div class="antislip-card__class">R9</div>
        <div class="antislip-card__name">Standardna</div>
        <p class="antislip-card__desc">Minimalna anti-slip zaštita za suhe prostore. Glatka površina, laka za čišćenje.</p>
        <div class="antislip-card__rooms">Dnevna soba · Spavaća soba · Kancelarija</div>
      </div>
      <div class="antislip-card antislip-card--recommended">
        <div class="antislip-card__badge">Preporučeno za kupatilo</div>
        <div class="antislip-card__class">R10</div>
        <div class="antislip-card__name">Kupatilska</div>
        <p class="antislip-card__desc">Standard za kupatilski pod. Dovoljna zaštita za povremeno mokre uslove i normalan promet.</p>
        <div class="antislip-card__rooms">Kupatilo · Kuhinja · Hodnik</div>
      </div>
      <div class="antislip-card antislip-card--recommended">
        <div class="antislip-card__badge">Preporučeno za tuševe</div>
        <div class="antislip-card__class">R11</div>
        <div class="antislip-card__name">Pojačana zaštita</div>
        <p class="antislip-card__desc">Za stalno mokre zone. Obavezno za tuš kabine bez postolja, bazene i terase.</p>
        <div class="antislip-card__rooms">Tuš zona · Terasa · Bazen</div>
      </div>
      <div class="antislip-card">
        <div class="antislip-card__class">R12</div>
        <div class="antislip-card__name">Industrijska</div>
        <p class="antislip-card__desc">Maksimalna anti-slip zaštita za industrijske i komercijalne prostore sa visokim rizikom.</p>
        <div class="antislip-card__rooms">Industrijski prostori · Javne površine</div>
      </div>
    </div>
  </div>
</section>

<?php
/*
 * Product katalog (prototip cat-content: filter sidebar + toolbar + product-grid, linije 936-1290)
 * NIJE ovde – to je WC produkt listing area. Renderuje ga parts/product-grid.php (WC loop) iz
 * subcategory.php. Prazan je dok se ne dodaju proizvodi. Filter sidebar = kasniji custom feature
 * (WC atributi), po planu. Vidi razgovor: odluka static-demo vs WC-reusable katalog.
 */
?>

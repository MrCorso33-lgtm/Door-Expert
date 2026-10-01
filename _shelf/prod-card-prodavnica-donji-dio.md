# Kartica proizvoda – donji dio iz prodavnice (ispod slike)

- **Naziv:** donji dio `.prod-card` kakav je bio u prodavnici i na kategorijama: atributi
  kao čipovi, cijena sa linijom iznad, amber dugme „Dodaj u ponudu" + dugme sa okom
  (brzi pregled).
- **Vraća se u:** `wp-content/themes/door-expert/template-parts/shop/product-card.php` —
  sve od `<div class="prod-card__body">` do `</article>`.
- **CSS/JS:** CSS ispod ide u `assets/css/product-card.css` (zamjenjuje blok „DONJI DIO").
  JS nema; dodavanje u korpu radi WooCommerce preko klasa `add_to_cart_button ajax_add_to_cart`.
- **Šta predstavlja / zašto je sklonjeno:** vlasnik je odlučio da sve kartice na sajtu
  dobiju donji dio sa kartice na početnoj („Odabrani za vas"): bež pozadina, atributi
  u jednom redu teksta, obrubljeno dugme preko cijele širine. Slika (gornji dio) je
  ostala iz prodavnice — cio proizvod, bez kropovanja.
- **Datum sklanjanja:** 2026-10-01

> Napomena: markup ispod koristi `$de_attrs`, `$de_full_label`, `$de_link`, `$de_id` koje
> računa vrh `product-card.php` — ti dijelovi fajla nisu dirani, pa se vraća samo ovo.

## PHP / HTML

```php
  <div class="prod-card__body">
    <?php if ( '' !== $de_full_label ) : ?>
      <span class="prod-card__cat"><?php echo esc_html( $de_full_label ); ?></span>
    <?php endif; ?>
    <h3 class="prod-card__name"><a href="<?php echo esc_url( $de_link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
    <?php if ( ! empty( $de_attrs ) ) : ?>
      <div class="prod-card__attrs">
        <?php foreach ( $de_attrs as $de_attr ) : ?>
          <?php // Bez prelamanja reda unutar čipa: prazan prostor u markupu bi se ispisao kao razmak uz padding. ?>
          <span class="prod-card__attr"><?php if ( '' !== $de_attr['label'] ) : ?><span class="prod-card__attr-key"><?php echo esc_html( $de_attr['label'] ); ?>:</span> <?php endif; ?><?php echo esc_html( $de_attr['value'] ); ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="prod-card__price-row">
      <?php if ( $product->is_on_sale() && '' !== (string) $product->get_regular_price() && '' !== (string) $product->get_sale_price() ) : ?>
        <?php
        $de_reg  = (float) $product->get_regular_price();
        $de_sale = (float) $product->get_sale_price();
        $de_pct  = ( $de_reg > 0 ) ? (int) round( ( ( $de_reg - $de_sale ) / $de_reg ) * 100 ) : 0;
        ?>
        <span class="prod-card__price-old"><?php echo wp_kses_post( wc_price( $de_reg ) ); ?></span>
        <span class="prod-card__price"><?php echo wp_kses_post( wc_price( $de_sale ) ); ?></span>
        <?php if ( $de_pct > 0 ) : ?>
          <span class="prod-card__price-save">-<?php echo esc_html( (string) $de_pct ); ?>%</span>
        <?php endif; ?>
      <?php else : ?>
        <span class="prod-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
      <?php endif; ?>
    </div>
    <div class="prod-card__cta">
      <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" data-product_id="<?php echo esc_attr( (string) $de_id ); ?>" class="prod-card__btn-cart add_to_cart_button<?php echo $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? ' ajax_add_to_cart' : ''; ?>" rel="nofollow">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Dodaj u ponudu
      </a>
      <a href="<?php echo esc_url( $de_link ); ?>" class="prod-card__btn-view" aria-label="Pogledaj <?php echo esc_attr( $product->get_name() ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
    </div>
  </div>
```

## CSS (iz `assets/css/category.css`)

```css
/* Card body */
.prod-card__body {
  padding: var(--space-4);
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: var(--space-2);
}

.prod-card__cat {
  font-family: var(--font-ui);
  font-size: var(--text-xs);
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-accent);
}

.prod-card__name {
  font-family: var(--font-body);
  font-size: var(--text-base);
  font-weight: 600;
  color: var(--color-antracit);
  margin: 0;
  line-height: 1.3;
}

/* Naslov je link, ali vizuelno ostaje običan tekst (bez underline/visited boje). */
.prod-card__name a,
.prod-card__name a:visited {
  color: inherit;
  text-decoration: none;
}

.prod-card__name a:hover,
.prod-card__name a:focus-visible {
  color: var(--color-jantar);
}

.prod-card__attrs {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-1);
}

.prod-card__attr {
  font-family: var(--font-ui);
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  background: var(--color-alabaster);
  padding: 2px 7px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
}

/* Naziv atributa u čipu ("Materijal:"). Nosi ga težina, ne boja: čip je mali,
   pa bi dvije boje unutar 12px teksta pravile šum. */
.prod-card__attr-key {
  font-weight: 600;
}

/* Variants (dimensions) */
.prod-card__variants {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-1);
}

.prod-card__variant {
  padding: 3px 8px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  font-family: var(--font-ui);
  font-size: 11px;
  color: var(--color-antracit-mid);
  cursor: pointer;
  transition: all var(--duration-fast) var(--ease-out);
  background: none;
}

.prod-card__variant:hover,
.prod-card__variant.active {
  border-color: var(--color-jantar);
  color: var(--color-jantar);
  background: rgba(160,120,64,0.06);
}

/* Pricing */
.prod-card__price-row {
  display: flex;
  align-items: baseline;
  gap: var(--space-3);
  margin-top: auto;
  padding-top: var(--space-2);
  border-top: 1px solid var(--color-border);
}

.prod-card__price {
  font-family: var(--font-body);
  font-size: var(--text-lg);
  font-weight: 700;
  color: var(--color-antracit);
}

.prod-card__price-old {
  font-family: var(--font-ui);
  font-size: var(--text-sm);
  color: var(--color-text-muted);
  text-decoration: line-through;
}

.prod-card__price-save {
  font-family: var(--font-ui);
  font-size: var(--text-xs);
  font-weight: 600;
  color: #C0392B;
  margin-left: auto;
}

/* CTA */
.prod-card__cta {
  display: flex;
  gap: var(--space-2);
  margin-top: var(--space-2);
}

.prod-card__btn-cart {
  flex: 1;
  padding: var(--space-3) var(--space-4);
  background: var(--color-jantar);
  color: var(--color-white);
  border: none;
  border-radius: var(--radius-sm);
  font-family: var(--font-ui);
  font-size: var(--text-sm);
  font-weight: 600;
  cursor: pointer;
  transition: background var(--duration-fast) var(--ease-out),
              transform var(--duration-fast) var(--ease-out);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
}

.prod-card__btn-cart:hover {
  background: var(--color-accent-hover);
}

.prod-card__btn-cart:active {
  transform: scale(0.97);
}

.prod-card__btn-cart.added {
  background: #4CAF50;
}

.prod-card__btn-view {
  width: 42px;
  height: 42px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--duration-fast) var(--ease-out);
  text-decoration: none;
  color: var(--color-antracit-mid);
}

.prod-card__btn-view:hover {
  border-color: var(--color-jantar);
  color: var(--color-jantar);
}

.prod-card__btn-view svg {
  width: 18px;
  height: 18px;
}
```

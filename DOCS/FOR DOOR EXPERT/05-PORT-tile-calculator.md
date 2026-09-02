
# PORT 05 — Tile m² calculator and per-m² cart pricing

**Verdict: `ADAPT (light)` · Priority 4**

Door Expert already has "a basic tile m² calculator" in `assets/js/product.js`. The gap is not the
arithmetic, it is everything around it: the packaging meta model, feeding the result into the cart,
and the pricing correction that stops the cart charging a fraction of the real price.

**The pricing correction is the part you cannot skip.** If you sell tiles priced per m² but the cart
counts boxes, WooCommerce multiplies `boxes × price_per_m²` and undercharges by a factor of the box
size. Saya hit this and fixed it at `functions.php:4492-4512`.

---

## 1. What it does

**On the PDP:** room length × width, plus a waste percentage, produces area, area with waste, number
of boxes needed (rounded up), coverage, total price, tile count and package weight. "Primeni" writes
the quantity into the add-to-cart form and stashes the calculation as hidden fields.

**In the cart:** the calculation travels with the line item as cart item meta, so sales sees
"18,40 m², +10% otpad, 5 kutija" on the order instead of a bare quantity.

**At price time:** a `woocommerce_before_calculate_totals` hook rewrites the line price from
"per m²" to "per box".

## 2. Saya source

| Piece | Location |
|---|---|
| Calculator UI + arithmetic | `wp-theme/js/product-single.js:774-1001` |
| Packaging data read from `data-*` | `wp-theme/js/product-single.js:733-739` |
| Quantity stepper it drives | `wp-theme/js/product-single.js:725-772` |
| Calc meta → cart item | `wp-theme/functions.php:4468-4479` |
| **Per-m² price correction** | `wp-theme/functions.php:4492-4512` |
| Meta helpers | `wp-theme/functions.php:2731` (`saya_price_unit`), `:2813` (`saya_pkg_data`), `:2826` (`saya_pkg_kom`), `:2835` (`saya_pkg_kg`) |

Background reading in this repo: `DOCS/KALKULATOR_PLOCICA.md` and `DOCS/NOVI_KALKULATOR_PLOCICA.md`.

## 3. Dependencies and coupling

| Dependency | Notes |
|---|---|
| WooCommerce | Core hooks only: `woocommerce_add_cart_item_data`, `woocommerce_before_calculate_totals`. |
| jQuery | **None.** |
| Page builder / CF7 / Jet* | **None.** |
| Product meta | `_price_unit`, `_pkg_qty`, `_pkg_label`, `_pkg_kom`, `_pkg_kg`. Plain post meta, so JetEngine as a data layer fits Door Expert's rules exactly. |
| Locale | `toLocaleString( 'sr-RS' )` and a hardcoded `rsd` suffix. Change to `'sr-ME'` / `EUR` for Montenegro. |
| Coupling | Low. The JS reads everything from one element's `data-*` attributes; the PHP reads post meta. Nothing touches Saya's taxonomies. |

## 4. Data model to create in Door Expert

Five meta fields on the product (JetEngine meta box is fine, it is data-layer only):

| Meta key | Meaning | Example |
|---|---|---|
| `_price_unit` | Unit the price is entered in. `m²` triggers the correction; anything else leaves the price alone. | `m²` |
| `_pkg_qty` | How much one package covers, in the price unit. | `1.44` |
| `_pkg_label` | Word for one package. | `kutija` |
| `_pkg_kom` | Pieces per package. Optional. | `8` |
| `_pkg_kg` | Package weight in kg. Optional. | `24.5` |

Doors will not use any of this; leave `_price_unit` empty and every hook below no-ops.

## 5. Adapted code

### `inc/tile-calculator.php`

```php
<?php
/**
 * Kalkulator pločica: podaci pakovanja, prenos u korpu i korekcija cijene.
 *
 * @package Door_Expert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jedinica u kojoj je unijeta cijena. Prazno znači "po komadu".
 *
 * @param int $product_id ID proizvoda.
 * @return string
 */
function door_expert_price_unit( $product_id ) {
	$unit = get_post_meta( $product_id, '_price_unit', true );

	return $unit ? (string) $unit : '';
}

/**
 * Podaci o pakovanju.
 *
 * @param int $product_id ID proizvoda.
 * @return array {
 *     @type float  $qty   Koliko jedno pakovanje pokriva.
 *     @type string $label Naziv pakovanja.
 *     @type int    $kom   Komada u pakovanju, 0 ako nije poznato.
 *     @type float  $kg    Težina pakovanja, 0 ako nije poznata.
 * }
 */
function door_expert_pkg_data( $product_id ) {
	return array(
		'qty'   => (float) str_replace( ',', '.', (string) get_post_meta( $product_id, '_pkg_qty', true ) ),
		'label' => get_post_meta( $product_id, '_pkg_label', true ) ? (string) get_post_meta( $product_id, '_pkg_label', true ) : 'pakovanje',
		'kom'   => (int) get_post_meta( $product_id, '_pkg_kom', true ),
		'kg'    => (float) str_replace( ',', '.', (string) get_post_meta( $product_id, '_pkg_kg', true ) ),
	);
}

/**
 * Rezultat kalkulatora putuje uz stavku korpe, da ga prodaja vidi na narudžbi.
 */
add_filter(
	'woocommerce_add_cart_item_data',
	function ( $cart_item_data, $product_id, $variation_id ) {
		if ( empty( $_POST['de_calc_povrsina'] ) ) {
			return $cart_item_data;
		}

		$cart_item_data['door_expert_calc'] = array(
			'povrsina' => sanitize_text_field( wp_unslash( $_POST['de_calc_povrsina'] ) ),
			'otpad'    => absint( wp_unslash( $_POST['de_calc_otpad'] ?? 0 ) ),
			'kolicina' => absint( wp_unslash( $_POST['de_calc_kolicina'] ?? 0 ) ),
			'label'    => sanitize_text_field( wp_unslash( $_POST['de_calc_label'] ?? 'pakovanje' ) ),
			'kom'      => absint( wp_unslash( $_POST['de_calc_kom'] ?? 0 ) ),
		);

		return $cart_item_data;
	},
	10,
	3
);

/**
 * Prikaz kalkulatora ispod naziva stavke u korpi i na narudžbi.
 */
add_filter(
	'woocommerce_get_item_data',
	function ( $item_data, $cart_item ) {
		if ( empty( $cart_item['door_expert_calc'] ) ) {
			return $item_data;
		}

		$calc  = $cart_item['door_expert_calc'];
		$parts = array( str_replace( '.', ',', $calc['povrsina'] ) . ' m²' );

		if ( ! empty( $calc['otpad'] ) ) {
			$parts[] = '+' . $calc['otpad'] . '% otpad';
		}

		if ( ! empty( $calc['kolicina'] ) ) {
			$parts[] = $calc['kolicina'] . ' ' . $calc['label'];
		}

		$item_data[] = array(
			'key'   => __( 'Proračun', 'door-expert' ),
			'value' => implode( ' · ', $parts ),
		);

		return $item_data;
	},
	10,
	2
);

/**
 * Korekcija cijene za proizvode koji se prodaju PO m².
 *
 * Cijena se unosi po m² (npr. 24,90/m²), a količina u korpi je broj PAKOVANJA.
 * Bez korekcije korpa naplaćuje broj_kutija × cijena_po_m², dakle znatno manje
 * nego što treba, jer jedna kutija pokriva _pkg_qty m².
 *
 * Baza se čita iz _price mete, ne iz objekta korpe, da višestruki poziv hooka
 * ne bi množio cijenu više puta. Cijena po kutiji se NE zaokružuje; WooCommerce
 * zaokružuje tek ukupan iznos, pa je total tačno
 * broj_kutija × pkg_qty × cijena_po_m².
 *
 * Proizvodi koji se prodaju po komadu ili setu se ne diraju.
 */
add_action(
	'woocommerce_before_calculate_totals',
	function ( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		if ( ! $cart instanceof WC_Cart ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			$product_id = $cart_item['product_id'];

			if ( 'm²' !== door_expert_price_unit( $product_id ) ) {
				continue;
			}

			$pkg = door_expert_pkg_data( $product_id );

			if ( 0 >= $pkg['qty'] ) {
				continue;
			}

			$source_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $product_id;
			$base_m2   = (float) get_post_meta( $source_id, '_price', true );

			if ( 0 >= $base_m2 ) {
				continue;
			}

			$cart_item['data']->set_price( $base_m2 * $pkg['qty'] );
		}
	},
	20
);
```

### PDP markup contract

Everything the JS needs sits on one element, so there is no second source of truth:

```php
<?php
$door_expert_pkg  = door_expert_pkg_data( $product->get_id() );
$door_expert_unit = door_expert_price_unit( $product->get_id() );

if ( 'm²' === $door_expert_unit && 0 < $door_expert_pkg['qty'] ) :
	?>
	<section class="tile-calc"
		id="tileCalc"
		data-unit="<?php echo esc_attr( $door_expert_unit ); ?>"
		data-pkg-qty="<?php echo esc_attr( $door_expert_pkg['qty'] ); ?>"
		data-pkg-label="<?php echo esc_attr( $door_expert_pkg['label'] ); ?>"
		data-pkg-kom="<?php echo esc_attr( $door_expert_pkg['kom'] ); ?>"
		data-pkg-kg="<?php echo esc_attr( $door_expert_pkg['kg'] ); ?>"
		data-price="<?php echo esc_attr( $product->get_price() ); ?>">

		<button type="button" class="tile-calc__toggle" id="tileCalcToggle" aria-expanded="false">
			<?php esc_html_e( 'Izračunaj koliko ti treba', 'door-expert' ); ?>
		</button>

		<div class="tile-calc__body" id="tileCalcBody" hidden>
			<label for="calcDuzina"><?php esc_html_e( 'Dužina, m', 'door-expert' ); ?></label>
			<input type="number" id="calcDuzina" min="0" step="0.01" inputmode="decimal">

			<label for="calcSirina"><?php esc_html_e( 'Širina, m', 'door-expert' ); ?></label>
			<input type="number" id="calcSirina" min="0" step="0.01" inputmode="decimal">

			<label for="calcOtpad"><?php esc_html_e( 'Otpad', 'door-expert' ); ?> <span id="calcOtpadVal">10%</span></label>
			<input type="range" id="calcOtpad" min="0" max="25" step="1" value="10">

			<div class="tile-calc__results" id="calcResults"></div>

			<button type="button" class="tile-calc__apply" id="tileCalcApply">
				<?php esc_html_e( 'Primijeni', 'door-expert' ); ?>
				<span id="tileCalcApplyCount">0</span>
			</button>
		</div>
	</section>
<?php endif; ?>
```

### `assets/js/tile-calculator.js`

```js
/**
 * Kalkulator pločica: površina prostorije → broj pakovanja i procjena cijene.
 *
 * Sve ulazne podatke čita sa #tileCalc data atributa, pa nema drugog izvora
 * istine osim onoga što je PHP ispisao.
 */
( function () {
	'use strict';

	var root = document.getElementById( 'tileCalc' );
	if ( ! root ) {
		return;
	}

	var pkgQty   = parseFloat( root.dataset.pkgQty ) || 0;
	var pkgLabel = root.dataset.pkgLabel || 'pakovanje';
	var pkgKom   = parseInt( root.dataset.pkgKom, 10 ) || 0;
	var pkgKg    = parseFloat( root.dataset.pkgKg ) || 0;
	var price    = parseFloat( root.dataset.price ) || 0;

	if ( ! pkgQty ) {
		return;
	}

	var dEl      = document.getElementById( 'calcDuzina' );
	var sEl      = document.getElementById( 'calcSirina' );
	var oEl      = document.getElementById( 'calcOtpad' );
	var oValEl   = document.getElementById( 'calcOtpadVal' );
	var resEl    = document.getElementById( 'calcResults' );
	var applyEl  = document.getElementById( 'tileCalcApply' );
	var countEl  = document.getElementById( 'tileCalcApplyCount' );
	var qtyInput = document.getElementById( 'qtyInput' );
	var wcQty    = document.getElementById( 'wcQty' );

	var result = null;

	function num( value, decimals ) {
		return value.toLocaleString( 'sr-ME', {
			minimumFractionDigits: undefined === decimals ? 2 : decimals,
			maximumFractionDigits: undefined === decimals ? 2 : decimals
		} );
	}

	/**
	 * Množina naziva pakovanja. Prilagoditi ako se uvede novi naziv.
	 */
	function pkgWord( n ) {
		if ( 'kutija' === pkgLabel ) {
			return 1 === n ? 'kutija' : ( 5 > n ? 'kutije' : 'kutija' );
		}
		if ( 'set' === pkgLabel ) {
			return 1 === n ? 'set' : ( 5 > n ? 'seta' : 'setova' );
		}
		return pkgLabel;
	}

	function calculate() {
		var d = parseFloat( dEl.value ) || 0;
		var s = parseFloat( sEl.value ) || 0;
		var o = parseInt( oEl ? oEl.value : 10, 10 ) || 0;

		if ( oValEl ) {
			oValEl.textContent = o + '%';
		}

		if ( 0 >= d || 0 >= s ) {
			resEl.innerHTML = '';
			if ( countEl ) {
				countEl.textContent = '0';
			}
			result = null;
			return;
		}

		var area      = d * s;
		var withWaste = area * ( 1 + o / 100 );
		var packages  = Math.ceil( withWaste / pkgQty );
		var covered   = packages * pkgQty;
		var total     = 0 < price ? packages * pkgQty * price : 0;

		var html = ''
			+ '<div class="cr"><span class="cr-lbl">Površina</span>'
			+ '<strong class="cr-val">' + num( area ) + '</strong><span class="cr-sub">m²</span></div>'
			+ '<div class="cr"><span class="cr-lbl">Sa otpadom</span>'
			+ '<strong class="cr-val">' + num( withWaste ) + '</strong><span class="cr-sub">m²</span></div>'
			+ '<div class="cr cr--highlight"><span class="cr-lbl">' + pkgWord( packages ) + '</span>'
			+ '<strong class="cr-val">' + packages + '</strong><span class="cr-sub">' + num( covered ) + ' m²</span></div>';

		if ( pkgKom ) {
			html += '<div class="cr"><span class="cr-lbl">Broj pločica</span>'
				+ '<strong class="cr-val">' + ( packages * pkgKom ) + '</strong><span class="cr-sub">kom</span></div>';
		}

		if ( pkgKg ) {
			html += '<div class="cr"><span class="cr-lbl">Težina</span>'
				+ '<strong class="cr-val">' + num( packages * pkgKg ) + '</strong><span class="cr-sub">kg</span></div>';
		}

		if ( 0 < price ) {
			html += '<div class="cr cr--highlight"><span class="cr-lbl">Ukupno</span>'
				+ '<strong class="cr-val">' + num( total ) + '</strong><span class="cr-sub">EUR</span></div>';
		}

		resEl.innerHTML = html;

		if ( countEl ) {
			countEl.textContent = packages;
		}

		result = {
			povrsina: area.toFixed( 2 ),
			otpad: o,
			kolicina: packages,
			label: pkgLabel,
			kom: pkgKom ? packages * pkgKom : 0
		};
	}

	function setHidden( form, name, value ) {
		var input = form.querySelector( 'input[name="' + name + '"]' );

		if ( ! input ) {
			input = document.createElement( 'input' );
			input.type = 'hidden';
			input.name = name;
			form.appendChild( input );
		}

		input.value = value;
	}

	if ( applyEl ) {
		applyEl.addEventListener( 'click', function () {
			if ( ! result ) {
				return;
			}

			if ( qtyInput ) {
				qtyInput.value = result.kolicina;
			}
			if ( wcQty ) {
				wcQty.value = result.kolicina;
			}

			var form = document.querySelector( '.pdp-cart-form' );

			if ( form ) {
				setHidden( form, 'de_calc_povrsina', result.povrsina );
				setHidden( form, 'de_calc_otpad', result.otpad );
				setHidden( form, 'de_calc_kolicina', result.kolicina );
				setHidden( form, 'de_calc_label', result.label );
				setHidden( form, 'de_calc_kom', result.kom );
			}

			var body   = document.getElementById( 'tileCalcBody' );
			var toggle = document.getElementById( 'tileCalcToggle' );

			if ( body ) {
				body.hidden = true;
			}
			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	[ dEl, sEl, oEl ].forEach( function ( el ) {
		if ( el ) {
			el.addEventListener( 'input', calculate );
		}
	} );

	var toggleEl = document.getElementById( 'tileCalcToggle' );
	var bodyEl   = document.getElementById( 'tileCalcBody' );

	if ( toggleEl && bodyEl ) {
		toggleEl.addEventListener( 'click', function () {
			var isOpen = ! bodyEl.hidden;
			bodyEl.hidden = isOpen;
			toggleEl.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
		} );
	}
}() );
```

**Note on the variable-product path.** The hidden inputs above only reach the server if the product
submits a real form. When add-to-cart goes through the AJAX handler in `03-PORT-variations.md`,
append the same `de_calc_*` keys to the request body; `woocommerce_add_cart_item_data` picks them up
from `$_POST` either way.

## 7. Verify after dropping it in

- A product with `_price_unit = m²`, `_pkg_qty = 1.44`, price `24.90`: one box in the cart must cost
  **35,86**, not 24,90. Getting 24,90 means the correction hook is not firing.
- Two boxes must be exactly double. Refresh the cart page three times: the price must not creep
  upward — that is the double-multiplication bug the `_price` read protects against.
- Enter 4 × 3 m with 10% waste on a 1.44 m² box: 12 m² → 13,2 m² → **10 boxes**.
- "Primijeni" writes the box count into the quantity field.
- The cart line shows "Proračun: 12,00 m² · +10% otpad · 10 kutija".
- A door product with empty `_price_unit`: no calculator renders and its cart price is untouched.

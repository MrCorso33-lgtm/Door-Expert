
# PORT 03 — Variable products: selector UI + custom add-to-cart

**Verdict: `ADAPT (heavy)` for the full UI, `ADAPT (light)` for the two pieces that matter · Priority 2**

Door Expert's PDP is currently v1: simple products, variants shown read-only from attributes with no
live price. This is the upgrade path. Doors have sizes and finishes, tiles have formats and colours,
so a real variation selector is not optional for long.

The honest split: Saya's variation module is ~950 lines and about half of it is Saya-specific
(colour-name dropdown, slip-resistance map, collection card syncing, per-variation gallery rebuild).
**Do not port the module.** Port the two pieces that are genuinely hard and genuinely general:

1. the **matching engine** (about 90 lines of JS), and
2. the **server-side add-to-cart handler** that makes a custom pill UI work at all with WooCommerce.

Everything else is markup and styling you will write against your own design anyway.

---

## 1. What it does

The PDP renders one row per variation attribute, each row a set of pill buttons (or swatches). As the
user picks values:

- impossible combinations grey out immediately,
- if only one option remains in another row, it is auto-selected,
- picking a value that contradicts an earlier pick clears only the contradicting pick, not everything,
- price, stock, SKU and image update live,
- the CTA switches between "add to inquiry" and "request price" depending on whether the matched
  variation has a price.

The subtlety that makes it correct: WooCommerce stores an **empty string as a wildcard** for "Any
<attribute>" variations. Naive matching treats `''` as a value and never matches. Both functions below
handle it.

## 2. Saya source

| Piece | Location |
|---|---|
| Hidden-attribute list (single source of truth) | `wp-theme/functions.php:5235-5256` |
| Custom add-to-cart AJAX | `wp-theme/functions.php:5273-5347` |
| Variation JSON emitted to the page | `wp-theme/woocommerce/single-product.php:517` (`get_available_variations()`), `:903` (the `<script type="application/json">` tag) |
| Attribute rows markup | `wp-theme/woocommerce/single-product.php:682-790` |
| Matching engine | `wp-theme/js/product-single.js:1291-1324` |
| Auto-select single option | `wp-theme/js/product-single.js:1329-1369` |
| Grey out impossible options | `wp-theme/js/product-single.js:1372-1420` |
| Smart cascade on click | `wp-theme/js/product-single.js:1145-1176` |
| State / price / CTA update | `wp-theme/js/product-single.js:1421-1623` |

Background reading in this repo: `DOCS/ADD_TO_CART_VARIJACIJE.md` and
`DOCS/BITNE FUNKCIONALNOSTI/WC_PROTIVKLIZNOST_VARIATION.md`.

## 3. Dependencies and coupling

| Dependency | Notes |
|---|---|
| WooCommerce | Core. The JSON payload is plain `WC_Product_Variable::get_available_variations()`, so no custom serialisation to port. |
| jQuery | **None.** |
| Page builder / CF7 / Jet* | **None.** |
| Saya-specific | `pa_naziv-boje` primary attribute, `pa_boja` derivation, `saya_protivkliznost` / `saya_pei_klasa` meta, `syncKolekcijaCards()`, per-variation gallery rebuild. **All dropped in the port.** |

### Why the custom add-to-cart handler exists

This is the part worth reading twice, because it is a real WooCommerce trap and the comment at
`functions.php:5258-5272` documents it well:

- Posting `product_id = parent` makes WooCommerce try to add a variable parent, which is not purchasable.
- Posting `product_id = variation` makes WooCommerce load `get_variation_attributes()` itself, and for
  an "Any" attribute that comes back empty, so it **throws** `"<Attribute> is a required field"`
  before any validation filter runs. You cannot catch it with a filter.

The fix is to build the complete `$variation` array server-side and resolve every "Any" attribute to a
real value — either the posted value or the parent's first term. That is what the handler below does.

## 4. Data-model mapping

| Saya | Door Expert |
|---|---|
| `pa_boja`, `pa_naziv-boje` | `pa_boja` — keep, drop the second one unless you introduce named colours |
| `pa_dimenzije-plocice` (note the **e**) | `pa_dimenzije-plocica` (note the **a**) — rename on the way over |
| — | `pa_dimenzije-vrata` — new, behaves exactly like any other variation attribute |
| `pa_zavrsna-obrada`, `pa_protivkliznost`, `pa_pei-klasa-habanja` | probably not needed for doors; keep the hidden-attribute mechanism, empty the list |

## 5. Adapted code

### `inc/product-variations.php`

```php
<?php
/**
 * Varijabilni proizvodi, atributi koji se ne nude kao izbor i custom add-to-cart.
 *
 * @package Door_Expert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Varijacijski atributi koji se NE prikazuju kao izbor na stranici proizvoda.
 *
 * Ostaju u varijacijama i u DOM-u, samo se ne nude korisniku. Add-to-cart ne
 * zavisi od ove liste, handler radi iz podataka same varijacije, pa je ovo
 * čisto pitanje prikaza.
 *
 * @param int $product_id Opciono, za pravila po kategoriji.
 * @return array Lista slugova.
 */
function door_expert_hidden_pdp_attributes( $product_id = 0 ) {
	$hidden = array();

	return apply_filters( 'door_expert_hidden_pdp_attributes', $hidden, $product_id );
}

add_action( 'wp_ajax_door_expert_add_to_cart', 'door_expert_add_to_cart_ajax' );
add_action( 'wp_ajax_nopriv_door_expert_add_to_cart', 'door_expert_add_to_cart_ajax' );

/**
 * Dodavanje varijacije u korpu iz custom pill UI-ja.
 *
 * WooCommerce-ov ?wc-ajax=add_to_cart ovdje ne radi:
 *   - product_id = roditelj  → WC pokušava da doda varijabilni proizvod, nije kupljiv
 *   - product_id = varijacija → WC sam učita get_variation_attributes(), a za "Any"
 *     atribut to je prazan string → baca "X is a required field" PRIJE validacionog
 *     filtera, pa se greška ne može uhvatiti.
 *
 * Rješenje: server-side gradimo kompletan $variation niz i "Any" atribute
 * razrješavamo na stvarnu vrijednost.
 */
function door_expert_add_to_cart_ajax() {
	if ( ! check_ajax_referer( 'door_expert_nonce', 'nonce', false ) ) {
		wp_send_json(
			array(
				'error'   => true,
				'message' => __( 'Sesija je istekla. Osvježite stranicu i pokušajte ponovo.', 'door-expert' ),
			)
		);
	}

	$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;

	if ( 0 >= $quantity ) {
		$quantity = 1;
	}

	$variation_obj = $variation_id ? wc_get_product( $variation_id ) : null;

	if ( ! $variation_obj || ! $variation_obj->is_type( 'variation' ) ) {
		wp_send_json(
			array(
				'error'   => true,
				'message' => __( 'Nevažeća varijacija.', 'door-expert' ),
			)
		);
	}

	$parent_id = $variation_obj->get_parent_id();
	$parent    = wc_get_product( $parent_id );

	if ( ! $parent ) {
		wp_send_json(
			array(
				'error'   => true,
				'message' => __( 'Proizvod nije pronađen.', 'door-expert' ),
			)
		);
	}

	// Na admin-ajax.php je is_admin() true, pa korpa možda nije učitana.
	if ( ! WC()->cart && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}

	// Sačuvani slugovi varijacije; prazan string znači "bilo koja vrijednost".
	$stored    = wc_get_product_variation_attributes( $variation_id );
	$variation = array();

	foreach ( $parent->get_attributes() as $attribute ) {
		if ( ! $attribute->get_variation() ) {
			continue;
		}

		$tax_key = 'attribute_' . sanitize_title( $attribute->get_name() );
		$value   = $stored[ $tax_key ] ?? '';

		if ( '' === $value ) {
			if ( ! empty( $_POST[ $tax_key ] ) ) {
				$value = wc_clean( wp_unslash( $_POST[ $tax_key ] ) );
			} else {
				$options = $attribute->get_options();

				if ( ! empty( $options ) ) {
					if ( $attribute->is_taxonomy() ) {
						$term  = get_term( (int) $options[0] );
						$value = ( $term && ! is_wp_error( $term ) ) ? $term->slug : '';
					} else {
						$value = $options[0];
					}
				}
			}
		}

		$variation[ $tax_key ] = $value;
	}

	$passed = WC()->cart->add_to_cart( $parent_id, $quantity, $variation_id, $variation );

	if ( false === $passed ) {
		$errors = wc_get_notices( 'error' );
		wc_clear_notices();

		$message = '';
		foreach ( $errors as $notice ) {
			$message .= ( is_array( $notice ) ? ( $notice['notice'] ?? '' ) : $notice ) . ' ';
		}

		wp_send_json(
			array(
				'error'   => true,
				'message' => trim( $message ) ? trim( $message ) : __( 'Greška pri dodavanju u korpu.', 'door-expert' ),
			)
		);
	}

	wp_send_json(
		array(
			'error'      => false,
			'cart_count' => WC()->cart->get_cart_contents_count(),
		)
	);
}
```

### Emitting the variation data — in `template-parts/product/single.php`

```php
<?php if ( $product->is_type( 'variable' ) ) : ?>
	<script id="doorExpertVariations" type="application/json">
		<?php echo wp_json_encode( $product->get_available_variations() ); ?>
	</script>
<?php endif; ?>
```

`get_available_variations()` already returns `attributes`, `display_price`, `price_html`,
`is_in_stock`, `sku`, `variation_id` and `image`, so there is nothing custom to serialise.

### Row markup contract

```php
<div class="variation-row<?php echo $is_hidden ? ' variation-row--hidden' : ''; ?>"
	data-attr-key="attribute_<?php echo esc_attr( $taxonomy ); ?>">
	<span class="variation-row__label"><?php echo esc_html( wc_attribute_label( $taxonomy ) ); ?></span>
	<div class="variation-options" role="group" aria-label="<?php echo esc_attr( wc_attribute_label( $taxonomy ) ); ?>">
		<?php foreach ( $terms as $term ) : ?>
			<button type="button"
				class="variation-opt"
				data-attr-key="attribute_<?php echo esc_attr( $taxonomy ); ?>"
				data-value="<?php echo esc_attr( $term->slug ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</button>
		<?php endforeach; ?>
	</div>
</div>
```

### `assets/js/variations.js` — the matching engine

This is the part that is genuinely worth copying. It is written against the markup contract above and
carries no Door Expert or Saya specifics.

```js
/**
 * Varijabilni proizvod: izbor atributa, dostupnost, živa cijena.
 *
 * WooCommerce čuva prazan string kao "bilo koja vrijednost" (Any). Zato se
 * svugdje ispod prazan string tretira kao džoker, a ne kao vrijednost.
 */
( function () {
	'use strict';

	var dataEl = document.getElementById( 'doorExpertVariations' );
	if ( ! dataEl ) {
		return;
	}

	var variations = JSON.parse( dataEl.textContent || dataEl.innerHTML );
	var rows       = document.querySelectorAll( '.variation-row[data-attr-key]' );
	var selected   = {};
	var autoPicked = new Set();

	var priceEl  = document.getElementById( 'variationPrice' );
	var skuEl    = document.getElementById( 'variationSku' );
	var stockEl  = document.getElementById( 'variationStock' );
	var ctaEl    = document.getElementById( 'variationCta' );
	var imageEl  = document.getElementById( 'variationImage' );

	function visibleRows() {
		return Array.prototype.filter.call( rows, function ( row ) {
			return ! row.classList.contains( 'variation-row--hidden' );
		} );
	}

	/**
	 * Prva varijacija koja odgovara zadatim atributima, ili null.
	 */
	function findVariation( attrs ) {
		for ( var i = 0; i < variations.length; i++ ) {
			var v     = variations[ i ];
			var match = true;

			for ( var key in attrs ) {
				if ( ! attrs.hasOwnProperty( key ) ) {
					continue;
				}
				if ( '' !== v.attributes[ key ] && v.attributes[ key ] !== attrs[ key ] ) {
					match = false;
					break;
				}
			}

			if ( match ) {
				return v;
			}
		}

		return null;
	}

	/**
	 * Da li djelimičan izbor uopšte vodi ka nekoj varijaciji.
	 */
	function isComboAvailable( testAttrs ) {
		return null !== findVariation( testAttrs );
	}

	/**
	 * Sivi opcije koje se ne mogu kombinovati sa trenutnim izborom.
	 */
	function updateAvailability() {
		visibleRows().forEach( function ( row ) {
			var rowKey = row.dataset.attrKey;

			row.querySelectorAll( '.variation-opt' ).forEach( function ( btn ) {
				var test = {};

				for ( var k in selected ) {
					if ( ! selected.hasOwnProperty( k ) || k === rowKey ) {
						continue;
					}
					var kRow = document.querySelector( '.variation-row[data-attr-key="' + k + '"]' );
					if ( ! kRow || kRow.classList.contains( 'variation-row--hidden' ) ) {
						continue;
					}
					test[ k ] = selected[ k ];
				}

				test[ rowKey ] = btn.dataset.value;
				btn.classList.toggle( 'is-unavailable', ! isComboAvailable( test ) );
			} );
		} );
	}

	/**
	 * Ako je u nekom drugom redu ostala samo jedna moguća opcija, izaberi je.
	 * Vraća true ako je nešto izabrano, da pozivalac zna da osvježi stanje.
	 */
	function autoSelectSingle( changedKey ) {
		var changed = false;

		visibleRows().forEach( function ( row ) {
			var rowKey = row.dataset.attrKey;

			if ( rowKey === changedKey || selected[ rowKey ] ) {
				return;
			}

			var opts = Array.prototype.slice.call( row.querySelectorAll( '.variation-opt' ) );
			var free = opts.filter( function ( b ) {
				return ! b.classList.contains( 'is-unavailable' );
			} );

			if ( 1 !== free.length ) {
				return;
			}

			/*
			 * Ako nijedna odgovarajuća varijacija ne precizira ovaj atribut
			 * (sve imaju džoker), atribut je nebitan za trenutni izbor pa se
			 * ne bira automatski.
			 */
			var specific = variations.some( function ( v ) {
				for ( var k in selected ) {
					if ( ! selected.hasOwnProperty( k ) || k === rowKey || autoPicked.has( k ) ) {
						continue;
					}
					if ( '' !== v.attributes[ k ] && v.attributes[ k ] !== selected[ k ] ) {
						return false;
					}
				}
				return '' !== v.attributes[ rowKey ];
			} );

			if ( ! specific ) {
				return;
			}

			opts.forEach( function ( b ) {
				b.classList.remove( 'active' );
			} );
			free[ 0 ].classList.add( 'active' );
			selected[ rowKey ] = free[ 0 ].dataset.value;
			autoPicked.add( rowKey );
			changed = true;
		} );

		return changed;
	}

	function money( variation ) {
		return variation.price_html || '';
	}

	function updateState() {
		updateAvailability();

		var complete = visibleRows().every( function ( row ) {
			return !! selected[ row.dataset.attrKey ];
		} );

		var match = complete ? findVariation( selected ) : null;

		if ( priceEl ) {
			priceEl.innerHTML = match ? money( match ) : '';
		}
		if ( skuEl ) {
			skuEl.textContent = match && match.sku ? match.sku : '';
		}
		if ( stockEl ) {
			stockEl.textContent = match
				? ( match.is_in_stock ? 'Na stanju' : 'Na upit' )
				: '';
		}
		if ( imageEl && match && match.image && match.image.src ) {
			imageEl.src = match.image.src;
			if ( match.image.srcset ) {
				imageEl.srcset = match.image.srcset;
			}
		}
		if ( ctaEl ) {
			ctaEl.disabled = ! match;
			ctaEl.dataset.variationId = match ? match.variation_id : '';
			ctaEl.textContent = match && 0 < parseFloat( match.display_price )
				? 'Dodaj u ponudu'
				: 'Zatraži cijenu';
		}
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.variation-opt' );
		if ( ! btn ) {
			return;
		}

		var key = btn.dataset.attrKey;
		var val = btn.dataset.value;
		var row = document.querySelector( '.variation-row[data-attr-key="' + key + '"]' );

		if ( row ) {
			row.querySelectorAll( '.variation-opt' ).forEach( function ( b ) {
				b.classList.remove( 'active' );
			} );
		}

		if ( selected[ key ] === val ) {
			delete selected[ key ];
			autoPicked.delete( key );
		} else {
			/*
			 * Zadrži svaki drugi izbor koji je i dalje moguć uz novi, a očisti
			 * samo one koji bi napravili nemoguću kombinaciju.
			 */
			visibleRows().forEach( function ( other ) {
				var otherKey = other.dataset.attrKey;

				if ( otherKey === key || ! selected[ otherKey ] ) {
					return;
				}

				var test = {};
				test[ key ]      = val;
				test[ otherKey ] = selected[ otherKey ];

				if ( ! isComboAvailable( test ) ) {
					delete selected[ otherKey ];
					autoPicked.delete( otherKey );
					other.querySelectorAll( '.variation-opt' ).forEach( function ( b ) {
						b.classList.remove( 'active' );
					} );
				}
			} );

			autoPicked.delete( key );
			selected[ key ] = val;
			btn.classList.add( 'active' );
		}

		updateState();

		if ( autoSelectSingle( key ) ) {
			updateState();
		}
	} );

	updateState();
}() );
```

### Add to cart from the selector

```js
ctaEl.addEventListener( 'click', function () {
	var body = new URLSearchParams( {
		action: 'door_expert_add_to_cart',
		nonce: doorExpert.nonce,
		variation_id: ctaEl.dataset.variationId,
		quantity: document.getElementById( 'qtyInput' ).value
	} );

	fetch( doorExpert.ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: body.toString()
	} )
		.then( function ( res ) {
			return res.json();
		} )
		.then( function ( res ) {
			if ( res.error ) {
				window.alert( res.message );
				return;
			}
			document.querySelectorAll( '.cart-badge' ).forEach( function ( el ) {
				el.textContent = res.cart_count;
				el.style.display = '';
			} );
		} );
} );
```

## 6. What was deliberately left behind

| Saya feature | Why it is not here |
|---|---|
| Colour-name dropdown (`naziv-dropdown`) | A second attribute layered on top of `pa_boja`; Door Expert has no equivalent. |
| Per-variation gallery rebuild (`product-single.js:130-186`) | Tightly bound to Saya's gallery DOM. If you want it later, the hook is `updateState()` — swap `imageEl.src` for a full rebuild. |
| Slip-resistance / PEI maps | Tile-specific technical attributes shown as specs, not choices. |
| `syncKolekcijaCards()` (`:1946`) | Syncs colour choice into collection cards elsewhere on the page. Saya-only feature. |
| Confirm strip (`applyConfirmStrip`, `:1624`) | Mobile summary bar. Nice, but pure UI you will design yourself. |

## 7. Verify after dropping it in

- A variable product with two attributes: picking one greys out impossible values in the other.
- A product where one attribute has a single valid option after the first pick: that option must
  auto-select.
- A product with an "Any <attribute>" variation: it must still add to cart, with no
  "<Attribute> is a required field" error. **This is the case that breaks with stock WooCommerce
  AJAX and the reason the custom handler exists.**
- Clicking an already-selected pill deselects it and re-enables everything.
- Price, SKU and stock text change on every complete selection.
- A variation priced at 0 shows "Zatraži cijenu" and still adds to the cart.



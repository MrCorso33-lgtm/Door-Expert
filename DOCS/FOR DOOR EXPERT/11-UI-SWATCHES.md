
# UI 11 — Colour and texture swatches

**Verdict: `ADAPT (light)` · The single most transferable UI pattern in this repo**

A tile is a texture, not a colour. A door is a wood grain, not a colour. Printing a flat hex circle
for "Rovere Naturale" tells the customer nothing. Saya solves this with a three-tier swatch that
falls back gracefully, and the top tier is **the product photo itself cropped into a circle**.

This is the pattern you asked about, and it is the one worth copying verbatim.

---

## 1. How it works

Three tiers, decided per option at render time:

| Tier | When | What renders |
|---|---|---|
| **1. Image swatch** | The variation for this option has its own image | A 36px circle filled with that variation's photo, `object-fit: cover`. For tiles this is the actual texture; for doors it would be the actual veneer. |
| **2. Hex swatch** | No variation image, but the term has a colour | A 36px circle filled from `color_code` term meta, falling back to the term description. |
| **3. Deterministic fallback** | Neither of the above | `'#' . substr( md5( $option ), 0, 6 )`. Not pretty, but **stable**: the same name always yields the same colour, so the UI never shows a grey blank and never flickers between page loads. |
| **4. Plain pill** | The attribute is not a colour attribute at all | Text button. |

The fallback chain is the part that makes this robust in production. Clients forget to set
`color_code`. With tier 3 the page still looks deliberate.

## 2. Saya source

| Piece | Location |
|---|---|
| Image map built from variations | `wp-theme/woocommerce/single-product.php:535-546` |
| Swatch rendering, all three tiers | `wp-theme/woocommerce/single-product.php:740-780` |
| Hex swatch CSS | `wp-theme/css/product-single.css:1408-1445` |
| Image swatch CSS | `wp-theme/css/product-single.css:1446-1478` |
| Unavailable state (diagonal hatch) | `wp-theme/css/product-single.css:1219-1240` |
| Site-wide colour map, slug → hex | `wp-theme/functions.php:2553-2577` (`saya_boja_color_map()`) |
| Same pattern in collection filters | `wp-theme/js/kolekcije-single.js` (colour dropdown, image-or-hex dot) |

## 3. Dependencies

None beyond WooCommerce. No jQuery, no library, no build step. The image map is derived from
`get_available_variations()`, which you are already calling for the variation selector in
`03-PORT-variations.md`.

## 4. Adapted code

### Building the image map

Put this next to where you already call `get_available_variations()`, so it costs nothing extra.

```php
<?php
/**
 * Mapa slika po opciji atributa: $images['attribute_pa_boja']['hrast'] => URL.
 *
 * Gradi se iz varijacija, pa uzorak pokazuje stvarnu teksturu proizvoda
 * umjesto ravne boje. Za pločice je to sam dezen, za vrata furnir.
 *
 * @param array $available_variations Rezultat get_available_variations().
 * @return array
 */
function door_expert_variation_swatch_images( $available_variations ) {
	$images = array();

	foreach ( $available_variations as $variation ) {
		foreach ( $variation['attributes'] as $key => $value ) {
			if ( ! $value ) {
				continue;
			}

			$image_id  = ! empty( $variation['image_id'] ) ? (int) $variation['image_id'] : 0;
			$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';

			if ( ! $image_url ) {
				$image_url = $variation['image']['src'] ?? '';
			}

			if ( $image_url ) {
				$images[ $key ][ $value ] = $image_url;
			}
		}
	}

	return $images;
}
```

### Resolving a colour to a hex value

```php
<?php
/**
 * Boja termina kao hex.
 *
 * Redoslijed: color_code meta → opis termina → determinisan fallback iz naziva.
 * Fallback nije lijep, ali je stabilan: isti naziv uvijek daje istu boju, pa
 * uzorak nikad nije prazan i ne mijenja se izmedju učitavanja.
 *
 * @param WP_Term|null $term Termin atributa.
 * @param string       $name Naziv opcije, za fallback.
 * @return string Hex u obliku #rrggbb.
 */
function door_expert_swatch_hex( $term, $name ) {
	$hex = '';

	if ( $term && ! is_wp_error( $term ) ) {
		$hex = (string) get_term_meta( $term->term_id, 'color_code', true );

		if ( ! $hex && $term->description ) {
			$hex = trim( $term->description );
		}
	}

	if ( ! preg_match( '/^#[0-9a-fA-F]{3,6}$/', $hex ) ) {
		$hex = '#' . substr( md5( $name ), 0, 6 );
	}

	return $hex;
}
```

### Rendering one option

```php
<?php
/**
 * Jedna opcija atributa. Bira uzorak sa slikom, uzorak sa bojom ili obično dugme.
 *
 * @param string $attr_key   Ključ atributa, npr. attribute_pa_boja.
 * @param string $taxonomy   Taksonomija, npr. pa_boja.
 * @param string $option     Slug ili naziv opcije.
 * @param bool   $is_colour  Da li je atribut vizuelni.
 * @param array  $images     Mapa iz door_expert_variation_swatch_images().
 */
function door_expert_render_variation_option( $attr_key, $taxonomy, $option, $is_colour, $images ) {
	$common = sprintf(
		'data-attr-key="%s" data-value="%s" aria-label="%s" title="%s"',
		esc_attr( $attr_key ),
		esc_attr( $option ),
		esc_attr( $option ),
		esc_attr( $option )
	);

	if ( ! $is_colour ) {
		printf(
			'<button type="button" class="variation-opt" %s>%s</button>',
			$common, // phpcs:ignore WordPress.Security.EscapeOutput -- već escapovano iznad.
			esc_html( $option )
		);
		return;
	}

	$image_url = $images[ $attr_key ][ $option ] ?? '';

	if ( $image_url ) {
		printf(
			'<button type="button" class="variation-opt variation-opt--img-swatch" %s><img src="%s" alt="%s" draggable="false"></button>',
			$common, // phpcs:ignore WordPress.Security.EscapeOutput -- već escapovano iznad.
			esc_url( $image_url ),
			esc_attr( $option )
		);
		return;
	}

	$term = get_term_by( 'name', $option, $taxonomy );
	$hex  = door_expert_swatch_hex( $term, $option );

	printf(
		'<button type="button" class="variation-opt variation-opt--swatch" %s style="--swatch-color:%s"></button>',
		$common, // phpcs:ignore WordPress.Security.EscapeOutput -- već escapovano iznad.
		esc_attr( $hex )
	);
}
```

### Site-wide colour map

Useful on the archive and brand pages where you have a term slug but no variation image. Adapted to
Montenegrin spellings, and worth extending as the catalogue grows.

```php
<?php
/**
 * Slug boje → hex. Jedino mjesto, koristi se na arhivi i stranici brenda.
 *
 * @return array
 */
function door_expert_colour_map() {
	return array(
		'bijela'      => '#FFFFFF',
		'bez'         => '#F5F0E8',
		'siva'        => '#B0B0B0',
		'crna'        => '#1A1A1A',
		'braon'       => '#8B6347',
		'hrast'       => '#C19A6B',
		'orah'        => '#6B4423',
		'trešnja'     => '#8B3A2E',
		'plava'       => '#4682B4',
		'tirkiz'      => '#009688',
		'zelena'      => '#3A9E3A',
		'crvena'      => '#DC143C',
		'žuta'        => '#FFD700',
		'narandžasta' => '#FF8C00',
		'roze'        => '#FFB6C1',
		'ljubičasta'  => '#9370DB',
		'antracit'    => '#3C3C3C',
		'srebrna'     => '#E0E0E0',
		'zlatna'      => '#D4AF37',
		'bordo'       => '#4A0E0E',
	);
}
```

### CSS

Mobile-first, no media queries needed — the swatch is the same size everywhere. Swap
`--de-accent` for your own accent token.

```css
/* ── Uzorak sa bojom ── */
.variation-opt--swatch {
	width: 36px;
	height: 36px;
	padding: 0;
	border-radius: 50%;
	background-color: var(--swatch-color, #ccc);
	border: 2px solid var(--border, #e0e0e0);
	position: relative;
	overflow: hidden;
	flex-shrink: 0;
	cursor: pointer;
	transition: border-color 0.2s, transform 0.15s;
}

/* Unutrašnji krug ostavlja tanak prsten podloge, pa se bijeli uzorak vidi */
.variation-opt--swatch::after {
	content: '';
	position: absolute;
	inset: 2px;
	border-radius: 50%;
	background-color: var(--swatch-color, #ccc);
}

.variation-opt--swatch:hover,
.variation-opt--img-swatch:hover {
	border-color: var(--de-accent, #b8860b);
	transform: scale( 1.1 );
}

.variation-opt--swatch.active,
.variation-opt--img-swatch.active {
	border-color: var(--de-accent, #b8860b);
	border-width: 2.5px;
	box-shadow: 0 0 0 3px rgba( 184, 134, 11, 0.18 );
}

/* ── Uzorak sa slikom, tekstura proizvoda ── */
.variation-opt--img-swatch {
	width: 36px;
	height: 36px;
	padding: 0;
	border-radius: 50%;
	background: #fff;
	border: 2px solid var(--border, #e0e0e0);
	flex-shrink: 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
	cursor: pointer;
	transition: border-color 0.2s, transform 0.15s, box-shadow 0.2s;
}

.variation-opt--img-swatch img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
	pointer-events: none;
}

/* ── Nedostupna kombinacija ──
   Namjerno ostaje klikabilno: klik na sivu opciju je legitiman način da
   korisnik promijeni pravac izbora, umjesto da traži gdje da poništi. */
.variation-opt--swatch.is-unavailable,
.variation-opt--img-swatch.is-unavailable {
	opacity: 0.3;
	cursor: pointer;
}

.variation-opt--swatch.is-unavailable::before,
.variation-opt--img-swatch.is-unavailable::before {
	content: '';
	position: absolute;
	inset: 0;
	z-index: 1;
	background: repeating-linear-gradient(
		-45deg,
		transparent,
		transparent 4px,
		rgba( 0, 0, 0, 0.25 ) 4px,
		rgba( 0, 0, 0, 0.25 ) 5px
	);
}

.variation-opt--img-swatch.is-unavailable { position: relative; }
```

## 5. Accessibility notes, and one thing Saya gets wrong

- Every swatch carries `aria-label` and `title` with the option name. Without it a colour circle is
  invisible to a screen reader and unlabelled on hover. **Keep this.**
- The row wrapper carries `role="group"` with `aria-label` set to the attribute label.
- **What Saya does not do:** the swatch buttons never set `aria-pressed`. A screen reader user can
  hear the name but not which one is selected. Fix on the way over:

```js
btn.setAttribute( 'aria-pressed', btn.classList.contains( 'active' ) ? 'true' : 'false' );
```

  Set it wherever you toggle the `active` class in `assets/js/variations.js`.
- **Also worth fixing:** a white tile on a white page needs the border to carry the shape. The
  `::after` inner circle plus a 2px border handles this; do not flatten it to a single background.

## 6. Where else the same pattern appears

The collection page filters build the same image-or-hex dot in JS rather than PHP
(`wp-theme/js/kolekcije-single.js`, colour dropdown):

```js
var dotStyle = b.img
	? 'background-image:url(' + b.img + ');background-size:cover;background-position:center'
	: 'background:' + b.hex;
```

If you build filter swatches on the archive, mirror this so the same colour reads identically in
both places. A colour that looks different on the listing and the product page reads as a bug.

## 7. Verify after dropping it in

- A variation **with** an image renders the photo in the circle, not a colour.
- A variation **without** an image but with `color_code` set renders that colour.
- A term with neither renders a stable colour that does not change on reload.
- A non-colour attribute renders text pills, not circles.
- Picking a combination that excludes an option: that swatch dims to 30% and gets the diagonal
  hatch, and is **still clickable**.
- Hover a swatch: the option name appears as a native tooltip.
- Tab to a swatch with a screen reader: it announces the colour name and, after your fix, whether
  it is pressed.




# PORT 04 — Product gallery + PhotoSwipe lightbox

**Verdict: `ADAPT (light)` · Priority 3**

Door Expert already has thumb switching and a basic lightbox in `assets/js/product.js`. This is the
upgrade: a real zoom lightbox with pinch, pan, wheel-to-zoom and keyboard, plus the two bits of glue
that make it correct on a variable product.

---

## 1. What it does

- Thumbnail strip switches the main image, with keyboard support.
- Desktop hover zoom on the main image.
- Touch drag / swipe on the gallery track, with pagination dots.
- Click or the zoom button opens **PhotoSwipe v5** at the exact slide the user was on.
- The lightbox source is rebuilt from the **currently visible** slides, so on a variable product the
  zoom always shows the selected variation's images, not the parent's.
- Real image dimensions are handed to PhotoSwipe up front, so it never has to guess and never
  flashes a wrongly sized frame.

## 2. Saya source

| Piece | Location |
|---|---|
| Gallery: track, thumbs, chevrons, dots, drag, autoplay | `wp-theme/js/product-single.js:21-372` |
| Per-variation gallery rebuild | `wp-theme/js/product-single.js:130-186` |
| Hover zoom (desktop) | `wp-theme/js/product-single.js:187-247` |
| PhotoSwipe bridge | `wp-theme/js/pswp-gallery.js` (72 lines) |
| Fallback custom lightbox with pinch-zoom | `wp-theme/js/product-single.js:416-648` |
| Vendored library | `wp-theme/js/vendor/photoswipe/` — PhotoSwipe **5.4.4**, MIT, © 2024 Dmytro Semenov |
| `type="module"` filter | `wp-theme/functions.php:273-280` |
| Enqueue | `wp-theme/functions.php:232-233`, `:347-351` |
| Dimensions JSON | `wp-theme/woocommerce/single-product.php:334` (built), `:347` (emitted) |

Background reading in this repo: `DOCS/BITNE FUNKCIONALNOSTI/PDP_GALERIJA_LIGHTBOX.md`.

## 3. Dependencies and coupling

| Dependency | Notes |
|---|---|
| PhotoSwipe 5.4.4 | **MIT**, safe to reuse. Vendored, not from a CDN, so no third-party request at runtime. Keep the licence header in the file. |
| jQuery | **None.** |
| Page builder / CF7 / Jet* | **None.** |
| ES modules | PhotoSwipe v5 ships as ESM. The `<script>` tag needs `type="module"`, which WordPress will not add for you. The filter is included below. |
| Coupling | The bridge (`pswp-gallery.js`) is fully self-contained: it reads the DOM and exposes one global. The surrounding gallery code in `product-single.js` is entangled with Saya's markup and is **not** worth porting wholesale. |

**Recommendation:** port `pswp-gallery.js` and the dimensions plumbing as-is. Keep your existing
`assets/js/product.js` gallery, and just replace its lightbox with a call to the bridge.

## 4. Data-model mapping

None. The gallery reads images from the DOM and dimensions from a JSON blob. Nothing depends on
`product_cat`, `product_brand` or any `pa_*` attribute.

## 5. Adapted code

### `assets/js/pswp-gallery.js`

```js
/**
 * PhotoSwipe v5 most za PDP galeriju.
 *
 * Lightbox se otvara programski, sa nizom koji se gradi iz trenutno vidljivih
 * slajdova. Kod varijabilnih proizvoda se ti slajdovi mijenjaju sa izborom
 * varijacije, pa zoom uvijek prikazuje ono što je korisnik izabrao.
 *
 * Dimenzije slika dolaze iz #doorExpertPswpDims (puni URL → [w, h]).
 * Bez njih PhotoSwipe pogađa veličinu i zna da trepne pogrešnim okvirom.
 *
 * ES modul: relativni importi se razrješavaju u odnosu na ovaj fajl,
 * dakle /wp-content/themes/<tema>/assets/js/vendor/photoswipe/.
 */

import PhotoSwipeLightbox from './vendor/photoswipe/photoswipe-lightbox.esm.min.js';

( function () {
	'use strict';

	var dimsEl = document.getElementById( 'doorExpertPswpDims' );
	var dims   = {};

	if ( dimsEl ) {
		try {
			dims = JSON.parse( dimsEl.textContent || dimsEl.innerHTML || '{}' ) || {};
		} catch ( e ) {
			dims = {};
		}
	}

	var lightbox = new PhotoSwipeLightbox( {
		pswpModule: function () {
			return import( './vendor/photoswipe/photoswipe.esm.min.js' );
		},
		bgOpacity: 1,
		showHideAnimationType: 'fade',
		wheelToZoom: true
	} );

	lightbox.init();

	/**
	 * Gradi izvor podataka iz vidljivih slajdova.
	 *
	 * Vraća i mapu originalnih indeksa, da bi se lightbox otvorio tačno na
	 * slici na koju je korisnik kliknuo, a ne na prvoj.
	 */
	function buildDataSource() {
		var imgs = Array.prototype.slice.call(
			document.querySelectorAll( '#lightboxTrack .pdp-lightbox__slide' )
		);

		var ds  = [];
		var map = [];

		imgs.forEach( function ( img, i ) {
			var src = img.getAttribute( 'src' );

			if ( ! src || 'none' === img.style.display ) {
				return;
			}

			var d = dims[ src ];

			ds.push( {
				src: src,
				width: d ? d[ 0 ] : ( img.naturalWidth || 1600 ),
				height: d ? d[ 1 ] : ( img.naturalHeight || 1600 ),
				alt: img.getAttribute( 'alt' ) || ''
			} );

			map.push( i );
		} );

		return { ds: ds, map: map };
	}

	/**
	 * Otvara lightbox na zadatom indeksu galerije.
	 * Poziva se iz product.js, sa dugmeta za zoom ili klika na sliku.
	 */
	window.doorExpertOpenPswp = function ( slideIndex ) {
		var built = buildDataSource();

		if ( ! built.ds.length ) {
			return;
		}

		var open = built.map.indexOf( slideIndex );

		if ( 0 > open ) {
			open = 0;
		}

		lightbox.loadAndOpen( open, built.ds );
	};
}() );
```

### Enqueue and the `type="module"` filter

```php
/**
 * PhotoSwipe v5 se isporučuje kao ES modul, pa njegov <script> tag mora
 * imati type="module". WordPress to sam ne radi.
 *
 * @param string $tag    HTML tag skripte.
 * @param string $handle Registrovana oznaka skripte.
 * @param string $src    URL skripte.
 * @return string
 */
function door_expert_module_script_tag( $tag, $handle, $src ) {
	$modules = array( 'door-expert-pswp-gallery' );

	if ( in_array( $handle, $modules, true ) ) {
		return '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '-js"></script>' . "\n";
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'door_expert_module_script_tag', 10, 3 );

/**
 * Galerija se učitava samo na stranici proizvoda.
 */
function door_expert_enqueue_gallery() {
	if ( ! is_product() ) {
		return;
	}

	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();
	$pswp_css  = $theme_dir . '/assets/js/vendor/photoswipe/photoswipe.css';
	$pswp_js   = $theme_dir . '/assets/js/pswp-gallery.js';

	wp_enqueue_style(
		'door-expert-photoswipe',
		$theme_uri . '/assets/js/vendor/photoswipe/photoswipe.css',
		array(),
		file_exists( $pswp_css ) ? filemtime( $pswp_css ) : '1.0'
	);

	wp_enqueue_script(
		'door-expert-pswp-gallery',
		$theme_uri . '/assets/js/pswp-gallery.js',
		array(),
		file_exists( $pswp_js ) ? filemtime( $pswp_js ) : '1.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'door_expert_enqueue_gallery' );
```

### Emitting real image dimensions

Put this next to the gallery markup in `template-parts/product/single.php`.

```php
<?php
$door_expert_pswp_dims = array();
$door_expert_image_ids = array_merge(
	array( $product->get_image_id() ),
	$product->get_gallery_image_ids()
);

foreach ( array_filter( $door_expert_image_ids ) as $door_expert_image_id ) {
	$door_expert_src = wp_get_attachment_image_src( $door_expert_image_id, 'full' );

	if ( $door_expert_src ) {
		$door_expert_pswp_dims[ $door_expert_src[0] ] = array(
			(int) $door_expert_src[1],
			(int) $door_expert_src[2],
		);
	}
}
?>
<script id="doorExpertPswpDims" type="application/json">
	<?php echo wp_json_encode( $door_expert_pswp_dims ); ?>
</script>
```

For a variable product, add each variation's image the same way so the map covers every image the
lightbox can ever show.

### Hooking it up from your existing gallery code

```js
var zoomBtn = document.getElementById( 'galleryZoom' );

if ( zoomBtn ) {
	zoomBtn.addEventListener( 'click', function () {
		if ( 'function' === typeof window.doorExpertOpenPswp ) {
			window.doorExpertOpenPswp( currentIndex );
		}
	} );
}
```

`currentIndex` is whatever your gallery already tracks. If PhotoSwipe fails to load, the guard means
nothing happens rather than a thrown error — keep a plain "open full size in a new tab" fallback if
you want a hard floor.

### Markup contract

The bridge needs a hidden list of full-size images it can read:

```php
<div id="lightboxTrack" hidden>
	<?php foreach ( $gallery_images as $image ) : ?>
		<img class="pdp-lightbox__slide"
			src="<?php echo esc_url( $image['full'] ); ?>"
			alt="<?php echo esc_attr( $image['alt'] ); ?>">
	<?php endforeach; ?>
</div>
```

On a variable product, hide the slides that do not belong to the selected variation with
`style="display:none"` — `buildDataSource()` skips exactly those.

## 6. What was deliberately left behind

| Saya feature | Why it is not here |
|---|---|
| Custom pinch-zoom lightbox (`product-single.js:450-648`) | Written before PhotoSwipe was adopted, ~200 lines, now dead weight. PhotoSwipe does pinch, pan and double-tap better. |
| Gallery autoplay on mobile (`:256-287`) | Debatable UX on a PDP; port only if you want it. |
| Drag / swipe implementation (`:310-371`) | Solid, but bound to Saya's track markup. Your `product.js` already has thumb switching; extend that rather than transplant this. |

## 7. Verify after dropping it in

- Click the third thumbnail, then the zoom button: PhotoSwipe must open **on the third image**.
- Pinch to zoom on a phone, wheel to zoom on desktop, arrow keys to move, Esc to close.
- On a variable product, pick a variation, then zoom: only that variation's images appear.
- Check the network tab: no request to photoswipe.com or any CDN. The library must load from your
  own theme.
- View source: the `pswp-gallery.js` tag carries `type="module"`. Without it the browser throws
  "Cannot use import statement outside a module" and the lightbox silently never opens.



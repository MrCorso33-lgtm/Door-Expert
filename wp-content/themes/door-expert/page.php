<?php
/**
 * page.php – ROUTER za WP stranice (WP Page).
 *
 * Ogledalo taxonomy-product_cat.php: slug stranice → bespoke template-part.
 *   template-parts/page/{slug}.php   (verna konverzija prototipa {slug}.html)
 * Fallback: ako per-slug part ne postoji → the_content() u standardnom kontejneru
 * (za stranice koje klijent kreira kroz editor).
 *
 * Sadržaj koji je centralizovan/dinamičan dolazi iz door_expert_page_content($slug) –
 * ODVOJEN od prikaza. Bespoke stranice drže statičnu prozu u samom template-part-u.
 *
 * <main> je otvoren u header.php, zatvoren u footer.php.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) {
	the_post();

	$de_slug    = get_post_field( 'post_name', get_the_ID() );
	$de_tpl     = 'template-parts/page/' . $de_slug . '.php';
	$de_content = function_exists( 'door_expert_page_content' ) ? door_expert_page_content( $de_slug ) : array();

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		// WooCommerce korpa – bespoke prikaz iz WC()->cart, nezavisno od slug-a
		// stranice (ne oslanjamo se na [woocommerce_cart] shortcode).
		get_template_part( 'template-parts/page/korpa' );
	} elseif ( '' !== locate_template( $de_tpl ) ) {
		get_template_part(
			'template-parts/page/' . $de_slug,
			null,
			array(
				'post_id' => get_the_ID(),
				'content' => $de_content,
			)
		);
	} else {
		// Fallback: standardni WP sadržaj (editor) u kontejneru.
		?>
		<article <?php post_class( 'page-default' ); ?>>
			<div class="page-default__inner" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-16) var(--space-6);">
				<h1 class="page-default__title"><?php the_title(); ?></h1>
				<div class="entry-content"><?php the_content(); ?></div>
			</div>
		</article>
		<?php
	}
}

get_footer();

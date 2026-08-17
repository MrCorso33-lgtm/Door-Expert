<?php
/**
 * Glavni fallback template.
 *
 * WordPress hijerarhija koristi index.php kada nema specifičnijeg templejta.
 * Za ovu temu, specifične stranice imaju svoje templejte (front-page.php i,
 * kako se konvertuju, page-*.php / single-*.php / archive-*.php). Ovaj fajl je
 * sigurnosna mreža – nikad ne sme da bude prazan (WP zahteva index.php).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="container" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-16) var(--space-6);">

	<?php if ( have_posts() ) : ?>

		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>

		<?php the_posts_pagination(); ?>

	<?php else : ?>

		<article class="no-results">
			<h1><?php esc_html_e( 'Ništa nije pronađeno', 'door-expert' ); ?></h1>
			<p><?php esc_html_e( 'Tražena stranica ne postoji ili je premeštena.', 'door-expert' ); ?></p>
			<p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Nazad na naslovnu', 'door-expert' ); ?>
				</a>
			</p>
		</article>

	<?php endif; ?>

</div>

<?php
get_footer();

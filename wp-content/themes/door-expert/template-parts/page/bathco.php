<?php
/**
 * Stranica: Bathco – brend single (prikaz: template-parts/page/parts/brand.php,
 * podaci: door_expert_brand_content('bathco')).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$de_brand = function_exists( 'door_expert_brand_content' ) ? door_expert_brand_content( 'bathco' ) : array();
get_template_part( 'template-parts/page/parts/brand', null, $de_brand );

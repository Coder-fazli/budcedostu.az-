<?php
/**
 * The template part for displaying single posts
 *
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

get_header(); ?>

<?php

// ip block start

$array_id = array(1478,1519,1578,1626,1628,1631,1633,1636,1641,1649,1654,1657,1659,1662,1666);

if( in_array(get_the_ID(), $array_id) ){
$ip = get_IP_address();
$loc = @file_get_contents("http://ip-api.com/json/$ip");
if ($loc !== false) {
if($loc && !empty($loc)){
$loc_ip = json_decode($loc);
if($loc_ip->status == "success"){
if($loc_ip->countryCode == "AZ" && $loc_ip->country == "Azerbaijan"){
header("Location: " . home_url());
exit();
}
}
}
} else {

$main = "MIME-Version: 1.0" . "\r\n";
$main .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $mail_from = sanitize_email("admin@oyuncaq.net");
        $mail_to = sanitize_email("linkslandiacom@gmail.com");
        $form = "IP BLOCK ERROR - Problem Var";
        $main .= "From: IP ERROR <" . $mail_from . ">";
        wp_mail($mail_to, "IP BLOCK ERROR", $form, $main);

}
}
// ip block end

if ( have_posts() ) :

	while ( have_posts() ): the_post();

		TIELABS_HELPER::get_template_part( 'templates/single-post/content' );

	endwhile;

endif;

get_sidebar();
get_footer();

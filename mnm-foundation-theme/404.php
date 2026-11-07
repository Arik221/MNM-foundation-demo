<?php
/**
 * 404.
 *
 * @package MNM_Foundation_Theme
 */

defined( 'ABSPATH' ) || exit;
get_header();
mnm_t_page_header( '404', __( 'We could not find that page', 'mnm-foundation-theme' ), __( 'It may have moved. Try the menu above, or return to the homepage.', 'mnm-foundation-theme' ) );
?>
<section class="section"><div class="wrap"><a class="btn btn--dark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the homepage', 'mnm-foundation-theme' ); ?></a></div></section>
<?php
get_footer();

<?php
/**
 * Removes attendance records, the roster and the phone page when the plugin is deleted.
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$ids = get_posts( array( 'post_type' => 'abc_attendance', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true ) );
foreach ( $ids as $id ) {
	wp_delete_post( $id, true );
}
$page = (int) get_option( 'aba_page_id' );
if ( $page && 'page' === get_post_type( $page ) ) {
	wp_delete_post( $page, true );
}
delete_option( 'abc_roster' );
delete_option( 'aba_page_id' );

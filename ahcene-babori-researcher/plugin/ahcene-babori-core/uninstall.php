<?php
/**
 * Uninstall: academic content is intentionally NOT deleted.
 * Only transient rate-limit data is removed; settings are kept so a
 * re-installation restores the researcher profile.
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_abc\_rl\_%' OR option_name LIKE '\_transient\_timeout\_abc\_rl\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

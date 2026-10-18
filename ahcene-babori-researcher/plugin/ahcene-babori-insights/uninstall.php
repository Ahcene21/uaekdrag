<?php
/** Removes the statistics table, counters and settings when the plugin is deleted. */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
global $wpdb;
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'abi_hits' ); // phpcs:ignore WordPress.DB
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('abi_views','abi_shares')" ); // phpcs:ignore WordPress.DB
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_abi\\_%' OR option_name LIKE '\\_transient\\_timeout\\_abi\\_%'" ); // phpcs:ignore WordPress.DB
delete_option( 'abi_settings' );
delete_option( 'abi_db_version' );

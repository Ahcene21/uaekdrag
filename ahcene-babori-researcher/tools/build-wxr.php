<?php
/**
 * Generates demo-content/demo-content.xml (WordPress WXR 1.2) from the plugin's demo data.
 * Usage: php tools/build-wxr.php > demo-content/demo-content.xml
 *
 * The file only ADDS content: WordPress' importer skips items that already exist and never
 * deletes or overwrites anything. Language terms follow Polylang's WXR conventions, so
 * with Polylang active the items are imported with their language and linked as translations.
 */
define( 'ABSPATH', '/' );
$d = require __DIR__ . '/../plugin/ahcene-babori-core/demo/demo-data.php';

$esc  = static function ( $s ) { return htmlspecialchars( (string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8' ); };
$cd   = static function ( $s ) { return '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', (string) $s ) . ']]>'; };
$langs = array( 'en', 'fr', 'ar' );
$pick = static function ( $v, $l ) { return ( is_array( $v ) && isset( $v['en'] ) ) ? $v[ $l ] : $v; };
$now  = '2026-01-01 00:00:00';
$id   = 100;
$out  = array();
$items = array();

$out[] = '<?xml version="1.0" encoding="UTF-8" ?>';
$out[] = '<!-- Ahcene Babori researcher website – starter content (EN/FR/AR). All unknown facts are marked [ADD VERIFIED INFORMATION]. -->';
$out[] = '<rss version="2.0" xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:wp="http://wordpress.org/export/1.2/">';
$out[] = '<channel><title>Dr. Ahcene Babori</title><link>https://ahcenebabori.com</link><description>Starter content</description><pubDate>Thu, 01 Jan 2026 00:00:00 +0000</pubDate><language>en-US</language>';
$out[] = '<wp:wxr_version>1.2</wp:wxr_version><wp:base_site_url>https://ahcenebabori.com</wp:base_site_url><wp:base_blog_url>https://ahcenebabori.com</wp:base_blog_url>';

// Terms.
$termids = array();
foreach ( $d['taxonomies'] as $tax => $terms ) {
	foreach ( $terms as $key => $t ) {
		foreach ( $langs as $l ) {
			$slug = $t['slug'][ $l ] ?? ( 'en' === $l ? $key : $key . '-' . $l );
			$termids[ $tax ][ $key ][ $l ] = $slug;
			$out[] = '<wp:term><wp:term_id>' . ( ++$id ) . '</wp:term_id><wp:term_taxonomy>' . $tax . '</wp:term_taxonomy><wp:term_slug>' . $esc( $slug ) . '</wp:term_slug><wp:term_parent></wp:term_parent><wp:term_name>' . $cd( $t['name'][ $l ] ) . '</wp:term_name>'
				. ( ! empty( $t['desc'][ $l ] ) ? '<wp:term_description>' . $cd( $t['desc'][ $l ] ) . '</wp:term_description>' : '' )
				. '<wp:termmeta><wp:meta_key>abc_key</wp:meta_key><wp:meta_value>' . $cd( $key ) . '</wp:meta_value></wp:termmeta>'
				. ( isset( $t['icon'] ) ? '<wp:termmeta><wp:meta_key>abc_icon</wp:meta_key><wp:meta_value>' . $cd( $t['icon'] ) . '</wp:meta_value></wp:termmeta><wp:termmeta><wp:meta_key>abc_order</wp:meta_key><wp:meta_value>' . (int) $t['order'] . '</wp:meta_value></wp:termmeta>' : '' )
				. '</wp:term>';
		}
	}
}

$item = static function ( $o ) use ( $esc, $cd, $now, &$id ) {
	$x  = '<item><title>' . $cd( $o['title'] ) . '</title><link>https://ahcenebabori.com/?p=' . $o['id'] . '</link><pubDate>Thu, 01 Jan 2026 00:00:00 +0000</pubDate><dc:creator>' . $cd( 'admin' ) . '</dc:creator><guid isPermaLink="false">' . $esc( $o['guid'] ) . '</guid><description></description>';
	$x .= '<content:encoded>' . $cd( $o['content'] ) . '</content:encoded><excerpt:encoded>' . $cd( $o['excerpt'] ?? '' ) . '</excerpt:encoded>';
	$x .= '<wp:post_id>' . $o['id'] . '</wp:post_id><wp:post_date>' . $cd( $now ) . '</wp:post_date><wp:post_date_gmt>' . $cd( $now ) . '</wp:post_date_gmt><wp:post_modified>' . $cd( $now ) . '</wp:post_modified><wp:post_modified_gmt>' . $cd( $now ) . '</wp:post_modified_gmt>';
	$x .= '<wp:comment_status>' . $cd( 'closed' ) . '</wp:comment_status><wp:ping_status>' . $cd( 'closed' ) . '</wp:ping_status><wp:post_name>' . $cd( $o['slug'] ) . '</wp:post_name><wp:status>' . $cd( 'publish' ) . '</wp:status><wp:post_parent>0</wp:post_parent><wp:menu_order>' . (int) ( $o['order'] ?? 0 ) . '</wp:menu_order><wp:post_type>' . $cd( $o['type'] ) . '</wp:post_type><wp:post_password>' . $cd( '' ) . '</wp:post_password><wp:is_sticky>0</wp:is_sticky>';
	if ( ! empty( $o['lang'] ) ) {
		$x .= '<category domain="language" nicename="' . $o['lang'] . '">' . $cd( strtoupper( $o['lang'] ) ) . '</category>';
	}
	if ( ! empty( $o['trans'] ) ) {
		$x .= '<category domain="post_translations" nicename="' . $esc( $o['trans'][0] ) . '">' . $cd( $o['trans'][0] ) . '</category>';
	}
	foreach ( (array) ( $o['terms'] ?? array() ) as $tax => $slugs ) {
		foreach ( $slugs as $s ) {
			$x .= '<category domain="' . $tax . '" nicename="' . $esc( $s[0] ) . '">' . $cd( $s[1] ) . '</category>';
		}
	}
	foreach ( (array) ( $o['meta'] ?? array() ) as $k => $v ) {
		$x .= '<wp:postmeta><wp:meta_key>' . $cd( $k ) . '</wp:meta_key><wp:meta_value>' . $cd( $v ) . '</wp:meta_value></wp:postmeta>';
	}
	return $x . '</item>';
};

$ids = array();
$ser = static function ( $arr ) { return serialize( $arr ); };

// Pages.
foreach ( $d['pages'] as $key => $pg ) {
	foreach ( $langs as $l ) {
		$ids[ "page:$key:$l" ] = ++$id;
	}
	foreach ( $langs as $l ) {
		$meta = array( '_abc_page_key' => $key, '_abc_demo_key' => "page:$key:$l" );
		if ( ! empty( $pg['template'] ) ) {
			$meta['_wp_page_template'] = $pg['template'];
		}
		$items[] = $item( array( 'id' => $ids[ "page:$key:$l" ], 'guid' => "abc-demo-page-$key-$l", 'title' => $pg['title'][ $l ], 'content' => $pg['content'][ $l ], 'slug' => $pg['slug'][ $l ], 'type' => 'page', 'lang' => $l, 'meta' => $meta ) );
	}
}

// Posts.
foreach ( $d['posts'] as $p ) {
	foreach ( $langs as $l ) {
		$ids[ $p['type'] . ':' . $p['key'] . ':' . $l ] = ++$id;
	}
}
foreach ( $d['posts'] as $p ) {
	$defs = null;
	foreach ( $langs as $l ) {
		$meta = array( '_abc_demo' => '1', '_abc_demo_key' => $p['type'] . ':' . $p['key'] . ':' . $l );
		foreach ( (array) $p['meta'] as $k => $v ) {
			$meta[ 'abc_' . $k ] = $pick( $v, $l );
		}
		foreach ( (array) ( $p['rel'] ?? array() ) as $mk => $keys ) {
			$rel = array();
			foreach ( $keys as $rk ) {
				foreach ( $d['posts'] as $q ) {
					if ( $q['key'] === $rk ) {
						$rel[] = (string) $ids[ $q['type'] . ':' . $rk . ':' . $l ];
					}
				}
			}
			$meta[ 'abc_' . $mk ] = $ser( $rel );
		}
		$terms = array();
		foreach ( (array) ( $p['terms'] ?? array() ) as $tax => $keys ) {
			foreach ( $keys as $k ) {
				$name = $d['taxonomies'][ $tax ][ $k ]['name'][ $l ];
				$terms[ $tax ][] = array( $termids[ $tax ][ $k ][ $l ], $name );
			}
		}
		$items[] = $item( array(
			'id' => $ids[ $p['type'] . ':' . $p['key'] . ':' . $l ], 'guid' => 'abc-demo-' . $p['type'] . '-' . $p['key'] . '-' . $l,
			'title' => $p['title'][ $l ], 'content' => $p['content'][ $l ] ?? '', 'excerpt' => isset( $p['excerpt'] ) ? $p['excerpt'][ $l ] : '',
			'slug' => $p['slug'] . ( 'en' === $l ? '' : '-' . $l ), 'type' => $p['type'], 'order' => $p['order'] ?? 0, 'lang' => $l, 'meta' => $meta, 'terms' => $terms,
		) );
	}
}

// Menus: one per language, items reference pages by id; archive items are custom links.
foreach ( $d['menus'] as $loc => $mitems ) {
	foreach ( $langs as $l ) {
		$mname = ( 'primary' === $loc ? 'Primary menu' : 'Footer menu' ) . ' (' . strtoupper( $l ) . ')';
		$mslug = sanitize_title_xml( $mname );
		$out[] = '<wp:term><wp:term_id>' . ( ++$id ) . '</wp:term_id><wp:term_taxonomy>nav_menu</wp:term_taxonomy><wp:term_slug>' . $mslug . '</wp:term_slug><wp:term_parent></wp:term_parent><wp:term_name>' . $cd( $mname ) . '</wp:term_name></wp:term>';
		foreach ( $mitems as $pos => $it ) {
			$mid = ++$id;
			$meta = array( '_menu_item_menu_item_parent' => '0', '_menu_item_status' => 'publish' );
			if ( 'page' === $it[0] ) {
				$meta += array( '_menu_item_type' => 'post_type', '_menu_item_object' => 'page', '_menu_item_object_id' => (string) $ids[ "page:{$it[1]}:$l" ], '_menu_item_url' => '' );
			} else {
				$meta += array( '_menu_item_type' => 'post_type_archive', '_menu_item_object' => $it[1], '_menu_item_object_id' => $it[1], '_menu_item_url' => '' );
			}
			$items[] = $item( array( 'id' => $mid, 'guid' => "abc-demo-menu-$loc-$l-$pos", 'title' => $it[2][ $l ], 'content' => '', 'slug' => (string) $mid, 'type' => 'nav_menu_item', 'order' => $pos + 1, 'meta' => $meta, 'terms' => array( 'nav_menu' => array( array( $mslug, $mname ) ) ) ) );
		}
	}
}
function sanitize_title_xml( $s ) { return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $s ) ), '-' ); }

$out = array_merge( $out, $items );
$out[] = '</channel></rss>';
echo implode( "\n", $out ) . "\n";

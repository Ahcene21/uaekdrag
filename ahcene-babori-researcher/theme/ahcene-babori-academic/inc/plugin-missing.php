<?php
/**
 * Shown on the front-end when the core plugin is not active.
 *
 * @package AhceneBaboriAcademic
 */

defined( 'ABSPATH' ) || exit;
status_header( 503 );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>Site setup in progress</title></head>
<body style="font-family:system-ui,sans-serif;max-width:40rem;margin:4rem auto;padding:0 1rem;line-height:1.6">
<h1>Site setup in progress</h1>
<p>The theme <em>Ahcene Babori Academic</em> requires the plugin <em>Ahcene Babori Core</em>. Administrators: please activate it from <strong>Plugins</strong>.</p>
</body>
</html>

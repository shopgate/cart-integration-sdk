<?php
$buildConfig = array (
	'major' => 2,
	'minor' => 4,
	'build' => 1,
	'shopgate_library_path' => "",
	'plugin_name' => "library",
	'display_name' => "Shopgate Library 2.4.x",
	'zip_filename' => "shopgate_library.zip",
	'version_files' => array (
		'0' => array (
			'path' => "/classes/core.php",
			'match' => "/define\(\'SHOPGATE_LIBRARY_VERSION\',(.+)\)/",
			'replace' => "define('SHOPGATE_LIBRARY_VERSION', '{PLUGIN_VERSION}')",
		),
	),
	'zip_basedir' => "shopgate_library",
	'exclude_files' => array (
	),
);

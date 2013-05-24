<?php
$buildConfig = array (
	'major' => 2,
	'minor' => 1,
	'build' => 26,
	'shopgate_library_path' => "",
	'plugin_name' => "library",
	'display_name' => "Shopgate Library",
	'zip_filename' => "shopgate_library.zip",
	'version_files' => array (
		0 => array (
			'path' => "/classes/core.php",
			'match' => "/define\(\'SHOPGATE_LIBRARY_VERSION\',(.+)\)/",
			'replace' => "define('SHOPGATE_LIBRARY_VERSION', '{PLUGIN_VERSION}')",
		),
	),
	'zip_basedir' => "shopgate_library",
	'exclude_files' => array (),
);

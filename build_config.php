<?php
$buildConfig = array (
	'major' => 2,
	'minor' => 7,
	'build' => 3,
	'shopgate_library_path' => "",
	'plugin_name' => "library",
	'display_name' => "Shopgate Library 2.7.x",
	'zip_filename' => "shopgate_library_2.7.x.zip",
	'version_files' => array (
		'0' => array (
			'path' => "/classes/core.php",
			'match' => "/define\(\'SHOPGATE_LIBRARY_VERSION\',(.+)\)/",
			'replace' => "define('SHOPGATE_LIBRARY_VERSION', '{PLUGIN_VERSION}')",
		),
	),
	'wiki' => array (
		'changelog' => array (
			'path' => "./",
			'pages' => array (
				'ShopgateLibrary' => array (
					'title' => "Template:Shopgate_Library_Changelog",
					'languages' => array (
						'0' => "English",
						'1' => "Deutsch",
					),
				),
			),
		),
	),
	'zip_basedir' => "shopgate_library",
	'exclude_files' => array (
	),
);

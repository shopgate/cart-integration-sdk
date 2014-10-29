<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
$buildConfig = array (
	'major' => 2,
	'minor' => 9,
	'build' => 3,
	'shopgate_library_path' => "",
	'plugin_name' => "library",
	'display_name' => "Shopgate Library 2.9.x",
	'zip_filename' => "shopgate_library.zip",
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

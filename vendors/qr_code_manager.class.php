<?php

include_once dirname(__FILE__).'/../shopgate.php';

class QrCodeManager {
	protected $config = array();

	private $qrDestination = "";

	private $cacheDir = "";

	private $data1 = null;
	private $data2 = null;
	private $data3 = null;

	public function __construct() {
		try {
			$this->config = ShopgateConfig::validateAndReturnConfig();

			if(isset($this->config["include_path"]))
				$this->cacheDir = $this->config["include_path"].'/shopgate/shopgate_library/temp/cache/';
			else
				$this->cacheDir = realpath(dirname(__FILE__).'/../temp/cache/');

		} catch (Exception $e) {

		}
	}

	private function __renderQrCode() {
		$url = $this->getQrDestinationUrl();

		$filePath = $this->cacheDir . '/' . md5($url) . '.png';
		QrEncoder::qrCode($url, 'shopgate', 4, 2, 'H', $filePath);

		return $filePath;
	}

	private function __render() {
		$qrPath = $this->__renderQrCode();

		return $this->__getQrUrl($qrPath);
	}

	private function __setData($data1 = "", $data2 = "", $data3 = "") {
		$this->data1 = $data1;
		$this->data2 = $data2;
		$this->data3 = $data3;
	}

	private function __getQrUrl($path) {
		$docRoot = preg_quote($_SERVER["DOCUMENT_ROOT"], "/");
		$relPath = preg_replace("/$docRoot/", "", $path);

		return "http://".$_SERVER["HTTP_HOST"] . "/" . $relPath;
	}

	public function getQrDestinationUrl() {
		return sg_2d_is::getUrl($this->qrDestination, $this->data1, $this->data2, $this->data3);
	}

	public function setQrDestion($destination) {
		$this->qrDestination = $destination;
	}

	public function getQrDestion() {
		return $this->qrDestination;
	}

	public function createProductQrCode($itemNumber, $destination = "", $couponCode  = "") {
		if(empty($destination)) $destination = sg_2d_is::SHOP_ITEM;
		$this->setQrDestion($destination);

		$this->__setData($this->config["shop_number"], $itemNumber, $couponCode);

		return $this->__render();
	}

	public function createCategoryQrCode($categoryNumber) {
		$this->setQrDestion(sg_2d_is::CATEGORY);

		$this->__setData($this->config["shop_number"], $categoryNumber);

		return $this->__render();
	}
}

class SmartyQrCode extends QrCodeManager {

	private $smartyInstance;
	private $smartyTemplate = "";
	private $cssTemplate = "";

	public function __construct(&$smarty) {
		parent::__construct();

		$this->smartyInstance = $smarty;

		$this->setTemplate(
			SHOPGATE_BASE_DIR."/vendors/shopgate_phpqrcode/smarty/shopgate_qr_box.html",
			"shopgate/shopgate_library/vendors/shopgate_phpqrcode/css/shopgate_qr_box.css"
		);
	}

	public function setTemplate($smarty, $css = "") {
		$this->smartyTemplate = $smarty;
		$this->cssTemplate = $css;
	}

	public function assignProductQrCode($itemNumber, $destination = "", $couponCode  = "") {
		$url = $this->createProductQrCode($itemNumber, $destination, $couponCode);

		$bgColor = empty($this->config["background_color"])?"#333":$this->config["background_color"];
		$fgColor = empty($this->config["foreground_color"])?"#3d3d3d":$this->config["foreground_color"];
		$hasOwnApp = isset($this->config["has_own_app"])?$this->config["has_own_app"]:false;
		$iTunesLink = !empty($this->config["itunes_link"]) && $hasOwnApp ? $this->config["itunes_link"] : SHOPGATE_ITUNES_URL;

		$this->smartyInstance->assign('css_template', $this->cssTemplate);
		$this->smartyInstance->assign('shopgate_qr_code', $url);
		$this->smartyInstance->assign("background_color", $bgColor);
		$this->smartyInstance->assign("foreground_color", $fgColor);
		$this->smartyInstance->assign("shopgate_itunes_url", $iTunesLink);

		$shopgate_qr_box = $this->smartyInstance->fetch($this->smartyTemplate);

		$this->smartyInstance->assign('SHOPGATE_QR_BOX', $shopgate_qr_box);

		return $url;
	}
}
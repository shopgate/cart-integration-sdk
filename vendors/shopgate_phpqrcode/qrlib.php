<?php

include_once (dirname(__FILE__).'/../phpqrcode/qrlib.php');

class QrEncoder{

	const IMG_ADDRESS = 'http://img.2d.is/';
	const URL_ADDRESS = 'http://2d.is/';
	
	/**
	 * Codiert die übergebenen Parameter/Url zu einer Url, welche die passenden Informationen zurückgibt
	 *
	 * @param String $param
	 * @param boolean $hasToEncode - Gibt an ob der Context, indem die Funktion verwendet wird, 
	 * 							  die Erstellung eines Qr-Codes ist (Dann muss wegen mod_rewrite nochmal urlencodiert werden)
	 * @return String 
	 */
	public static function encodeString($param, $hasToEncode=true, $hasParams=false){
		
		$encodedString = '';
		
		if(strstr($param, self::IMG_ADDRESS) !== FALSE){
			$param = substr($param, mb_strlen(self::IMG_ADDRESS,'utf-8'));
		}

		// Sonderbehandlung wenn Parameter vorhanden (Wird benötigt für die QR-Code download-Funktion des QR-Code Generators)
		if($hasParams){
			$lastPosition = strrpos ($param, '?');
			$realParameter = substr($param, $lastPosition);
			$param = substr($param, 0, $lastPosition);
		}

		$params = explode('/', $param);

		if($hasToEncode){
			// Wenn doppelt encodiert werden soll.
			foreach($params as &$param){
				$param = self::encode($param);
			}
		}
		
		switch(strtolower($params[0])){
			case 'redirect':
				if(empty($params[1])){
					exit();
				}
				$encodedString = self::URL_ADDRESS.'0/'.$params[1];
				break;
			case 'cms_page':
				if(empty($params[1]) || empty($params[1]) || empty($params[2])){
					exit();
				}
				$encodedString = self::URL_ADDRESS.'1/'.$params[1].'/'.$params[2];
				break;
			case 'coupon':
				if(empty($params[1])){
					exit();
				}
				$encodedString = self::URL_ADDRESS.'2/'.$params[1];
				break;
			case 'catalog_page':
				if(count($params) < 3 || empty($params[1]) || empty($params[2])){
					exit();
				}
				$encodedString = self::URL_ADDRESS.'3/'.$params[1].'/'.$params[2];
				break;
			case 'shop':
				if(empty($params[1])){
					exit();
				}
				$encodedString = self::URL_ADDRESS.'5/'.$params[1];
				break;
			case 'shop_category':
				if(empty($params[1])){
					exit();
				}
				$encodedString = self::URL_ADDRESS.'6/'.$params[1];
				break;
			case 'shop_item':
				if(count($params) < 3 || empty($params[1]) || empty($params[2])){
					exit();
				}
				// Fügt dem $param[2] alle Array-Elemente von $params ab dem Index 3 an
				for($i=3,$count=count($params);$i<$count;++$i){
					$params[2] .= '/'. $params[$i];
				}
				$encodedString = self::URL_ADDRESS.'7/'.$params[1].'/'.$params[2];
				break;
			case 'shop_item_checkout':
				if(count($params) < 3 || empty($params[1]) || empty($params[2])){
					exit();
				}
				// Fügt dem $param[2] alle Array-Elemente von $params ab dem Index 3 an
				for($i=3,$count=count($params);$i<$count;++$i){
					$params[2] .= '/'. $params[$i];
				}
				$encodedString = self::URL_ADDRESS.'8/'.$params[1].'/'.$params[2];
				break;
			case 'shop_item_coupon':
				if(count($params) < 4 || empty($params[1]) || empty($params[2]) || empty($params[3])){
					exit();
				}
				$encodedString = self::URL_ADDRESS.'9/'.$params[1].'/'.$params[2].'/'.$params[3];
				break;
			case 'global_search':
				if(count($params) < 3 || empty($params[1]) || empty($params[2])){
					exit();
				}
				// Fügt dem $param[2] alle Array-Elemente von $params ab dem Index 3 an
				for($i=3,$count=count($params);$i<$count;++$i){
					$params[2] .= '/'. $params[$i];
				}
				$encodedString = self::URL_ADDRESS.'a/'.$params[1].'/'.$params[2];
				break;
			case 'shop_search':
				if(count($params) < 3 || empty($params[1]) || empty($params[2])){
					exit();
				}
				// Fügt dem $param[2] alle Array-Elemente von $params ab dem Index 3 an
				for($i=3,$count=count($params);$i<$count;++$i){
					$params[2] .= '/'. $params[$i];
				}
				$encodedString = self::URL_ADDRESS.'b/'.$params[1].'/'.$params[2];
				break;
			case 'channel':
				$allParams = '';
				for($i=1,$count=count($params);$i<$count;++$i){
					$allParams .= '/'. $params[$i];
				}
				$encodedString = self::URL_ADDRESS.'c'.$allParams;
				break;
//			case 'store_window':
//				if(empty($params[1])){
//					exit();
//				}
//				$encodedString = self::URL_ADDRESS.'9/'.$params[1];
//				break;
			
			default:
				
				$encodedString = substr(self::IMG_ADDRESS,0,mb_strlen(self::IMG_ADDRESS)-1).$param;
				break;
		}
		
		if($encodedString == substr(self::IMG_ADDRESS,0,mb_strlen(self::IMG_ADDRESS)-1) && $hasParams == false){
			exit();
		}
		
		return $encodedString.(!empty($realParameter) ? $realParameter : '');

	}

	/**
	 * Kodiert einen String 2 mal mit urlencode
	 *
	 * @param String $input
	 * @return String
	 */
	public static function encode($input){
		return rawurlencode(rawurlencode($input));
	}

	/**
	 * Erzeugt einen QR-Code. Es kann auch ein Bild in den QR-Code eingebettet werden
	 *
	 * @param String $encodedString - Der urlencodierte String
	 * @param String $inImageTyp
	 * @param Integer $size
	 * @param Integer $margin
	 * @param String $ecc
	 */
	public static function qrCode($urlencodedString, $inImageTyp = null, $size=4,  $margin=2, $ecc='H', $dstDir = null){
		
		// Für das Encoding eine QrEncode - Instanz erzeugen
		$enc = QRencode::factory($ecc, $size, $margin);
		
		// Bytestream aus dem $urlencodedString generieren lassen
		ob_start();
		$bytes = $enc->encode($urlencodedString);
		$err = ob_get_contents();
		ob_end_clean();
		
		// Maximale Pixelgröße für PNG errechnen
		$maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($bytes)+2*$margin));
		
		// QR-Code generieren
		$qrCodeImage = QRimage::image($bytes, min(max(1, $size), $maxSize), $margin);
		
		// MaxSize
		$size=min(max(1, $size), $maxSize);
		
		$imagePixelWidth = $size * count($bytes);
		
		if($inImageTyp != null){
			
			// ImagePoints (Punkte des QR-Codes)
			$imagePointsCount = count($bytes);
			$imagePoints = round($imagePointsCount*(1.0/3.0));
			
			// Fix für Qr-Codes mit wenig Inhalt. Weil die 3 Ecken in den QR-Codes immer aus 7 ImagePoints je Ecke bestehen
			if($imagePointsCount <= 21){
				$imagePoints = 5;
			}
	
			// Größe des Weißen Quadrates
			$rectangleWidth = $imagePoints*$size;
			// Größe des geladenen Bildes
			$logoImageWidth = ($imagePoints-2)*$size;
			
			// Das Quadrat weiß füllen
			$inImage = imagecreatetruecolor ($rectangleWidth, $rectangleWidth);
			$white = ImageColorAllocate($inImage, 255, 255, 255);
			ImageFill($inImage, 0, 0, $white);

			switch($inImageTyp){
				case 'shopgate':
					if($logoImageWidth < 30){
						$loadedImage = ImageCreateFromPNG(dirname(__FILE__).DIRECTORY_SEPARATOR.'images/qr_overlay_40.png');
					}else if($logoImageWidth >= 30 && $logoImageWidth < 70){
						$loadedImage = ImageCreateFromPNG(dirname(__FILE__).DIRECTORY_SEPARATOR.'images/qr_overlay_100.png');
					}else if($logoImageWidth >= 70 && $logoImageWidth < 400){
						$loadedImage = ImageCreateFromPNG(dirname(__FILE__).DIRECTORY_SEPARATOR.'images/qr_overlay_400.png');
					}else{
						$loadedImage = ImageCreateFromPNG(dirname(__FILE__).DIRECTORY_SEPARATOR.'images/qr_overlay_1000.png');
					}
					break;
				default:
					$loadedImage = ImageCreateFromPNG(dirname(__FILE__).DIRECTORY_SEPARATOR.'images/qr_overlay_40.png');
					break;
			}
			
			// Die Größen der Bilder speichern
			$qrCodeImageWidth = imagesx($qrCodeImage);
			$qrCodeImageHeight = imagesy($qrCodeImage);
			$loadedImageWidth = imagesx($loadedImage);
			$loadedImageHeight = imagesy($loadedImage);
			
			$positionMiddle = ($rectangleWidth/2) - ($logoImageWidth/2);
			
			// Das geladene Image wird in das Rechteck geladen
			imagecopyresampled($inImage, $loadedImage, $positionMiddle , $positionMiddle, 0, 0, $logoImageWidth, $logoImageWidth,  $loadedImageWidth, $loadedImageHeight);
			
			// Berechnung der Mittigen Position des (weißen Quadrates+des geladenen Bildes)
			$positionMiddle = ($qrCodeImageWidth/2)-($rectangleWidth/2);
			
			// Die Mitte soweit verschieben bis keine Punkte des QR-Codes mehr (zur Hälfte) geschnitten werden
			if($positionMiddle%$size < $size/2){
				$positionMiddle -= $positionMiddle%$size;
			}else{
				$positionMiddle += ($size-($positionMiddle%$size));
			}
			
			// In das Qr-Code Image wird das Quadrat+geladene Image transformiert
			imagecopyresampled($qrCodeImage, $inImage, $positionMiddle , $positionMiddle, 0, 0,$rectangleWidth, $rectangleWidth,  $rectangleWidth, $rectangleWidth);
		}
			
		if(is_null($dstDir)) {
			Header("Content-type: image/png");
		}
		
		return ImagePng($qrCodeImage, $dstDir, 9, PNG_NO_FILTER);
	}
}

?>
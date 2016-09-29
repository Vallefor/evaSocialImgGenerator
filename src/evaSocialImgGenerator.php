<?php
/**
 *
 * Author:  Lipovtsev Dmitry (Vallefor)
 * Email:   madsorcerer@gmail.com
 * Version: 1.0.2
 * License: Creative Commons Attribution NonCommercial (CC-BY-NC)
 *
 * Date: 15.07.16
 * Time: 15:04
 */
namespace Eva\Social;

class imgGenerator
{
	const position_left_top=0;
	const position_center_top=1;
	const position_right_top=2;
	const position_right_center=3;
	const position_right_bottom=4;
	const position_center_bottom=5;
	const position_left_bottom=6;
	const position_left_center=7;
	const position_center_center=8;

	/** @var  \Imagick $im */
	protected $im;

	protected $opts=array();
	function fromImg($path)
	{
		$this->opts["img"]=$path;
		return $this;
	}
	function fromColor($path)
	{
		$this->opts["color"]=$path;
		return $this;
	}
	static function getSocial()
	{
		if(strpos($_SERVER["HTTP_USER_AGENT"],"facebookexternalhit")!==false) {
			$type="facebook";
		} elseif(strpos($_SERVER["HTTP_USER_AGENT"],"vkShare")!==false) {
			$type="vk";
		} elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Twitterbot")!==false) {
			$type="twitter";
		} elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Google")!==false) {
			$type="google_plus";
		} elseif(strpos($_SERVER["HTTP_USER_AGENT"],"OdklBot")!==false) {
			$type="ok";
		} else {
			$type="vk";
		}
		return $type;
	}
	function withoutCrop($colorOrFile,$paddings=0)
	{
		if(is_file($colorOrFile)) {
			$this->opts["without_crop"]["file"]=$colorOrFile;
		} else {
			$this->opts["without_crop"]["color"]=$colorOrFile;
		}
		$this->opts["without_crop"]["paddings"]=$paddings;
		return $this;
	}
	function resizeFor($type)
	{
		if($type=="autodetect") {
			$type=$this::getSocial();
		}
		if($type=="facebook") {
			$this->opts["resize_and_crop"]=array("width"=>1200,"height"=>630);
		}
		if($type=="twitter") {
			$this->opts["resize_and_crop"]=array("width"=>978,"height"=>511);
		}
		if($type=="google_plus") {
			$this->opts["resize_and_crop"]=array("width"=>2120,"height"=>1192);
		}
		if($type=="vk") {
			$this->opts["resize_and_crop"]=array("width"=>537,"height"=>240);
		}
		if($type=="ok") {
			$this->opts["resize_and_crop"]=array("width"=>780,"height"=>585);
		}
		return $this;
	}
	function addBlackOverlay()
	{
		$this->addOverlay("0.5","#000000");
		return $this;
	}
	function addOverlay($opacity,$color)
	{
		$this->opts["overlay"]=array("opacity"=>$opacity,"color"=>$color);
		return $this;
	}
	function setLogo($src,$position,$padding=20,$resize="auto",$opacity=false)
	{
		$this->opts["logo"]=array(
			"src"=>$src,
			"padding"=>$padding,
			"position"=>$position,
			"resize"=>$resize,
			"opacity"=>$opacity
		);
		return $this;
	}
	function parseImg()
	{
		if($this->opts["img"] && is_file($this->opts["img"]) && !$this->opts["img_opened"]) {
			$this->im = new \Imagick($this->opts["img"]);
			$this->opts["img_opened"]=true;
		}
	}
	function parseColor()
	{
		if($this->opts["color"] && !$this->opts["img_opened"]) {
			$this->im = new \Imagick();
			$this->im->newImage(100,100,$this->opts["color"]);
			$this->opts["img_opened"]=true;
		}
	}
	function resizeAndCrop(&$im,$width,$height,$position=imgGenerator::position_center_center)
	{
		$oldGeometry=$im->getImageGeometry();

		$max=max($this->opts["resize_and_crop"]["width"],$this->opts["resize_and_crop"]["height"]);
		//$max=max($oldGeometry["width"],$oldGeometry["height"]);

		if($max==$this->opts["resize_and_crop"]["width"]) {
			$otn=$oldGeometry["height"]/$oldGeometry["width"];
			$width=$max;
			$height=$max*$otn;
			if($height-$this->opts["resize_and_crop"]["height"] < 0) {
				$height=$this->opts["resize_and_crop"]["height"];
				$width=$height/$otn;
				$x=($width-$this->opts["resize_and_crop"]["width"])/2;
			} else {
				$x = 0;
			}
			if($position==imgGenerator::position_center_center) {
				$y=($height-$this->opts["resize_and_crop"]["height"])/2;
			}
		} else {
			$otn=$oldGeometry["width"]/$oldGeometry["height"];
			$height=$max;
			$width=$max*$otn;
			if($width-$this->opts["resize_and_crop"]["width"] < 0) {
				$width=$this->opts["resize_and_crop"]["width"];
				$height=$width/$otn;
				$y=($width-$this->opts["resize_and_crop"]["height"])/2;
			} else {
				$y = 0;
			}
			if($position==imgGenerator::position_center_center) {
				$x=($width-$this->opts["resize_and_crop"]["width"])/2;
			}
		}

		$im->resizeImage($width,$height,\Imagick::FILTER_LANCZOS,1,false);
		/*print_r(array($this->opts["resize_and_crop"]["width"],$this->opts["resize_and_crop"]["height"],$x,$y));
		die();*/
		$im->cropimage($this->opts["resize_and_crop"]["width"],$this->opts["resize_and_crop"]["height"],$x,$y);
	}
	function parseSize()
	{
		if($this->opts["resize_and_crop"]) {
			if($this->opts["without_crop"]) {
				$bgImage=new \Imagick();
				$bgImage->newImage($this->opts["resize_and_crop"]["width"], $this->opts["resize_and_crop"]["height"],$this->opts["without_crop"]["color"]);
				$this->im->resizeImage($this->opts["resize_and_crop"]["width"], $this->opts["resize_and_crop"]["height"],\Imagick::FILTER_LANCZOS,1,true);
				$geometry=$this->im->getImageGeometry();
				$x=intval(($this->opts["resize_and_crop"]["width"]-$geometry["width"])/2);
				$y=intval(($this->opts["resize_and_crop"]["height"]-$geometry["height"])/2);
				$bgImage->compositeImage($this->im, \Imagick::COMPOSITE_OVER, $x ,$y);
				$this->im=$bgImage;
			} else {
				$this->resizeAndCrop($this->im, $this->opts["resize_and_crop"]["width"], $this->opts["resize_and_crop"]["height"]);
			}
		}
	}
	function parseOverlay()
	{
		if($this->opts["overlay"]) {
			$overlay=new \Imagick();
			$geometry=$this->im->getImageGeometry();
			$color=new \ImagickPixel($this->opts["overlay"]["color"]);
			//$color=new \ImagickPixel("rgba(0,0,0,0.1)");
			//$color->setColorValue(\Imagick::COLOR_ALPHA,0.4);
			$overlay->newImage($geometry["width"],$geometry["height"],$color);
			$overlay->setImageOpacity($this->opts["overlay"]["opacity"]);


			$this->im->compositeimage($overlay,\Imagick::COMPOSITE_DEFAULT,0,0);
		}
	}
	function parseText()
	{
		if($this->opts["text"]) {
			/** @var imgTextGenerator $textOb */
			foreach($this->opts["text"] as $textOb) {
				$textOb->compositeTextTo($this->im);
			}
		}
	}
	function enableCache($str)
	{
		$this->opts["enable_cache"]=$str;
		return $this;
	}
	function parseLogo()
	{
		if($this->opts["logo"]) {
			if(is_file($this->opts["logo"]["src"])) {
				$im=new \Imagick($this->opts["logo"]["src"]);

				$geometry=$this->im->getImageGeometry();

				if($this->opts["logo"]["resize"]=="auto") {
					$im->resizeImage(intval($geometry["width"]*0.25),intval($geometry["height"]*0.25),\Imagick::FILTER_LANCZOS,1,true);
				}

				$logoGemetry=$im->getImageGeometry();

				if(!is_array($this->opts["logo"]["padding"])) {
					if (strpos($this->opts["logo"]["padding"], "%")) {
						$mult = intval($this->opts["logo"]["padding"]) / 100;
						$this->opts["logo"]["padding"] = intval($geometry["width"] * $mult);
					}

					$padding["left"] = $this->opts["logo"]["padding"];
					$padding["top"] = $this->opts["logo"]["padding"];
					$padding["right"] = $this->opts["logo"]["padding"];
					$padding["bottom"] = $this->opts["logo"]["padding"];
				} else {
					foreach($this->opts["logo"]["padding"] as &$val) {
						if (strpos($val, "%")) {
							$mult = intval($val) / 100;
							$val = intval($geometry["width"] * $mult);
						}
					}
					unset($val);
					$padding["top"] = $this->opts["logo"]["padding"][0];
					$padding["right"] = $this->opts["logo"]["padding"][1];
					$padding["bottom"] = $this->opts["logo"]["padding"][2];
					$padding["left"] = $this->opts["logo"]["padding"][3];

				}

				if(
					$this->opts["logo"]["position"] == $this::position_left_top ||
					$this->opts["logo"]["position"] == $this::position_left_center ||
					$this->opts["logo"]["position"] == $this::position_left_bottom
				) {
					$x=0 + $padding["left"];
				}

				if(
					$this->opts["logo"]["position"] == $this::position_right_top ||
					$this->opts["logo"]["position"] == $this::position_right_center ||
					$this->opts["logo"]["position"] == $this::position_right_bottom
				) {
					$x=$geometry["width"]-$logoGemetry["width"] - $padding["right"];
				}

				if(
					$this->opts["logo"]["position"] == $this::position_right_bottom ||
					$this->opts["logo"]["position"] == $this::position_left_bottom ||
					$this->opts["logo"]["position"] == $this::position_center_bottom
				) {
					$y=$geometry["height"]-$logoGemetry["height"] - $padding["bottom"];
				}
				if(
					$this->opts["logo"]["position"] == $this::position_left_top ||
					$this->opts["logo"]["position"] == $this::position_right_top ||
					$this->opts["logo"]["position"] == $this::position_center_top
				) {
					$y=0+$padding["top"];
				}
				if(
					$this->opts["logo"]["position"] == $this::position_center_center ||
					$this->opts["logo"]["position"] == $this::position_center_bottom ||
					$this->opts["logo"]["position"] == $this::position_center_top
				) {
					$x=($geometry["width"]-$logoGemetry["width"])/2;
				}
				if(
					$this->opts["logo"]["position"] == $this::position_center_center ||
					$this->opts["logo"]["position"] == $this::position_left_center ||
					$this->opts["logo"]["position"] == $this::position_right_center
				) {
					$y=($geometry["height"]-$logoGemetry["height"])/2;
				}

				/*print_r(array($x,$y));
				die();*/
				if($this->opts["logo"]["opacity"]) {
					$im->setImageOpacity($this->opts["logo"]["opacity"]);
				}

				$this->im->compositeimage($im,\Imagick::COMPOSITE_DEFAULT,$x,$y);
			}
		}
	}
	function addText(imgTextGenerator $imgText)
	{
		if(!$this->opts["text"]) {
			$this->opts["text"]=array();
		}
		$this->opts["text"][]=$imgText;
		return $this;
	}
	function parse()
	{
		$this->parseImg();
		$this->parseColor();
		$this->parseSize();
		$this->parseOverlay();
		$this->parseLogo();
		$this->parseText();
	}
	function show()
	{
		$this->parse();
		$this->im->setimageformat("jpg");
		$this->im->setimagecompression(95);
		$this->im->setimagecompressionquality(95);
		header('Content-type: image/jpeg');

		if($this->opts["enable_cache"]) {
			$fileName=md5(serialize($this->opts));

			if(is_file("{$this->opts["enable_cache"]}/{$fileName}.jpg")) {
				echo file_get_contents("{$this->opts["enable_cache"]}/{$fileName}.jpg");
			} else {
				$this->im->writeimage("{$this->opts["enable_cache"]}/{$fileName}.jpg");
				echo $this->im;
			}
		} else {
			echo $this->im;
		}
	}
	function getPath()
	{
		$this->parse();

		$this->im->setimageformat("jpg");
		$fileName=md5(serialize($this->opts));
		$file="{$this->opts["enable_cache"]}/{$fileName}.jpg";
		//unlink($_SERVER["DOCUMENT_ROOT"]."/upload/social_images/{$fileName}.png");
		if(is_file($file)) {
			return str_replace($_SERVER["DOCUMENT_ROOT"],"",$file);
		}
		$this->im->writeimage("{$this->opts["enable_cache"]}/{$fileName}.jpg");

		return str_replace($_SERVER["DOCUMENT_ROOT"],"",$file);
	}
}
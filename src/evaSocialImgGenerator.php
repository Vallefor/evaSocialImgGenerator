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
	function withoutCrop($colorOrFile,$paddings=0,$position=imgGenerator::position_center_center)
	{
		if(is_file($colorOrFile)) {
			$this->opts["without_crop"]["file"]=$colorOrFile;
		} else {
			$this->opts["without_crop"]["color"]=$colorOrFile;
		}
		$this->opts["without_crop"]["paddings"]=$paddings;
		$this->opts["without_crop"]["position"]=$position;
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
			$this->opts["resize_and_crop"]=array("width"=>780,"height"=>385);
			//$this->opts["resize_and_crop"]=array("width"=>780,"height"=>585);
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
		$im->cropimage($this->opts["resize_and_crop"]["width"],$this->opts["resize_and_crop"]["height"],$x,$y);
	}
	function getPaddings($imgGeometry,$setPaddings)
	{
		if(!is_array($setPaddings)) {
			$paddings["top"]=$setPaddings;
			$paddings["left"]=$setPaddings;
			$paddings["right"]=$setPaddings;
			$paddings["bottom"]=$setPaddings;
		} else {
			$paddings=$setPaddings;
		}
		foreach ($paddings as $ind=>&$val) {
			if(strpos($val,"%")) {
				$val=intval($val)/100;
				if($ind=="left" || $ind=="right") {
					$val=intval($imgGeometry["width"]*$val);
				} else {
					$val=intval($imgGeometry["height"]*$val);
				}
			}
		}
		unset($val);

		return $paddings;
	}
	function getXY($imgGeometry,$itemGeometry,$paddings,$position)
	{
		$paddings=$this->getPaddings($imgGeometry,$paddings);

		$x=0;
		$y=0;
		switch($position) {
			case imgGenerator::position_left_top:
				$x=0+$paddings["left"];
				$y=0+$paddings["top"];
			break;
			case imgGenerator::position_center_top:
				$x=($imgGeometry["width"]-$itemGeometry["width"])/2+$paddings["left"];
				$y=0+$paddings["top"];
			break;
			case imgGenerator::position_right_top:
				$x=$imgGeometry["width"]-$itemGeometry["width"]-$paddings["right"];
				$y=0+$paddings["top"];
			break;
			case imgGenerator::position_right_center:
				$x=$imgGeometry["width"]-$itemGeometry["width"]-$paddings["right"];
				$y=($imgGeometry["height"]-$itemGeometry["height"])/2+$paddings["top"];
			break;
			case imgGenerator::position_right_bottom:
				$x=$imgGeometry["width"]-$itemGeometry["width"]-$paddings["right"];
				$y=$imgGeometry["height"]-$itemGeometry["height"]-$paddings["bottom"];
			break;
			case imgGenerator::position_center_bottom:
				$x=($imgGeometry["width"]-$itemGeometry["width"])/2+$paddings["left"];
				$y=$imgGeometry["height"]-$itemGeometry["height"]-$paddings["bottom"];
			break;
			case imgGenerator::position_left_bottom:
				$x=0+$paddings["left"];
				$y=$imgGeometry["height"]-$itemGeometry["height"]-$paddings["bottom"];
			break;
			case imgGenerator::position_left_center:
				$x=0+$paddings["left"];
				$y=($imgGeometry["height"]-$itemGeometry["height"])/2+$paddings["top"];
			break;
		}

		return array("x"=>$x, "y"=>$y);
	}
	function parseSize()
	{
		if($this->opts["resize_and_crop"]) {
			if($this->opts["without_crop"]) {
				$padding=$this->getPaddings($this->opts["resize_and_crop"],$this->opts["without_crop"]["paddings"]);
				$bgImage=new \Imagick();
				$bgImage->newImage($this->opts["resize_and_crop"]["width"], $this->opts["resize_and_crop"]["height"],$this->opts["without_crop"]["color"]);
				$this->im->resizeImage(
					$this->opts["resize_and_crop"]["width"]-$padding["left"]-$padding["right"],
					$this->opts["resize_and_crop"]["height"]-$padding["top"]-$padding["bottom"],
					\Imagick::FILTER_LANCZOS,1,true
				);
				$geometry=$this->im->getImageGeometry();

				$xy=$this->getXY($this->opts["resize_and_crop"],$geometry,$this->opts["without_crop"]["paddings"],$this->opts["without_crop"]["position"]);

				$bgImage->compositeImage($this->im, \Imagick::COMPOSITE_OVER, $xy["x"], $xy["y"]);
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
				} elseif(strpos($this->opts["logo"]["resize"],"x")) {
					$wh=explode("x",$this->opts["logo"]["resize"]);
					if(strpos($wh[0],"/")) {
						$ex=explode("/",$wh[0]);
						$width=$ex[0]/$ex[1]*$geometry["width"];
					} else {
						$width=intval($wh[0]);
					}
					if(strpos($wh[1],"/")) {
						$ex=explode("/",$wh[1]);
						$height=$ex[0]/$ex[1]*$geometry["height"];
					} else {
						$height=intval($wh[1]);
					}
					$im->resizeImage(intval($width),intval($height),\Imagick::FILTER_LANCZOS,1,true);

				} elseif(strpos($this->opts["logo"]["resize"],"/")) {
					$ex=explode("/",$this->opts["logo"]["resize"]);
					$width=$ex[0]/$ex[1]*$geometry["width"];
					$height=$ex[0]/$ex[1]*$geometry["height"];
					$im->resizeImage(intval($width),intval($height),\Imagick::FILTER_LANCZOS,1,true);
				}

				$logoGemetry=$im->getImageGeometry();

				if(!is_array($this->opts["logo"]["padding"])) {
					/*if (strpos($this->opts["logo"]["padding"], "%")) {
						$mult = intval($this->opts["logo"]["padding"]) / 100;
						$this->opts["logo"]["padding"] = intval($geometry["width"] * $mult);
					}*/

					$padding["left"] = $this->opts["logo"]["padding"];
					$padding["top"] = $this->opts["logo"]["padding"];
					$padding["right"] = $this->opts["logo"]["padding"];
					$padding["bottom"] = $this->opts["logo"]["padding"];
				} else {
					$padding["top"] = $this->opts["logo"]["padding"][0];
					$padding["right"] = $this->opts["logo"]["padding"][1];
					$padding["bottom"] = $this->opts["logo"]["padding"][2];
					$padding["left"] = $this->opts["logo"]["padding"][3];
				}
				foreach($padding as $ind=>&$val) {
					if (strpos($val, "%")) {
						$mult = intval($val) / 100;
						if($ind=="left" || $ind=="right") {
							$val = intval($geometry["width"] * $mult);
						} else {
							$val = intval($geometry["height"] * $mult);
						}
					}
				}
				unset($val);

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
					$x=($geometry["width"]-$logoGemetry["width"])/2+$padding["left"];
				}
				if(
					$this->opts["logo"]["position"] == $this::position_center_center ||
					$this->opts["logo"]["position"] == $this::position_left_center ||
					$this->opts["logo"]["position"] == $this::position_right_center
				) {
					$y=($geometry["height"]-$logoGemetry["height"])/2+$padding["top"];
				}

				if($this->opts["logo"]["opacity"]) {
					$im->setImageFormat("png");
					if($im->getImageFormat()!="png") {
						$im->setImageOpacity(255);
					}
					$im->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $this->opts["logo"]["opacity"], \Imagick::CHANNEL_ALPHA);
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
		$fileName=md5(serialize($this->opts));
		$file="{$this->opts["enable_cache"]}/{$fileName}.jpg";
		//unlink($_SERVER["DOCUMENT_ROOT"]."/upload/social_images/{$fileName}.png");
		if(is_file($file)) {
			return str_replace($_SERVER["DOCUMENT_ROOT"],"",$file);
		}

		$this->parse();
		$this->im->setimageformat("jpg");
		$this->im->writeimage("{$this->opts["enable_cache"]}/{$fileName}.jpg");

		return str_replace($_SERVER["DOCUMENT_ROOT"],"",$file);
	}
}
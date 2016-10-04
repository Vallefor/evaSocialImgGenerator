<?php
/**
 * Created by PhpStorm.
 * User: vallefor
 * Date: 29.09.16
 * Time: 16:59
 */

namespace Eva\Social;

class imgTextGenerator
{
	protected $opts=array();

	function seTextShadow($color="#000000",$opacity=75,$sigma=5,$x=5,$y=5)
	{
		$this->opts["big_text_shadow"]=array("color"=>$color,"opacity"=>$opacity,"sigma"=>$sigma,"x"=>$x,"y"=>$y);
		return $this;
	}
	function setBackground($color,$paddings)
	{
		$this->opts["big_text_bg"]["color"]=$color;
		$this->opts["big_text_bg"]["paddings"]=$paddings;
		return $this;
	}
	function setFont($font)
	{
		$this->opts["big_text_font"]=$font;
		return $this;
	}

	/**
	 * Добавляет текст на картинку.
	 *
	 *
	 * @param $str
	 * @param string $color
	 * @param int $position
	 * @param int $fontSize
	 * @param int $padding
	 * @param int $style
	 * @param int $weight
	 * @return $this
	 */
	function setText($str, $color="#ffffff", $position=imgGenerator::position_center_center, $fontSize=50, $padding=20, $style=\Imagick::STYLE_NORMAL, $weight=300)
	{
		$this->opts["big_text"]=array(
			"text"=>$str,
			"color"=>$color,
			"position"=>$position,
			"font_size"=>$fontSize,
			"padding"=>$padding,
			"style"=>$style,
			"weight"=>$weight,
		);
		return $this;
	}
	function splitToLines($draw,$text,$maxWidth)
	{
		$ex=explode(" ",$text);
		$checkLine="";
		$textImage=new \Imagick();
		foreach ($ex as $val) {
			if($checkLine) {
				$checkLine.=" ";
			}
			$checkLine.=$val;
			$metrics=$textImage->queryFontMetrics($draw, $checkLine);
			if($metrics["textWidth"]>$maxWidth) {
				$checkLine=preg_replace('/\s(?=\S*$)/',"\n",$checkLine);
			}
		}
		return $checkLine;
	}
	function compositeTextTo($im)
	{
		if($this->opts["big_text"] || $this->opts["small_text"]) {
			$geometry=$im->getImageGeometry();

			if(!is_array($this->opts["big_text"]["padding"])) {
				$padding["left"] = $this->opts["big_text"]["padding"];
				$padding["top"] = $this->opts["big_text"]["padding"];
				$padding["right"] = $this->opts["big_text"]["padding"];
				$padding["bottom"] = $this->opts["big_text"]["padding"];

			} else {
				$padding["top"] = $this->opts["big_text"]["padding"][0];
				$padding["right"] = $this->opts["big_text"]["padding"][1];
				$padding["bottom"] = $this->opts["big_text"]["padding"][2];
				$padding["left"] = $this->opts["big_text"]["padding"][3];
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

			$draw=new \ImagickDraw();
			$draw->setFont($this->opts["big_text_font"]?$this->opts["big_text_font"]:'Arial');

			if($this->opts["big_text"]["font_size"]=="auto") {
				$fs=intval($geometry["height"]/10);
			} else {
				if(strpos($this->opts["big_text"]["font_size"],"1/")===0) {
					$fs=intval(str_replace("1/","",$this->opts["big_text"]["font_size"]));
					$fs=intval($geometry["height"]/$fs);
				} else {
					$fs = $this->opts["big_text"]["font_size"];
				}
			}
			$draw->setFontSize($fs);

			$draw->setFillColor(new \ImagickPixel($this->opts["big_text"]["color"]));
			$draw->setStrokeAntialias(true);
			$draw->setTextAntialias(true);

			$bgPaddings=array(0,0,0,0);
			if($this->opts["big_text_bg"]["paddings"]) {
				if (!is_array($this->opts["big_text_bg"]["paddings"])) {
					$this->opts["big_text_bg"]["paddings"] = array(
						$this->opts["big_text_bg"]["paddings"],
						$this->opts["big_text_bg"]["paddings"],
						$this->opts["big_text_bg"]["paddings"],
						$this->opts["big_text_bg"]["paddings"]
					);
				}
				foreach ($this->opts["big_text_bg"]["paddings"] as $pInd => &$padd) {
					if (strpos($padd, "%")) {
						if ($pInd == 0 || $pInd == 2) {
							$padd = intval(intval($padd) / 100 * $geometry["height"]);
						} else {
							$padd = intval(intval($padd) / 100 * $geometry["width"]);
						}
					}
				}
				unset($padd);
				$bgPaddings=$this->opts["big_text_bg"]["paddings"];
			}

			$this->opts["big_text"]["text"]=$this->splitToLines($draw,$this->opts["big_text"]["text"],$geometry["width"]-$padding["left"]-$padding["right"]-$bgPaddings[1]-$bgPaddings[3]);

			if(
				$this->opts["big_text"]["position"]==imgGenerator::position_left_top
				||
				$this->opts["big_text"]["position"]==imgGenerator::position_left_center
				||
				$this->opts["big_text"]["position"]==imgGenerator::position_left_bottom
			) {
				$draw->setTextAlignment(\Imagick::ALIGN_LEFT);
			}
			if(
				$this->opts["big_text"]["position"]==imgGenerator::position_right_top
				||
				$this->opts["big_text"]["position"]==imgGenerator::position_right_center
				||
				$this->opts["big_text"]["position"]==imgGenerator::position_right_bottom
			) {
				$draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
			}
			if(
				$this->opts["big_text"]["position"]==imgGenerator::position_center_center
				||
				$this->opts["big_text"]["position"]==imgGenerator::position_center_top
				||
				$this->opts["big_text"]["position"]==imgGenerator::position_center_bottom
				||
				$this->opts["big_text"]["position"]==imgGenerator::position_center_center
			) {
				$draw->setTextAlignment(\Imagick::ALIGN_CENTER);
			}


			$textIm=new \Imagick();

			$metrics=$textIm->queryFontMetrics($draw, $this->opts["big_text"]["text"]);
			$baseline = $metrics['boundingBox']['y2'];
			$textwidth = $metrics['textWidth'] + 2 * $metrics['boundingBox']['x1'];
			$textheight = $metrics['textHeight'] + $metrics['descender'];

			$draw->annotation ($textwidth*1.3, $textheight*1.3, $this->opts["big_text"]["text"]);

			/*print_r(array($baseline,$textwidth,$textheight));
			print_r($metrics);
			die();*/
			//Сделать переносы

			$textImage=new \Imagick();

			$textImage->newImage($textwidth*3,$textheight*3,"none");
			$textImage->drawImage($draw);
			//$textImage->annotateImage($draw, $baseline, $metrics["boundingBox"]["x2"], 0, $this->opts["big_text"]["text"]);
			if($this->opts["big_text_shadow"]) {
				$shadow_layer = clone $textImage;
				$shadow_layer->setImageBackgroundColor(new \ImagickPixel($this->opts["big_text_shadow"]["color"]));
				$shadow_layer->shadowImage($this->opts["big_text_shadow"]["opacity"], $this->opts["big_text_shadow"]["sigma"], $this->opts["big_text_shadow"]["x"], $this->opts["big_text_shadow"]["y"]);
				$shadow_layer->compositeImage($textImage, \Imagick::COMPOSITE_OVER, 0, 0);
				$textImage=clone $shadow_layer;
			}
			$textImage->trimImage(0);
			$textImage->setImagePage(0, 0, 0, 0);
			$textGeometry=$textImage->getImageGeometry();

			if($this->opts["big_text_bg"]["color"]) {
				if(!is_array($this->opts["big_text_bg"]["paddings"])) {
					$this->opts["big_text_bg"]["paddings"]=array(
						$this->opts["big_text_bg"]["paddings"],
						$this->opts["big_text_bg"]["paddings"],
						$this->opts["big_text_bg"]["paddings"],
						$this->opts["big_text_bg"]["paddings"]
					);
				}
				foreach($this->opts["big_text_bg"]["paddings"] as $pInd=>&$padd) {
					if (strpos($padd, "%")) {
						if($pInd==0 || $pInd==2) {
							$padd = intval(intval($padd) / 100 * $geometry["height"]);
						} else {
							$padd = intval(intval($padd) / 100 * $geometry["width"]);
						}
					}
				}
				unset($padd);

				$bgImage=new \Imagick();
				$bgImage->newImage(
					$textGeometry["width"]+$this->opts["big_text_bg"]["paddings"][1]+$this->opts["big_text_bg"]["paddings"][3],
					$textGeometry["height"]+$this->opts["big_text_bg"]["paddings"][0]+$this->opts["big_text_bg"]["paddings"][2],
					$this->opts["big_text_bg"]["color"]
				);

				$bgImage->compositeImage($textImage, \Imagick::COMPOSITE_OVER, $this->opts["big_text_bg"]["paddings"][3], $this->opts["big_text_bg"]["paddings"][0]);
				$textImage=$bgImage;
				$textGeometry=$textImage->getImageGeometry();
			}

			if($this->opts["big_text"]["position"]==imgGenerator::position_center_center) {
				$x = ($geometry["width"] - $textGeometry["width"]) / 2 + $padding["left"];
				$y = ($geometry["height"] - $textGeometry["height"]) / 2  + $padding["top"];
			}
			if($this->opts["big_text"]["position"]==imgGenerator::position_left_top) {
				$x = 0 + $padding["left"];
				$y = 0 + $padding["top"];
			}
			if($this->opts["big_text"]["position"]==imgGenerator::position_center_top) {
				$x = ($geometry["width"] - $textGeometry["width"]) / 2 + $padding["left"];
				$y = 0 + $padding["top"];
			}
			if($this->opts["big_text"]["position"]==imgGenerator::position_right_top) {
				$x = $geometry["width"] - $textGeometry["width"] - $padding["right"];
				$y = 0 + $padding["top"];
			}
			if($this->opts["big_text"]["position"]==imgGenerator::position_right_center) {
				$x = $geometry["width"] - $textGeometry["width"] - $padding["right"];
				$y = ($geometry["height"] - $textGeometry["height"]) / 2 + $padding["top"];
			}
			if($this->opts["big_text"]["position"]==imgGenerator::position_right_bottom) {
				$x = $geometry["width"] - $textGeometry["width"] - $padding["right"];
				$y = $geometry["height"] - $textGeometry["height"] - $padding["bottom"];
			}
			if($this->opts["big_text"]["position"]==imgGenerator::position_center_bottom) {
				$x = ($geometry["width"] - $textGeometry["width"]) / 2 + $padding["left"];
				$y = $geometry["height"] - $textGeometry["height"] - $padding["bottom"];
			}
			if($this->opts["big_text"]["position"]==imgGenerator::position_left_bottom) {
				$x = 0 + $padding["left"];
				$y = $geometry["height"] - $textGeometry["height"] - $padding["bottom"];
			}
			if($this->opts["big_text"]["position"]==imgGenerator::position_left_center) {
				$x = 0 + $padding["left"];
				$y = ($geometry["height"] - $textGeometry["height"]) / 2 + $padding["top"];
			}


			$im->compositeimage($textImage,\Imagick::COMPOSITE_DEFAULT,$x,$y);
			//die();
		}
	}
}
<?php
//Copyright DMTSOFT (c) - http://dmtsoft.ru
//Make by DMT


class CCaptcha{
var $keystring;
 function CCaptcha(){
 require(dirname(__FILE__).'/config.php');
			while(true){
				$this->keystring='';
				for($i=0;$i<$length;$i++)
					$this->keystring.=$use_symbols{mt_rand(0,$use_symbols_len-1)};
				if(!preg_match('/cb|rn|rm|mm|co|do|db|qp|qb|dp|ww/', $this->keystring)) break;
			}
			//$im=imagecreatefrompng(dirname(__FILE__)."/fonts/back".mt_rand(1,$back_count).".png");
			$im=imagecreatefrompng(dirname(__FILE__)."/fonts/white.png");
			$imb=imagecreatefrompng(dirname(__FILE__)."/fonts/black.png");
   			$border_color = imagecolorallocate($im, 255, 0, 0);
			$width = imagesx($im);
			$height = imagesy($im);			
			$angle = 0;
			$px=$margin_left;
			if ($plain){
			imagettftext($im, mt_rand($font_size_min,$font_size_max),$angle, $px, $margin_top, $font_color,dirname(__FILE__)."/fonts/font".mt_rand(1,$font_count).".ttf",$this->keystring);
			} else {
				for($i=0;$i<$length;$i++){
						if ($rotate_simbol){
    						$angle=mt_rand(-45, 45);
    						if ($angle<0) $angle=360+$angle;
						}
						if ($simbol_color == "random") {
                            $font_color = imagecolorresolve($im, rand(0, 150), rand(0, 150), rand(0, 150));
						} else {
						    $font_color = imagecolorresolve($im, $scolor[$simbol_color][0], $scolor[$simbol_color][1], $scolor[$simbol_color][2]);
						    $font_color_b = imagecolorresolve($imb, 255, 255, 255);
						}
						$sz = mt_rand($font_size_min,$font_size_max);
						$font_name = mt_rand(1, $font_count);
						imagettftext($im, $sz, $angle, $px, $margin_top, $font_color,dirname(__FILE__)."/fonts/font".$font_name.".ttf",$this->keystring[$i]);
						imagettftext($imb, $sz, $angle, $px, $margin_top, $font_color_b,dirname(__FILE__)."/fonts/font".$font_name.".ttf",$this->keystring[$i]);
   						$px += $font_width + mt_rand($rand_bsimb_min, $rand_bsimb_max); 
				}
			}
		$rand=mt_rand(0,1);
		if ($rand)$rand=-1; else $rand=1;
		image_make_pomexi($im,$imb, $font_size_min,$shum_count);
		//$im=ImageSkew($im,$im,mt_rand(-10,10),mt_rand(0,360));
		wave_region($im, $imb, 0,0,$width,$height,$rand*mt_rand($amplitude_min,$amplitude_max),mt_rand(40,50));
		sum_images($im, $imb);
		header('Expires: Sat, 30 Jan 2000 08:00:00 GMT'); 
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header('Cache-Control: no-store, no-cache, must-revalidate'); 
		header('Cache-Control: post-check=0, pre-check=0', FALSE); 
		header('Pragma: no-cache');  
		if(function_exists("imagepng")){
			header("Content-Type: image/x-png");
			imagepng($im);
		}else if(function_exists("imagegif")){
			header("Content-Type: image/gif");
			imagegif($im);
		}elseif(function_exists("imagejpeg")){
			header("Content-Type: image/jpeg");
			imagejpeg($im, null, $jpeg_quality);
		}
 }
 

function getKeyString(){
		return $this->keystring;
	}
}

function sum_images(&$im, $imb) {
	$start = rand(50, 100);
	$end = rand(170, 160);
	$path = array();
	$max = 0;
	$min = 245;
	$invert = ( (rand(0, 11) % 2) == 0 );
	for ($i = 0, $m = 0, $n = 0; $i < 88; $i++) {
		if ($m == 0) {
			$m = $k = rand($start, $end);
			$n = rand(-1, 1);
		}
		 else { 
		 	 if ($i % rand(1, 11) == 0) {
		 	 	$n = rand(-1, 1);
		 	 }
		     $m = $k = $m + $n;
		 }
		if ($k > $max) $max = $k;
		if ($k < $min) $min = $k;
		$path[] = $k;
	}
	if ($invert) {
		$buf = $im;
		$im = $imb;
		$imb = $buf;
	}
    imagecopyresampled($im, $imb, 0, 0, 0, 0, $min, 88, $min, 88);
	for ($i = 0; $i < count($path); $i++) {
		imagecopyresampled($im, $imb, $min, $i, $min, $i, $path[$i] - $min, 1, $path[$i] - $min, 1);
	}
}
 

function image_make_pomexi(&$im, &$imb, $size,$colch){
   $max_x=imagesx($im);
   $max_y=imagesy($im);
	for ($i=0;$i<$colch;$i++){
	    $size=mt_rand(10,$size);
	    $px1=mt_rand(0,$max_x);
		$py1=mt_rand(0,$max_y);
		$col=imagecolorresolve($im, rand(1,255), rand(1,255), rand(1,255)); 
		$pk1=mt_rand(0,1);
				if ($pk1===0)$pk1=-1; else $pk1=1;
				$pk2=mt_rand(0,1);
				if ($pk2===0)$pk2=-1; else $pk2=1;
				switch (mt_rand(1,3)){
    				case 1: imagerectangle($im,$px1,$py1,$px1+$size*$pk1,$py1+$size*$pk2,$col);imagerectangle($imb,$px1,$py1,$px1+$size*$pk1,$py1+$size*$pk2,$col); break;
    				case 2: imageline($im,$px1,$py1,$px1+$size*$pk1,$py1+$size*$pk2,$col);imageline($imb,$px1,$py1,$px1+$size*$pk1,$py1+$size*$pk2,$col); break;
    				case 3: imageellipse($im,$px1,$py1,$size*$pk1,$size*$pk2,$col);imageellipse($imb,$px1,$py1,$size*$pk1,$size*$pk2,$col); break;
				}
			}	
	}

function wave_region($img,  $imb, $x, $y, $width, $height,$amplitude = 4.5,$period = 30){
        $mult = 2;
        $img2 = imagecreatetruecolor($width * $mult, $height * $mult);
        $img3 = imagecreatetruecolor($width * $mult, $height * $mult);
        imagecopyresampled ($img2, $img,0,0,$x,$y,$width * $mult,$height * $mult,$width, $height);
        imagecopyresampled ($img3, $imb,0,0,$x,$y,$width * $mult,$height * $mult,$width, $height);
        for ($i = 0;$i < ($width * $mult);$i += 2) {
           imagecopy($img2,$img2,$x + $i - 2,$y + sin($i / $period) * $amplitude,$x + $i,$y, 2,($height * $mult));
           imagecopy($img3,$img3,$x + $i - 2,$y + sin($i / $period) * $amplitude,$x + $i,$y, 2,($height * $mult));
        }
        imagecopyresampled($img,$img2,$x,$y,0,0,$width, $height,$width * $mult,$height * $mult);
        imagecopyresampled($imb,$img3,$x,$y,0,0,$width, $height,$width * $mult,$height * $mult);
        imagedestroy($img2);
        imagedestroy($img3);
 }
 
 function ImageSkew($pImage,$iSource, $pAngle, $pDirection = 0) {  
  // Source image  
  $width = imagesx($pImage);
  $height = imagesy($pImage); 
  $iCanvas = @imagecreate($width, $height);  
  $cCyan = imagecolorallocate($iCanvas, 255, 255, 255);  
  imagefill($iCanvas, 0, 0, $cCyan);  
  $diff = ($pAngle / 90);  
  $currentHeight = $height;  
  $currentY = 0; 
  if ($pDirection == 1) { 
    $currentHeight = 0;  
    $currentY = $height; 
  } 
  for ($i = 0; $i < $width; $i++) {  
    if ($pDirection == 0) { 
      imagecopyresampled($iCanvas, $iSource, $i, $currentY, $i, 0, 1, $currentHeight, 1, $height);  
    } else { 
      imagecopyresampled($iCanvas, $iSource, ($width - $i), $currentY, ($width - $i), 0, 1, $currentHeight, 1, $height);  
    } 
    if ($pDirection == 0) { 
      $currentHeight = $currentHeight - ($diff * 2);  
      $currentY = ($height - $currentHeight) / 2;  
    } else { 
      $currentHeight = $height - ( $i * ($diff * 2) ); 
      $currentY = ($height - $currentHeight) / 2;  
    } 
  }
  return $iCanvas;  
} 
 
?>
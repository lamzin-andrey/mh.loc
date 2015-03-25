<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/dbc.php";
$rq = mysql_query("UPDATE failstat SET old_browser = old_browser + 1 WHERE id = 1");

$im=imagecreatefrompng(dirname(__FILE__)."/fonts/e.png");
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
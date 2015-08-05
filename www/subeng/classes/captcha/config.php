<?php
$use_symbols = "012345679abcdefghijhkmntwpuvxyz";
$use_symbols_len=strlen($use_symbols);

$simbol_color='brown'; //red, blue, green или random - случайный

$scolor=array('red'=>array(255,100,100),
'green'=>array(100,255,100),
'blue'=>array(26, 26, 125),
'brown'=>array(100, 58, 14),
'black'=>array(0,0,0),
'random'=>array(mt_rand(60,200),mt_rand(60,200),mt_rand(60,200))
);
if ($simbol_color == 'random'){
 $r=230;
 switch(mt_rand(1,3)) {
  case 1:$scolor['random']=array($r,0,0); break;
  case 3:$scolor['random']=array(0,0,$r); break;
  case 2:$scolor['random']=array(0,$r,0); break;
 }
}

$plain = false;

$amplitude_min=0; // Минимальная амплитуда волны
$amplitude_max=0; // Максимальная амплитуда волны

$font_width=22; // Приблизительная ширина символа в пикселях

$font_size_min=43;
$font_size_max=52;

$rand_bsimb_min=10; // Минимальное расстояние между символами (можно отрицательное)
$rand_bsimb_max=20; // Максимальное расстояние между символами

$rotate_simbol = 1; // Поворачивать случайно каждый символ 1 - д

$margin_left=mt_rand(20,20);// отступ слева
$margin_top=mt_rand(50, 64); // отступ сверху

$shum_count=0; // Количество шума
$font_count = 4;// Количество шрифтов в папке DMT_captcha_fonts идущих по порядку от 1 до $font_count
$jpeg_quality = 100; // Качество картинки
$back_count = 4; // Количество фоновых рисунков в папке DMT_captcha_fonts идущих по порядку от 1 до $back_count
$length = mt_rand(5,6);
	// Количество символов случайно от 3 до 4
	// Если Вы укажите символов больше 4, 
	// то увеличте ширину фонового рисунка ./DMT_captcha_fonts/back[все номера].gif	

?>
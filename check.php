header("Content-Type: image/png");
$image = @imagecreatefrompng("./pr.png")
    or die("Невозможно создать поток изображения");

$red = imageColorAllocate($image, 184, 39, 42);
$black = imageColorAllocate($image, 51, 51, 51);

$font_file = './optima.ttf';
$font_avenir = './avenirnextfont.ttf';
$rand = array(175.16,145.11,115.09,143.02);
$rand = $rand[array_rand($rand)];
 
imagefttext($image, 40, 0, 29, 320, $red, $font_file, '-'.$s.' '.$c);
imagefttext($image, 25, 0, 29, 420, $black, $font_avenir, date('d-m-Y'));
imagefttext($image, 28, 0, 29, 830, $black, $font_avenir, ($s+$rand).' '.$c);
imagefttext($image, 25, 0, 29, 1020, $black, $font_avenir, date('d-m-Y'));
 
imagepng($image);
imagedestroy($image);

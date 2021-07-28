 ini_set('display_errors', 1); 
error_reporting(E_ALL);  
header('Content-type: image/png');
$image = imagecreatefrompng('https://github.com/geraldforkin/bbb/blob/main/pr.png?raw=true');
$red = imageColorAllocate($image, 184, 39, 42);
$black = imageColorAllocate($image, 51, 51, 51);

$font_file = 'https://github.com/geraldforkin/bbb/blob/main/optima.ttf?raw=true';
$font_avenir = 'https://github.com/geraldforkin/bbb/blob/main/avenirnextfont.ttf?raw=true';
$rand = array(175.16,145.11,115.09,143.02);
$rand = $rand[array_rand($rand)];
 
imagefttext($image, 40, 0, 29, 320, $red, $font_file, '-'.$_GET['sum'].' '.$_GET['cur']);
imagefttext($image, 25, 0, 29, 420, $black, $font_avenir, date('d-m-Y'));
imagefttext($image, 28, 0, 29, 830, $black, $font_avenir, ($_GET['sum']+$rand).' '.$_GET['cur']);
imagefttext($image, 25, 0, 29, 1020, $black, $font_avenir, date('d-m-Y'));


imagepng($image);
imageDestroy($image);

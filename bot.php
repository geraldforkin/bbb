header("Content-Type: image/png");
$im = @imagecreatefrompng("https://raw.githubusercontent.com/geraldforkin/bbb/main/pr.png")
    or die("Невозможно создать поток изображения");

$red = imageColorAllocate($image, 184, 39, 42);
$black = imageColorAllocate($image, 51, 51, 51);

$background_color = imagecolorallocate($im, 0, 0, 0);
$text_color = imagecolorallocate($im, 233, 14, 91);
imagestring($im, 1, 5, 5,  "A Simple Text String", $text_color);
imagepng($im);
imagedestroy($im);

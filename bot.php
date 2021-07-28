 
header('Content-type: image/png');
$image = imagecreatefrompng('https://github.com/geraldforkin/bbb/blob/main/pr.png?raw=true');
 

imagepng($image);
imageDestroy($image);

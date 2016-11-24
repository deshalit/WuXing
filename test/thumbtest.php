<?php

function makeThumbnail(SplFileInfo $fi)
{
    $thumbnail_width = 134;
    $thumbnail_height = 189;
    $arr_image_details = getimagesize($fi->getPathname()); // full filename (with path)
    $original_width = $arr_image_details[0];
    $original_height = $arr_image_details[1];
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);
    switch(strtolower($arr_image_details['mime'])) {
        case 'image/png':
            $imgt = "ImagePNG";
            $ext = 'png';
            $imgcreatefrom = "ImageCreateFromPNG";
            break;
        case 'image/jpeg':
            $imgt = "ImageJPEG";
            $ext = 'jpg';
            $imgcreatefrom = "ImageCreateFromJPEG";
            break;
        case 'image/gif':
            $imgt = "ImageGIF";
            $ext = 'gif';
            $imgcreatefrom = "ImageCreateFromGIF";
            break;
        default:
            echo 'unknown type'; die();
    }

    if ($imgt) {
        $old_image = $imgcreatefrom($fi->getPathname());
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        $imgt($new_image, $fi->getPath() . '/thumb/' . $fi->getBasename());
    }
}
$dir = 'C:/OpenServer/domains/wuxing.local/photos/';

foreach (glob($dir . "8.*.*") as $filename) {
   echo $filename . '<br/>';   
}    
    /*
$fsi = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
foreach($fsi as $fileInfo) {
    if ($fileInfo->isFile()) {
        makeThumbnail($fileInfo);
    }
}
    */
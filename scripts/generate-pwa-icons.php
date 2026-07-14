<?php

declare(strict_types=1);

function makeIcon(int $size, string $path): void
{
    $img = imagecreatetruecolor($size, $size);
    $bg = imagecolorallocate($img, 11, 106, 57);
    imagefilledrectangle($img, 0, 0, $size, $size, $bg);
    $white = imagecolorallocate($img, 255, 255, 255);
    $font = 5;
    $text = '7';
    $tw = imagefontwidth($font) * strlen($text);
    $th = imagefontheight($font);
    imagestring($img, $font, (int) (($size - $tw) / 2), (int) (($size - $th) / 2), $text, $white);
    imagepng($img, $path);
    imagedestroy($img);
}

$dir = __DIR__.'/../public/icons';
makeIcon(192, $dir.'/icon-192x192.png');
makeIcon(512, $dir.'/icon-512x512.png');

echo "PWA icons written to public/icons/\n";

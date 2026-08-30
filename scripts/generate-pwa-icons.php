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

$root = __DIR__.'/../public';
$dir = $root.'/icons';

if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

makeIcon(192, $dir.'/icon-192x192.png');
makeIcon(512, $dir.'/icon-512x512.png');
makeIcon(180, $root.'/apple-touch-icon.png');
makeIcon(32, $root.'/favicon-32x32.png');
makeIcon(16, $root.'/favicon-16x16.png');
copy($dir.'/icon-192x192.png', $root.'/logo.png');
copy($root.'/favicon-32x32.png', $root.'/favicon.ico');

echo "PWA + favicon assets written to public/ and public/icons/\n";

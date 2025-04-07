<?php
session_start();
function generateCaptcha() {
    $image = imagecreatetruecolor(120, 40);
    $background = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $background);
    
    // Generate random string
    $string = substr(str_shuffle("23456789ABCDEFGHJKLMNPQRSTUVWXYZ"), 0, 6);
    $_SESSION['captcha'] = $string;
    
    // Add noise
    for ($i = 0; $i < 100; $i++) {
        $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imagesetpixel($image, rand(0, 120), rand(0, 40), $color);
    }
    
    // Add random lines
    for ($i = 0; $i < 4; $i++) {
        $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imageline($image, rand(0, 120), rand(0, 40), rand(0, 120), rand(0, 40), $color);
    }
    
    // Add the text (use a system font if no specific font is available)
    $textcolor = imagecolorallocate($image, 0, 0, 0);
    
    // Fallback to system default font if no specific font is found
    $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'; // Common system font path
    if (!file_exists($font)) {
        $font = false; // Use default font if specific font not found
    }
    
    if ($font) {
        imagettftext($image, 20, rand(-10, 10), 10, 30, $textcolor, $font, $string);
    } else {
        imagestring($image, 5, 10, 10, $string, $textcolor);
    }
    
    // Output the image
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// If this file is accessed directly, generate a new CAPTCHA
if (basename($_SERVER['PHP_SELF']) == 'captcha.php') {
    generateCaptcha();
}
?>
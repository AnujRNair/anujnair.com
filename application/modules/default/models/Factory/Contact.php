<?php

class Factory_Contact extends Factory_Model {

    public static function createCaptcha($width, $height, $captcha, $difficulty = 4) {
        /* variables */
        $font_size = $height * 0.50;
        $font = realpath(APPLICATION_PATH . '/../public/font/monofont.ttf');
        $image = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');

        /* set the colours */
        $background_color = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 14, 51, 62);
        $noise_color = imagecolorallocate($image, 28, 102, 125);

        /* generate random dots in background */
        for ($i = 0; $i < ($width * $height) / 3; $i++) {
            imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), 1, 1, ($i % $difficulty == 0
                ? $text_color : $noise_color));
        }
        /* generate random lines in background */
        for ($i = 0; $i < ($width * $height) / 150; $i++) {
            imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), ($i % $difficulty == 0
                ? $text_color : $noise_color));
        }

        /* create textbox and add text */
        $textbox = imagettfbbox($font_size, 0, $font, $captcha) or die('Error in imagettfbbox function');
        $x = ($width - $textbox[4]) / 2;
        $y = ($height - $textbox[5]) / 2;
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, $captcha) or die('Error in imagettftext function');

        /* output captcha image to browser */
        ob_start();
        imagejpeg($image);
        $outputBuffer = ob_get_clean();
        imagedestroy($image);
        return 'data:image/jpeg;base64,' . base64_encode($outputBuffer);
    }

}
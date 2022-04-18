<?php

namespace App\Packages\tools;

class ImageProcess
{
    //生成海报
    public static function createPoster()
    {
        $source_file = imagecreatefromjpeg('./poster.jpg');
        $source_file_width = imagesx($source_file);
        $source_file_height = imagesy($source_file);
        $target_file = imagecreatetruecolor($source_file_width, $source_file_height);
        imagecopyresampled($target_file, $source_file, 0, 0, 0, 0, $source_file_width, $source_file_height, $source_file_width, $source_file_height);
        $code_file = imagecreatefromjpeg('./code.jpg');
        $code_file_width = config('config.posterwh');
        $code_file_height = $code_file_width;
        imagecopyresampled($target_file, $code_file, config('config.posterx'),config('config.postery') , 0, 0, $code_file_width, $code_file_height, imagesx($code_file), imagesy($code_file));
        imagedestroy($code_file);
        imagejpeg($target_file, './posterCode.jpg');
        imagedestroy($target_file);
        imagedestroy($source_file);
        //imagedestroy($code_file);
    }
}

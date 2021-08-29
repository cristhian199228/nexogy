<?php

namespace App\Service;

use Intervention\Image\Facades\Image;

class StickerService
{
    private $img;
    private const WIDTH = 300;
    private const HEIGHT = 300;
    private const RADIUS = 300;

    public function __construct()
    {
        $this->img = Image::canvas(self::WIDTH, self::HEIGHT, array(0, 0, 0));
    }

    public function drawText($text, $y, $fontSize) {
        $this->img->text($text, self::WIDTH / 2, $y, function($font) use ($fontSize) {
            $font->file(public_path('./arial.ttf'));
            $font->size($fontSize);
            $font->color('#fff');
            $font->align('center');
            $font->valign('top');
        });
    }

    public function drawCircle($color) : void {
        $this->img->circle(self::RADIUS, self::WIDTH / 2, self::HEIGHT / 2, function ($draw) use ($color) {
            $draw->background($color);
        });
    }

    public function drawMultiLineText($text, $y, $addY, $fontSize) : void {
        $strExploded = explode(" ", $text);

        $chunks = array_chunk($strExploded, 3);

        foreach ($chunks as $chunk) {
            $chunked_str = implode(' ', $chunk);
            $this->drawText($chunked_str, $y, $fontSize);
            $y += $addY;
        }
    }

    public function getImg(): \Intervention\Image\Image
    {
        return $this->img;
    }

}
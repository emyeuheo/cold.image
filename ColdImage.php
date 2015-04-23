<?php

class ColdImage {
    
    private $imageData = NULL;
    private $height = 0;
    private $width = 0;
    private $hexIndex = NULL;
    private $imageType = NULL;
    
    public function __construct() {
    }
    
    private function getMimeType($path = NULL) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $return = finfo_file($finfo, $path);
        finfo_close($finfo);
        return $return;
    }
    
    public function loadImage($path = NULL) {
        if(!$path || !file_exists($path)) {
            return false;
        }
        
        $mimeType = $this->getMimeType($path);
        
        if($mimeType != 'image/jpeg' && $mimeType != 'image/png') {
            return false;
        }
        
        // Start loading
        
        // Type check
        if($mimeType == 'image/jpeg') {
            $this->imageData = imagecreatefromjpeg($path);
        }
        
        if($mimeType == 'image/png') {
            $this->imageData = imagecreatefrompng($path);
        }
        
        if(!$this->imageData) {
            return false;
        }
        
        // Set property
        $this->imageType = $mimeType;
        
        $this->height = imagesy($this->imageData);
        $this->width = imagesx($this->imageData);
    }
    
    public function saveResToFile($res = NULL, $path = NULL) {
        if(!$path || !$res) {
            return false;
        }
        
        imagepng($res, $path);
    }
    
    public function colorExtract() {
        if($this->hexIndex != NULL) {
            return $this->hexIndex;
        }
        
        for($i = 0; $i<$this->height; $i++) {
            for($j = 0; $j<$this->width; $j++) {
                $this->hexIndex[imagecolorat($this->imageData, $j, $i)]++;
            }
        }
        
        arsort($this->hexIndex);
        
        return $this->hexIndex;
    }
    
    public function inverseColor() {
        if(!$this->imageData) {
            return false;
        }
        
        $res = imagecreatetruecolor($this->width, $this->height);
        
        for($i = 0; $i<$this->height; $i++) {
            for($j = 0; $j<$this->width; $j++) {
                $color = imagecolorat($this->imageData, $j, $i);
                // Inversing
                $ir = 255 - (($color >> 16) & 0xFF);
                $ig = 255 - (($color >> 8) & 0xFF);
                $ib = 255 - (($color) & 0xFF);
                $icolor = $ir << 16 | $ig << 8 | $ib;
                // Set color to each pixel
                imagesetpixel($res, $j, $i, $icolor);
            }
        }
        
        return $res;
    }
    
    public function blackWhite() {
        if(!$this->imageData) {
            return false;
        }
        
        $res = imagecreatetruecolor($this->width, $this->height);
        
        for($i = 0; $i<$this->height; $i++) {
            for($j = 0; $j<$this->width; $j++) {
                $color = imagecolorat($this->imageData, $j, $i);
                // Inversing
                $r = (($color >> 16) & 0xFF);
                $g = (($color >> 8) & 0xFF);
                $b = (($color) & 0xFF);
                
                // Using euclid to compute distance
                $distanceToBlack = sqrt($r*$r + $g*$g + $b*$b);
                $distanceToWhite = sqrt(pow(255-$r, 2) + pow(255-$g,2 )+ pow(255-$b, 2));
                                
                if($distanceToBlack > $distanceToWhite) {
                    $icolor = 0xFFFFFF;
                } else {
                    $icolor = 0x000000;
                }
                imagesetpixel($res, $j, $i, $icolor);
            }
        }
        
        return $res;
    }
    
    public function grayScale() {
        if(!$this->imageData) {
            return false;
        }
        
        $res = imagecreatetruecolor($this->width, $this->height);
        
        for($i = 0; $i<$this->height; $i++) {
            for($j = 0; $j<$this->width; $j++) {
                $color = imagecolorat($this->imageData, $j, $i);
                // Inversing
                $r = (($color >> 16) & 0xFF);
                $g = (($color >> 8) & 0xFF);
                $b = (($color) & 0xFF);
                $mean = ($r + $g + $b) / 3;
                $icolor = $mean << 16 | $mean << 8 | $mean;
                imagesetpixel($res, $j, $i, $icolor);
            }
        }
        
        return $res;
    }
    
    public function intToHexString($int) {
        $str .= "(";
        $str .= ($int >> 16) & 0xFF;
        $str .= ",";
        $str .= ($int >> 8) & 0xFF;
        $str .= ",";
        $str .= ($int) & 0xFF;
        $str .= ")";
        
        return $str;
    }
    
    public function applyFilter($filter) {
        $res = $this->imageData;
        imagefilter($res, $filter);
        return $res;
    }
}
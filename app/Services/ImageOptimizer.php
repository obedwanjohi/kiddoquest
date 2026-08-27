<?php

namespace App\Services;

class ImageOptimizer
{
    /**
     * Resizes and converts an uploaded image to an ultra-compressed WebP file.
     *
     * @param string $sourcePath Absolute path to the original source image.
     * @param string $destPath Absolute path where the WebP image should be saved.
     * @param int $maxDimension Maximum width or height in pixels (default 512px).
     * @param int $quality Compression quality (1-100, default 80).
     * @return array|false Returns array with new dimensions & file size, or false on failure.
     */
    public static function optimizeAndConvertToWebp(string $sourcePath, string $destPath, int $maxDimension = 512, int $quality = 80)
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Load image resource based on mime type
        $srcImage = null;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $srcImage = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null;
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($sourcePath);
                break;
        }

        if (!$srcImage) {
            return false;
        }

        // Calculate proportional scale
        $newWidth = $origWidth;
        $newHeight = $origHeight;

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth >= $origHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
            }
        }

        // Create canvas with transparency preservation
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);

        // Resample image
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Ensure target directory exists
        $dir = dirname($destPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Save as WebP
        $success = function_exists('imagewebp') ? imagewebp($dstImage, $destPath, $quality) : false;

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        if (!$success || !file_exists($destPath)) {
            return false;
        }

        return [
            'width' => $newWidth,
            'height' => $newHeight,
            'size_bytes' => filesize($destPath),
        ];
    }
}

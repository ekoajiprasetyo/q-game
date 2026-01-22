<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\WebpEncoder;

class ImageOptimizerService
{
    /**
     * Maximum width for resizing (maintains aspect ratio)
     */
    protected int $maxWidth = 1920;
    
    /**
     * WebP quality (0-100, higher = better quality but larger file)
     */
    protected int $quality = 85;

    /**
     * Process and optimize an uploaded image file
     * Converts to WebP format and resizes if necessary
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $directory Storage directory (relative to public disk or public folder)
     * @param bool $useStorage Use Laravel Storage (true) or public_path (false)
     * @return string The path to the saved image
     */
    public function optimize(UploadedFile $file, string $directory, bool $useStorage = true): string
    {
        // Generate unique filename with webp extension
        $filename = time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';
        
        // Read image using Intervention
        $image = Image::read($file->getRealPath());
        
        // Resize if width exceeds max (maintains aspect ratio)
        $currentWidth = $image->width();
        if ($currentWidth > $this->maxWidth) {
            $image->scale(width: $this->maxWidth);
        }
        
        // Encode to WebP format
        $encoded = $image->encode(new WebpEncoder(quality: $this->quality));
        
        if ($useStorage) {
            // Use Laravel Storage (storage/app/public/...)
            $path = $directory . '/' . $filename;
            Storage::disk('public')->put($path, (string) $encoded);
            return 'storage/' . $path;
        } else {
            // Use public folder directly (public/uploads/...)
            $fullDirectory = public_path($directory);
            if (!is_dir($fullDirectory)) {
                mkdir($fullDirectory, 0755, true);
            }
            $fullPath = $fullDirectory . '/' . $filename;
            file_put_contents($fullPath, (string) $encoded);
            return '/' . $directory . '/' . $filename;
        }
    }

    /**
     * Optimize an image from a file path (for existing images)
     * 
     * @param string $sourcePath Full path to source image
     * @param string $directory Output directory
     * @param bool $useStorage Use Laravel Storage or public_path
     * @return string|null The path to the saved image, or null on failure
     */
    public function optimizeFromPath(string $sourcePath, string $directory, bool $useStorage = true): ?string
    {
        if (!file_exists($sourcePath)) {
            return null;
        }

        $filename = time() . '_' . pathinfo($sourcePath, PATHINFO_FILENAME) . '.webp';
        
        try {
            $image = Image::read($sourcePath);
            
            if ($image->width() > $this->maxWidth) {
                $image->scale(width: $this->maxWidth);
            }
            
            $encoded = $image->encode(new WebpEncoder(quality: $this->quality));
            
            if ($useStorage) {
                $path = $directory . '/' . $filename;
                Storage::disk('public')->put($path, (string) $encoded);
                return 'storage/' . $path;
            } else {
                $fullDirectory = public_path($directory);
                if (!is_dir($fullDirectory)) {
                    mkdir($fullDirectory, 0755, true);
                }
                $fullPath = $fullDirectory . '/' . $filename;
                file_put_contents($fullPath, (string) $encoded);
                return '/' . $directory . '/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error('Image optimization failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set maximum width for resizing
     */
    public function setMaxWidth(int $width): self
    {
        $this->maxWidth = $width;
        return $this;
    }

    /**
     * Set WebP quality
     */
    public function setQuality(int $quality): self
    {
        $this->quality = min(100, max(0, $quality));
        return $this;
    }

    /**
     * Calculate estimated size reduction percentage
     */
    public function estimateSizeReduction(int $originalSize, int $newSize): float
    {
        if ($originalSize === 0) return 0;
        return round((1 - ($newSize / $originalSize)) * 100, 1);
    }
}

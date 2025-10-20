<?php

namespace App\Services;

use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ChatFileService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Upload and process chat file
     */
    public function uploadFile(UploadedFile $file, string $type): array
    {
        $data = [
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ];

        switch ($type) {
            case 'image':
                return $this->processImage($file, $data);

            case 'video':
                return $this->processVideo($file, $data);

            case 'audio':
                return $this->processAudio($file, $data);

            case 'document':
                return $this->processDocument($file, $data);

            default:
                return $data;
        }
    }


    /**
     * Process image upload
     */
    protected function processImage(UploadedFile $file, array $data): array
    {
        // Store original image
        $path = $file->store('chat/images', 'public');
        $data['file_path'] = $path;

        // Get image dimensions
        try {
            $image = Image::make($file);
            $data['width'] = $image->width();
            $data['height'] = $image->height();

            // Create thumbnail
            $thumbnailPath = 'chat/thumbnails/' . basename($path);
            $thumbnail = Image::make($file)->fit(300, 300);
            Storage::disk('public')->put($thumbnailPath, $thumbnail->encode());
            $data['thumbnail'] = $thumbnailPath;
        } catch (\Exception $e) {
            // Fallback if image processing fails
            $data['width'] = null;
            $data['height'] = null;
        }

        return $data;
    }


    /**
     * Process video upload
     */
    protected function processVideo(UploadedFile $file, array $data): array
    {
        // Store video
        $path = $file->store('chat/videos', 'public');
        $data['file_path'] = $path;

        try {
            // Get video duration and create thumbnail using FFMpeg
            // Note: You need to install php-ffmpeg package
            // composer require php-ffmpeg/php-ffmpeg

            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => env('FFMPEG_BINARY', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => env('FFPROBE_BINARY', '/usr/bin/ffprobe'),
            ]);

            $video = $ffmpeg->open(Storage::disk('public')->path($path));

            // Get duration
            $duration = $video->getStreams()->videos()->first()->get('duration');
            $data['duration'] = (int) $duration;

            // Create thumbnail at 1 second
            $thumbnailPath = 'chat/thumbnails/' . pathinfo($path, PATHINFO_FILENAME) . '.jpg';
            $video->frame(TimeCode::fromSeconds(1))
                ->save(Storage::disk('public')->path($thumbnailPath));
            $data['thumbnail'] = $thumbnailPath;

            // Get dimensions
            $dimensions = $video->getStreams()->videos()->first()->getDimensions();
            $data['width'] = $dimensions->getWidth();
            $data['height'] = $dimensions->getHeight();
        } catch (\Exception $e) {
            // Fallback if video processing fails
            Log::error('Video processing failed: ' . $e->getMessage());
        }

        return $data;
    }


    /**
     * Process audio upload
     */
    protected function processAudio(UploadedFile $file, array $data): array
    {
        // Store audio
        $path = $file->store('chat/audios', 'public');
        $data['file_path'] = $path;

        try {
            // Get audio duration using getID3 or FFMpeg
            $ffmpeg = FFMpeg::create();
            $audio = $ffmpeg->open(Storage::disk('public')->path($path));
            $duration = $audio->getStreams()->audios()->first()->get('duration');
            $data['duration'] = (int) $duration;
        } catch (Exception $e) {
            Log::error('Audio processing failed: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Process document upload
     */
    protected function processDocument(UploadedFile $file, array $data): array
    {
        // Store document
        $path = $file->store('chat/documents', 'public');
        $data['file_path'] = $path;

        // Generate thumbnail for PDF files
        if ($file->getMimeType() === 'application/pdf') {
            try {
                // You can use Spatie PDF package for thumbnail generation
                // composer require spatie/pdf-to-image

                // For now, we'll skip thumbnail generation
                // You can implement it based on your requirements
            } catch (Exception $e) {
                Log::error('PDF thumbnail generation failed: ' . $e->getMessage());
            }
        }

        return $data;
    }

    /**
     * Get file type from uploaded file
     */
    public function getFileType(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } else {
            return 'document';
        }
    }


    /**
     * Validate file size based on type
     */
    public function validateFileSize(UploadedFile $file, string $type): bool
    {
        $maxSizes = [
            'image' => 5 * 1024, // 5MB in KB
            'video' => 50 * 1024, // 50MB in KB
            'audio' => 10 * 1024, // 10MB in KB
            'document' => 10 * 1024, // 10MB in KB
        ];

        $maxSize = $maxSizes[$type] ?? 5 * 1024;
        return $file->getSize() <= ($maxSize * 1024); // Convert to bytes
    }


    /**
     * Delete file from storage
     */
    public function deleteFile(?string $filePath, ?string $thumbnailPath = null): void
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }
    }
}

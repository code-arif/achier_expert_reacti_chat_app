<?php

namespace App\Helper;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;


class Helper
{
    // upload image helper function
    public static function uploadImage($file, $folder)
    {

        if (!$file->isValid()) {
            return null;
        }

        $imageName = time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension(); // Unique name
        $path = public_path('uploads/' . $folder);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $imageName);
        return 'uploads/' . $folder . '/' . $imageName;
    }

    // file upload helper funciton
    public static function fileUpload($file, string $folder, string $name): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        // FIX: always use original extension
        $ext = strtolower($file->getClientOriginalExtension());

        // FIX: fallback for HEIC / HEIF / QuickTime
        if (!$ext) {
            $mime = $file->getMimeType();
            $map = [
                'image/heic' => 'heic',
                'image/heif' => 'heif',
                'video/quicktime' => 'mov',
            ];
            $ext = $map[$mime] ?? 'bin';
        }

        // FIX: DO NOT slug extension
        $filename = Str::slug(pathinfo($name, PATHINFO_FILENAME)) . '.' . $ext;

        $path = public_path("uploads/$folder");
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file->move($path, $filename);

        return "uploads/$folder/$filename";
    }


    // delete image helper funciton
    public static function deleteImage($imageUrl)
    {
        if (!$imageUrl) {

            dd("jalis");
            return false;
        }
        $filePath = public_path($imageUrl);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    // file delete helper funciton
    public static function fileDelete(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public static function deleteImages($imageUrls)
    {
        if (is_array($imageUrls)) {
            foreach ($imageUrls as $imageUrl) {
                $baseUrl = url('/');
                $relativePath = str_replace($baseUrl . '/', '', $imageUrl);
                $fullPath = public_path($relativePath);


                if (file_exists($fullPath) && is_file($fullPath)) {

                    if (!unlink($fullPath)) {
                        return false;
                    }
                }
            }
            return true;
        }

        return false;
    }

    //delete videos
    public static function deleteVideos($videoPaths)
    {
        if (!is_array($videoPaths)) {
            $videoPaths = [$videoPaths];
        }

        foreach ($videoPaths as $path) {
            $fullPath = public_path($path);
            if (file_exists($fullPath) && is_file($fullPath)) {
                @unlink($fullPath); // suppress warning
            }
        }
    }



    // calculate age from date of birth
    public static function calculateAge($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return null;
        }

        $dob = \Carbon\Carbon::parse($dateOfBirth);
        $now = \Carbon\Carbon::now();

        return (int) $dob->diffInYears($now);
    }

    //delete file
    public static function deleteFile($filePath)
    {
        if ($filePath && file_exists(public_path($filePath))) {
            unlink(public_path($filePath));
        }
    }

    // create username
    public static function generateUniqueUsername($firstName, $lastName)
    {
        $base = Str::slug("{$firstName} {$lastName}", '_');
        $username = "@{$base}";

        $counter = 1;
        $original = $username;

        while (User::where('username', $username)->exists()) {
            $username = "@{$base}{$counter}";
            $counter++;
        }

        return $username;
    }


    public static function sendNotifyMobile($token, $notifyData): void
    {
        try {
            $messaging = Firebase::messaging();

            $notification = Notification::create(
                $notifyData['title'],
                Str::limit($notifyData['body'], 100),
                $notifyData['icon']
            );

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            $messaging->send($message);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}

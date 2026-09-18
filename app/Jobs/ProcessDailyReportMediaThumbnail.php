<?php

namespace App\Jobs;

use App\Models\DailyReportMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessDailyReportMediaThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DailyReportMedia $media)
    {
    }

    public function handle(): void
    {
        if ($this->media->file_type !== 'image') {
            return;
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($this->media->file_path)) {
            return;
        }

        $sourceData = $disk->get($this->media->file_path);
        if (!$sourceData) {
            return;
        }

        $image = @imagecreatefromstring($sourceData);
        if (!$image) {
            return;
        }

        $origW = imagesx($image);
        $origH = imagesy($image);

        $maxSize = 360;
        if ($origW > $origH) {
            $thumbW = $maxSize;
            $thumbH = (int) round(($origH / $origW) * $maxSize);
        } else {
            $thumbH = $maxSize;
            $thumbW = (int) round(($origW / $origH) * $maxSize);
        }

        $thumb = imagecreatetruecolor($thumbW, $thumbH);

        // Preserve transparency for PNG/GIF
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);

        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumbW, $thumbH, $origW, $origH);

        $pathInfo = pathinfo($this->media->file_path);
        $thumbRelativePath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];

        ob_start();
        imagejpeg($thumb, null, 80);
        $thumbData = ob_get_clean();

        imagedestroy($thumb);
        imagedestroy($image);

        if ($thumbData) {
            $disk->put($thumbRelativePath, $thumbData);
            $this->media->update([
                'thumbnail_path' => $thumbRelativePath,
            ]);
        }
    }
}

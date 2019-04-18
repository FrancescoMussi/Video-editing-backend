<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use FFMpeg;

class VideoController extends Controller
{
    public function concat_videos() {
        $videos = json_decode(request('videos'));

        $pathArray = [];
        $nameArray = [];

        // Clean up storage directory
        $allStorageFiles = Storage::files('public/videos');
        foreach($allStorageFiles as $file) {
            Storage::delete($file);
        }

        // Delete previous single files in public directory
        $allPreviousSingleVideos = Storage::disk('public')->files('videos/single');
        foreach($allPreviousSingleVideos as $file) {
            Storage::disk('public')->delete($file);
        }

        foreach ($videos as $index => $video) {
            // Save base64 as file
            $base64 = $video->base64;
            $base64withoutURIScheme = explode(',', $base64)[1];
            $data = base64_decode($base64withoutURIScheme);
            $file_name = 'video' . $index . '.mp4';
            $uploadPath = storage_path() . '/app/public/videos/' . $file_name;
            file_put_contents($uploadPath , $data);

            array_push($pathArray, $uploadPath);
            array_push($nameArray, $file_name);

            // Save single files in public directory (just for extra safety in case editing goes wrong)
            $filePublicPath = public_path('videos/single/' . $file_name);
            file_put_contents($filePublicPath , $data);
        }

        $output_path = $this->ffmpeg_concat($pathArray, $nameArray);

        return response(compact('output_path'), 201);
    }


    private function ffmpeg_concat($pathArray, $nameArray) {

        $disk = FFMpeg::fromDisk('local');

        // $output_path = storage_path('app/public/videos/output.mp4');
        $output_path = public_path('videos/output.mp4');

        // remove output file if exists
        if( file_exists($output_path) ) {
            // Storage::delete('public/videos/output.mp4');
            // File::delete($output_path);
            unlink($output_path);
        }

        $format = new FFMpeg\Format\Video\X264();
        $format->setAudioCodec("libmp3lame");

        $video1 = $disk->open('public/videos/' . $nameArray[0]);

        $video1
            ->concat($pathArray)
            ->saveFromDifferentCodecs($format, $output_path);

        return $output_path;
    }
}

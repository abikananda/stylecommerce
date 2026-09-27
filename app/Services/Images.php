<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class Images {
    public function store(UploadedFile $file,string $folder='products'):string {
        $info=getimagesize($file->getRealPath());
        if (!$info || $info[0]*$info[1]>30000000) throw ValidationException::withMessages(['images'=>'This image is too large to process.']);
        $image=match($info[2]){IMAGETYPE_JPEG=>imagecreatefromjpeg($file->getRealPath()),IMAGETYPE_PNG=>imagecreatefrompng($file->getRealPath()),IMAGETYPE_WEBP=>imagecreatefromwebp($file->getRealPath()),default=>false};
        if (!$image) throw ValidationException::withMessages(['images'=>'Unsupported image file.']);
        $scale=min(1,1600/$info[0],1600/$info[1]);$width=max(1,(int)round($info[0]*$scale));$height=max(1,(int)round($info[1]*$scale));
        $resized=imagecreatetruecolor($width,$height);imagealphablending($resized,false);imagesavealpha($resized,true);
        imagecopyresampled($resized,$image,0,0,0,0,$width,$height,$info[0],$info[1]);
        ob_start();imagewebp($resized,null,82);$contents=ob_get_clean();imagedestroy($resized);imagedestroy($image);
        $path=$folder.'/'.Str::uuid().'.webp';Storage::disk('public')->put($path,$contents);return $path;
    }
}

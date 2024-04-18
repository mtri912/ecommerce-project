<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Decoders\FilePathImageDecoder;
class TempImagesController extends Controller
{
    public function create(Request $request) {

        if(!empty($image)) {
            $image = $request->image;
            $extenstion = $image->getClientOriginalExtension();
            $newFileName = time().'.'.$extenstion;

            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path().'/temp',$newName);

            // Generate thumbnail
            $sourcePath = public_path().'/temp/'.$newName;
            $destPath = public_path().'/temp/thumb/'.$newName;
            $manager = new ImageManager(new Driver());
            $image = $manager->read($sourcePath);
            $image->resize(300,275);
            $image->save($destPath);

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'ImagePath' => asset('/temp/thumb/'.$newName),
                'message' => 'Image Uploaded Successfully'
            ]);
        }
    }
}

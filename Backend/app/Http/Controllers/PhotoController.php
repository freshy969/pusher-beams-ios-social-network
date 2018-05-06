<?php

namespace App\Http\Controllers;

use App\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $photos = Photo::orderBy('id', 'desc')->paginate(20);

        return response()->json($photos->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $photo_data = $request->validate([
            'caption' => 'required|between:1,1000',
            'image' => 'required|image|mimes:jpeg,gif,png',
        ]);

        $imagePath = Storage::disk('public')->putFile('photos', $request->file('image'));

        $photo_data = array_merge($photo_data, [
            'user_id' => $request->user()->id,
            'image' => asset("storage/{$imagePath}"),
            'image_path' => storage_path('app/public') . "/{$imagePath}",
        ]);

        $photo = Photo::create($photo_data);

        return response()->json([
            'status' => 'success',
            'data' => $photo->load(['user', 'comments'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Photo $photo)
    {
        $data = $request->validate(['caption' => 'required|between:1,1000']);

        $updated = $photo->update($data);

        $statusCode = $updated ? 200 : 401;
        $status = $updated ? 'success' : 'error';

        return response()->json(['status' => $status], $statusCode);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Photo $photo)
    {
        $deleted = $photo->delete();

        $statusCode = $deleted ? 200 : 401;
        $status = $deleted ? 'success' : 'error';

        return response()->json(['status' => $status], $statusCode);
    }
}

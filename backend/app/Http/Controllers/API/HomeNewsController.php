<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\homeNews;
use Validator;
use Illuminate\Support\Facades\Log;

class HomeNewsController extends Controller
{
    public function index(){
        $news = homeNews::all();

        return response()->json([
            "success" => true,
            "message" => "News retrieved successfully.",
            "data"=>$news
          ]);
    }

    public function show($id){
        $new = homeNews::find($id);
        if (is_null($new)) {
            return $this->sendError('New not found.');
        }
        return response()->json([
            "success" => true,
            "message" => "New retrieved successfully.",
            "data" => $new
        ]);
    }

    public function store(Request $request){
        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Add image validation rules
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        // Check if the image file was uploaded successfully
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = $request->file('image');
            $imageName = time().'.'.$image->extension();
            $imagePath = public_path().'/images';
    
            $image->move($imagePath, $imageName);
    
            $new = new homeNews;
            $new->title = $request->title;
            $new->content = $request->content;
            $new->image = $imageName;
            $new->save();
    
            return response()->json([
                "message" => "New added"
            ]);
        } else {
            // Handle the case where the image upload fails
            return response()->json(['error' => 'Image upload failed'], 400);
        }
    }


    public function update(Request $request, String $id){
        // Validación de la solicitud
        $input = $request->all();

        // Encuentra la noticia por ID
        $new = homeNews::find($id);

        if (!$new) {
            return response()->json(['error' => 'Noticia no encontrada'], 404);
        }

        // Lógica de actualización
        // $new->title = $request->title;
        // $new->content = $request->content;

        // Manejo de la imagen
        if ($request->hasFile('image')) {
            // Elimina la imagen existente si hay una
            if ($new->image) {
                unlink(public_path('images/' . $new->image));
            }

            // Sube la nueva imagen
            $image = $request->file('image');
            $imageName = time().'.'.$image->extension();
            $imagePath = public_path(). '/images';
            $image->move($imagePath, $imageName);
            
            // Actualiza el nombre de la imagen en la base de datos
            // $new->image = $imageName;
            $new->update([
                'image' => $imageName,
            ]);
        }

        if ($input['title']){
            $new->title = $input['title'];
        }
        if ($input['content']){
            $new->content = $input['content'];
        }
        $new->save();

        return response()->json([
            "success" => true,
            "message" => "New updated successfully.",
            "data" => $new
        ]);
    }

    public function destroy($id){
        $new = homeNews::find($id);

        if (!is_null($new)) {
        $new->delete();
        return response()->json(['message' => 'Successfully deleted']);
        } else {
            return response()->json(['error' => 'Resource not found'],404);
        }
    }
}

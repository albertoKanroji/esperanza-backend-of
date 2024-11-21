<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\User;


class ProfilePhotoController extends Controller
{
    public function upload(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'image' => 'required|file|image|mimes:jpeg,png,jpg|max:20480', // El tamaño máximo es 20480KB (20MB)
            'id' => 'required'
        ]);

        $id = $request->input('id');
        $image = $request->file('image');

        // Crear un nombre único para la imagen
        $imageName = 'profile_' . $id . '.' . $image->getClientOriginalExtension();

        // Almacenar la imagen en el directorio 'public/profile-photos'
        $path = $image->storeAs('public/profile-photos', $imageName);

        // Actualizar la ruta de la imagen en la base de datos si es necesario
        // Por ejemplo:
        $user = User::find($id);
        $user->image = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto de perfil subida exitosamente',
            'path' => $path
        ]);
    }

    public function getProfilePhoto(Request $request)
{
    $id = $request->input('id');

    // Suponiendo que tienes un modelo User y una columna profile_photo
    $user = User::find($id);

    if ($user && $user->image) {
        $url = Storage::url($user->image);

        return response()->json([
            'success' => true,
            'url' => $url
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Usuario o foto no encontrada'
        ], 404);
    }
}

public function uploadProfilePhoto(Request $request)
{
    // Validar que la imagen se envíe como una cadena base64
    $request->validate([
        'image' => 'required|string', // La imagen base64 debe ser una cadena
        'id' => 'required',           // ID del usuario es obligatorio
    ]);

    // Obtener la imagen base64 y el ID del usuario
    $image_base64 = $request->input('image');
    $id = $request->input('id');

    // Decodificar la imagen base64
    $image_data = base64_decode($image_base64);

    // Verificar si la imagen fue decodificada correctamente
    if ($image_data === false) {
        return response()->json(['error' => 'No se pudo decodificar la imagen base64'], 422);
    }

    // Generar un nombre único para la imagen
    $image_name = 'profile_' . $id . '_' . Str::random(10) . '.jpg';

    // Guardar la imagen en el almacenamiento (en la carpeta `public/profile_photos`)
    Storage::put("public/profile_photos/{$image_name}", $image_data);

    // Obtener la URL de la imagen guardada
    $image_url = Storage::url("public/profile_photos/{$image_name}");

    // Actualizar la URL de la imagen en la base de datos
    User::where('id', $id)->update(['image' => $image_url]);

    // Retornar respuesta exitosa
    return response()->json([
        'success' => true,
        'message' => 'Imagen subida correctamente',
        'url' => $image_url,
    ]);
}


}

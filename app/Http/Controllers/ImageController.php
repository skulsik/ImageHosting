<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    private function createThumbnail($sourcePath, $destinationPath, $width, $height)
    {
        // Проверяем наличие директории и создаем, если она отсутствует
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($sourcePath);

        switch ($sourceType) {
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            default:
                return false;
        }

        // Вычисляем высоту на основе соотношения сторон
        $height = intval(($sourceHeight / $sourceWidth) * $width);

        $thumb = imagecreatetruecolor($width, $height);
        imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        switch ($sourceType) {
            case IMAGETYPE_GIF:
                imagegif($thumb, $destinationPath);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $destinationPath);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $destinationPath);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($thumb);

        return true;
    }
    public function uploadImages(Request $request)
    {
        // Валидация
        $request->validate([
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|dimensions:max_width=3840'
        ],
        [
            'images.required' => 'Вы должны выбрать хотя бы одно изображение.',
            'images.*.image' => 'Каждый файл должен быть изображением.',
            'images.*.mimes' => 'Допустимые форматы изображений: jpeg, png, jpg, gif.',
            'images.*.dimensions' => 'Размер изображения не должен превышать 3840 пикселей по ширине.',
            'images.*.max' => 'Размер файла не должен превышать 3MB.',
        ]);

        if ($request->hasfile('images')) {
            $images = $request->file('images');

            // Проверка, что количество файлов не превышает 5
            if (count($images) > 5) {
                return back()->with('error', 'Вы можете загрузить не более 5 изображений за раз.');
            }

            // Получаем имена изображений из запроса
            $imageNames = $request->input('image_names');

            foreach ($images as $key => $image) {
                // Получаем имя изображения из массива
                $imageName = $imageNames[$key];

                // Генерация уникального имени для файла
                $filename = uniqid() . '-' . $image->getClientOriginalName();

                // Сохранение исходного изображения
                $path_full = $image->storeAs('public/images', $filename);

                // Создание превью изображения
                $path_preview = 'public/images/thumbnails/' . $filename;
                $this->createThumbnail(storage_path('app/public/images/' . $filename), storage_path('app/' . $path_preview), 300, 300);

                // Сохранение пути в базе данных
                Image::create([
                    'name' => $imageName,
                    'path_full' => $path_full,
                    'path_preview' => $path_preview
                ]);
            }
        }

        return back()->with('success', 'Изображения успешно загружены!');
    }

    public function getImages()
    {
        // Получаем изображения с пагинацией
        $images = Image::latest()->paginate(10); // Пагинация по 10 элементов на странице

        // Возвращаем представление с данными об изображениях
        return view('images.index', ['images' => $images]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ImageController extends Controller
{
    /**
     * Создать уменьшенное изображение (эскиз).
     *
     * @param string $sourcePath Путь к исходному изображению.
     * @param string $destinationPath Путь для сохранения уменьшенного изображения.
     * @param int $width Ширина уменьшенного изображения.
     * @return bool Успешно ли создано уменьшенное изображение.
     */
    private function createThumbnail(
        string $sourcePath,
        string $destinationPath,
        int $width): bool
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
        imagecopyresampled(
            $thumb,
            $sourceImage,
            0,
            0,
            0,
            0,
            $width,
            $height,
            $sourceWidth,
            $sourceHeight
        );

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

    /**
     * Загрузить изображения на сервер.
     *
     * @param \Illuminate\Http\Request $request Запрос с данными о загружаемых изображениях.
     * @return \Illuminate\Http\RedirectResponse Редирект с сообщением об успешной загрузке или ошибкой валидации.
     */
    public function uploadImages(Request $request): RedirectResponse
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
                // Получает имя изображения из массива
                $imageName = $imageNames[$key];

                // Получает имя изображения из массива и переводит его на транслит
                // Генерация уникального имени для файла
                $filename = uniqid() . '-' . Str::slug($image->getClientOriginalName());

                // Сохранение исходного изображения
                $path_full = $image->storeAs('public/images', $filename);

                // Создание превью изображения
                $path_preview = 'public/images/thumbnails/' . $filename;
                $this->createThumbnail(
                    storage_path('app/public/images/' . $filename),
                    storage_path('app/' . $path_preview),
                    300
                );

                // Сохранение пути в базе данных
                Image::create([
                    'name' => $imageName,
                    'path_full' => 'images/'.$filename,
                    'path_preview' => 'images/thumbnails/'.$filename
                ]);
            }
        }

        return back()->with('success', 'Изображения успешно загружены!');
    }

    /**
     * Получить изображения с возможностью сортировки.
     *
     * @param \Illuminate\Http\Request $request Запрос с данными о методе сортировки и направлении.
     * @return \Illuminate\View\View Представление с отсортированными изображениями.
     */
    public function getImages(Request $request): View
    {
        // Определяем метод сортировки и направление сортировки
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Получаем изображения, учитывая выбранный метод сортировки
        $images = Image::orderBy($sortBy, $sortDirection)->paginate(6);

        // Передаем параметры сортировки в представление
        return view('layouts.images', compact('images', 'sortBy', 'sortDirection'));
    }

    /**
     * Скачать изображение в виде ZIP-архива.
     *
     * @param \Illuminate\Http\Request $request Запрос с данными о пути к изображению.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse HTTP-ответ с файлом ZIP-архива или сообщением об ошибке.
     */
    public function downloadImage(Request $request): BinaryFileResponse
    {
        // Получение пути и имени файла из пути
        $imagePath = $request->input('imagePath');
        $fileName = basename($imagePath);

        // Создание нового ZIP-архива
        $zip = new ZipArchive;
        $zipFileName = 'images.zip';
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Добавление изображения в архив
            $zip->addFile(storage_path('app/public/' . $imagePath), $fileName);
            $zip->close();
        }

        // Отправление файла пользователю для скачивания
        return response()->download($zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Получить все изображения.
     *
     * @return \Illuminate\Http\JsonResponse JSON-ответ с информацией о изображениях.
     */
    public function getAllImages(): JsonResponse
    {
        $images = Image::all();
        return response()->json($images);
    }

    /**
     * Получить изображение по его идентификатору.
     *
     * @param int $id Идентификатор изображения.
     * @return \Illuminate\Http\JsonResponse JSON-ответ с информацией о изображении или сообщением об ошибке.
     */
    public function getImageById(int $id): JsonResponse
    {
        $image = Image::find($id);
        if (!$image) {
            return response()->json(['error' => 'Изображение не найдено'], 404);
        }
        return response()->json($image);
    }
}

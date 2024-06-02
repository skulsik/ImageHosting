@extends('welcome')

@section('content')
    @foreach ($images as $image)
        <!-- Отображаем изображение -->
        <img src="{{ asset($image->path_full) }}" alt="{{ $image->name }}">
    @endforeach

    <!-- Добавляем ссылки на предыдущую и следующую страницы -->
    {{ $images->links() }}
@endsection

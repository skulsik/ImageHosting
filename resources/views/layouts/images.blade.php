@extends('welcome')

@section('content')
    <div class="container">

            <div class="row">
                <div class="col-md-12 mt-3">
                    <h1 class="text-white">Просмотр загруженных изображений</h1>
                </div>
                <div class="col-md-12 mt-3 border-item d-flex align-content-center justify-content-center">
                    <form method="GET" action="{{ route('images.index') }}" class="w-100">
                    <div class="row">
                        <div class="col-4 d-flex justify-content-center">
                            <a href="{{ route('index') }}" class="btn btn-primary">Загрузка изображений</a>
                        </div>
                        <div class="col-3">
                            <select name="sort_by" class="form-select">
                                <option value="created_at" @if($sortBy == 'created_at') selected @endif>Дата создания</option>
                                <option value="name" @if($sortBy == 'name') selected @endif>Название</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <select name="sort_direction" class="form-select">
                                <option value="asc" @if($sortDirection == 'asc') selected @endif>По возрастанию</option>
                                <option value="desc" @if($sortDirection == 'desc') selected @endif>По убыванию</option>
                            </select>
                        </div>
                        <div class="col-2 d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary">Сортировать</button>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        @foreach ($images as $image)
                            <div class="col-sm-4 d-flex align-content-center justify-content-center">
                                <div class="border-item-view">
                                    <a href="{{ asset('storage/'.$image->path_full) }}" data-fancybox="gallery" data-caption="{{ $image->name }}" class="">
                                        <img src="{{ asset('storage/'.$image->path_preview) }}" alt="{{ $image->name }}">
                                    </a>
                                    <div class="text-light p-3">
                                        Имя: {{ $image->name }}<br>
                                        <span class="text-secondary">Дата создания: {{ $image->created_at }}</span>
{{--                                        <a href="{{ route('download.image', ['imagePath' => str_replace('.jpg', '', str_replace('images/', '', $image->path_full))]) }}" class="btn btn-primary">Скачать</a>--}}
                                        <form action="{{ route('download.image') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="imagePath" value="{{ $image->path_full }}">
                                            <button type="submit" class="btn btn-primary">Скачать</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-12 p-3 d-flex justify-content-center">
                    {{ $images->render('pagination::bootstrap-4') }}
                </div>
            </div>
    </div>
@endsection

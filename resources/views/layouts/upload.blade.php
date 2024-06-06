
<div class="container">
    <form action="{{ route('images.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-12 mt-3">
                <h1 class="text-white">Загрузка изображений</h1>
            </div>
            <div class="col-md-12 mt-3 border-item">

                @if ($message = Session::get('success'))
                    <div class="alert alert-light d-flex align-content-center justify-center">
                        <h3 class="text-success">{{ $message }}</h3>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-light">
                        <h3 class="text-danger">Ошибка загрузки!</h3>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li class="text-danger">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row form-group">
                    <div class="col-8">
                        <label for="images" class="btn btn-primary custom-file-label">Выбрать файлы</label>
                        <input type="file" class="custom-file-input" id="images" name="images[]" multiple required>
                        <button type="submit" class="btn btn-primary">Загрузить</button>
                    </div>
                    <div class="col-4 text-right">
                        <a href="{{ route('images.index') }}" class="btn btn-primary">Просмотр загруженных изображений</a>
                    </div>
                </div>
            </div>
            <div class="col-md-12 d-flex align-content-center justify-center">
                <div id="preview" class="m-2 d-flex flex-wrap"></div>
            </div>
        </div>
    </form>
</div>

import './bootstrap';
import '@fancyapps/fancybox/dist/jquery.fancybox.min.css';
import $ from 'jquery';
import '@fancyapps/fancybox';

$(document).ready(function() {
    $('[data-fancybox]').fancybox();
});

$(document).ready(function() {
    $('#images').on('change', function() {
        $('#preview').empty();
        var files = this.files;

        if (files.length > 5) {
            alert('Вы можете загрузить не более 5 изображений за раз.');
            this.value = '';
            return;
        }

        $.each(files, function(index, file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = $('<div class="preview-item"><a href="' + e.target.result + '" data-fancybox="gallery"><img src="' + e.target.result + '" alt="image" class="img-thumbnail" /></a></div>');
                var filenameInput = $('<label class="form-label text-white">Имя изображения:</label><input type="text" class="filename form-control" name="image_names[]" placeholder="Введите имя" value="' + file.name + '">');
                var previewItem = $('<div class="preview-item border-item"></div>');
                previewItem.append(img);
                previewItem.append(filenameInput);
                $('#preview').append(previewItem);

                // Обновление имени изображения при изменении ввода
                filenameInput.on('input', function() {
                    var newName = $(this).val();
                    $(this).siblings('a').find('img').attr('alt', newName);
                });
            };
            reader.readAsDataURL(file);
        });

        $('[data-fancybox="gallery"]').fancybox();
    });

    $('.custom-file-label').on('click', function() {
        $('#images').click();
    });
});


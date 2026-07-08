/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";


$(document).ready(function () {
    $('#date-picker').datepicker({
        todayBtn: 'linked',
        format: 'yyyy-mm-dd',
        autoclose: true
    })

    if ($('#visit_destination_ids').length) {
        $('#visit_destination_ids').select2({
            placeholder: 'Seleccione destinos de visita',
            width: '100%',
        });
    }

    // Función AJAX para cargar sedes por región
    function loadHeadquartersByRegion(regionId) {
        if (!regionId) {
            $('#headquarters_id').empty().append('<option value="">' + $('#headquarters_id option:first').text() + '</option>');
            $('#headquarters_id').prop('disabled', true);
            return;
        }

        $.ajax({
            url: '/admin/get-headquarters-by-region',
            type: 'GET',
            data: { region_id: regionId },
            dataType: 'json',
            success: function(response) {
                $('#headquarters_id').empty().append('<option value="">' + $('#headquarters_id option:first').text() + '</option>');
                
                if (response.length > 0) {
                    $.each(response, function(index, headquarters) {
                        $('#headquarters_id').append('<option value="' + headquarters.id + '">' + headquarters.name + '</option>');
                    });
                    $('#headquarters_id').prop('disabled', false);
                } else {
                    $('#headquarters_id').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar sedes:', error);
                $('#headquarters_id').empty().append('<option value="">' + $('#headquarters_id option:first').text() + '</option>');
                $('#headquarters_id').prop('disabled', true);
            }
        });
    }

    // Evento change para el select de regiones
    $('#region_id').on('change', function() {
        var regionId = $(this).val();
        loadHeadquartersByRegion(regionId);
    });

    // Inicializar: cargar sedes si hay una región seleccionada
    var initialRegionId = $('#region_id').val();
    if (initialRegionId) {
        loadHeadquartersByRegion(initialRegionId);
    } else {
        $('#headquarters_id').prop('disabled', true);
    }
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#previewImage').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Add the following code if you want the name of the file appear on select
$(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});


if(jQuery().summernote) {
    $(".summernote").summernote({
        dialogsInBody: true,
        minHeight: 250,
    });
    $(".summernote-simple").summernote({
        dialogsInBody: true,
        minHeight: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough']],
            ['para', ['paragraph']]
        ]
    });
}

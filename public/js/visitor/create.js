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
    });

    // Función para cargar región y sede del empleado seleccionado
    function loadEmployeeRegionHeadquarters(employeeId) {
        if (!employeeId) {
            return;
        }

        $.ajax({
            url: '/admin/visitor/get-employee-region-headquarters',
            type: 'GET',
            data: { employee_id: employeeId },
            dataType: 'json',
            success: function(response) {
                // Actualizar región
                $('#region_id').val(response.region_id);
                
                // Actualizar sede y filtrar por región
                filterHeadquartersByRegion(response.region_id, response.headquarters_id);
            },
            error: function(xhr) {
                console.error('Error al cargar región y sede del empleado:', xhr.responseText);
            }
        });
    }

    // Función para filtrar sedes por región y seleccionar una específica
    function filterHeadquartersByRegion(regionId, headquartersId = null) {
        if (!regionId) {
            $('#headquarters_id option').show();
            return;
        }

        // Mostrar solo sedes de la región seleccionada
        $('#headquarters_id option').each(function() {
            var $option = $(this);
            if ($option.val() === '') {
                return; // Mantener opción "Seleccione..."
            }
            
            var optionRegionId = $option.data('region');
            if (optionRegionId == regionId) {
                $option.show();
            } else {
                $option.hide();
            }
        });

        // Seleccionar la sede especificada o limpiar selección
        if (headquartersId) {
            $('#headquarters_id').val(headquartersId);
        } else {
            $('#headquarters_id').val('');
        }
    }

    // Evento cuando cambia el empleado seleccionado
    $('#employee_id').on('change', function() {
        var employeeId = $(this).val();
        if (employeeId) {
            loadEmployeeRegionHeadquarters(employeeId);
        }
    });

    // Evento cuando cambia la región (mantener funcionalidad existente)
    $('#region_id').on('change', function() {
        var regionId = $(this).val();
        filterHeadquartersByRegion(regionId);
    });

    // Si hay un empleado preseleccionado (en caso de edición o valores antiguos)
    var selectedEmployeeId = $('#employee_id').val();
    if (selectedEmployeeId) {
        loadEmployeeRegionHeadquarters(selectedEmployeeId);
    }
});

if(jQuery().timepicker && $(".timepicker").length) {
    $(".timepicker").timepicker({
        icons: {
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down'
        }
    });
}

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

'use strict';

load_data();

function load_data() {
    const table = $('#maintable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: $('#maintable').data('url'),
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'headquarters_name', name: 'headquarters_name', orderable: false, searchable: false },
            { data: 'rules_count', name: 'rules_count', searchable: false },
            { data: 'users_count', name: 'users_count', searchable: false },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        ordering: false,
    });

    const hidecolumn = $('#maintable').data('hidecolumn');
    if (hidecolumn == 0 || hidecolumn === '0') {
        table.column(6).visible(false);
    }
}

$('#maintable').on('draw.dt', function () {
    $('[data-toggle="tooltip"]').tooltip();
});

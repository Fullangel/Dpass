'use strict';

let destinationQueueTable;

function loadDestinationQueueTable() {
    const todayOnly = $('#filter-today-only').is(':checked') ? 1 : 0;
    const insideOnly = $('#filter-inside-only').is(':checked') ? 1 : 0;

    if (destinationQueueTable) {
        destinationQueueTable.destroy();
    }

    destinationQueueTable = $('#destination-queue-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: $('#destination-queue-table').data('url'),
            data: {
                today_only: todayOnly,
                inside_only: insideOnly,
            },
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'visitor_id', name: 'visitor_id' },
            { data: 'name', name: 'name' },
            { data: 'destination', name: 'destination' },
            { data: 'purpose', name: 'purpose' },
            { data: 'registered_at', name: 'created_at' },
            { data: 'date', name: 'checkin_at' },
            { data: 'checkout', name: 'checkout_at' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
    });
}

$(document).ready(function () {
    loadDestinationQueueTable();

    $('#filter-today-only, #filter-inside-only').on('change', function () {
        loadDestinationQueueTable();
    });
});

setInterval(function () {
    if (destinationQueueTable) {
        destinationQueueTable.ajax.reload(null, false);
    }
}, 60000);

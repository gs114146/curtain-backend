var DataTablesContactUsConfig = {
    initContactUsTable: function () {
        return $('#contactUsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.routes.admin['contact-us'].index,
                type: 'GET'
            },
            columns: [
                { data: 'full_name', name: 'full_name' },
                { data: 'email', name: 'email' },
                { data: 'phone_number', name: 'phone_number' },
                { data: 'subject', name: 'subject' },
                { data: 'project_type', name: 'project_type' },
                { data: 'created_at', name: 'created_at' }
            ],
            order: [[5, 'desc']],
            pageLength: 10,
            language: {
                processing: "Loading...",
                emptyTable: "No contact messages found",
                zeroRecords: "No matching contact messages found"
            }
        });
    },
    refresh: function (table) {
        if (table && typeof table.ajax.reload === 'function') {
            table.ajax.reload();
        }
    }
};

window.DataTablesContactUsConfig = DataTablesContactUsConfig;


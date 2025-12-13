var DataTablesConsultationsConfig = {
    initConsultationsTable: function () {
        return $('#consultationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.routes.admin.consultations.index,
                type: 'GET'
            },
            columns: [
                { data: 'full_name', name: 'full_name' },
                { data: 'email', name: 'email' },
                { data: 'phone_number', name: 'phone_number' },
                { data: 'preferred_date', name: 'preferred_date' },
                { data: 'service_type', name: 'service_type' },
                { data: 'created_at', name: 'created_at' }
            ],
            order: [[5, 'desc']],
            pageLength: 10,
            language: {
                processing: "Loading...",
                emptyTable: "No appointments found",
                zeroRecords: "No matching appointments found"
            }
        });
    },
    refresh: function (table) {
        if (table && typeof table.ajax.reload === 'function') {
            table.ajax.reload();
        }
    }
};

window.DataTablesConsultationsConfig = DataTablesConsultationsConfig;


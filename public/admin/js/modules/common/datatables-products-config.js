var DataTablesProductsConfig = {
    initProductsTable: function () {
        return $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.routes.admin.products.index,
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'variants', name: 'variants', defaultContent: '-', orderable: false, searchable: false },
                { data: 'price_range', name: 'price_range', defaultContent: '-' },
                { data: 'stock_total', name: 'stock_total', defaultContent: 0 },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data) {
                        const badgeClass = data === 'active' ? 'success' : data === 'draft' ? 'warning' : 'secondary';
                        return `<span class="badge bg-${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        const editUrl = window.routes.admin.products.edit.replace(':id', data);
                        return `
                            <a class="btn  btn-outline-primary me-1" href="${editUrl}">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button class="btn  btn-outline-danger btn-delete" data-id="${data}">
                                <i class="bx bx-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                processing: "Loading...",
                emptyTable: "No products found",
                zeroRecords: "No matching products found"
            }
        });
    },
    refresh: function (table) {
        if (table && typeof table.ajax.reload === 'function') {
            table.ajax.reload();
        }
    }
};

window.DataTablesProductsConfig = DataTablesProductsConfig;

var DataTablesProductsConfig = {
    initProductsTable: function () {
        return $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            scrollX: false,
            ajax: {
                url: window.routes.admin.products.index,
                type: 'GET'
            },
            columns: [
                {
                    data: 'name',
                    name: 'name',
                    width: '20%',
                    render: function (data) {
                        if (!data) return '-';
                        return `<span class="products-cell-text" title="${$('<div>').text(data).html()}">${$('<div>').text(data).html()}</span>`;
                    }
                },
                {
                    data: 'variants',
                    name: 'variants',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false,
                    width: '30%',
                    render: function (data) {
                        if (!data) return '-';
                        const safe = $('<div>').text(data).html();
                        return `<span class="products-cell-text" title="${safe}">${safe}</span>`;
                    }
                },
                { data: 'price_range', name: 'price_range', defaultContent: '-', width: '15%' },
                { data: 'stock_total', name: 'stock_total', defaultContent: 0, width: '10%' },
                {
                    data: 'status',
                    name: 'status',
                    width: '10%',
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
                    width: '15%',
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

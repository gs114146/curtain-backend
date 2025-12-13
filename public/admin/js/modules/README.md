# Admin Modules Structure

This directory contains organized JavaScript modules for different CRUD operations in the admin panel.

## Folder Structure

```
modules/
├── common/                    # Shared utilities and common functionality
│   ├── dark-mode.js          # Dark mode toggle functionality
│   ├── datatables-config.js  # DataTables configuration
│   └── utils.js              # Common utility functions
├── colors/                   # Colors CRUD module
│   └── colors.js            # Colors management functionality
├── designs/                  # Designs CRUD module
│   └── designs.js           # Designs management functionality
├── materials/                # Materials CRUD module
│   └── materials.js         # Materials management functionality
├── products/                 # Products CRUD module
│   └── products.js          # Products management functionality
├── transparency/             # Transparency CRUD module
│   └── transparency.js      # Transparency management functionality
└── characteristics/          # Characteristics CRUD module
    └── characteristics.js   # Characteristics management functionality
```

## Usage

### Including Modules in Blade Templates

For common modules (loaded on all pages):

```html
<!-- In admin/layouts/js.blade.php -->
<script src="{{ asset('admin/js/modules/common/dark-mode.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/utils.js') }}"></script>
<script src="{{ asset('admin/js/modules/common/datatables-config.js') }}"></script>
```

For specific CRUD modules (loaded only on relevant pages):

```html
<!-- In specific CRUD pages -->
<script src="{{ asset('admin/js/modules/colors/colors.js') }}"></script>
<script src="{{ asset('admin/js/modules/designs/designs.js') }}"></script>
```

### Module Structure

Each CRUD module follows this pattern:

```javascript
class [ModuleName]Manager {
    constructor() {
        this.table = null;
        this.init();
    }

    init() {
        this.initializeDataTable();
        this.bindEvents();
    }

    initializeDataTable() {
        // DataTable initialization
    }

    bindEvents() {
        // Event binding for CRUD operations
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#[module]-table')) {
        new [ModuleName]Manager();
    }
});
```

## Adding New Modules

1. Create a new folder under `modules/` with the module name
2. Create a JavaScript file with the same name as the folder
3. Follow the established pattern for consistency
4. Include the module in the appropriate Blade template

## Benefits

-   **Organized Structure**: Each CRUD operation has its own dedicated folder
-   **Maintainable**: Easy to find and modify specific functionality
-   **Scalable**: Easy to add new modules without cluttering
-   **Reusable**: Common functionality is shared across modules
-   **Performance**: Only load modules that are needed on specific pages

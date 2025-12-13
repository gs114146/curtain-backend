# Curtain Shop Public API Documentation

## Base URL

```
https://your-domain.com/api/public
```

## Authentication

All public API endpoints are accessible without authentication.

## Endpoints

### 1. Get Products with Search and Filters

**Endpoint:** `GET /api/public/products`

**Description:** Retrieve a paginated list of products with search and filtering capabilities.

**Query Parameters:**

-   `search` (string, optional): Search term for product name, description, SKU, color, design, or material
-   `color_id` (integer, optional): Filter by color ID
-   `design_id` (integer, optional): Filter by design ID
-   `material_id` (integer, optional): Filter by material ID
-   `transparency_id` (integer, optional): Filter by transparency ID
-   `characteristic_id` (integer, optional): Filter by characteristic ID
-   `min_price` (decimal, optional): Minimum price filter
-   `max_price` (decimal, optional): Maximum price filter
-   `in_stock` (boolean, optional): Filter by stock availability (1 for in stock, 0 for out of stock)
-   `featured` (boolean, optional): Filter by featured products (1 for featured, 0 for non-featured)
-   `sort_by` (string, optional): Sort field (name, price, created_at, stock_quantity)
-   `sort_order` (string, optional): Sort order (asc, desc)
-   `per_page` (integer, optional): Number of items per page (default: 12, max: 50)
-   `page` (integer, optional): Page number for pagination

**Example Request:**

```
GET /api/public/products?search=curtain&color_id=1&min_price=50&max_price=200&featured=1&sort_by=price&sort_order=asc&per_page=20
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Elegant Curtain",
            "description": "Beautiful curtain for your home",
            "sku": "CUR-001",
            "price": "99.99",
            "sale_price": "79.99",
            "stock_quantity": 50,
            "dimensions": "120cm x 150cm",
            "weight": "2.5kg",
            "featured": true,
            "in_stock": true,
            "status": "active",
            "meta_title": "Elegant Curtain - Premium Quality",
            "meta_description": "High-quality elegant curtain for modern homes",
            "color": {
                "id": 1,
                "name": "White",
                "color_value": "#FFFFFF"
            },
            "design": {
                "id": 1,
                "name": "Plain"
            },
            "material": {
                "id": 1,
                "name": "Cotton"
            },
            "transparency": {
                "id": 1,
                "name": "Opaque"
            },
            "characteristic": {
                "id": 1,
                "name": "Latest collection"
            },
            "media": [
                {
                    "id": 1,
                    "file_path": "/storage/products/curtain-1.jpg",
                    "file_type": "image",
                    "is_primary": true
                }
            ],
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 12,
        "total": 50,
        "from": 1,
        "to": 12,
        "has_more_pages": true
    },
    "filters_applied": {
        "search": "curtain",
        "color_id": "1",
        "design_id": null,
        "material_id": null,
        "transparency_id": null,
        "characteristic_id": null,
        "min_price": "50",
        "max_price": "200",
        "in_stock": null,
        "featured": "1",
        "sort_by": "price",
        "sort_order": "asc"
    }
}
```

### 2. Get Single Product

**Endpoint:** `GET /api/public/products/{id}`

**Description:** Retrieve a single product by ID.

**Path Parameters:**

-   `id` (integer, required): Product ID

**Example Request:**

```
GET /api/public/products/1
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Elegant Curtain",
        "description": "Beautiful curtain for your home",
        "sku": "CUR-001",
        "price": "99.99",
        "sale_price": "79.99",
        "stock_quantity": 50,
        "dimensions": "120cm x 150cm",
        "weight": "2.5kg",
        "featured": true,
        "in_stock": true,
        "status": "active",
        "meta_title": "Elegant Curtain - Premium Quality",
        "meta_description": "High-quality elegant curtain for modern homes",
        "color": {
            "id": 1,
            "name": "White",
            "color_value": "#FFFFFF"
        },
        "design": {
            "id": 1,
            "name": "Plain"
        },
        "material": {
            "id": 1,
            "name": "Cotton"
        },
        "transparency": {
            "id": 1,
            "name": "Opaque"
        },
        "characteristic": {
            "id": 1,
            "name": "Latest collection"
        },
        "media": [
            {
                "id": 1,
                "file_path": "/storage/products/curtain-1.jpg",
                "file_type": "image",
                "is_primary": true
            }
        ],
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

### 3. Get Filter Options

**Endpoint:** `GET /api/public/filters`

**Description:** Retrieve all available filter options for products.

**Example Request:**

```
GET /api/public/filters
```

**Response:**

```json
{
    "success": true,
    "data": {
        "colors": [
            {
                "id": 1,
                "name": "White",
                "color_value": "#FFFFFF"
            },
            {
                "id": 2,
                "name": "Black",
                "color_value": "#000000"
            }
        ],
        "designs": [
            {
                "id": 1,
                "name": "Plain"
            },
            {
                "id": 2,
                "name": "Geometric"
            }
        ],
        "materials": [
            {
                "id": 1,
                "name": "Cotton"
            },
            {
                "id": 2,
                "name": "Polyester"
            }
        ],
        "transparencies": [
            {
                "id": 1,
                "name": "Opaque"
            },
            {
                "id": 2,
                "name": "Semi-transparent"
            }
        ],
        "characteristics": [
            {
                "id": 1,
                "name": "Latest collection"
            },
            {
                "id": 2,
                "name": "Flame-retardant"
            }
        ],
        "price_range": {
            "min": "29.99",
            "max": "299.99"
        }
    }
}
```

### 4. Get Featured Products

**Endpoint:** `GET /api/public/products/featured`

**Description:** Retrieve featured products.

**Query Parameters:**

-   `limit` (integer, optional): Number of featured products to return (default: 8, max: 20)

**Example Request:**

```
GET /api/public/products/featured?limit=6
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Elegant Curtain",
            "description": "Beautiful curtain for your home",
            "sku": "CUR-001",
            "price": "99.99",
            "sale_price": "79.99",
            "stock_quantity": 50,
            "featured": true,
            "in_stock": true,
            "color": {
                "id": 1,
                "name": "White",
                "color_value": "#FFFFFF"
            },
            "design": {
                "id": 1,
                "name": "Plain"
            },
            "material": {
                "id": 1,
                "name": "Cotton"
            },
            "primary_image": {
                "id": 1,
                "file_path": "/storage/products/curtain-1.jpg",
                "file_type": "image"
            }
        }
    ]
}
```

### 5. Get Product Statistics

**Endpoint:** `GET /api/public/stats`

**Description:** Retrieve product statistics for the catalog.

**Example Request:**

```
GET /api/public/stats
```

**Response:**

```json
{
    "success": true,
    "data": {
        "total_products": 150,
        "featured_products": 25,
        "in_stock_products": 120,
        "total_colors": 19,
        "total_designs": 7,
        "total_materials": 18
    }
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
    "success": false,
    "message": "Error message",
    "error": "Detailed error information"
}
```

**Common HTTP Status Codes:**

-   `200`: Success
-   `404`: Product not found
-   `500`: Server error

## Usage Examples

### Frontend Integration Examples

#### JavaScript/Fetch API

```javascript
// Get products with filters
const fetchProducts = async (filters = {}) => {
    const params = new URLSearchParams(filters);
    const response = await fetch(`/api/public/products?${params}`);
    const data = await response.json();
    return data;
};

// Usage
const products = await fetchProducts({
    search: "curtain",
    color_id: 1,
    min_price: 50,
    max_price: 200,
    featured: 1,
    per_page: 20,
});
```

#### jQuery/AJAX

```javascript
// Get featured products
$.ajax({
    url: "/api/public/products/featured",
    method: "GET",
    data: { limit: 8 },
    success: function (response) {
        if (response.success) {
            displayProducts(response.data);
        }
    },
    error: function (xhr) {
        console.error("Error fetching featured products:", xhr.responseJSON);
    },
});
```

#### React Hook Example

```javascript
import { useState, useEffect } from "react";

const useProducts = (filters = {}) => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                setLoading(true);
                const params = new URLSearchParams(filters);
                const response = await fetch(`/api/public/products?${params}`);
                const data = await response.json();

                if (data.success) {
                    setProducts(data.data);
                } else {
                    setError(data.message);
                }
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchProducts();
    }, [filters]);

    return { products, loading, error };
};
```

## Rate Limiting

The API implements rate limiting to ensure fair usage:

-   1000 requests per hour per IP address
-   100 requests per minute per IP address

## CORS

CORS is enabled for all origins. If you need to restrict access, please contact the administrator.

## Support

For API support or questions, please contact the development team.

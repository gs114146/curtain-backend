# Curtain Shop API Documentation

This document describes the API endpoints for the Curtain Shop application, including Consultation and Contact Us APIs.

## Overview

The Curtain Shop API provides two main functionalities:

1. **Consultation API**: Allows customers to submit consultation requests and administrators to manage them
2. **Contact Us API**: Allows customers to submit general inquiries and administrators to manage them

Both APIs include public endpoints (for customer submissions) and protected endpoints (for admin management).

## Base URL

```
http://your-domain.com/api
```

## Authentication

-   **Public endpoints**: No authentication required
-   **Admin endpoints**: Requires Sanctum authentication token

## Endpoints

## Consultation API

### 1. Submit Consultation Request (Public)

**POST** `/api/public/consultations`

Submit a new consultation request. This endpoint is publicly accessible.

#### Request Body

```json
{
    "full_name": "John Doe",
    "email": "john.doe@example.com",
    "phone_number": "+1-555-0123",
    "preferred_date": "2025-10-15",
    "service_type": "Custom Curtain Design",
    "additional_message": "I need help with window treatments for my living room."
}
```

#### Field Requirements

| Field                | Type   | Required | Description                                       |
| -------------------- | ------ | -------- | ------------------------------------------------- |
| `full_name`          | string | Yes      | Customer's full name (max 255 characters)         |
| `email`              | string | Yes      | Valid email address (max 255 characters)          |
| `phone_number`       | string | Yes      | Phone number (max 20 characters)                  |
| `preferred_date`     | date   | Yes      | Preferred consultation date (must be after today) |
| `service_type`       | string | Yes      | Type of service requested (max 255 characters)    |
| `additional_message` | string | No       | Additional details (max 1000 characters)          |

#### Success Response (201)

```json
{
    "success": true,
    "message": "Consultation request submitted successfully.",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone_number": "+1-555-0123",
        "preferred_date": "2025-10-15T00:00:00.000000Z",
        "service_type": "Custom Curtain Design",
        "additional_message": "I need help with window treatments for my living room.",
        "status": "pending",
        "created_at": "2025-10-07T17:38:08.000000Z",
        "updated_at": "2025-10-07T17:38:08.000000Z"
    }
}
```

#### Error Response (422)

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "full_name": ["The full name field is required."],
        "email": ["The email field must be a valid email address."],
        "preferred_date": [
            "The preferred date field must be a date after today."
        ]
    }
}
```

### 2. Get All Consultations (Admin)

**GET** `/api/consultations`

Retrieve all consultation requests. Requires authentication.

#### Success Response (200)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "phone_number": "+1-555-0123",
            "preferred_date": "2025-10-15T00:00:00.000000Z",
            "service_type": "Custom Curtain Design",
            "additional_message": "I need help with window treatments for my living room.",
            "status": "pending",
            "created_at": "2025-10-07T17:38:08.000000Z",
            "updated_at": "2025-10-07T17:38:08.000000Z"
        }
    ]
}
```

### 3. Get Single Consultation (Admin)

**GET** `/api/consultations/{id}`

Retrieve a specific consultation request. Requires authentication.

#### Success Response (200)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone_number": "+1-555-0123",
        "preferred_date": "2025-10-15T00:00:00.000000Z",
        "service_type": "Custom Curtain Design",
        "additional_message": "I need help with window treatments for my living room.",
        "status": "pending",
        "created_at": "2025-10-07T17:38:08.000000Z",
        "updated_at": "2025-10-07T17:38:08.000000Z"
    }
}
```

### 4. Update Consultation (Admin)

**PUT/PATCH** `/api/consultations/{id}`

Update a consultation request. Requires authentication.

#### Request Body

```json
{
    "full_name": "John Doe Updated",
    "email": "john.doe.updated@example.com",
    "phone_number": "+1-555-0124",
    "preferred_date": "2025-10-16",
    "service_type": "Custom Curtain Design",
    "additional_message": "Updated message",
    "status": "confirmed"
}
```

#### Success Response (200)

```json
{
    "success": true,
    "message": "Consultation updated successfully.",
    "data": {
        "id": 1,
        "full_name": "John Doe Updated",
        "email": "john.doe.updated@example.com",
        "phone_number": "+1-555-0124",
        "preferred_date": "2025-10-16T00:00:00.000000Z",
        "service_type": "Custom Curtain Design",
        "additional_message": "Updated message",
        "status": "confirmed",
        "created_at": "2025-10-07T17:38:08.000000Z",
        "updated_at": "2025-10-07T17:40:15.000000Z"
    }
}
```

### 5. Update Consultation Status (Admin)

**PATCH** `/api/consultations/{id}/status`

Update only the status of a consultation request. Requires authentication.

#### Request Body

```json
{
    "status": "confirmed"
}
```

#### Valid Status Values

-   `pending` - Initial status when consultation is submitted
-   `confirmed` - Consultation has been confirmed
-   `completed` - Consultation has been completed
-   `cancelled` - Consultation has been cancelled

#### Success Response (200)

```json
{
    "success": true,
    "message": "Consultation status updated successfully.",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone_number": "+1-555-0123",
        "preferred_date": "2025-10-15T00:00:00.000000Z",
        "service_type": "Custom Curtain Design",
        "additional_message": "I need help with window treatments for my living room.",
        "status": "confirmed",
        "created_at": "2025-10-07T17:38:08.000000Z",
        "updated_at": "2025-10-07T17:40:15.000000Z"
    }
}
```

### 6. Get Consultations by Status (Admin)

**GET** `/api/consultations/status/{status}`

Retrieve consultations filtered by status. Requires authentication.

#### URL Parameters

-   `status` - One of: `pending`, `confirmed`, `completed`, `cancelled`

#### Success Response (200)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "phone_number": "+1-555-0123",
            "preferred_date": "2025-10-15T00:00:00.000000Z",
            "service_type": "Custom Curtain Design",
            "additional_message": "I need help with window treatments for my living room.",
            "status": "pending",
            "created_at": "2025-10-07T17:38:08.000000Z",
            "updated_at": "2025-10-07T17:38:08.000000Z"
        }
    ]
}
```

### 7. Delete Consultation (Admin)

**DELETE** `/api/consultations/{id}`

Delete a consultation request. Requires authentication.

#### Success Response (200)

```json
{
    "success": true,
    "message": "Consultation deleted successfully."
}
```

## Error Responses

### 400 Bad Request

```json
{
    "success": false,
    "message": "Invalid status provided."
}
```

### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

### 404 Not Found

```json
{
    "success": false,
    "message": "An error occurred while fetching the consultation."
}
```

### 500 Internal Server Error

```json
{
    "success": false,
    "message": "An error occurred while creating the consultation request."
}
```

## Example Usage

### Submit a Consultation Request

```bash
curl -X POST http://your-domain.com/api/public/consultations \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Jane Smith",
    "email": "jane.smith@example.com",
    "phone_number": "+1-555-9876",
    "preferred_date": "2025-10-20",
    "service_type": "Window Treatment Consultation",
    "additional_message": "Looking for blackout curtains for bedroom"
  }'
```

### Get All Consultations (with authentication)

```bash
curl -X GET http://your-domain.com/api/consultations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Update Consultation Status

```bash
curl -X PATCH http://your-domain.com/api/consultations/1/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "confirmed"}'
```

## Service Types

Common service types that can be used:

-   Custom Curtain Design
-   Window Treatment Consultation
-   Commercial Installation
-   Repair and Maintenance
-   Curtain Installation
-   Design Consultation
-   Material Selection
-   Measurement Service

## Notes

-   All dates should be in YYYY-MM-DD format
-   The `preferred_date` must be a future date
-   Email addresses must be valid
-   Phone numbers can include formatting characters like +, -, (, )
-   The `additional_message` field is optional but recommended for better service
-   All admin endpoints require proper authentication tokens
-   Consultations are ordered by creation date (newest first)

---

## Contact Us API

### 1. Submit Contact Message (Public)

**POST** `/api/public/contact-us`

Submit a new contact message. This endpoint is publicly accessible.

#### Request Body

```json
{
    "full_name": "John Doe",
    "email": "john.doe@example.com",
    "phone_number": "+1-555-0123",
    "project_type": "Residential",
    "subject": "Custom Curtain Design Inquiry",
    "message": "I am interested in getting custom curtains designed for my new home."
}
```

#### Field Requirements

| Field          | Type   | Required | Description                               |
| -------------- | ------ | -------- | ----------------------------------------- |
| `full_name`    | string | Yes      | Customer's full name (max 255 characters) |
| `email`        | string | Yes      | Valid email address (max 255 characters)  |
| `phone_number` | string | Yes      | Phone number (max 20 characters)          |
| `project_type` | string | Yes      | Type of project (max 255 characters)      |
| `subject`      | string | Yes      | Message subject (max 255 characters)      |
| `message`      | string | Yes      | Message content (max 2000 characters)     |

#### Success Response (201)

```json
{
    "success": true,
    "message": "Contact message submitted successfully.",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone_number": "+1-555-0123",
        "project_type": "Residential",
        "subject": "Custom Curtain Design Inquiry",
        "message": "I am interested in getting custom curtains designed for my new home.",
        "status": "unread",
        "created_at": "2025-10-07T17:42:49.000000Z",
        "updated_at": "2025-10-07T17:42:49.000000Z"
    }
}
```

#### Error Response (422)

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "full_name": ["The full name field is required."],
        "email": ["The email field must be a valid email address."],
        "phone_number": ["The phone number field is required."],
        "project_type": ["The project type field is required."],
        "subject": ["The subject field is required."],
        "message": ["The message field is required."]
    }
}
```

### 2. Get All Contact Messages (Admin)

**GET** `/api/contact-us`

Retrieve all contact messages. Requires authentication.

#### Success Response (200)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "phone_number": "+1-555-0123",
            "project_type": "Residential",
            "subject": "Custom Curtain Design Inquiry",
            "message": "I am interested in getting custom curtains designed for my new home.",
            "status": "unread",
            "created_at": "2025-10-07T17:42:49.000000Z",
            "updated_at": "2025-10-07T17:42:49.000000Z"
        }
    ]
}
```

### 3. Get Single Contact Message (Admin)

**GET** `/api/contact-us/{id}`

Retrieve a specific contact message. Requires authentication.

#### Success Response (200)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone_number": "+1-555-0123",
        "project_type": "Residential",
        "subject": "Custom Curtain Design Inquiry",
        "message": "I am interested in getting custom curtains designed for my new home.",
        "status": "unread",
        "created_at": "2025-10-07T17:42:49.000000Z",
        "updated_at": "2025-10-07T17:42:49.000000Z"
    }
}
```

### 4. Update Contact Message (Admin)

**PUT/PATCH** `/api/contact-us/{id}`

Update a contact message. Requires authentication.

#### Request Body

```json
{
    "full_name": "John Doe Updated",
    "email": "john.doe.updated@example.com",
    "phone_number": "+1-555-0124",
    "project_type": "Commercial",
    "subject": "Updated Subject",
    "message": "Updated message content",
    "status": "read"
}
```

#### Success Response (200)

```json
{
    "success": true,
    "message": "Contact message updated successfully.",
    "data": {
        "id": 1,
        "full_name": "John Doe Updated",
        "email": "john.doe.updated@example.com",
        "phone_number": "+1-555-0124",
        "project_type": "Commercial",
        "subject": "Updated Subject",
        "message": "Updated message content",
        "status": "read",
        "created_at": "2025-10-07T17:42:49.000000Z",
        "updated_at": "2025-10-07T17:45:15.000000Z"
    }
}
```

### 5. Update Contact Message Status (Admin)

**PATCH** `/api/contact-us/{id}/status`

Update only the status of a contact message. Requires authentication.

#### Request Body

```json
{
    "status": "read"
}
```

#### Valid Status Values

-   `unread` - Initial status when message is submitted
-   `read` - Message has been read
-   `replied` - Response has been sent
-   `closed` - Message has been closed/resolved

#### Success Response (200)

```json
{
    "success": true,
    "message": "Contact message status updated successfully.",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone_number": "+1-555-0123",
        "project_type": "Residential",
        "subject": "Custom Curtain Design Inquiry",
        "message": "I am interested in getting custom curtains designed for my new home.",
        "status": "read",
        "created_at": "2025-10-07T17:42:49.000000Z",
        "updated_at": "2025-10-07T17:45:15.000000Z"
    }
}
```

### 6. Get Contact Messages by Status (Admin)

**GET** `/api/contact-us/status/{status}`

Retrieve contact messages filtered by status. Requires authentication.

#### URL Parameters

-   `status` - One of: `unread`, `read`, `replied`, `closed`

#### Success Response (200)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "phone_number": "+1-555-0123",
            "project_type": "Residential",
            "subject": "Custom Curtain Design Inquiry",
            "message": "I am interested in getting custom curtains designed for my new home.",
            "status": "unread",
            "created_at": "2025-10-07T17:42:49.000000Z",
            "updated_at": "2025-10-07T17:42:49.000000Z"
        }
    ]
}
```

### 7. Get Contact Messages by Project Type (Admin)

**GET** `/api/contact-us/project-type/{projectType}`

Retrieve contact messages filtered by project type. Requires authentication.

#### URL Parameters

-   `projectType` - The project type to filter by

#### Success Response (200)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "phone_number": "+1-555-0123",
            "project_type": "Residential",
            "subject": "Custom Curtain Design Inquiry",
            "message": "I am interested in getting custom curtains designed for my new home.",
            "status": "unread",
            "created_at": "2025-10-07T17:42:49.000000Z",
            "updated_at": "2025-10-07T17:42:49.000000Z"
        }
    ]
}
```

### 8. Delete Contact Message (Admin)

**DELETE** `/api/contact-us/{id}`

Delete a contact message. Requires authentication.

#### Success Response (200)

```json
{
    "success": true,
    "message": "Contact message deleted successfully."
}
```

## Project Types

Common project types that can be used:

-   Residential
-   Commercial
-   Hospitality
-   Educational
-   Healthcare
-   Retail
-   Industrial
-   Government

## Example Usage

### Submit a Contact Message

```bash
curl -X POST http://your-domain.com/api/public/contact-us \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Jane Smith",
    "email": "jane.smith@example.com",
    "phone_number": "+1-555-9876",
    "project_type": "Residential",
    "subject": "Window Treatment Inquiry",
    "message": "I need help choosing the right curtains for my living room."
  }'
```

### Get All Contact Messages (with authentication)

```bash
curl -X GET http://your-domain.com/api/contact-us \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Update Contact Message Status

```bash
curl -X PATCH http://your-domain.com/api/contact-us/1/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "read"}'
```

### Get Contact Messages by Project Type

```bash
curl -X GET http://your-domain.com/api/contact-us/project-type/Residential \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## Notes

-   All dates should be in YYYY-MM-DD format
-   Email addresses must be valid
-   Phone numbers can include formatting characters like +, -, (, )
-   All admin endpoints require proper authentication tokens
-   Contact messages are ordered by creation date (newest first)
-   The `message` field has a maximum length of 2000 characters

# UserPurchaseRequest API Documentation

## Overview

The UserPurchaseRequest API allows authenticated users to create purchase requests programmatically using Personal Access Tokens. This API automatically handles user authentication and department assignment based on the provided access credentials.

## Base URL

```
https://your-domain.com/api
```

## Authentication

The API uses Personal Access Token authentication with two required headers:

- `X-Access-Key`: 24-character alphanumeric access key
- `X-Access-Secret`: 32-character alphanumeric access secret

### Generating Access Tokens

1. Log into the admin panel
2. Navigate to **Users Management** → **Users**
3. Edit the desired user
4. Click **"Generate Personal Access Token"** button
5. Copy the `access_key` and `access_secret` from the notification
6. Tokens can be managed in the **"Personal Access Tokens"** tab

### Token Management

- View all tokens for a user in the Personal Access Tokens section
- Activate/deactivate tokens as needed
- No editing or deleting allowed (security feature)
- Inactive tokens will be rejected by the API

## Endpoints

### Create User Purchase Request

Creates a new purchase request with associated items.

**Endpoint:** `POST /api/user-purchase-requests`

**Headers:**
```http
Content-Type: application/json
X-Access-Key: your-24-char-access-key
X-Access-Secret: your-32-char-access-secret
```

**Request Body:**
```json
{
  "purpose": "Office supplies for Q1 2025",
  "contact_no": "1234567890",
  "requested_delivery_date": "2025-08-15",
  "remarks": "Urgent requirement - please prioritize",
  "items": [
    {
      "product_details": "A4 Paper - Premium quality, 80 GSM",
      "quantity": 10,
      "uom": "Box"
    },
    {
      "product_details": "Blue ballpoint pens",
      "quantity": 50,
      "uom": "Piece"
    },
    {
      "product_details": "Stapler - Heavy duty",
      "quantity": 2,
      "uom": "Piece"
    }
  ]
}
```

**Field Descriptions:**

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `purpose` | string | Yes | 511 chars | Purpose of the purchase request |
| `contact_no` | string | Yes | 12 chars | Contact number for this request |
| `requested_delivery_date` | date | Yes | - | Delivery date (YYYY-MM-DD format, cannot be in the past) |
| `remarks` | string | No | - | Additional remarks or notes |
| `items` | array | Yes | - | Array of items to be purchased (minimum 1 item) |
| `items[].product_details` | string | Yes | 255 chars | Detailed description of the product |
| `items[].quantity` | number | Yes | - | Quantity required (minimum 1) |
| `items[].uom` | string | Yes | 50 chars | Unit of measurement (e.g., "Piece", "Box", "Kg") |

**Automatic Fields:**
- `user_id`: Automatically set from the authenticated user's token
- `department_id`: Automatically retrieved from the Employee table based on user_id
- `status`: Automatically set to "pending"

## Response Format

### Success Response (201 Created)

```json
{
  "message": "User Purchase Request created successfully",
  "data": {
    "user_purchase_request": {
      "id": 123,
      "user_id": 1,
      "department_id": 5,
      "purpose": "Office supplies for Q1 2025",
      "contact_no": "1234567890",
      "requested_delivery_date": "2025-08-15",
      "remarks": "Urgent requirement - please prioritize",
      "status": "pending",
      "created_at": "2025-07-28T10:30:00.000000Z",
      "updated_at": "2025-07-28T10:30:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@company.com"
      },
      "department": {
        "id": 5,
        "name": "IT Department"
      },
      "purchase_request_items": [
        {
          "id": 456,
          "user_purchase_request_id": 123,
          "product_details": "A4 Paper - Premium quality, 80 GSM",
          "quantity": 10,
          "uom": "Box",
          "created_at": "2025-07-28T10:30:00.000000Z",
          "updated_at": "2025-07-28T10:30:00.000000Z"
        }
      ]
    },
    "items": [
      {
        "id": 456,
        "user_purchase_request_id": 123,
        "product_details": "A4 Paper - Premium quality, 80 GSM",
        "quantity": 10,
        "uom": "Box",
        "created_at": "2025-07-28T10:30:00.000000Z",
        "updated_at": "2025-07-28T10:30:00.000000Z"
      }
    ]
  }
}
```

## Error Responses

### Authentication Errors (401 Unauthorized)

**Missing Headers:**
```json
{
  "error": "Authentication failed",
  "message": "Access key and secret are required in headers"
}
```

**Invalid Token:**
```json
{
  "error": "Authentication failed",
  "message": "Invalid or inactive access token"
}
```

### Employee Not Found (404 Not Found)

```json
{
  "error": "Employee record not found",
  "message": "No employee record or department found for this user"
}
```

### Validation Errors (422 Unprocessable Entity)

```json
{
  "message": "The purpose field is required. (and 2 more errors)",
  "errors": {
    "purpose": [
      "Purpose is required"
    ],
    "contact_no": [
      "Contact number is required"
    ],
    "items.0.quantity": [
      "Quantity must be at least 1"
    ]
  }
}
```

### Server Error (500 Internal Server Error)

```json
{
  "error": "Internal server error",
  "message": "Failed to create purchase request"
}
```

## Example Usage

### cURL Example

```bash
curl -X POST https://your-domain.com/api/user-purchase-requests \
  -H "Content-Type: application/json" \
  -H "X-Access-Key: abc123def456ghi789jkl012" \
  -H "X-Access-Secret: mno345pqr678stu901vwx234yz567890" \
  -d '{
    "purpose": "Monthly office supplies",
    "contact_no": "9876543210",
    "requested_delivery_date": "2025-08-20",
    "remarks": "Please deliver to main office reception",
    "items": [
      {
        "product_details": "Printer paper A4 - 500 sheets per ream",
        "quantity": 5,
        "uom": "Ream"
      },
      {
        "product_details": "Black ink cartridge for HP LaserJet",
        "quantity": 3,
        "uom": "Piece"
      }
    ]
  }'
```

### JavaScript/Fetch Example

```javascript
const createPurchaseRequest = async () => {
  try {
    const response = await fetch('https://your-domain.com/api/user-purchase-requests', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Access-Key': 'abc123def456ghi789jkl012',
        'X-Access-Secret': 'mno345pqr678stu901vwx234yz567890'
      },
      body: JSON.stringify({
        purpose: 'Monthly office supplies',
        contact_no: '9876543210',
        requested_delivery_date: '2025-08-20',
        remarks: 'Please deliver to main office reception',
        items: [
          {
            product_details: 'Printer paper A4 - 500 sheets per ream',
            quantity: 5,
            uom: 'Ream'
          },
          {
            product_details: 'Black ink cartridge for HP LaserJet',
            quantity: 3,
            uom: 'Piece'
          }
        ]
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      console.log('Purchase request created:', data);
    } else {
      console.error('Error:', data);
    }
  } catch (error) {
    console.error('Network error:', error);
  }
};
```

### Python Example

```python
import requests
import json

def create_purchase_request():
    url = "https://your-domain.com/api/user-purchase-requests"
    
    headers = {
        "Content-Type": "application/json",
        "X-Access-Key": "abc123def456ghi789jkl012",
        "X-Access-Secret": "mno345pqr678stu901vwx234yz567890"
    }
    
    data = {
        "purpose": "Monthly office supplies",
        "contact_no": "9876543210",
        "requested_delivery_date": "2025-08-20",
        "remarks": "Please deliver to main office reception",
        "items": [
            {
                "product_details": "Printer paper A4 - 500 sheets per ream",
                "quantity": 5,
                "uom": "Ream"
            },
            {
                "product_details": "Black ink cartridge for HP LaserJet",
                "quantity": 3,
                "uom": "Piece"
            }
        ]
    }
    
    try:
        response = requests.post(url, headers=headers, json=data)
        response.raise_for_status()
        
        result = response.json()
        print("Purchase request created:", json.dumps(result, indent=2))
        
    except requests.exceptions.RequestException as e:
        print(f"Error: {e}")
        if hasattr(e, 'response') and e.response is not None:
            print("Response:", e.response.text)

# Usage
create_purchase_request()
```

## Validation Rules

### Main Request Fields

- **purpose**: Required, string, maximum 511 characters
- **contact_no**: Required, string, maximum 12 characters
- **requested_delivery_date**: Required, valid date format (YYYY-MM-DD), cannot be in the past
- **remarks**: Optional, string, no length limit
- **items**: Required, array, minimum 1 item

### Item Fields

- **product_details**: Required, string, maximum 255 characters
- **quantity**: Required, numeric, minimum value 1
- **uom**: Required, string, maximum 50 characters

## Important Notes

1. **Department Assignment**: The `department_id` is automatically assigned based on the user's Employee record. If no Employee record exists for the user, the request will fail with a 404 error.

2. **Status Management**: All new purchase requests are created with status "pending" and follow the standard approval workflow.

3. **Rate Limiting**: Consider implementing rate limiting on your server to prevent abuse of the API.

4. **Token Security**: 
   - Keep access tokens secure and never expose them in client-side code
   - Tokens can be deactivated if compromised
   - Generate separate tokens for different applications/integrations

5. **Date Format**: All dates should be in ISO 8601 format (YYYY-MM-DD)

6. **Error Logging**: All API errors are logged on the server for debugging and monitoring purposes.

## Troubleshooting

### Common Issues

1. **401 Authentication Failed**
   - Verify access key and secret are correct
   - Check if token is active in the admin panel
   - Ensure headers are properly set

2. **404 Employee Record Not Found**
   - Verify the user has an associated Employee record
   - Check if the Employee record has a valid department_id

3. **422 Validation Error**
   - Review the validation rules above
   - Check the error response for specific field issues
   - Ensure date format is correct (YYYY-MM-DD)

4. **500 Internal Server Error**
   - Check server logs for detailed error information
   - Verify database connectivity
   - Contact system administrator

### Support

For additional support or questions about the API, please contact the system administrator or refer to the application logs for detailed error information.
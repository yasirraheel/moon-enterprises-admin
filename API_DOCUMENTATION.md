# MOON ENTERPRISES - Prize Bond Booking System API Documentation

## Create Order API

### Endpoint
```
POST /api/orders/create
```

### Authentication
This endpoint requires authentication using Laravel Sanctum. Include the Bearer token in the Authorization header.

### Headers
```
Content-Type: application/json
Authorization: Bearer {your_token}
Accept: application/json
```

### Request Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `game_name` | string | Yes | Name of the game | "Pakistan Prize Bond" |
| `bond_name` | string | Yes | Name of the bond | "Bond 1 Muzafarabad" |
| `rttp` | string | Yes | RTTP value | "12345" |
| `first` | number | Yes | First amount (numeric) | 67890 |
| `second` | number | Yes | Second amount (numeric) | 11111 |
| `user_phone` | string | No | User's phone number (optional, will use profile phone if not provided) | "+1234567890" |

### Request Example
```json
{
    "game_name": "Pakistan Prize Bond",
    "bond_name": "Bond 1 Muzafarabad", 
    "rttp": "12345",
    "first": 67890,
    "second": 11111,
    "user_phone": "+1234567890"
}
```

### Success Response (201 Created)
```json
{
    "success": true,
    "message": "Order created successfully",
                "data": {
                    "id": 1,
                    "user_id": 123,
                    "username": "john_doe",
                    "user_phone": "+1234567890",
                    "game_name": "Pakistan Prize Bond",
                    "bond_name": "Bond 1 Muzafarabad",
                    "rttp": "12345",
                    "first": 67890,
                    "second": 11111,
                    "total_amount": 78900,
                    "status": "pending",
                    "created_at": "2025-10-10 15:30:45",
                    "balance_after": 2100
                }
}
```

### Error Responses

#### 401 Unauthorized (Not Authenticated)
```json
{
    "success": false,
    "message": "Authentication required"
}
```

#### 400 Bad Request (Insufficient Balance)
```json
{
    "success": false,
    "message": "Insufficient balance. Required: 78900, Available: 5000"
}
```

#### 422 Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "game_name": ["The game name field is required."],
        "first": ["The first field must be a number."],
        "second": ["The second field must be a number."]
    }
}
```

#### 500 Server Error
```json
{
    "success": false,
    "message": "Server error occurred"
}
```

## Usage Examples

### JavaScript/Fetch
```javascript
const createOrder = async (orderData, token) => {
    try {
        const response = await fetch('/api/orders/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify(orderData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('Order created:', result.data);
            return result.data;
        } else {
            console.error('Error:', result.message);
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
};

// Usage
const orderData = {
    game_name: "Pakistan Prize Bond",
    bond_name: "Bond 1 Muzafarabad",
    rttp: "12345",
    first: 67890,
    second: 11111,
    user_phone: "+1234567890"
};

createOrder(orderData, 'your_bearer_token')
    .then(order => {
        console.log('Order created successfully:', order);
    })
    .catch(error => {
        console.error('Failed to create order:', error);
    });
```

### cURL
```bash
curl -X POST "https://yourdomain.com/api/orders/create" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_bearer_token" \
  -H "Accept: application/json" \
  -d '{
    "game_name": "Pakistan Prize Bond",
    "bond_name": "Bond 1 Muzafarabad",
    "rttp": "12345",
    "first": 67890,
    "second": 11111,
    "user_phone": "+1234567890"
  }'
```

### PHP
```php
<?php
$url = 'https://yourdomain.com/api/orders/create';
$data = [
    'game_name' => 'Pakistan Prize Bond',
    'bond_name' => 'Bond 1 Muzafarabad',
    'rttp' => '12345',
    'first' => 67890,
    'second' => 11111,
    'user_phone' => '+1234567890'
];

$options = [
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'Authorization: Bearer your_bearer_token',
            'Accept: application/json'
        ],
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "Error creating order";
} else {
    $response = json_decode($result, true);
    if ($response['success']) {
        echo "Order created successfully: " . $response['data']['id'];
    } else {
        echo "Error: " . $response['message'];
    }
}
?>
```

### Flutter/Dart
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class OrderService {
  static const String baseUrl = 'https://yourdomain.com/api';
  
  static Future<Map<String, dynamic>> createOrder({
    required String token,
    required String gameName,
    required String bondName,
    required String rttp,
    required String first,
    required String second,
    String? userPhone,
  }) async {
    final url = Uri.parse('$baseUrl/orders/create');
    
    final headers = {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    };
    
    final body = {
      'game_name': gameName,
      'bond_name': bondName,
      'rttp': rttp,
      'first': first,
      'second': second,
      if (userPhone != null) 'user_phone': userPhone,
    };
    
    try {
      final response = await http.post(
        url,
        headers: headers,
        body: json.encode(body),
      );
      
      final data = json.decode(response.body);
      
      if (response.statusCode == 201 && data['success']) {
        return data['data'];
      } else {
        throw Exception(data['message'] ?? 'Failed to create order');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}

// Usage
try {
  final order = await OrderService.createOrder(
    token: 'your_bearer_token',
    gameName: 'Pakistan Prize Bond',
    bondName: 'Bond 1 Muzafarabad',
    rttp: '12345',
    first: 67890,
    second: 11111,
    userPhone: '+1234567890',
  );
  print('Order created: ${order['id']}');
} catch (e) {
  print('Error: $e');
}
```

## Authentication

To use this API, you need to authenticate first using the login endpoint:

### Login Endpoint
```
POST /api/login
```

### Login Request
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

### Login Response
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "username": "john_doe"
        },
        "token": "1|abc123def456ghi789..."
    }
}
```

Use the `token` from the login response as the Bearer token for subsequent API calls.

## Notes

1. **Authentication Required**: All order creation requests must include a valid Bearer token.
2. **Auto-filled Fields**: The `username` is automatically filled from the authenticated user's profile.
3. **Phone Number**: If `user_phone` is not provided, it will use the user's profile phone number.
4. **Status**: All new orders are created with `pending` status by default.
5. **Validation**: All required fields are validated on the server side.
6. **Balance Deduction**: The total amount (first + second) is automatically deducted from the user's balance.
7. **Insufficient Balance**: If the user doesn't have enough balance, the order creation will fail with a 400 error.
8. **Numeric Values**: The `first` and `second` fields must be numeric values, not strings.
9. **Rate Limiting**: The API may have rate limiting in place to prevent abuse.

## Error Handling

Always check the `success` field in the response to determine if the request was successful. Handle different HTTP status codes appropriately:

- `201`: Order created successfully
- `401`: Authentication required
- `422`: Validation errors
- `500`: Server error

## Get User Orders API

### Endpoint
```
GET /api/orders
```

### Authentication
This endpoint requires authentication using Laravel Sanctum. Include the Bearer token in the Authorization header.

### Headers
```
Authorization: Bearer {your_token}
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `per_page` | integer | No | Number of orders per page (default: 20) | `10` |
| `page` | integer | No | Page number (default: 1) | `2` |
| `status` | string | No | Filter by status: `pending`, `approved`, `rejected` | `pending` |

### Request Examples

#### Get all orders (default pagination)
```
GET /api/orders
```

#### Get orders with custom pagination
```
GET /api/orders?per_page=10&page=2
```

#### Get only pending orders
```
GET /api/orders?status=pending
```

#### Get approved orders with custom pagination
```
GET /api/orders?status=approved&per_page=5&page=1
```

### Success Response (200 OK)
```json
{
    "success": true,
    "message": "Orders retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 123,
            "username": "john_doe",
            "user_phone": "+1234567890",
            "game_name": "Pakistan Prize Bond",
            "bond_name": "Bond 1 Muzafarabad",
            "rttp": "12345",
            "first": "67890",
            "second": "11111",
            "status": "pending",
            "created_at": "2025-10-10 15:30:45",
            "updated_at": "2025-10-10 15:30:45"
        },
        {
            "id": 2,
            "user_id": 123,
            "username": "john_doe",
            "user_phone": "+1234567890",
            "game_name": "Thailand Draw",
            "bond_name": "Bond 2 Bangkok",
            "rttp": "54321",
            "first": "98765",
            "second": "22222",
            "status": "approved",
            "created_at": "2025-10-09 10:15:30",
            "updated_at": "2025-10-09 14:20:15"
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 3,
        "per_page": 20,
        "total": 45,
        "from": 1,
        "to": 20,
        "has_more_pages": true
    }
}
```

### Error Responses

#### 401 Unauthorized (Not Authenticated)
```json
{
    "success": false,
    "message": "Authentication required"
}
```

#### 500 Server Error
```json
{
    "success": false,
    "message": "Server error occurred"
}
```

### Usage Examples

#### JavaScript/Fetch
```javascript
const getUserOrders = async (token, options = {}) => {
    try {
        const params = new URLSearchParams();
        if (options.perPage) params.append('per_page', options.perPage);
        if (options.page) params.append('page', options.page);
        if (options.status) params.append('status', options.status);
        
        const url = `/api/orders${params.toString() ? '?' + params.toString() : ''}`;
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('Orders:', result.data);
            console.log('Pagination:', result.pagination);
            return result;
        } else {
            console.error('Error:', result.message);
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
};

// Usage examples
getUserOrders('your_bearer_token')
    .then(result => {
        console.log('All orders:', result.data);
    })
    .catch(error => {
        console.error('Failed to get orders:', error);
    });

// Get only pending orders
getUserOrders('your_bearer_token', { status: 'pending' })
    .then(result => {
        console.log('Pending orders:', result.data);
    });

// Get orders with custom pagination
getUserOrders('your_bearer_token', { perPage: 10, page: 2 })
    .then(result => {
        console.log('Page 2 orders:', result.data);
        console.log('Has more pages:', result.pagination.has_more_pages);
    });
```

#### cURL
```bash
# Get all orders
curl -X GET "https://yourdomain.com/api/orders" \
  -H "Authorization: Bearer your_bearer_token" \
  -H "Accept: application/json"

# Get pending orders only
curl -X GET "https://yourdomain.com/api/orders?status=pending" \
  -H "Authorization: Bearer your_bearer_token" \
  -H "Accept: application/json"

# Get orders with custom pagination
curl -X GET "https://yourdomain.com/api/orders?per_page=10&page=2" \
  -H "Authorization: Bearer your_bearer_token" \
  -H "Accept: application/json"
```

#### Flutter/Dart
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class OrderService {
  static const String baseUrl = 'https://yourdomain.com/api';
  
  static Future<Map<String, dynamic>> getUserOrders({
    required String token,
    int? perPage,
    int? page,
    String? status,
  }) async {
    final params = <String, String>{};
    if (perPage != null) params['per_page'] = perPage.toString();
    if (page != null) params['page'] = page.toString();
    if (status != null) params['status'] = status;
    
    final uri = Uri.parse('$baseUrl/orders').replace(queryParameters: params);
    
    final headers = {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    };
    
    try {
      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      
      if (response.statusCode == 200 && data['success']) {
        return data;
      } else {
        throw Exception(data['message'] ?? 'Failed to get orders');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}

// Usage
try {
  final result = await OrderService.getUserOrders(
    token: 'your_bearer_token',
    perPage: 10,
    page: 1,
    status: 'pending',
  );
  
  print('Orders: ${result['data']}');
  print('Total: ${result['pagination']['total']}');
} catch (e) {
  print('Error: $e');
}
```

## Support

For API support or questions, please contact the development team.

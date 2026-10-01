# Abu Endowment API Documentation

## Base URL
```
https://your-domain.com/api
```

## Authentication
- Most endpoints are public (no authentication required)
- Protected endpoints require `Authorization: Bearer {token}` header
- Admin endpoints require admin role

---

## 🔍 Donor Search Endpoints

### Search by Registration Number
```http
GET /api/donors/search/{reg_number}
```
**Example:** `GET /api/donors/search/ABU123456`

### Search by Email
```http
GET /api/donors/search/email/{email}
```
**Example:** `GET /api/donors/search/email/john@example.com`

### Search by Phone Number
```http
GET /api/donors/search/phone/{phone}
```
**Example:** `GET /api/donors/search/phone/+2348123456789`

**Response Format:**
```json
{
  "success": true,
  "donor": {
    "id": 1,
    "name": "John",
    "surname": "Doe", 
    "email": "john@example.com",
    "phone": "+2348123456789",
    "reg_number": "ABU123456",
    "donor_type": "addressable_alumni",
    "faculty": "Engineering",
    "department": "Computer Science"
  }
}
```

---

## 📱 Device Session Management

### Register Device Session
```http
POST /api/devices/register
```
**Body:**
```json
{
  "device_fingerprint": "unique-device-id",
  "donor_id": 123,
  "expires_in": 10080
}
```

### Check Session Validity
```http
POST /api/sessions/check
```
**Body:**
```json
{
  "session_id": 456,
  "device_fingerprint": "unique-device-id"
}
```

### Check Device Recognition
```http
GET /api/devices/check/{fingerprint}
```
**Example:** `GET /api/devices/check/unique-device-id`

**Response Format:**
```json
{
  "recognized": true,
  "donor": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "total_donations": 50000
  },
  "session_id": 456,
  "expires_at": "2024-01-01T00:00:00Z"
}
```

---

## 👥 Donor Management

### Create New Donor
```http
POST /api/donors
```
**Body:**
```json
{
  "name": "John",
  "surname": "Doe",
  "email": "john@example.com",
  "phone": "+2348123456789",
  "reg_number": "ABU123456",
  "donor_type": "addressable_alumni",
  "entry_year": 2015,
  "graduation_year": 2019,
  "faculty_vision_id": 1,
  "department_vision_id": 1
}
```

### Update Donor
```http
PUT /api/donors/{id}
```
**Body:** Same as create donor

---

## 🏛️ Faculty & Department Data

### Get All Faculties
```http
GET /api/faculties
```

### Get Departments by Faculty
```http
GET /api/faculties/{id}/departments
```
**Example:** `GET /api/faculties/1/departments`

### Public Fallback Endpoints
```http
GET /api/public/faculties
GET /api/public/faculties/{id}/departments
```

**Response Format:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "current_name": "Engineering",
      "visions": [
        {
          "id": 1,
          "name": "Faculty of Engineering (1970-1990)",
          "start_year": 1970,
          "end_year": 1990
        }
      ]
    }
  ]
}
```

---

## 💳 Payment Processing

### Initialize Payment
```http
POST /api/payments/initialize
```
**Body:**
```json
{
  "donor_id": 123,
  "project_id": 1,
  "amount": 10000,
  "currency": "NGN",
  "callback_url": "https://your-app.com/payment/callback"
}
```

### Verify Payment
```http
GET /api/payments/verify/{reference}
```
**Example:** `GET /api/payments/verify/TXN_123456789`

**Response Format:**
```json
{
  "success": true,
  "data": {
    "reference": "TXN_123456789",
    "amount": 10000,
    "currency": "NGN",
    "status": "success",
    "gateway_response": "Approved",
    "paid_at": "2024-01-01T00:00:00Z"
  }
}
```

---

## 📊 Additional Endpoints

### Get Projects
```http
GET /api/projects
```

### Get Projects with Photos
```http
GET /api/projects-with-photos
```

### Alumni Contacts
```http
GET /api/alumni/contacts
```

### Faculty Vision (by year range)
```http
GET /api/faculty-vision?entry_year=2015&graduation_year=2019
```

### Department Vision (by faculty and year range)
```http
GET /api/department-vision?faculty_id=1&entry_year=2015&graduation_year=2019
```

---

---

## 🔐 Password Reset (Link-Based)

### Request Reset Link
```http
POST /api/donor-sessions/forgot-password
```
**Body:**
```json
{
  "email": "user@example.com"
}
```
**Note:** Always returns success message for security.

### Verify Reset Token
```http
GET /api/donor-sessions/reset/{token}
```
**Response:** Returns `{ "success": true, "data": { "username": "..." } }` if valid.

### Submit New Password
```http
POST /api/donor-sessions/reset/{token}
```
**Body:**
```json
{
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

---

## 🔐 Protected Endpoints (Require Authentication)

### Donation Management
```http
POST /api/donations
GET /api/donations/summary
GET /api/donations/history
```

### Rankings
```http
GET /api/rankings
GET /api/rankings/top-donors
GET /api/rankings/faculty
GET /api/rankings/department
```

---

## ⚡ Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

---

## 📝 Notes

1. **Rate Limiting:** API endpoints may have rate limiting applied
2. **CORS:** Configured to allow cross-origin requests
3. **Validation:** All input data is validated before processing
4. **Logging:** All API requests and errors are logged
5. **Security:** Sensitive operations require proper authentication

## 🧪 Testing

Use tools like Postman or curl to test endpoints:

```bash
# Test donor search
curl -X GET "https://your-domain.com/api/donors/search/ABU123456"

# Test device registration
curl -X POST "https://your-domain.com/api/devices/register" \
  -H "Content-Type: application/json" \
  -d '{"device_fingerprint":"test-device","donor_id":1}'
```
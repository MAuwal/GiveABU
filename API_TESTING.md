# API Testing Guide

## Quick API Tests

Test these endpoints in your browser or with curl to ensure they're working:

### 1. Test Basic API Response
```
GET http://localhost:8000/api/test
```
Expected: JSON response with success message

### 2. Test Faculty Data (Public)
```
GET http://localhost:8000/api/faculties
GET http://localhost:8000/api/public/faculties
```
Expected: JSON response with faculty list

### 3. Test Department Data (Public)
```
GET http://localhost:8000/api/faculties/1/departments
GET http://localhost:8000/api/public/faculties/1/departments
```
Expected: JSON response with departments for faculty ID 1

### 4. Test Donor Search (Public)
```
GET http://localhost:8000/api/donors/search/ABU123456
GET http://localhost:8000/api/donors/search/email/test@example.com
GET http://localhost:8000/api/donors/search/phone/+2348123456789
```
Expected: JSON response with donor data or not found message

### 5. Test Device Registration (Public)
```
POST http://localhost:8000/api/devices/register
Content-Type: application/json

{
    "device_fingerprint": "test-device-123",
    "donor_id": 1,
    "expires_in": 10080
}
```
Expected: JSON response with session details

## CORS Testing

From your React app, test these fetch calls:

```javascript
// Test basic API
fetch('http://localhost:8000/api/test')
    .then(res => res.json())
    .then(data => console.log(data));

// Test faculties
fetch('http://localhost:8000/api/public/faculties')
    .then(res => res.json())
    .then(data => console.log(data));
```

## Expected JSON Response Format

All endpoints should return JSON in this format:

### Success Response
```json
{
    "success": true,
    "data": [...],
    "message": "Optional success message"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "error": "Detailed error message",
    "errors": {...}
}
```

## Troubleshooting

If you still get HTML responses:

1. **Check CORS**: Ensure requests include proper headers
2. **Clear Cache**: Run `php artisan config:clear`
3. **Check URL**: Ensure you're hitting `/api/` routes
4. **Check Headers**: API should return `Content-Type: application/json`

## Browser Network Tab

In browser dev tools, verify:
- ✅ Status: 200 OK (not 302 redirect)
- ✅ Content-Type: application/json
- ✅ Response: Valid JSON (not HTML)
- ✅ CORS headers present
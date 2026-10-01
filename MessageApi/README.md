# SMS Service Integration - ABU Endowment

This document explains how to set up and use the SMS service with Ozeki SMS Gateway in the ABU Endowment project.

## 📋 Prerequisites

1. **Ozeki SMS Gateway** installed and running
2. **Mobile network connection** configured in Ozeki
3. **HTTP API user** created in Ozeki with username `http_user` and password `qwe123`

## 🔧 Installation & Setup

### 1. Install Ozeki SMS Gateway

1. Download Ozeki SMS Gateway from [https://www.ozeki.com](https://www.ozeki.com)
2. Install it on your server or local machine
3. Configure mobile network connection (GSM modem, USB dongle, or internet SMS gateway)
4. Test SMS sending from Ozeki GUI

### 2. Create HTTP API User

1. Open Ozeki SMS Gateway
2. Go to **Users** → **HTTP API Users**
3. Create a new user with:
   - **Username**: `http_user`
   - **Password**: `qwe123`
   - **Permissions**: Send SMS, Receive SMS

### 3. Configure Environment Variables

Add these variables to your `.env` file:

```env
# Ozeki SMS Gateway Configuration
OZEKI_USERNAME=http_user
OZEKI_PASSWORD=qwe123
OZEKI_API_URL=http://127.0.0.1:9509/api?action=rest
SMS_VERIFICATION_ENABLED=true
```

**Note**: Replace `127.0.0.1` with the IP address of your Ozeki SMS Gateway if it's on a different machine.

## 🚀 Usage

### 1. Send Verification SMS

```php
use App\Services\SmsService;

$smsService = new SmsService();
$result = $smsService->sendVerificationSms('+2348012345678', '123456');
```

### 2. Send Custom SMS

```php
$result = $smsService->sendSms('+2348012345678', 'Your custom message', [
    'tag' => 'custom',
    'submit_report' => true,
    'delivery_report' => true
]);
```

### 3. Send Welcome SMS

```php
$result = $smsService->sendWelcomeSms('+2348012345678', 'John Doe');
```

### 4. Send Donation Confirmation

```php
$result = $smsService->sendDonationConfirmationSms(
    '+2348012345678', 
    'John Doe', 
    50000, 
    'Faculty of Engineering Project'
);
```

## 🧪 Testing

### Test SMS Service

```bash
php artisan sms:test +2348012345678
```

### Test via API

```bash
curl -X POST http://localhost:8000/api/verification/send-sms \
  -H "Content-Type: application/json" \
  -d '{"phone": "+2348012345678"}'
```

## 📊 API Endpoints

### Send SMS Verification
- **POST** `/api/verification/send-sms`
- **Body**: `{"phone": "+2348012345678"}`

### Verify SMS Code
- **POST** `/api/verification/verify-sms`
- **Body**: `{"phone": "+2348012345678", "code": "123456"}`

## 🔍 Troubleshooting

### Common Issues

1. **Connection Failed**
   - Check if Ozeki SMS Gateway is running
   - Verify API URL and credentials
   - Check firewall settings

2. **SMS Not Delivered**
   - Verify mobile network connection in Ozeki
   - Check SIM card balance
   - Review Ozeki logs for errors

3. **Authentication Error**
   - Verify username and password
   - Check HTTP API user permissions

### Logs

Check Laravel logs for SMS service errors:
```bash
tail -f storage/logs/laravel.log
```

Check Ozeki SMS Gateway logs for delivery status.

## 📱 Phone Number Format

The service automatically formats phone numbers to international format:
- `08012345678` → `+2348012345678`
- `+2348012345678` → `+2348012345678` (unchanged)

## 🔒 Security

- SMS codes expire after 10 minutes
- Codes are stored in cache, not database
- Failed attempts are logged
- Phone numbers are validated and formatted

## 📈 Monitoring

Monitor SMS delivery through:
- Laravel logs (`storage/logs/laravel.log`)
- Ozeki SMS Gateway logs
- Database records (if implemented)

## 🆘 Support

For issues with:
- **Ozeki SMS Gateway**: Contact Ozeki support
- **Laravel Integration**: Check Laravel logs and documentation
- **ABU Endowment Project**: Contact development team 
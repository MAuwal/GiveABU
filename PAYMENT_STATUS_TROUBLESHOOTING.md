# Payment Status Troubleshooting Guide

## Problem: Donations Always Show "Pending" Status

If donations are always showing as "pending" instead of "completed" after payment, follow these steps:

---

## ✅ Backend Fixes Applied

### 1. **Enhanced Payment Verification**

The `verify()` method has been improved with:
- ✅ Better logging for debugging
- ✅ Handles both initial reference and Paystack reference
- ✅ Checks multiple status indicators (`status` and `gateway_response`)
- ✅ Updates payment_reference if Paystack returns a different one
- ✅ Comprehensive error logging

### 2. **Status Check Logic**

```php
$isSuccessful = ($data['status'] === 'success') || 
               (isset($data['gateway_response']) && 
                strtolower($data['gateway_response']) === 'successful');
```

Now checks both:
- Paystack `status` field
- Paystack `gateway_response` field

---

## 🔍 Debugging Steps

### Step 1: Check Logs

```bash
# View payment verification logs
tail -f storage/logs/laravel.log | grep "Payment verification"

# View Paystack responses
tail -f storage/logs/laravel.log | grep "Paystack verification response"

# View donation status updates
tail -f storage/logs/laravel.log | grep "Donation status updated"
```

### Step 2: Verify Frontend is Calling Verify Endpoint

**Expected Frontend Call:**
```javascript
// After Paystack payment success
window.location.href = `/api/payments/verify/${response.reference}?redirect=${encodeURIComponent(window.location.origin + '/?payment_status=success')}`;
```

**Check:**
- Is the frontend redirecting to `/api/payments/verify/{reference}`?
- Is the `reference` from Paystack callback being passed correctly?

### Step 3: Check Paystack Response

The backend now logs the full Paystack response. Check logs for:
```json
{
  "status": "success",  // Should be "success"
  "gateway_response": "Successful",  // Should be "Successful"
  "paid_at": "2025-01-15T10:00:00.000Z",
  "amount": 100000  // Amount in kobo
}
```

### Step 4: Verify Donation Record

Check if donation exists with matching reference:

```sql
-- Check donation by reference
SELECT id, payment_reference, status, amount, created_at, verified_at, paid_at
FROM donations
WHERE payment_reference = 'YOUR_REFERENCE_HERE';

-- Check all pending donations
SELECT id, payment_reference, status, amount, created_at
FROM donations
WHERE status = 'pending'
ORDER BY created_at DESC;
```

---

## 🚨 Common Issues & Solutions

### Issue 1: Frontend Not Calling Verify Endpoint

**Symptom:** Donations stay "pending", no verification logs

**Solution:**
1. Check frontend Paystack callback
2. Ensure redirect happens after payment success
3. Verify the route is accessible: `GET /api/payments/verify/{reference}`

**Frontend Fix:**
```javascript
callback: function(response) {
    // ✅ This should redirect to verify endpoint
    window.location.href = `/api/payments/verify/${response.reference}?redirect=${encodeURIComponent(window.location.origin + '/?payment_status=success')}`;
}
```

### Issue 2: Reference Mismatch

**Symptom:** "Donation not found" in logs

**Solution:**
The backend now tries both:
- The reference from the request
- The reference from Paystack response

**Check:**
- Are payment_reference values matching?
- Is Paystack returning a different reference than we sent?

### Issue 3: Paystack Status Not "success"

**Symptom:** Status shows in logs but donation not updated

**Solution:**
The backend now checks both:
- `data['status'] === 'success'`
- `data['gateway_response'] === 'Successful'`

**Check logs for actual Paystack response:**
```bash
grep "Paystack verification response" storage/logs/laravel.log
```

### Issue 4: Webhook Not Configured

**Symptom:** Only manual verification works, not automatic

**Solution:**
1. Configure Paystack webhook URL: `https://your-domain.com/api/payments/webhook`
2. Ensure webhook secret is set in `.env`:
   ```
   PAYSTACK_WEBHOOK_SECRET=your_webhook_secret
   ```
3. Check webhook logs:
   ```bash
   tail -f storage/logs/laravel.log | grep "Paystack webhook"
   ```

---

## 🧪 Manual Verification

### Option 1: Use API Endpoint

```bash
# Replace REFERENCE with actual Paystack reference
curl "https://your-domain.com/api/payments/verify/REFERENCE"
```

### Option 2: Database Query

```sql
-- Manually update donation status (use with caution)
UPDATE donations
SET status = 'completed',
    verified_at = NOW(),
    paid_at = NOW()
WHERE payment_reference = 'YOUR_REFERENCE'
AND status = 'pending';
```

### Option 3: Artisan Command (Create if needed)

```php
// In app/Console/Commands/VerifyPayment.php
php artisan payment:verify REFERENCE
```

---

## 📊 Status Flow

### Normal Flow:
```
1. Payment Initialized → status = 'pending'
2. User pays via Paystack
3. Paystack redirects to callback
4. Frontend calls /api/payments/verify/{reference}
5. Backend verifies with Paystack API
6. Backend updates → status = 'completed'
7. Project raised amount updated
8. Thank you email sent
```

### Webhook Flow (Backup):
```
1. Payment Initialized → status = 'pending'
2. User pays via Paystack
3. Paystack sends webhook to /api/payments/webhook
4. Backend verifies webhook signature
5. Backend updates → status = 'completed'
6. Project raised amount updated
7. Thank you email sent
```

---

## ✅ Verification Checklist

- [ ] Frontend redirects to verify endpoint after payment
- [ ] Verify endpoint is accessible (`GET /api/payments/verify/{reference}`)
- [ ] Paystack returns `status: "success"` in response
- [ ] Donation record exists with matching `payment_reference`
- [ ] Logs show "Donation status updated" message
- [ ] Database shows `status = 'completed'` after verification
- [ ] Webhook is configured (optional but recommended)

---

## 🔧 Quick Fixes

### Fix 1: Add Manual Verify Button (Admin)

```php
// In admin donations table
@if($donation->status === 'pending')
    <a href="/api/payments/verify/{{ $donation->payment_reference }}" 
       class="btn btn-sm btn-primary">
        Verify Payment
    </a>
@endif
```

### Fix 2: Auto-Verify on Page Load (Frontend)

```javascript
// Check URL for payment reference
const urlParams = new URLSearchParams(window.location.search);
const reference = urlParams.get('reference');

if (reference) {
    // Auto-verify payment
    fetch(`/api/payments/verify/${reference}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log('Payment verified:', data);
                // Refresh page or update UI
            }
        });
}
```

### Fix 3: Scheduled Job to Verify Pending Payments

```php
// In app/Console/Kernel.php
$schedule->command('payments:verify-pending')->hourly();

// Create command to verify all pending payments older than 1 hour
```

---

## 📝 Log Examples

### Successful Verification:
```
[2025-01-15 10:00:00] local.INFO: Payment verification attempt {"reference":"ABU_1234567890_1"}
[2025-01-15 10:00:01] local.INFO: Paystack verification response {"status":"success","gateway_response":"Successful"}
[2025-01-15 10:00:01] local.INFO: Donation status updated {"old_status":"pending","new_status":"completed"}
```

### Failed Verification:
```
[2025-01-15 10:00:00] local.INFO: Payment verification attempt {"reference":"ABU_1234567890_1"}
[2025-01-15 10:00:01] local.WARNING: Payment verification: Donation not found {"reference":"ABU_1234567890_1"}
```

---

## 🎯 Next Steps

1. **Check Logs First** - Most issues are visible in logs
2. **Verify Frontend** - Ensure verify endpoint is being called
3. **Test Manually** - Use curl or browser to test verify endpoint
4. **Check Paystack Dashboard** - Verify payment status in Paystack
5. **Enable Webhooks** - For automatic verification (recommended)

---

## 📞 Support

If issues persist:
1. Check `storage/logs/laravel.log` for detailed errors
2. Verify Paystack API credentials
3. Check Paystack dashboard for transaction status
4. Test with Paystack test keys first

---

**The backend is now more robust and should handle payment verification correctly!** ✅


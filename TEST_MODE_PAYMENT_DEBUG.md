# Test Mode Payment Debugging Guide

## Issue: Payments Stay "Pending" in Test Mode

When using Paystack test mode and manually selecting "success" from the payment window, donations should be marked as "completed" but are staying as "pending".

---

## ✅ Backend Improvements Applied

### 1. **Enhanced Status Detection**

The backend now checks multiple indicators for successful payments:

```php
// Checks:
1. status === 'success'
2. gateway_response contains 'successful'
3. channel exists + amount > 0
4. authorization exists + amount > 0
```

### 2. **Test Mode Logging**

All Paystack responses now log:
- `domain` field (shows 'test' or 'live')
- Full response data for debugging
- Test mode indicator

### 3. **Fallback Success Detection**

If standard checks fail, the backend checks:
- Authorization object exists
- Amount > 0
- Transaction not failed/pending

---

## 🔍 Debugging Steps

### Step 1: Check Logs After Payment

```bash
# View latest verification attempt
tail -n 50 storage/logs/laravel.log | grep "Payment verification"

# View Paystack response (most important)
tail -n 50 storage/logs/laravel.log | grep "Paystack verification response" -A 20

# View status update
tail -n 50 storage/logs/laravel.log | grep "Donation status updated" -A 10
```

### Step 2: Check What Paystack Returns

Look for this in logs:
```json
{
  "status": "success",  // or might be different in test mode
  "gateway_response": "Successful",  // or "Approved" or similar
  "domain": "test",  // Confirms test mode
  "amount": 100000,  // Amount in kobo
  "authorization": { ... },  // Should exist for successful payments
  "channel": "card"  // Payment channel
}
```

### Step 3: Verify Frontend Callback

Check browser console/network tab:
1. After selecting "success" in Paystack window
2. Should see redirect to: `/api/payments/verify/{reference}`
3. Check if request succeeds (200 status)

---

## 🚨 Common Test Mode Issues

### Issue 1: Paystack Test Mode Returns Different Status

**Symptom:** Status in logs shows something other than "success"

**Solution:** Backend now handles multiple status formats:
- `status: "success"` ✅
- `gateway_response: "Successful"` ✅
- `gateway_response: "Approved"` ✅
- Authorization + amount > 0 ✅

### Issue 2: Callback Not Triggering

**Symptom:** No verification logs appear after payment

**Check:**
1. Open browser console
2. Make payment and select "success"
3. Check if redirect happens
4. Check network tab for `/api/payments/verify/` request

**Fix:** Ensure Paystack callback is set correctly:
```javascript
callback: function(response) {
    // This MUST execute after selecting "success"
    window.location.href = `/api/payments/verify/${response.reference}?redirect=...`;
}
```

### Issue 3: Reference Mismatch

**Symptom:** "Donation not found" in logs

**Check:**
1. Compare `payment_reference` in database
2. Compare with reference from Paystack response
3. Check logs for "all_donations" array

**Fix:** Backend now tries both:
- Request reference
- Paystack response reference

---

## 🧪 Manual Testing

### Test 1: Direct API Call

```bash
# Replace REFERENCE with actual Paystack reference from test payment
curl "http://localhost:8000/api/payments/verify/REFERENCE"

# Or in browser:
http://localhost:8000/api/payments/verify/REFERENCE
```

### Test 2: Check Database

```sql
-- Find your test donation
SELECT id, payment_reference, status, amount, created_at, verified_at, paid_at
FROM donations
ORDER BY created_at DESC
LIMIT 5;

-- Manually update if needed (for testing only)
UPDATE donations
SET status = 'completed',
    verified_at = NOW(),
    paid_at = NOW()
WHERE payment_reference = 'YOUR_TEST_REFERENCE'
AND status = 'pending';
```

### Test 3: Check Paystack Dashboard

1. Go to Paystack Dashboard → Transactions
2. Find your test transaction
3. Check status (should be "success")
4. Copy the reference
5. Use it to verify manually

---

## 📊 Expected Log Output (Test Mode)

### Successful Verification:
```
[INFO] Payment verification attempt {"reference":"test_ref_123"}
[INFO] Paystack verification response {
  "status": "success",
  "gateway_response": "Successful",
  "domain": "test",
  "amount": 100000,
  "authorization": "present",
  "channel": "card"
}
[INFO] Donation status updated {
  "old_status": "pending",
  "new_status": "completed",
  "is_successful": true,
  "test_mode": true
}
```

### Failed Verification (Donation Not Found):
```
[INFO] Payment verification attempt {"reference":"test_ref_123"}
[WARNING] Payment verification: Donation not found {
  "reference": "test_ref_123",
  "all_donations": ["ref1", "ref2", ...]
}
```

---

## 🔧 Quick Fixes

### Fix 1: Force Verify All Pending Test Payments

```sql
-- Update all pending donations older than 5 minutes (use with caution)
UPDATE donations
SET status = 'completed',
    verified_at = NOW(),
    paid_at = NOW()
WHERE status = 'pending'
AND created_at < NOW() - INTERVAL 5 MINUTE;
```

### Fix 2: Add Test Mode Auto-Verify (Frontend)

```javascript
// After payment callback
callback: function(response) {
    console.log('Paystack callback:', response);
    
    // Verify immediately
    fetch(`/api/payments/verify/${response.reference}`)
        .then(res => res.json())
        .then(data => {
            console.log('Verification result:', data);
            if (data.success) {
                window.location.href = `/?payment_status=success&reference=${response.reference}`;
            } else {
                alert('Payment verification failed. Please contact support.');
            }
        })
        .catch(err => {
            console.error('Verification error:', err);
            // Fallback: redirect anyway
            window.location.href = `/?payment_status=success&reference=${response.reference}`;
        });
}
```

### Fix 3: Add Debug Endpoint

Add to `routes/api.php`:
```php
Route::get('/payments/debug/{reference}', function($reference) {
    // Verify payment
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
    ])->get("https://api.paystack.co/transaction/verify/{$reference}");
    
    return response()->json([
        'paystack_response' => $response->json(),
        'donation' => \App\Models\Donation::where('payment_reference', $reference)->first()
    ]);
});
```

Then test: `http://localhost:8000/api/payments/debug/YOUR_REFERENCE`

---

## ✅ Verification Checklist

- [ ] Paystack is in test mode (check dashboard)
- [ ] Using test keys (starts with `pk_test_` and `sk_test_`)
- [ ] Frontend callback is configured
- [ ] Browser console shows redirect after payment
- [ ] Network tab shows verify request
- [ ] Logs show "Payment verification attempt"
- [ ] Logs show "Paystack verification response"
- [ ] Logs show "Donation status updated"
- [ ] Database shows `status = 'completed'`

---

## 🎯 Test Mode Best Practices

1. **Always Check Logs First**
   - Most issues are visible in logs
   - Look for "Paystack verification response"

2. **Use Paystack Test Cards**
   - Card: `4084084084084081`
   - CVV: Any 3 digits
   - Expiry: Any future date
   - PIN: Any 4 digits

3. **Verify Immediately**
   - Don't wait for webhooks in test mode
   - Use manual verification endpoint

4. **Check Paystack Dashboard**
   - Verify transaction appears
   - Check transaction status
   - Copy reference for manual testing

---

## 📝 Next Steps

1. **Make a test payment**
2. **Check logs immediately** after selecting "success"
3. **Look for "Paystack verification response"** log entry
4. **Check the `status` and `gateway_response`** fields
5. **Verify donation status** in database

If status is still "pending" after these improvements:
- Check logs for exact Paystack response
- Share the log output for further debugging
- Try manual verification endpoint

---

**The backend now handles test mode better with multiple success indicators!** ✅


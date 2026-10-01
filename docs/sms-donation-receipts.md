# Donation phone fields and SMS receipts

Branch SMS is based on hardening/stage-2-security, including its payment and Kudi changes. Review against that branch; merge prerequisites first. Nothing is automatically merged or deployed.

Header donation modal and homepage donation form require a phone number and submit it to the selected payment flow. Signed-in donors receive a prefilled field. The nullable donations.receipt_phone column stores the submitted receipt destination separately from donor profiles; existing donors are not modified merely because a payer supplies their email. The field is hidden in Donation JSON. Legacy donations and API clients that omit phone continue using the donor phone when available.

After verified completion, the Kudi SMS is exactly:

```
Thank you for your generous donation to ABU Zaria. Your payment of ₦{amount} has been received successfully.

Payment Reference: {reference}
```

Amount uses the persisted expected payment amount. SMS is plain text, with a blank line before Payment Reference. Amounts include thousands separators and exactly two decimal places. Existing one-attempt claims and payment failure isolation remain in place. No new live SMS was sent for this form change; Kudi delivery was already confirmed in the parent branch.

Deployment: merge/deploy the parent payment/security changes, then run php artisan migrate --force before serving this branch. The forward migration adds a nullable 30-character receipt phone column. No existing values are changed. Rebuild view caches normally. Rolling back the schema loses newly captured receipt numbers; prefer retaining the column during an application rollback.

Validation: 69 relevant security/payment/SMS tests pass, including exact SMS text, submitted receipt-phone selection, preserved existing donor profiles and phone validation. PHP syntax, Blade compilation and diff whitespace checks pass. Browser end-to-end testing remains a staging check.

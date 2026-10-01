# Frontend Registration Prompt — Anonymous Donor Registration (COMPACT)

## 1. Unified Academic Details (Alumni Only)

The registration flow for Alumni has been simplified. **Ignore all year-based filtering**. 

- **Entry Year:** (Number input)
- **Graduation Year:** (Number input)
- **Department Dropdown:**
    - Fetch the list from: `GET /api/departments`
    - Response Format: `{ success: true, data: [{ id: 1, name: "Chemistry" }, ...] }`
    - Display the `name` and send the `id` as `department_id`.

> **Backend Magic:** You no longer need to send `faculty_id`. The backend will automatically map the department to its correct faculty on the server.

---

## 2. API Contract: `POST /api/donors`

**Payload:**
```json
{
  "donor_type": "Alumni",
  "name": "Jane",
  "surname": "Doe",
  "email": "jane@example.com",
  "password": "secret_password",
  "department_id": 14,
  "entry_year": 2018,
  "graduation_year": 2022,
  "device_fingerprint": "persistent-browser-id"
}
```

---

## 3. Device Fingerprinting & Check

**Step 1: Generate Identity**
On page load, generate a fingerprint (e.g. via `@fingerprintjs/fingerprintjs`).

**Step 2: Check Existing Session**
`POST /api/donors/check-device`
```json
{ "device_fingerprint": "..." }
```
- If `exists: true` → User is recognized. Pre-fill donor info from `response.data.donor`.

---

## 4. Duplicate Email (409) Handling
If the API returns `409 Conflict`, the email already exists.
1. Attempt auto-login using the provided email and password: `POST /api/donor-sessions/login`.
2. If login fails, show error: "Account exists. Please use the login form or reset your password."

---

## Summary of URLS for Developer:
- **Get Departments:** `GET /api/departments`
- **Register:** `POST /api/donors`
- **Check Device:** `POST /api/donors/check-device`
- **Login:** `POST /api/donor-sessions/login`

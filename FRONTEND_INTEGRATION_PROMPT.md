# Frontend Integration Prompt for Donor Sessions API

Copy and paste this prompt into your frontend Cursor window:

---

I need to implement a complete authentication system using the donor_sessions API endpoints. The backend API is already set up with the following endpoints:

## API Endpoints Available:

1. **Register**: `POST /api/donor-sessions/register`
   - Request body: `{ username: string (min 3 chars, unique), password: string (min 6 chars), donor_id: number }`
   - Success response (201): `{ success: true, message: string, data: { id: number, username: string, donor: DonorObject } }`
   - Error responses: 422 (validation), 409 (donor already registered), 500 (server error)

2. **Login**: `POST /api/donor-sessions/login`
   - Request body: `{ username: string, password: string }`
   - Success response (200): `{ success: true, message: string, data: { session_id: number, username: string, donor: DonorObject } }`
   - Error responses: 422 (validation), 401 (invalid credentials), 500 (server error)

3. **Logout**: `POST /api/donor-sessions/logout`
   - Request body: (optional, can be empty)
   - Success response (200): `{ success: true, message: string }`

4. **Get Current Session**: `POST /api/donor-sessions/me`
   - Request body: `{ session_id: number }`
   - Success response (200): `{ success: true, data: { id: number, username: string, donor: DonorObject } }`
   - Error responses: 422 (validation), 404 (session not found), 500 (server error)

## Requirements:

1. **Create an API service/utility file** that handles all API calls to these endpoints with proper error handling
2. **Create authentication context/store** (React Context, Zustand, Redux, or similar) to manage:
   - Current user session (session_id, username, donor data)
   - Login state (isAuthenticated, isLoading)
   - Methods: login(), logout(), register(), getCurrentSession()
3. **Create login component/page** with:
   - Username and password input fields
   - Form validation (username required, password min 6 chars)
   - Error message display
   - Loading state during API call
   - Redirect to dashboard/home on successful login
4. **Create registration component/page** with:
   - Username, password, and donor_id input fields
   - Form validation (username min 3 chars, unique check, password min 6 chars, donor_id required)
   - Error message display (handle 409 conflict if donor already registered)
   - Loading state during API call
   - Success message and redirect to login or auto-login after registration
5. **Session persistence**: Store session_id in localStorage/sessionStorage so user stays logged in on page refresh
6. **Protected routes**: Create route guards that check if user is authenticated, redirect to login if not
7. **Logout functionality**: Clear session from storage and redirect to login page
8. **Auto-check session on app load**: On app initialization, check if session_id exists in storage and call `/me` endpoint to verify and restore session

## Technical Details:

- Base API URL: Use environment variable (e.g., `process.env.REACT_APP_API_URL` or `import.meta.env.VITE_API_URL`)
- All requests should include `Content-Type: application/json` header
- Handle network errors gracefully
- Show user-friendly error messages
- Implement proper loading states for better UX
- Use TypeScript if possible for type safety

## Expected User Flow:

1. **Registration Flow**: User enters username, password, and donor_id → API call → Success → Store session_id → Redirect to dashboard OR show success and redirect to login
2. **Login Flow**: User enters username and password → API call → Success → Store session_id and donor data → Redirect to dashboard
3. **App Load Flow**: Check localStorage for session_id → If exists, call `/me` endpoint → If valid, restore session → If invalid, clear storage and show login
4. **Logout Flow**: User clicks logout → Clear localStorage → Call logout API (optional) → Redirect to login

Please implement this complete authentication system with clean, maintainable code following best practices for the frontend framework being used (React, Vue, Angular, etc.).


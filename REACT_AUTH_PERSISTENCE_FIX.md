# React Frontend: Fix Auto-Logout on Page Refresh

## Problem

Full page refresh causes automatic logout because authentication state is stored only in React state (useState), which is lost on refresh.

---

## ✅ Solution: Persist Auth State & Restore on Load

### Step 1: Update AuthContext to Persist State

**File:** `src/contexts/AuthContext.js` (or your auth context file)

```javascript
import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const AuthContext = createContext();

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true); // Start with loading true

  // Storage keys
  const STORAGE_KEY = 'donor_session';
  const SESSION_ID_KEY = 'donor_session_id';

  // ✅ Load session from localStorage on mount
  useEffect(() => {
    checkSession();
  }, []);

  /**
   * Check if user has valid session on app load/refresh
   */
  const checkSession = async () => {
    try {
      setIsLoading(true);
      
      // Get session data from localStorage
      const storedSession = localStorage.getItem(STORAGE_KEY);
      const sessionId = localStorage.getItem(SESSION_ID_KEY);

      if (!storedSession || !sessionId) {
        // No stored session
        setIsAuthenticated(false);
        setUser(null);
        setIsLoading(false);
        return;
      }

      // Parse stored session
      const sessionData = JSON.parse(storedSession);

      // ✅ Verify session with backend
      try {
        const response = await axios.post(
          `${process.env.REACT_APP_API_URL || 'https://abu-endowment.cloud'}/api/donor-sessions/me`,
          { session_id: parseInt(sessionId) },
          {
            headers: {
              'Content-Type': 'application/json',
            },
          }
        );

        if (response.data.success && response.data.data) {
          // ✅ Session is valid - restore user state
          setUser({
            id: response.data.data.donor?.id || sessionData.donor?.id,
            username: response.data.data.username || sessionData.username,
            ...response.data.data.donor,
          });
          setIsAuthenticated(true);
          
          // Update stored session with fresh data
          localStorage.setItem(STORAGE_KEY, JSON.stringify({
            id: response.data.data.id,
            username: response.data.data.username,
            donor: response.data.data.donor,
          }));
        } else {
          // Session invalid - clear storage
          clearSession();
        }
      } catch (error) {
        // Session verification failed - clear storage
        console.error('Session verification failed:', error);
        clearSession();
      }
    } catch (error) {
      console.error('Error checking session:', error);
      clearSession();
    } finally {
      setIsLoading(false);
    }
  };

  /**
   * Login user and persist session
   */
  const login = async (username, password) => {
    try {
      const response = await axios.post(
        `${process.env.REACT_APP_API_URL || 'https://abu-endowment.cloud'}/api/donor-sessions/login`,
        { username, password },
        {
          headers: {
            'Content-Type': 'application/json',
          },
        }
      );

      if (response.data.success && response.data.data) {
        const { session_id, username: userUsername, donor } = response.data.data;

        // ✅ Store session in localStorage
        const sessionData = {
          id: session_id,
          username: userUsername,
          donor: donor,
        };
        
        localStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));
        localStorage.setItem(SESSION_ID_KEY, session_id.toString());

        // Update state
        setUser({
          id: donor?.id,
          username: userUsername,
          ...donor,
        });
        setIsAuthenticated(true);

        return { success: true, data: response.data.data };
      }

      return { success: false, message: response.data.message || 'Login failed' };
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Login failed';
      return { success: false, message };
    }
  };

  /**
   * Logout user and clear session
   */
  const logout = async () => {
    try {
      const sessionId = localStorage.getItem(SESSION_ID_KEY);
      
      // Call logout API (optional - backend cleanup)
      if (sessionId) {
        try {
          await axios.post(
            `${process.env.REACT_APP_API_URL || 'https://abu-endowment.cloud'}/api/donor-sessions/logout`,
            { session_id: parseInt(sessionId) },
            {
              headers: {
                'Content-Type': 'application/json',
              },
            }
          );
        } catch (error) {
          // Logout API call failed, but continue with local logout
          console.error('Logout API error:', error);
        }
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // ✅ Always clear local storage and state
      clearSession();
    }
  };

  /**
   * Clear session from storage and state
   */
  const clearSession = () => {
    localStorage.removeItem(STORAGE_KEY);
    localStorage.removeItem(SESSION_ID_KEY);
    setUser(null);
    setIsAuthenticated(false);
  };

  /**
   * Register new user
   */
  const register = async (username, password, donorId) => {
    try {
      const response = await axios.post(
        `${process.env.REACT_APP_API_URL || 'https://abu-endowment.cloud'}/api/donor-sessions/register`,
        { username, password, donor_id: donorId },
        {
          headers: {
            'Content-Type': 'application/json',
          },
        }
      );

      if (response.data.success) {
        return { success: true, data: response.data.data };
      }

      return { success: false, message: response.data.message || 'Registration failed' };
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Registration failed';
      return { success: false, message };
    }
  };

  /**
   * Google OAuth Login
   */
  const googleLogin = async (token) => {
    try {
      const response = await axios.post(
        `${process.env.REACT_APP_API_URL || 'https://abu-endowment.cloud'}/api/donor-sessions/google-login`,
        { token },
        {
          headers: {
            'Content-Type': 'application/json',
          },
        }
      );

      if (response.data.success && response.data.data) {
        const { session_id, username, donor } = response.data.data;

        // ✅ Store session in localStorage
        const sessionData = {
          id: session_id,
          username: username,
          donor: donor,
        };
        
        localStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));
        localStorage.setItem(SESSION_ID_KEY, session_id.toString());

        // Update state
        setUser({
          id: donor?.id,
          username: username,
          ...donor,
        });
        setIsAuthenticated(true);

        return { success: true, data: response.data.data };
      }

      return { success: false, message: response.data.message || 'Google login failed' };
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Google login failed';
      return { success: false, message };
    }
  };

  const value = {
    user,
    isAuthenticated,
    isLoading,
    login,
    logout,
    register,
    googleLogin,
    checkSession, // Expose for manual refresh if needed
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
```

---

### Step 2: Wrap App with AuthProvider

**File:** `src/App.js` (or `src/index.js`)

```javascript
import React from 'react';
import { AuthProvider } from './contexts/AuthContext';
import AppRoutes from './AppRoutes'; // Your routes component

function App() {
  return (
    <AuthProvider>
      <AppRoutes />
    </AuthProvider>
  );
}

export default App;
```

---

### Step 3: Handle Loading State in Components

**File:** `src/components/ProtectedRoute.js` (or similar)

```javascript
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const ProtectedRoute = ({ children }) => {
  const { isAuthenticated, isLoading } = useAuth();

  // ✅ Show loading while checking session
  if (isLoading) {
    return (
      <div className="loading-container">
        <div className="spinner">Loading...</div>
      </div>
    );
  }

  // ✅ Redirect to login if not authenticated
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return children;
};

export default ProtectedRoute;
```

---

### Step 4: Update Components to Use Persistent Auth

**Example:** `src/pages/Home.js`

```javascript
import React, { useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';

const Home = () => {
  const { user, isAuthenticated, isLoading } = useAuth();

  // ✅ Auth state is automatically restored from localStorage
  // No need to manually check on every render

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return (
    <div>
      {isAuthenticated ? (
        <div>
          <h1>Welcome, {user?.username || user?.name}!</h1>
          <p>You are logged in.</p>
        </div>
      ) : (
        <div>
          <h1>Welcome Guest</h1>
          <p>Please log in to continue.</p>
        </div>
      )}
    </div>
  );
};

export default Home;
```

---

## 🔧 Alternative: Using sessionStorage (More Secure)

If you prefer sessionStorage (clears on browser close):

```javascript
// In AuthContext.js, replace localStorage with sessionStorage:

// Change these lines:
const storedSession = sessionStorage.getItem(STORAGE_KEY);
const sessionId = sessionStorage.getItem(SESSION_ID_KEY);

// And in login/googleLogin:
sessionStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));
sessionStorage.setItem(SESSION_ID_KEY, session_id.toString());

// And in clearSession:
sessionStorage.removeItem(STORAGE_KEY);
sessionStorage.removeItem(SESSION_ID_KEY);
```

---

## 🎯 Key Points

### ✅ What This Fixes:

1. **Persistent State**: Auth state survives page refresh
2. **Session Verification**: Validates session with backend on load
3. **Auto-Restore**: Automatically restores user state from storage
4. **Loading State**: Shows loading while checking session
5. **Error Handling**: Clears invalid sessions gracefully

### ✅ How It Works:

1. **On App Load:**
   - Checks localStorage for stored session
   - If found, verifies with backend `/api/donor-sessions/me`
   - If valid, restores user state
   - If invalid, clears storage

2. **On Login:**
   - Stores session in localStorage
   - Updates React state
   - User stays logged in

3. **On Logout:**
   - Clears localStorage
   - Clears React state
   - Calls backend logout API

4. **On Refresh:**
   - `useEffect` runs `checkSession()`
   - Restores state from localStorage
   - Verifies with backend
   - User stays logged in ✅

---

## 🧪 Testing

### Test 1: Login and Refresh
```javascript
1. Login with username/password
2. Verify user is logged in
3. Refresh page (F5)
4. ✅ User should still be logged in
```

### Test 2: Check localStorage
```javascript
// In browser console after login:
localStorage.getItem('donor_session')
// Should show: {"id":1,"username":"john","donor":{...}}

localStorage.getItem('donor_session_id')
// Should show: "1"
```

### Test 3: Invalid Session
```javascript
1. Login
2. Manually delete session from backend
3. Refresh page
4. ✅ Should automatically logout and clear storage
```

---

## 🚨 Common Issues & Fixes

### Issue 1: Still Logging Out on Refresh

**Check:**
- Is `checkSession()` being called in `useEffect`?
- Is `isLoading` starting as `true`?
- Are storage keys correct?

**Fix:**
```javascript
// Make sure useEffect runs on mount
useEffect(() => {
  checkSession();
}, []); // Empty dependency array = run once on mount
```

### Issue 2: Infinite Loop

**Cause:** `checkSession` in dependency array

**Fix:**
```javascript
// ❌ Wrong:
useEffect(() => {
  checkSession();
}, [checkSession]); // Causes infinite loop

// ✅ Correct:
useEffect(() => {
  checkSession();
}, []); // Run once on mount
```

### Issue 3: API URL Not Found

**Fix:**
```javascript
// Add to .env file:
REACT_APP_API_URL=https://abu-endowment.cloud

// Or use default:
const API_URL = process.env.REACT_APP_API_URL || 'https://abu-endowment.cloud';
```

---

## 📝 Quick Checklist

- [ ] AuthContext persists session to localStorage
- [ ] `checkSession()` runs on app mount
- [ ] Session is verified with backend on load
- [ ] `isLoading` starts as `true`
- [ ] Components handle loading state
- [ ] Invalid sessions are cleared automatically
- [ ] Logout clears storage and state

---

## 🎉 Result

After implementing this:

✅ **User logs in** → Session stored in localStorage  
✅ **User refreshes page** → Session restored from localStorage  
✅ **Session verified** → Backend confirms session is valid  
✅ **User stays logged in** → No more auto-logout on refresh!

---

**Your React app will now maintain authentication state across page refreshes!** 🚀


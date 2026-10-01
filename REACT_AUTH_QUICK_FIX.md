# Quick Fix: React Auth Persistence (TL;DR)

## The Problem
Page refresh → Auth state lost → User logged out

## The Solution
Store auth in `localStorage` + Restore on app load

---

## 🚀 Quick Implementation

### 1. Update Your AuthContext

Add this to your `AuthContext.js`:

```javascript
// ✅ Add useEffect to check session on mount
useEffect(() => {
  const storedSession = localStorage.getItem('donor_session');
  const sessionId = localStorage.getItem('donor_session_id');
  
  if (storedSession && sessionId) {
    // Verify session with backend
    axios.post('/api/donor-sessions/me', { session_id: parseInt(sessionId) })
      .then(res => {
        if (res.data.success) {
          setUser(res.data.data.donor);
          setIsAuthenticated(true);
        } else {
          clearSession();
        }
      })
      .catch(() => clearSession());
  }
  
  setIsLoading(false);
}, []);

// ✅ Update login to store session
const login = async (username, password) => {
  const res = await axios.post('/api/donor-sessions/login', { username, password });
  
  if (res.data.success) {
    const { session_id, donor } = res.data.data;
    
    // Store in localStorage
    localStorage.setItem('donor_session', JSON.stringify({ session_id, donor }));
    localStorage.setItem('donor_session_id', session_id.toString());
    
    setUser(donor);
    setIsAuthenticated(true);
  }
};

// ✅ Update logout to clear storage
const logout = () => {
  localStorage.removeItem('donor_session');
  localStorage.removeItem('donor_session_id');
  setUser(null);
  setIsAuthenticated(false);
};
```

### 2. Start with Loading State

```javascript
const [isLoading, setIsLoading] = useState(true); // ✅ Start as true
```

### 3. Show Loading While Checking

```javascript
if (isLoading) {
  return <div>Loading...</div>;
}
```

---

## ✅ That's It!

Now:
- ✅ Login stores session
- ✅ Refresh restores session
- ✅ User stays logged in

---

**See `REACT_AUTH_PERSISTENCE_FIX.md` for complete implementation!**


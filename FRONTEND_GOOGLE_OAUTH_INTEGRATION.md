# Frontend Integration Guide: Google OAuth Authentication

## Overview

The backend now supports Google OAuth authentication. Users can register and login using their Google accounts. This document provides complete integration instructions for the frontend.

---

## 🔑 Backend Endpoints

### 1. Google Login
**Endpoint:** `POST /api/donor-sessions/google-login`

**Request Body:**
```json
{
  "token": "google_id_token_from_google_signin",
  "device_session_id": 123  // optional, number or null
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Google login successful",
  "data": {
    "session_id": 456,
    "username": "user@example.com",
    "donor": {
      "id": 789,
      "name": "John",
      "surname": "Doe",
      "email": "user@example.com",
      "profile_image": "https://lh3.googleusercontent.com/...",
      // ... other donor fields
    },
    "device_session_id": 123
  }
}
```

**Error Responses:**

- **401 - Invalid Token:**
```json
{
  "success": false,
  "message": "Invalid or expired Google token"
}
```

- **401 - Email Not Verified:**
```json
{
  "success": false,
  "message": "Google email is not verified"
}
```

- **500 - Server Error:**
```json
{
  "success": false,
  "message": "Google login failed. Please try again."
}
```

---

### 2. Google Register
**Endpoint:** `POST /api/donor-sessions/google-register`

**Request Body:**
```json
{
  "token": "google_id_token_from_google_signin",
  "device_session_id": 123  // optional, number or null
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Google registration successful",
  "data": {
    "session_id": 456,
    "username": "user@example.com",
    "donor": {
      "id": 789,
      "name": "John",
      "surname": "Doe",
      "email": "user@example.com",
      "profile_image": "https://lh3.googleusercontent.com/...",
      // ... other donor fields
    },
    "device_session_id": 123
  }
}
```

**Error Responses:**

- **401 - Invalid Token:**
```json
{
  "success": false,
  "message": "Invalid or expired Google token"
}
```

- **409 - Account Already Exists (Register):**
```json
{
  "success": false,
  "message": "This Google account is already registered. Please login instead."
}
```

- **409 - Email Already Registered:**
```json
{
  "success": false,
  "message": "An account with this email already exists. Please login with your email and password, or link your Google account in settings."
}
```

---

## 📱 Frontend Implementation

### Step 1: Install Google Sign-In Library

**For React Native:**
```bash
npm install @react-native-google-signin/google-signin
# or
yarn add @react-native-google-signin/google-signin
```

**For React Web:**
```bash
npm install @react-oauth/google
# or
yarn add @react-oauth/google
```

**For Flutter:**
```yaml
dependencies:
  google_sign_in: ^6.0.0
```

---

### Step 2: Configure Google Sign-In

#### React Native Example:
```javascript
import { GoogleSignin } from '@react-native-google-signin/google-signin';

// Configure Google Sign-In
GoogleSignin.configure({
  webClientId: '470253699627-a50centdev8a3ahhq0e01oiakatu3qh4.apps.googleusercontent.com',
  offlineAccess: true,
  forceCodeForRefreshToken: true,
});
```

#### React Web Example:
```javascript
import { GoogleOAuthProvider, useGoogleLogin } from '@react-oauth/google';

function App() {
  return (
    <GoogleOAuthProvider clientId="470253699627-a50centdev8a3ahhq0e01oiakatu3qh4.apps.googleusercontent.com">
      <YourApp />
    </GoogleOAuthProvider>
  );
}
```

---

### Step 3: Implement Google Login Function

#### React Native Example:
```javascript
import { GoogleSignin } from '@react-native-google-signin/google-signin';

const handleGoogleLogin = async () => {
  try {
    // Check if Google Play Services are available
    await GoogleSignin.hasPlayServices();
    
    // Get user info and ID token
    const userInfo = await GoogleSignin.signIn();
    const idToken = userInfo.data?.idToken;
    
    if (!idToken) {
      throw new Error('Failed to get Google ID token');
    }
    
    // Get device_session_id if available
    const deviceSessionId = await getDeviceSessionId(); // Your function to get device session
    
    // Call backend API
    const response = await fetch('http://your-api-url/api/donor-sessions/google-login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: idToken,
        device_session_id: deviceSessionId,
      }),
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Store session data
      await storeSessionData(data.data);
      
      // Navigate to home/dashboard
      navigation.navigate('Home');
    } else {
      // Handle error
      Alert.alert('Login Failed', data.message);
    }
  } catch (error) {
    console.error('Google login error:', error);
    Alert.alert('Error', 'Failed to login with Google. Please try again.');
  }
};
```

#### React Web Example:
```javascript
import { useGoogleLogin } from '@react-oauth/google';

const GoogleLoginButton = () => {
  const login = useGoogleLogin({
    onSuccess: async (tokenResponse) => {
      try {
        // Get user info from Google
        const userInfoResponse = await fetch(
          `https://www.googleapis.com/oauth2/v3/userinfo?access_token=${tokenResponse.access_token}`
        );
        const userInfo = await userInfoResponse.json();
        
        // For web, you need to get ID token differently
        // You might need to use Google Identity Services library
        const idToken = tokenResponse.id_token; // If available
        
        // Call backend API
        const response = await fetch('http://your-api-url/api/donor-sessions/google-login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            token: idToken,
            device_session_id: null, // Web doesn't use device sessions
          }),
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Store session data
          localStorage.setItem('session_id', data.data.session_id);
          localStorage.setItem('donor', JSON.stringify(data.data.donor));
          
          // Navigate to home/dashboard
          window.location.href = '/dashboard';
        } else {
          alert(data.message);
        }
      } catch (error) {
        console.error('Google login error:', error);
        alert('Failed to login with Google. Please try again.');
      }
    },
    onError: () => {
      alert('Google login failed');
    },
  });
  
  return (
    <button onClick={() => login()}>
      Sign in with Google
    </button>
  );
};
```

---

### Step 4: Implement Google Register Function

The register function is similar to login, but uses the `/google-register` endpoint:

```javascript
const handleGoogleRegister = async () => {
  try {
    // Get Google ID token (same as login)
    const idToken = await getGoogleIdToken(); // Your function to get ID token
    
    // Call backend API
    const response = await fetch('http://your-api-url/api/donor-sessions/google-register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: idToken,
        device_session_id: deviceSessionId,
      }),
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Store session data
      await storeSessionData(data.data);
      
      // Navigate to home/dashboard
      navigation.navigate('Home');
    } else {
      // Handle specific errors
      if (response.status === 409) {
        // Account already exists - redirect to login
        Alert.alert(
          'Account Exists',
          data.message + ' Would you like to login instead?',
          [
            { text: 'Cancel', style: 'cancel' },
            { text: 'Login', onPress: () => handleGoogleLogin() },
          ]
        );
      } else {
        Alert.alert('Registration Failed', data.message);
      }
    }
  } catch (error) {
    console.error('Google registration error:', error);
    Alert.alert('Error', 'Failed to register with Google. Please try again.');
  }
};
```

---

## 🔄 Important Notes

### 1. **Login vs Register Logic**

The backend handles both new and existing users in the `google-login` endpoint:
- If Google account exists → Login
- If Google account doesn't exist → Creates new account and logs in

**Recommendation:** Use `google-login` for both login and register buttons. The backend will automatically handle account creation.

**Use `google-register` only if:**
- You want to explicitly show a "Register" flow
- You want to show different error messages for registration conflicts

### 2. **Error Handling**

**Common Error Scenarios:**

```javascript
const handleGoogleAuthError = (error, response) => {
  if (response.status === 401) {
    if (error.message.includes('email is not verified')) {
      Alert.alert(
        'Email Not Verified',
        'Please verify your Google email address and try again.'
      );
    } else {
      Alert.alert('Authentication Failed', error.message);
    }
  } else if (response.status === 409) {
    // Account conflict - offer to login instead
    Alert.alert(
      'Account Exists',
      error.message,
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Login Instead', onPress: () => handleGoogleLogin() },
      ]
    );
  } else {
    Alert.alert('Error', 'An unexpected error occurred. Please try again.');
  }
};
```

### 3. **Session Management**

After successful Google authentication, store the session data:

```javascript
const storeSessionData = async (sessionData) => {
  // Store session ID
  await AsyncStorage.setItem('session_id', sessionData.session_id.toString());
  
  // Store donor data
  await AsyncStorage.setItem('donor', JSON.stringify(sessionData.donor));
  
  // Store username
  await AsyncStorage.setItem('username', sessionData.username);
  
  // Store device session ID if available
  if (sessionData.device_session_id) {
    await AsyncStorage.setItem('device_session_id', sessionData.device_session_id.toString());
  }
  
  // Update your app's authentication state
  setUser(sessionData.donor);
  setIsAuthenticated(true);
};
```

### 4. **Logout**

Google OAuth logout works the same as regular logout:

```javascript
const handleLogout = async () => {
  try {
    // Call your existing logout endpoint
    await fetch('http://your-api-url/api/donor-sessions/logout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        session_id: await AsyncStorage.getItem('session_id'),
      }),
    });
    
    // Clear local storage
    await AsyncStorage.multiRemove(['session_id', 'donor', 'username', 'device_session_id']);
    
    // Sign out from Google (optional, for better UX)
    await GoogleSignin.signOut();
    
    // Update app state
    setUser(null);
    setIsAuthenticated(false);
    
    // Navigate to login screen
    navigation.navigate('Login');
  } catch (error) {
    console.error('Logout error:', error);
  }
};
```

---

## 🎨 UI/UX Recommendations

### 1. **Login Screen**

Add a "Sign in with Google" button alongside your email/password login:

```jsx
<View style={styles.loginContainer}>
  {/* Email/Password Login Form */}
  <TextInput placeholder="Email" />
  <TextInput placeholder="Password" secureTextEntry />
  <Button title="Login" onPress={handleEmailLogin} />
  
  {/* Divider */}
  <View style={styles.divider}>
    <View style={styles.dividerLine} />
    <Text>OR</Text>
    <View style={styles.dividerLine} />
  </View>
  
  {/* Google Login Button */}
  <Button
    title="Sign in with Google"
    onPress={handleGoogleLogin}
    icon={<GoogleIcon />}
  />
</View>
```

### 2. **Registration Screen**

Similar layout for registration:

```jsx
<View style={styles.registerContainer}>
  {/* Registration Form */}
  {/* ... */}
  
  {/* Google Register Button */}
  <Button
    title="Sign up with Google"
    onPress={handleGoogleRegister}
    icon={<GoogleIcon />}
  />
</View>
```

### 3. **Profile Screen**

Show authentication method:

```jsx
<View style={styles.profileContainer}>
  <Image source={{ uri: donor.profile_image }} />
  <Text>{donor.name} {donor.surname}</Text>
  <Text>{donor.email}</Text>
  
  {/* Show auth method if available */}
  {donorSession?.auth_provider === 'google' && (
    <View style={styles.authBadge}>
      <GoogleIcon />
      <Text>Signed in with Google</Text>
    </View>
  )}
</View>
```

---

## 🔒 Security Best Practices

1. **Never store Google ID tokens locally** - Only send them to the backend immediately
2. **Always verify backend responses** - Check `success` field before trusting data
3. **Handle token expiration** - If login fails, prompt user to sign in again
4. **Clear tokens on logout** - Sign out from Google when user logs out
5. **Use HTTPS in production** - Never send tokens over unencrypted connections

---

## 📝 Testing Checklist

- [ ] Google login with new user (should create account)
- [ ] Google login with existing user (should login)
- [ ] Google register with new user (should create account)
- [ ] Google register with existing Google account (should show error)
- [ ] Google register with existing email (should show error)
- [ ] Invalid/expired token handling
- [ ] Unverified email handling
- [ ] Logout functionality
- [ ] Session persistence after app restart
- [ ] Error message display

---

## 🐛 Troubleshooting

### Issue: "Invalid or expired Google token"
**Solution:** 
- Ensure you're getting the ID token (not access token)
- Check that Google Sign-In is properly configured
- Verify the token is sent immediately after receiving it

### Issue: "Google email is not verified"
**Solution:**
- User needs to verify their Google email address
- Show a message directing them to verify their email

### Issue: Token works in development but not production
**Solution:**
- Check that the Google Client ID matches your environment
- Verify OAuth redirect URIs are configured correctly in Google Console

---

## 📚 Additional Resources

- [Google Sign-In for React Native](https://github.com/react-native-google-signin/google-signin)
- [Google OAuth for React](https://www.npmjs.com/package/@react-oauth/google)
- [Google Identity Services](https://developers.google.com/identity/gsi/web)

---

## ✅ Summary

**What Changed:**
- ✅ Two new endpoints: `/google-login` and `/google-register`
- ✅ Both endpoints accept `token` (Google ID token) and optional `device_session_id`
- ✅ Response format matches existing login/register endpoints
- ✅ Same session management as email/password authentication
- ✅ Logout works the same way (no changes needed)

**What You Need to Do:**
1. Install Google Sign-In library for your platform
2. Configure Google Client ID
3. Implement `handleGoogleLogin()` function
4. Add Google Sign-In button to login/register screens
5. Handle errors appropriately
6. Test thoroughly

The backend is ready! Just integrate the Google Sign-In flow on the frontend and you're good to go! 🚀


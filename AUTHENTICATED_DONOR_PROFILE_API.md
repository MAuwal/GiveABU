# Backend API: Authenticated Donor Profile Creation/Update

## ✅ New Endpoint Added

Authenticated users can now create or update their donor profile, which is automatically linked to their session.

---

## 📡 API Endpoint

### Create/Update Donor Profile

**Endpoint:** `POST /api/donor-sessions/profile`

**Authentication:** Requires `session_id` in request body

**Description:** Creates or updates the donor profile for the authenticated user and automatically links it to their session.

---

## 📋 Request Format

### Headers
```json
{
  "Content-Type": "application/json"
}
```

### Request Body

```json
{
  "session_id": 123,  // ✅ Required - Authenticated user's session ID
  "donor_type": "Individual",  // ✅ Required
  "name": "John",  // ✅ Required
  "surname": "Doe",  // ✅ Required
  "other_name": "Michael",  // Optional
  "email": "john.doe@example.com",  // ✅ Required (should match session username)
  "phone": "+2348012345678",  // Optional
  "nationality": "Nigerian",  // Optional (defaults to "Nigerian")
  "state": "Kaduna",  // Optional
  "lga": "Kaduna North",  // Optional
  "address": "123 Main Street",  // Optional
  "gender": "male",  // Optional (male/female)
  "country": "Nigeria"  // Optional
}
```

---

## 📊 Response Format

### Success Response (201 - New Profile Created)

```json
{
  "success": true,
  "message": "Profile created successfully",
  "data": {
    "donor": {
      "id": 456,
      "name": "John",
      "surname": "Doe",
      "other_name": "Michael",
      "full_name": "Doe John Michael",
      "email": "john.doe@example.com",
      "phone": "+2348012345678",
      "donor_type": "Individual",
      "nationality": "Nigerian",
      "state": "Kaduna",
      "lga": "Kaduna North",
      "address": "123 Main Street",
      "gender": "male",
      "country": "Nigeria"
    },
    "session": {
      "id": 123,
      "username": "john.doe@example.com",
      "donor_id": 456  // ✅ Automatically linked
    }
  }
}
```

### Success Response (200 - Profile Updated)

```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "donor": { ... },
    "session": { ... }
  }
}
```

### Error Responses

#### 422 - Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "donor_type": ["The donor type field is required."],
    "surname": ["The surname field is required."]
  }
}
```

#### 404 - Session Not Found
```json
{
  "success": false,
  "message": "Session not found"
}
```

#### 422 - Email Already Exists
```json
{
  "success": false,
  "message": "Email already exists. Please use a different email or contact support."
}
```

---

## 🔄 How It Works

### Flow 1: User Has No Donor (First Time)

```
1. User is authenticated (has session_id)
2. User calls POST /api/donor-sessions/profile
3. Backend creates new donor record
4. Backend automatically links donor to session (updates donor_id)
5. Returns created donor data
```

### Flow 2: User Already Has Donor (Update)

```
1. User is authenticated (has session_id with donor_id)
2. User calls POST /api/donor-sessions/profile
3. Backend finds existing donor by donor_id
4. Backend updates donor record
5. Returns updated donor data
```

---

## 🎯 Frontend Implementation

### React Example

```javascript
import axios from 'axios';

const createOrUpdateProfile = async (sessionId, profileData) => {
  try {
    const response = await axios.post(
      `${process.env.REACT_APP_API_URL || 'https://abu-endowment.cloud'}/api/donor-sessions/profile`,
      {
        session_id: sessionId,
        ...profileData
      },
      {
        headers: {
          'Content-Type': 'application/json',
        },
      }
    );

    if (response.data.success) {
      // Profile created/updated successfully
      console.log('Profile saved:', response.data.data.donor);
      
      // Update local state with new donor data
      return {
        success: true,
        donor: response.data.data.donor,
        session: response.data.data.session
      };
    }

    return { success: false, message: response.data.message };
  } catch (error) {
    if (error.response) {
      // Server responded with error
      return {
        success: false,
        message: error.response.data.message || 'Failed to save profile',
        errors: error.response.data.errors
      };
    }
    
    return {
      success: false,
      message: 'Network error. Please try again.'
    };
  }
};

// Usage in component
const ProfileForm = () => {
  const { user, sessionId } = useAuth(); // Get from AuthContext
  
  const handleSubmit = async (formData) => {
    const result = await createOrUpdateProfile(sessionId, {
      donor_type: formData.donorType,
      name: formData.name,
      surname: formData.surname,
      other_name: formData.otherName,
      email: user.username, // Use authenticated user's email
      phone: formData.phone,
      state: formData.state,
      lga: formData.lga,
      address: formData.address,
      gender: formData.gender,
    });
    
    if (result.success) {
      // Update user state with new donor data
      updateUser({ ...user, donor: result.donor });
      showSuccess('Profile saved successfully!');
    } else {
      showError(result.message);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields */}
    </form>
  );
};
```

---

## 🔐 Security Features

### 1. Session Validation
- ✅ Verifies `session_id` exists and is valid
- ✅ Only authenticated users can create/update profiles

### 2. Email Validation
- ✅ Checks if email matches session username (logs warning if different)
- ✅ Prevents email conflicts with other donors
- ✅ Validates email format

### 3. Automatic Linking
- ✅ Automatically links created donor to user's session
- ✅ Updates `donor_id` in `donor_sessions` table
- ✅ No manual linking required

### 4. Update Protection
- ✅ If donor exists, updates it (doesn't create duplicate)
- ✅ Checks email conflicts before updating
- ✅ Preserves existing donor_id relationship

---

## 📝 Validation Rules

### Required Fields:
- ✅ `session_id` - Must exist in `donor_sessions` table
- ✅ `donor_type` - Must be one of: `supporter`, `addressable_alumni`, `non_addressable_alumni`, `Individual`, `Organization`, `NGO`
- ✅ `name` - Required, max 255 characters
- ✅ `surname` - Required, max 255 characters
- ✅ `email` - Required, valid email format, max 255 characters

### Optional Fields:
- ✅ `other_name` - Optional, max 255 characters
- ✅ `phone` - Optional, max 20 characters
- ✅ `nationality` - Optional (defaults to "Nigerian")
- ✅ `state` - Optional, max 255 characters
- ✅ `lga` - Optional, max 255 characters
- ✅ `address` - Optional, max 500 characters
- ✅ `gender` - Optional (`male` or `female`)
- ✅ `country` - Optional, max 100 characters

---

## 🧪 Testing

### Test 1: Create Profile (First Time)

```bash
POST /api/donor-sessions/profile
{
  "session_id": 123,
  "donor_type": "Individual",
  "name": "John",
  "surname": "Doe",
  "email": "john@example.com"
}
```

**Expected:** ✅ 201 Created - Donor created and linked to session

### Test 2: Update Profile (Existing Donor)

```bash
POST /api/donor-sessions/profile
{
  "session_id": 123,  // Same session, now has donor_id
  "donor_type": "Individual",
  "name": "John",
  "surname": "Doe",
  "email": "john@example.com",
  "phone": "+2348012345678"  // Adding phone
}
```

**Expected:** ✅ 200 OK - Donor updated

### Test 3: Invalid Session

```bash
POST /api/donor-sessions/profile
{
  "session_id": 99999,  // Non-existent session
  "donor_type": "Individual",
  "name": "John",
  "surname": "Doe",
  "email": "john@example.com"
}
```

**Expected:** ❌ 404 - Session not found

### Test 4: Missing Required Fields

```bash
POST /api/donor-sessions/profile
{
  "session_id": 123,
  "name": "John"
  // Missing donor_type, surname, email
}
```

**Expected:** ❌ 422 - Validation failed

---

## 🔄 Complete User Flow

### Step 1: User Registers
```javascript
POST /api/donor-sessions/register
{
  "username": "user@example.com",
  "password": "password123"
}

Response: { session_id: 123, donor: null }
```

### Step 2: User Completes Profile
```javascript
POST /api/donor-sessions/profile
{
  "session_id": 123,
  "donor_type": "Individual",
  "name": "John",
  "surname": "Doe",
  "email": "user@example.com",
  "phone": "+2348012345678"
}

Response: { 
  donor: { id: 456, ... },
  session: { id: 123, donor_id: 456 }  // ✅ Linked
}
```

### Step 3: User Updates Profile Later
```javascript
POST /api/donor-sessions/profile
{
  "session_id": 123,
  "donor_type": "Individual",
  "name": "John",
  "surname": "Doe",
  "email": "user@example.com",
  "phone": "+2348012345678",
  "address": "123 Main St"  // Adding address
}

Response: { donor: { ... }, session: { ... } }
```

---

## ✅ Benefits

1. **Automatic Linking**: Donor automatically linked to session
2. **Single Endpoint**: One endpoint for both create and update
3. **Secure**: Requires valid session_id
4. **Smart Updates**: Updates existing donor if it exists
5. **Email Protection**: Prevents email conflicts
6. **User-Friendly**: Simple API for frontend integration

---

## 📋 Frontend Integration Checklist

- [ ] Get `session_id` from authenticated user's session
- [ ] Call `POST /api/donor-sessions/profile` with session_id and profile data
- [ ] Handle success response (201 for new, 200 for update)
- [ ] Update local user state with new donor data
- [ ] Handle validation errors (422)
- [ ] Handle session not found (404)
- [ ] Handle email conflicts (422)
- [ ] Show success/error messages to user

---

## 🎯 Key Points

1. ✅ **Always include `session_id`** - Required for authentication
2. ✅ **Email should match username** - For security (logged if different)
3. ✅ **Automatic linking** - Donor is automatically linked to session
4. ✅ **Create or Update** - Same endpoint handles both
5. ✅ **Email validation** - Prevents conflicts with other donors

---

## 📝 Example: Complete Profile Form

```javascript
const ProfileForm = () => {
  const { user, sessionId } = useAuth();
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const formData = {
        session_id: sessionId,
        donor_type: e.target.donorType.value,
        name: e.target.name.value,
        surname: e.target.surname.value,
        other_name: e.target.otherName.value || null,
        email: user.username, // Use authenticated email
        phone: e.target.phone.value || null,
        state: e.target.state.value || null,
        lga: e.target.lga.value || null,
        address: e.target.address.value || null,
        gender: e.target.gender.value || null,
      };

      const response = await fetch('/api/donor-sessions/profile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (data.success) {
        // Update user state
        updateUser({ ...user, donor: data.data.donor });
        alert('Profile saved successfully!');
      } else {
        setErrors(data.errors || {});
        alert(data.message || 'Failed to save profile');
      }
    } catch (error) {
      alert('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields */}
      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Profile'}
      </button>
    </form>
  );
};
```

---

**The backend now supports authenticated donor profile creation/update with automatic session linking!** 🎉


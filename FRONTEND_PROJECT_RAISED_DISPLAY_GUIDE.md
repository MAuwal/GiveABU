# Frontend Guide: Display Project Raised Amount

## Overview

The backend automatically updates the `raised` column in the `projects` table whenever a donation payment is successfully completed. The `raised` amount is the sum of all **completed** donations for that project.

---

## ✅ Backend Status

**Already Implemented:**
- ✅ `raised` column exists in `projects` table
- ✅ `updateProjectRaised()` method automatically updates `raised` when payment succeeds
- ✅ Updates happen in two places:
  1. Payment verification endpoint (`/api/payments/verify`)
  2. Paystack webhook handler

**How It Works:**
```php
// When payment is successful:
$totalRaised = Donation::where('project_id', $projectId)
    ->where('status', 'completed')
    ->sum('amount');

$project->update(['raised' => $totalRaised]);
```

---

## 📡 API Endpoints

### 1. Get All Projects
**Endpoint:** `GET /api/projects`

**Response:**
```json
[
  {
    "id": 1,
    "project_title": "New Library Building",
    "project_description": "...",
    "target": 5000000.00,  // ✅ Fundraising target in naira
    "raised": 1250000.00,  // ✅ Amount raised in naira
    "icon_image_url": "/storage/...",
    "photos": [...]
  }
]
```

### 2. Get Projects with Photos (for slider/cards)
**Endpoint:** `GET /api/projects-with-photos`

**Response:**
```json
[
  {
    "id": 1,
    "project_title": "New Library Building",
    "project_description": "...",
    "target": 5000000.00,  // ✅ Fundraising target in naira
    "raised": 1250000.00,  // ✅ Amount raised in naira
    "icon_image_url": "/storage/...",
    "created_at": "2025-01-15T10:00:00.000000Z",
    "photos": [...]
  }
]
```

---

## 🎨 Frontend Implementation

### Step 1: Fetch Projects with Raised Amount

```javascript
// Example: Fetch projects
const fetchProjects = async () => {
  try {
    const response = await fetch('https://abu-endowment.cloud/api/projects-with-photos');
    const projects = await response.json();
    
    // Each project now has:
    // - project.raised (amount raised in naira)
    // - project.target (fundraising target in naira)
    
    return projects;
  } catch (error) {
    console.error('Error fetching projects:', error);
    return [];
  }
};
```

### Step 2: Format Naira Amount

```javascript
// Helper function to format naira
const formatNaira = (amount) => {
  if (!amount && amount !== 0) return '₦0';
  
  // Format with commas and 2 decimal places
  return new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
  
  // Alternative: Simple formatting
  // return `₦${amount.toLocaleString('en-NG')}`;
};

// Examples:
formatNaira(1250000)  // "₦1,250,000"
formatNaira(50000.50) // "₦50,001" (rounded)
formatNaira(0)        // "₦0"
```

### Step 3: Display on Project Card

#### React Example:

```jsx
import React from 'react';

const ProjectCard = ({ project }) => {
  const formatNaira = (amount) => {
    if (!amount && amount !== 0) return '₦0';
    return new Intl.NumberFormat('en-NG', {
      style: 'currency',
      currency: 'NGN',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  // Calculate progress percentage
  const progress = project.target > 0 
    ? Math.min((project.raised / project.target) * 100, 100)
    : 0;

  return (
    <div className="project-card">
      <img 
        src={project.icon_image_url} 
        alt={project.project_title}
        className="project-image"
      />
      
      <div className="project-content">
        <h3>{project.project_title}</h3>
        <p>{project.project_description}</p>
        
        {/* ✅ Display Raised Amount */}
        <div className="funding-info">
          <div className="raised-amount">
            <span className="label">Raised:</span>
            <span className="amount">{formatNaira(project.raised)}</span>
          </div>
          
          {project.target > 0 && (
            <>
              <div className="target-amount">
                <span className="label">Target:</span>
                <span className="amount">{formatNaira(project.target)}</span>
              </div>
              
              {/* Progress Bar */}
              <div className="progress-bar">
                <div 
                  className="progress-fill" 
                  style={{ width: `${progress}%` }}
                />
              </div>
              
              <div className="progress-text">
                {progress.toFixed(1)}% funded
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default ProjectCard;
```

#### React Native Example:

```jsx
import React from 'react';
import { View, Text, Image, StyleSheet } from 'react-native';

const ProjectCard = ({ project }) => {
  const formatNaira = (amount) => {
    if (!amount && amount !== 0) return '₦0';
    return new Intl.NumberFormat('en-NG', {
      style: 'currency',
      currency: 'NGN',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  const progress = project.target > 0 
    ? Math.min((project.raised / project.target) * 100, 100)
    : 0;

  return (
    <View style={styles.card}>
      <Image 
        source={{ uri: project.icon_image_url }}
        style={styles.image}
      />
      
      <View style={styles.content}>
        <Text style={styles.title}>{project.project_title}</Text>
        <Text style={styles.description}>{project.project_description}</Text>
        
        {/* ✅ Display Raised Amount */}
        <View style={styles.fundingInfo}>
          <View style={styles.amountRow}>
            <Text style={styles.label}>Raised:</Text>
            <Text style={styles.amount}>{formatNaira(project.raised)}</Text>
          </View>
          
          {project.target > 0 && (
            <>
              <View style={styles.amountRow}>
                <Text style={styles.label}>Target:</Text>
                <Text style={styles.amount}>{formatNaira(project.target)}</Text>
              </View>
              
              <View style={styles.progressBar}>
                <View 
                  style={[styles.progressFill, { width: `${progress}%` }]}
                />
              </View>
              
              <Text style={styles.progressText}>
                {progress.toFixed(1)}% funded
              </Text>
            </>
          )}
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#fff',
    borderRadius: 8,
    marginBottom: 16,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  image: {
    width: '100%',
    height: 200,
    resizeMode: 'cover',
  },
  content: {
    padding: 16,
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 8,
  },
  description: {
    fontSize: 14,
    color: '#666',
    marginBottom: 16,
  },
  fundingInfo: {
    marginTop: 8,
  },
  amountRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  label: {
    fontSize: 14,
    color: '#666',
  },
  amount: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2563eb',
  },
  progressBar: {
    height: 8,
    backgroundColor: '#e5e7eb',
    borderRadius: 4,
    overflow: 'hidden',
    marginTop: 8,
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#10b981',
  },
  progressText: {
    fontSize: 12,
    color: '#666',
    marginTop: 4,
    textAlign: 'center',
  },
});

export default ProjectCard;
```

### Step 4: CSS Styling (Web)

```css
.project-card {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

.project-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.project-content {
  padding: 20px;
}

.funding-info {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.raised-amount,
.target-amount {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.raised-amount .label,
.target-amount .label {
  color: #666;
  font-size: 14px;
}

.raised-amount .amount {
  color: #2563eb;
  font-size: 18px;
  font-weight: bold;
}

.target-amount .amount {
  color: #059669;
  font-size: 16px;
  font-weight: 600;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background-color: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
  margin-top: 12px;
}

.progress-fill {
  height: 100%;
  background-color: #10b981;
  transition: width 0.3s ease;
}

.progress-text {
  text-align: center;
  font-size: 12px;
  color: #666;
  margin-top: 8px;
}
```

---

## 📊 Display Options

### Option 1: Simple Raised Amount
```jsx
<div className="raised">
  <span>Raised: {formatNaira(project.raised)}</span>
</div>
```

### Option 2: Raised + Target with Progress
```jsx
<div className="funding">
  <div className="amounts">
    <span>Raised: {formatNaira(project.raised)}</span>
    <span>Target: {formatNaira(project.target)}</span>
  </div>
  <div className="progress-bar">
    <div style={{ width: `${(project.raised / project.target) * 100}%` }} />
  </div>
</div>
```

### Option 3: Percentage Only
```jsx
{project.target > 0 && (
  <div className="progress">
    {((project.raised / project.target) * 100).toFixed(1)}% funded
  </div>
)}
```

---

## 🔄 Real-time Updates

The `raised` amount is automatically updated on the backend when:
1. ✅ Payment is verified via `/api/payments/verify`
2. ✅ Paystack webhook confirms payment

**Frontend Refresh Options:**

### Option A: Poll for Updates
```javascript
// Refresh projects every 30 seconds
useEffect(() => {
  const interval = setInterval(() => {
    fetchProjects();
  }, 30000);
  
  return () => clearInterval(interval);
}, []);
```

### Option B: Refresh After Payment
```javascript
// After successful payment, refresh projects
const handlePaymentSuccess = async () => {
  // Show success message
  showSuccess('Payment successful!');
  
  // Refresh projects to show updated raised amount
  await fetchProjects();
};
```

### Option C: WebSocket (Advanced)
If you implement WebSockets, you can update in real-time when payments complete.

---

## ✅ Checklist

- [ ] Fetch projects from `/api/projects` or `/api/projects-with-photos`
- [ ] Access `project.raised` and `project.target` from API response
- [ ] Create `formatNaira()` helper function
- [ ] Display `raised` amount on project cards
- [ ] Optionally show `target` and progress bar
- [ ] Test with real payment flow
- [ ] Verify `raised` updates after payment completion

---

## 🎯 Example API Response

```json
{
  "id": 1,
  "project_title": "New Library Building",
  "project_description": "Building a modern library...",
  "target": 5000000.00,
  "raised": 1250000.00,
  "icon_image_url": "https://abu-endowment.cloud/storage/projects/icons/library.jpg",
  "created_at": "2025-01-15T10:00:00.000000Z",
  "photos": [
    {
      "image_url": "https://abu-endowment.cloud/storage/projects/photos/photo1.jpg"
    }
  ]
}
```

**Display:**
- **Raised:** ₦1,250,000
- **Target:** ₦5,000,000
- **Progress:** 25.0% funded

---

## 🚀 Quick Start

1. **Fetch projects:**
   ```javascript
   const projects = await fetch('/api/projects-with-photos').then(r => r.json());
   ```

2. **Display raised:**
   ```jsx
   <div>Raised: ₦{projects[0].raised.toLocaleString('en-NG')}</div>
   ```

3. **Done!** ✅

---

**The backend is already working!** Just fetch the projects and display the `raised` field. 🎉


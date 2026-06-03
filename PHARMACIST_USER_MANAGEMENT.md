# Pharmacist User Management System

## Overview

The Pharmacist User Management module enables pharmacists to view and manage patient profiles, refill requests, and pharmacy communications without having system administration privileges. This follows a **healthcare operations manager** model where pharmacists can support patients without accessing sensitive system settings.

---

## 🔐 Permission Model

### Pharmacist CAN:

#### ✅ View User Profiles

- Full name
- Email address
- Phone number
- Registration date
- Account status
- Last login date
- Profile picture
- **Cannot edit personal details directly**

#### ✅ View Refill History

- All refill requests submitted by a user
- Refill status history (pending, approved, rejected, ready, collected)
- Approval/rejection history
- Pharmacist notes and timestamps
- Refill audit trail

#### ✅ View Medication Activity

- Total refill requests
- Active refill requests
- Pending refill requests
- Last refill date
- Most requested medications (top 5)
- Refill statistics and trends

#### ✅ Approve or Reject Refill Requests

- Approve refill requests
- Reject refill requests with reasons
- Mark requests as Ready for Pickup
- Mark requests as Completed
- Add pharmacist notes to refills
- View complete refill audit trail

#### ✅ Contact Users

- View user phone number
- Initiate phone calls (tel: link)
- Send in-app notifications
- Send pharmacy-related messages
- Notify users when medications are ready

**Example Notifications:**

- ✅ Refill approved
- ✅ Refill rejected
- ❌ Medication unavailable
- 🎉 Medication ready for pickup
- 📞 Please contact the pharmacy

#### ✅ View User Pharmacy Activity

- Total refill requests count
- Active refill requests count
- Last refill date
- Most requested medications
- **Limited to pharmacy-related activities only**

---

### Pharmacist CANNOT:

#### ❌ Account Management Restrictions

Pharmacists **MUST NOT** be able to:

- ❌ Delete users
- ❌ Permanently deactivate users
- ❌ Change user roles
- ❌ Promote users
- ❌ Create admins
- ❌ Create pharmacists
- ❌ Assign permissions
- ❌ Change account ownership
- ❌ Reset system settings
- ❌ Access security configuration
- ❌ Manage authentication settings
- ❌ Access super admin functionality

#### ❌ Sensitive Information Restrictions

Pharmacists **MUST NOT** be able to:

- ❌ View passwords
- ❌ View password hashes
- ❌ Access security logs
- ❌ Access MFA settings
- ❌ Access private system data
- ❌ Access super admin accounts
- ❌ View or modify security settings
- ❌ Access authentication configuration

---

## 🎨 User Interface

### Users List Page (`/admin/users`)

**For Pharmacists:**

- Shows only **patients** (role='user')
- Cannot see other pharmacists or super admins
- Displays patient-centric information

**Table Columns:**

1. **Patient** - Avatar, name, and ID
2. **Contact** - Email and phone number
3. **Pharmacy Activity** - Total refills, active requests, last refill
4. **Status** - Active/Suspended badge
5. **Joined** - Registration date
6. **Actions:**
    - 👁️ **View Profile** - Opens detailed patient profile
    - 📞 **Call** - Direct phone call link (if phone provided)
    - 🔔 **Notify** - Send in-app notification

**Filters:**

- Search by name, email, or phone
- Filter by account status (Active, Suspended)
- Pagination

---

### User Profile Page (`/admin/users/{id}`)

#### Profile Header Card

- **Avatar** (or initials with gradient background)
- **Name** and Patient ID
- **Account Status** badge
- **Contact Information:**
    - 📧 Email address
    - 📞 Phone number
    - 📅 Member since date
    - 🕐 Last login timestamp

#### Pharmacy Activity Stats (4 Cards)

1. **Total Refills** - Lifetime refill request count
2. **Pending Requests** - Currently pending refills
3. **Ready for Pickup** - Medications ready to collect
4. **Completed** - Collected refills

#### Most Requested Medications

- Top 5 medications with request counts
- Displays medication name, dosage, form
- Badge showing number of requests

#### Refill Request History

- Complete list of all refill requests
- For each refill:
    - Medication name, dosage, form
    - Quantity requested
    - Status badge with color coding
    - Urgent flag (if marked urgent)
    - Patient notes
    - Pharmacist notes
    - Rejection reason (if rejected)
    - Timestamps (requested, reviewed)
    - Reviewed by pharmacist name
- Paginated list (10 per page)

#### Recent Notifications

- Last 10 pharmacy-related notifications sent to user
- Shows title, message, and timestamp

---

## 🔔 Send Notification Feature

### Notification Types:

1. **Pharmacy Update** - General pharmacy information
2. **Refill Status** - Refill request updates
3. **Important Alert** - Urgent messages
4. **System Message** - System-related communications

### Notification Form Fields:

- **Notification Type** (dropdown) _required_
- **Title** (text input, max 255 chars) _required_
- **Message** (textarea, max 1000 chars) _required_
- Character counter
- Patient info preview (avatar, name, email)

### Use Cases:

- ✅ "Your prescription is ready for pickup"
- ✅ "We need additional information for your refill request"
- ✅ "Your medication is out of stock, please call the pharmacy"
- ✅ "Your refill has been approved and is being prepared"
- ❌ "Please provide additional documents"

---

## 🛡️ Security Features

### 1. **Role-Based Access Control (RBAC)**

- Pharmacists can only view/contact regular users (role='user')
- Pharmacists cannot see other pharmacists or super admins
- Permission checks on both backend and frontend

### 2. **Tenant Isolation**

- All queries scoped to authenticated user's `tenant_id`
- Users can only see patients in their own pharmacy
- No cross-tenant data access

### 3. **Read-Only Profile Access**

- Pharmacists can **view** profiles but not **edit** them
- No profile editing buttons shown to pharmacists
- Backend enforces read-only access

### 4. **Action Auditing**

- All notifications logged with sender info
- Refill actions tracked in audit logs
- IP address and user agent captured
- Complete audit trail for compliance

### 5. **Permission Enforcement**

```php
// Backend Permission Checks
canViewUserProfile()    // ✅ Pharmacist can view patients
canViewUserRefills()    // ✅ Pharmacist can view refill history
canContactUser()        // ✅ Pharmacist can send notifications
canEditUser()           // ❌ Pharmacist CANNOT edit profiles
canSuspendUser()        // ❌ Pharmacist CANNOT suspend accounts
canDeleteUser()         // ❌ Pharmacist CANNOT delete users
```

---

## 🔄 Workflow Examples

### Workflow 1: View Patient Profile

1. Pharmacist navigates to **Users** page
2. Searches for patient by name/email/phone
3. Clicks **"View"** button
4. Views complete patient profile with:
    - Contact information
    - Refill statistics
    - Most requested medications
    - Complete refill history
    - Recent notifications

### Workflow 2: Send Notification to Patient

1. From Users list, click **"Notify"** button
2. Or from Profile page, click **"Send Notification"**
3. Select notification type (Pharmacy Update, Refill Status, etc.)
4. Enter notification title
5. Write message (up to 1000 characters)
6. Click **"Send Notification"**
7. Patient receives in-app notification
8. Notification logged in system

### Workflow 3: Contact Patient by Phone

1. View patient profile or list
2. Click **"Call"** button (if phone number provided)
3. System opens phone dialer with patient's number
4. Pharmacist can directly call patient

### Workflow 4: Review Refill Activity

1. Open patient profile
2. Review **Pharmacy Activity Stats** cards
3. Check **Most Requested Medications** section
4. Browse **Refill Request History**
5. See all refill statuses, notes, and timestamps
6. Navigate to Refill Management to approve/reject

---

## 📊 Data Structure

### User Model (Relevant Fields)

```php
- id
- tenant_id
- name
- email
- phone
- avatar_path (converted to avatar_url)
- account_status (active, suspended)
- last_login_at
- created_at
```

### RefillRequest Model

```php
- id
- tenant_id
- user_id
- medication_id
- quantity
- status (pending, approved, rejected, ready_for_pickup, collected)
- notes (patient notes)
- admin_notes (pharmacist notes)
- rejection_reason
- is_urgent
- reviewed_at
- reviewed_by (pharmacist ID)
- created_at
```

### Notification Model

```php
- id
- tenant_id
- user_id
- title
- message
- notification_type (pharmacy, refill, system, alert)
- status (unread, read)
- created_at
```

---

## 🚀 API Endpoints

### User Management Routes

```php
GET    /admin/users              // List all patients (pharmacist view)
GET    /admin/users/{id}         // View patient profile
POST   /admin/users/{id}/notify  // Send notification to patient
```

### Permission-Protected Actions

- `admin.users.index` - Pharmacists can access (shows only patients)
- `admin.users.show` - Pharmacists can access (view patient details)
- `admin.users.notify` - Pharmacists can access (send notifications)
- `admin.users.store` - **Super Admin ONLY**
- `admin.users.update` - **Super Admin ONLY**
- `admin.users.suspend` - **Super Admin ONLY**
- `admin.users.activate` - **Super Admin ONLY**

---

## 🎯 Role Comparison

| Feature                      | Pharmacist         | Super Admin          |
| ---------------------------- | ------------------ | -------------------- |
| **View Patients**            | ✅ Yes             | ✅ Yes               |
| **View Patient Profiles**    | ✅ Yes (read-only) | ✅ Yes (full access) |
| **View Refill History**      | ✅ Yes             | ✅ Yes               |
| **Send Notifications**       | ✅ Yes             | ✅ Yes               |
| **Call Patients**            | ✅ Yes             | ✅ Yes               |
| **Edit User Profiles**       | ❌ No              | ✅ Yes               |
| **Suspend Users**            | ❌ No              | ✅ Yes               |
| **Delete Users**             | ❌ No              | ✅ Yes               |
| **Create Users**             | ❌ No              | ✅ Yes               |
| **Create Pharmacists**       | ❌ No              | ✅ Yes               |
| **Change Roles**             | ❌ No              | ✅ Yes               |
| **View Other Pharmacists**   | ❌ No              | ✅ Yes               |
| **View Super Admins**        | ❌ No              | ✅ Yes               |
| **Access Security Settings** | ❌ No              | ✅ Yes               |

---

## 🧪 Testing Checklist

### Pharmacist Permissions

- [ ] Pharmacist can view patient list
- [ ] Pharmacist cannot see other pharmacists
- [ ] Pharmacist cannot see super admins
- [ ] Pharmacist can view patient profile
- [ ] Pharmacist can send notifications
- [ ] Pharmacist can access call link
- [ ] Pharmacist cannot edit patient details
- [ ] Pharmacist cannot suspend users
- [ ] Pharmacist cannot delete users
- [ ] Pharmacist cannot create users
- [ ] Pharmacist cannot change roles

### Super Admin Permissions

- [ ] Super admin can view all users
- [ ] Super admin can edit any user
- [ ] Super admin can suspend/activate users
- [ ] Super admin can create users
- [ ] Super admin can create pharmacists
- [ ] Super admin can change roles
- [ ] Super admin can delete users (not self)
- [ ] Super admin cannot suspend self

### Data Integrity

- [ ] Tenant isolation enforced
- [ ] Search filters work correctly
- [ ] Pagination works correctly
- [ ] Refill stats are accurate
- [ ] Top medications calculated correctly
- [ ] Notifications sent successfully
- [ ] Audit logs created properly

### UI/UX

- [ ] Patient avatars display correctly
- [ ] Badges show correct colors
- [ ] Call links work (tel:)
- [ ] Notification modal opens/closes
- [ ] Character counter works
- [ ] Empty states display when no data
- [ ] Responsive on mobile devices

---

## 📝 Usage Instructions

### For Pharmacists

#### To View a Patient Profile:

1. Navigate to **Users** in the sidebar
2. Use search to find a specific patient
3. Click the **"View"** button next to their name
4. Review their profile, refills, and activity

#### To Send a Notification:

1. From the Users list or Profile page, click **"Notify"**
2. Select the appropriate notification type
3. Enter a clear, professional title
4. Write your message (keep it concise and helpful)
5. Click **"Send Notification"**

#### To Call a Patient:

1. Locate the patient in the Users list or Profile page
2. Click the green **"Call"** button
3. Your phone dialer will open with their number
4. Complete the call as needed

---

## 🔒 Best Practices

### For Pharmacists:

1. **Privacy First** - Only access patient profiles when necessary for pharmacy operations
2. **Professional Communication** - Keep notifications clear, professional, and helpful
3. **Accurate Notes** - When adding pharmacist notes to refills, be specific and clear
4. **Timely Responses** - Review and respond to refill requests promptly
5. **Verify Information** - Double-check patient details before calling or notifying

### For Super Admins:

1. **Role Assignment** - Assign pharmacist role only to qualified staff
2. **Regular Audits** - Review activity logs periodically
3. **Training** - Ensure pharmacists understand their permissions and limitations
4. **Security** - Never share super admin credentials with pharmacists
5. **Compliance** - Maintain audit trails for regulatory compliance

---

## 🛠️ Technical Implementation

### Backend (Laravel)

- **Controller**: `App\Http\Controllers\Admin\UserController`
- **Models**: `User`, `RefillRequest`, `Notification`, `RefillAuditLog`
- **Middleware**: `auth`, `web.pharmacist`
- **Routes**: `routes/web.php` (admin.users.\*)

### Frontend (Vue.js + Inertia)

- **Index Page**: `resources/js/Pages/Admin/Users/Index.vue`
- **Profile Page**: `resources/js/Pages/Admin/Users/Show.vue`
- **Components**: `AdminLayout`, `Card`, `Button`, `Input`, `Badge`, `Modal`, `EmptyState`
- **Composables**: `useDebounce`, `usePagination`

### Database Tables

- `users` - User accounts
- `refill_requests` - Refill submissions
- `notifications` - In-app notifications
- `refill_audit_logs` - Refill action audit trail

---

## 📖 Related Documentation

- [Inventory Management](./INVENTORY_SECURITY_VALIDATION.md)
- [Refill Management](./RBAC_AND_IMPROVEMENTS.md)
- [Admin Dashboard](./ADMIN_DASHBOARD_IMPLEMENTATION.md)
- [Quick Start Guide](./QUICK_START_GUIDE.md)

---

**Version**: 1.0  
**Last Updated**: June 3, 2026  
**Status**: ✅ Production Ready  
**Author**: PrimeChem Development Team

# Pharmacist Quick Reference Card

## 👨‍⚕️ User Management for Pharmacists

### 🔍 Finding Patients

**Navigate**: Sidebar → **Users**

**Search**: Type in search box to find by:

- Name
- Email
- Phone number

**Filter**: Use status dropdown:

- All Status
- Active
- Suspended

---

### 👁️ Viewing Patient Profiles

**From Users List**:

- Click the blue **"View"** button next to patient name

**What You'll See**:

- ✅ Patient contact info (name, email, phone)
- ✅ Account status and member since date
- ✅ Last login timestamp
- ✅ Total refills and statistics
- ✅ Pending requests count
- ✅ Most requested medications (top 5)
- ✅ Complete refill request history
- ✅ Recent notifications sent

**What You CAN'T Do**:

- ❌ Edit patient personal information
- ❌ Change email or password
- ❌ Delete patient account
- ❌ Suspend or activate account

---

### 🔔 Sending Notifications

**How to Send**:

1. Click **"Notify"** button (purple) from:
    - Users list page, OR
    - Patient profile page
2. Select notification type:
    - **Pharmacy Update** - General pharmacy info
    - **Refill Status** - Refill request updates
    - **Important Alert** - Urgent messages
    - **System Message** - System-related info
3. Enter notification title (max 255 characters)
4. Write message (max 1000 characters)
5. Click **"Send Notification"**

**Example Notifications**:

- "Your prescription is ready for pickup"
- "We need additional information for your refill request"
- "Your refill has been approved and is being prepared"
- "Please contact the pharmacy regarding your medication"
- "Your medication is out of stock, expected arrival: [date]"

**Tips**:

- Keep messages clear and professional
- Include specific details (medication name, pickup time)
- Use professional language
- Avoid medical advice (refer to pharmacist consultation)

---

### 📞 Calling Patients

**How to Call**:

1. Locate patient in Users list or Profile page
2. Click green **"Call"** button
3. Your phone dialer opens with patient's number
4. Complete call as needed

**When to Call**:

- Urgent medication questions
- Clarify refill request details
- Follow-up on rejected requests
- Confirm pickup arrangements
- Address patient concerns

**Note**: Only available if patient has provided phone number

---

### 📊 Understanding Pharmacy Activity Stats

**Total Refills**:

- Lifetime count of all refill requests submitted
- Appears in Users list and Profile page

**Active Requests**:

- Current requests in progress (pending, under review, approved, ready)
- Shows workload for that patient

**Pending Requests**:

- Waiting for pharmacist review
- Requires immediate attention

**Last Refill**:

- Timestamp of most recent refill request
- Helps identify inactive patients

---

### 💊 Viewing Refill History

**In Patient Profile**:

- Scroll to **"Refill Request History"** section
- Shows all refill requests ever submitted

**For Each Refill, You See**:

- Medication name, dosage, and form
- Quantity requested
- Status badge (color-coded)
- Urgent flag (if marked)
- Patient notes
- Pharmacist notes (added by reviewer)
- Rejection reason (if rejected)
- Requested date and time
- Reviewed date and time
- Reviewed by (pharmacist name)

**Status Colors**:

- 🟡 **Yellow** - Pending
- 🔵 **Blue** - Under Review / Approved
- 🟢 **Green** - Ready for Pickup
- ⚫ **Gray** - Collected
- 🔴 **Red** - Rejected / Cancelled

---

### 🚫 What Pharmacists CANNOT Do

You do **NOT** have permission to:

- ❌ Edit patient personal details (name, email, phone)
- ❌ Change patient passwords
- ❌ Delete patient accounts
- ❌ Suspend or activate accounts
- ❌ Create new user accounts
- ❌ Create other pharmacists
- ❌ Change user roles
- ❌ View other pharmacists' profiles
- ❌ View super admin accounts
- ❌ Access security settings
- ❌ View password hashes or MFA settings
- ❌ Access system configuration

**If you need any of these actions, contact your Super Admin.**

---

### ✅ What Pharmacists CAN Do

You **HAVE** permission to:

- ✅ View all patient profiles (read-only)
- ✅ View complete refill history
- ✅ Send notifications to patients
- ✅ Call patients (if phone provided)
- ✅ View pharmacy activity statistics
- ✅ See most requested medications
- ✅ Review patient notes on refills
- ✅ View refill audit trail
- ✅ Search and filter patient list

**For refill approvals/rejections, use the Refill Management page.**

---

### 🔐 Privacy & Security

**Remember**:

- Only access patient profiles when necessary for pharmacy operations
- Do not share patient information outside the pharmacy
- All your actions are logged for audit purposes
- Only patients in YOUR pharmacy are visible (tenant isolation)
- You cannot see other pharmacists or admins in the user list

**Best Practices**:

- Verify patient identity before discussing medications
- Keep notifications professional and HIPAA-compliant
- Do not include sensitive medical information in notifications
- Use secure channels for detailed medical discussions
- Log out when leaving your workstation

---

### 🆘 Common Tasks

#### Task: Find a patient by phone number

1. Go to **Users** page
2. Type phone number in search box
3. Results appear instantly

#### Task: Check patient's refill status

1. Search for patient
2. Click **"View"** button
3. See "Pharmacy Activity Stats" at top
4. Review "Refill Request History" section

#### Task: Notify patient medication is ready

1. Find patient in list or profile
2. Click **"Notify"** button
3. Select "Refill Status"
4. Title: "Your medication is ready"
5. Message: "Your [medication name] prescription is ready for pickup at [pharmacy name]. Hours: [hours]"
6. Send

#### Task: Contact patient about refill issue

1. View patient profile
2. Click green **"Call"** button, OR
3. Click purple **"Notify"** button for non-urgent issues

#### Task: Review patient medication history

1. Open patient profile
2. Check **"Most Requested Medications"** section
3. Review complete **"Refill Request History"**

---

### 💡 Tips for Efficiency

**Use Search Effectively**:

- Search by last name for quick lookup
- Use phone number if name is common
- Use email for online-only patients

**Prioritize Patients**:

- Look for red "pending" badges in Pharmacy Activity column
- Check patients with urgent refill requests first
- Follow up on recent notifications

**Professional Communication**:

- Use patient's preferred name
- Keep notifications brief and clear
- Include next steps or action items
- Provide contact info if needed

**Stay Organized**:

- Review refill history before calling patients
- Note most requested medications for inventory planning
- Check last refill date to identify inactive patients

---

### 📱 Mobile Usage

The system is mobile-responsive:

- Access from tablet or phone
- All features work on mobile
- Touch-friendly buttons
- Readable on small screens

**Recommended**: Use desktop/laptop for best experience

---

### 🆘 Need Help?

**For Technical Issues**:

- Contact your Super Admin
- Report bugs or errors immediately
- Provide screenshots if possible

**For Permission Issues**:

- Contact your Super Admin
- Super Admin can grant additional access if needed

**For Training**:

- Review full documentation: `PHARMACIST_USER_MANAGEMENT.md`
- Ask senior pharmacists for guidance
- Practice with test patient accounts

---

**Quick Access**: Bookmark this page for easy reference!

**Version**: 1.0  
**Last Updated**: June 3, 2026  
**For**: PrimeChem Pharmacy Pharmacists

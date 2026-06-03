# How to Add New Products to Inventory

## Overview

The "Add New Product" form allows pharmacists to add completely new medications to the pharmacy inventory system. This guide explains each field and how to use the form properly.

---

## Accessing the Form

1. Navigate to **Inventory** page from the sidebar
2. Click the blue **"Add New Product"** button in the top right
3. A modal will open with three sections

---

## Form Sections

### 📦 Section 1: Product Information

_Enter the basic details about the medication_

#### **Medication Name** _(Required)_

- **What it is**: The generic or brand name of the drug
- **Examples**:
    - Paracetamol
    - Ibuprofen
    - Amoxicillin
    - Lisinopril
- **Tips**:
    - Use the standard medical name
    - Check spelling carefully
    - Use Title Case (capitalize first letter)

#### **Dosage** _(Required)_

- **What it is**: The strength/concentration of the medication
- **Examples**:
    - 500mg
    - 250mg
    - 10mg/5ml
    - 100mcg
- **Tips**:
    - Include the unit (mg, ml, mcg, etc.)
    - Be precise with numbers
    - For liquids, use format like "10mg/5ml"

#### **Form** _(Required)_

- **What it is**: The physical form of the medication
- **Options**:
    - **Tablet** - Solid pills taken orally
    - **Capsule** - Gelatin shells with powder/liquid inside
    - **Syrup** - Liquid medicine, usually sweet
    - **Injection** - Given via needle
    - **Cream** - Topical application
    - **Drops** - Liquid drops (eye, ear, nasal)
    - **Inhaler** - Breathable medication
    - **Powder** - Dry powder form
- **Tips**: Select the most accurate form

#### **Manufacturer** _(Optional)_

- **What it is**: The pharmaceutical company that produces the drug
- **Examples**:
    - Pfizer
    - GSK
    - Novartis
    - Emzor
    - May & Baker
- **Tips**:
    - Helps identify authentic products
    - Useful for recalls or quality checks

---

### 📊 Section 2: Stock Details

_Manage quantities, batches, and expiry dates_

#### **Quantity** _(Required)_

- **What it is**: How many units you're adding to inventory
- **Examples**:
    - 100 (for 100 tablets)
    - 50 (for 50 bottles)
    - 200 (for 200 capsules)
- **Tips**:
    - Count carefully before entering
    - This is the TOTAL number of units
    - For strips/packs, count individual units

#### **Reorder Level** _(Optional but Recommended)_

- **What it is**: Minimum stock before you need to reorder
- **Examples**:
    - 20 (reorder when stock drops below 20)
    - 50 (for fast-moving drugs)
    - 10 (for slow-moving drugs)
- **Tips**:
    - Set higher for popular medications
    - Consider lead time from supplier
    - System will alert you when stock is low

#### **Batch Number** _(Optional but Important)_

- **What it is**: Manufacturer's batch/lot number for tracking
- **Examples**:
    - BATCH2024-001
    - LOT-A12345
    - MFG-20240601
- **Tips**:
    - Critical for recalls
    - Found on product packaging
    - Helps track expiry dates

#### **Expiry Date** _(Optional but Critical)_

- **What it is**: Date when medication expires and can't be sold
- **Format**: YYYY-MM-DD (e.g., 2025-12-31)
- **Tips**:
    - Must be a future date
    - Check the actual product packaging
    - System will alert when approaching expiry
    - NEVER sell expired medications

#### **Supplier** _(Optional but Recommended)_

- **What it is**: Where you purchased the medication
- **Examples**:
    - ABC Pharmaceuticals
    - XYZ Medical Supplies
    - Direct from Manufacturer
- **Tips**:
    - Helps with reordering
    - Useful for quality issues
    - Track reliable suppliers

---

### 💰 Section 3: Pricing

_Set cost and selling prices_

#### **Cost Price** _(Optional)_

- **What it is**: How much YOU paid for each unit
- **Examples**:
    - 50.00 (₦50 per unit)
    - 1200.50 (₦1,200.50 per bottle)
- **Tips**:
    - Include per-unit cost, not total
    - Helps calculate profit margins
    - Useful for financial reports

#### **Selling Price** _(Optional)_

- **What it is**: How much customers pay for each unit
- **Examples**:
    - 80.00 (₦80 per unit)
    - 1500.00 (₦1,500 per bottle)
- **Tips**:
    - Must be higher than cost price
    - Consider market rates
    - Include reasonable markup
    - Should cover operational costs

---

## Step-by-Step Process

### 1️⃣ **Open the Form**

```
Click "Add New Product" button → Modal opens
```

### 2️⃣ **Fill Product Information**

- Enter medication name
- Enter dosage
- Select form from dropdown
- (Optional) Enter manufacturer

### 3️⃣ **Enter Stock Details**

- Enter quantity
- (Recommended) Set reorder level
- (Important) Enter batch number
- (Critical) Select expiry date
- (Recommended) Enter supplier name

### 4️⃣ **Set Pricing**

- (Optional) Enter cost price
- (Optional) Enter selling price

### 5️⃣ **Review & Submit**

- Double-check all information
- Ensure required fields are filled (marked with red \*)
- Click blue **"Add Product"** button

### 6️⃣ **Success!**

- Product is added to inventory
- Medication is created in the system
- You'll see it in the inventory table

---

## Important Notes

### ⚠️ Required Fields

You MUST fill these fields (marked with red \*):

- ✅ Medication Name
- ✅ Dosage
- ✅ Form
- ✅ Quantity

### 💡 Best Practices

1. **Always enter batch numbers** - Critical for tracking and recalls
2. **Always enter expiry dates** - Prevents selling expired medications
3. **Set reorder levels** - Prevents stock-outs
4. **Enter supplier info** - Makes reordering easier
5. **Set pricing** - Helps with financial tracking
6. **Double-check spelling** - Especially medication names
7. **Use consistent formats** - For dosages and batch numbers

### 🚫 Common Mistakes

❌ **Don't**:

- Leave medication name misspelled
- Enter total cost instead of per-unit cost
- Forget to check expiry dates
- Mix up dosage units (mg vs mcg)
- Leave reorder level too low

✅ **Do**:

- Verify medication name is correct
- Enter per-unit prices
- Always check expiry dates on packaging
- Be precise with dosage units
- Set appropriate reorder levels

---

## Example: Adding Paracetamol

### Complete Example:

**Product Information:**

- Medication Name: `Paracetamol`
- Dosage: `500mg`
- Form: `Tablet`
- Manufacturer: `GSK`

**Stock Details:**

- Quantity: `200`
- Reorder Level: `50`
- Batch Number: `BATCH2024-0615`
- Expiry Date: `2026-06-30`
- Supplier: `ABC Pharmaceuticals Ltd`

**Pricing:**

- Cost Price: `35.00`
- Selling Price: `50.00`

**Result**:

- 200 tablets of Paracetamol 500mg added to inventory
- System will alert when stock drops below 50
- Will flag for expiry 30 days before June 30, 2026
- Profit margin: ₦15 per tablet

---

## After Adding a Product

### What Happens Next?

1. **Product appears in inventory table**
    - Shows in the main inventory list
    - Displays quantity, status, and expiry info

2. **Available for customers**
    - Customers can search for it in the mobile app
    - Can request refills for this medication

3. **Tracking begins**
    - System monitors stock levels
    - Alerts when low stock (below reorder level)
    - Alerts when approaching expiry (30 days before)

4. **Can be edited or refilled**
    - Click "Edit" to update details
    - Use "Refill Inventory" to add more stock

---

## Troubleshooting

### Form won't submit?

- ✅ Check all required fields (marked with \*)
- ✅ Ensure quantity is a positive number
- ✅ Verify expiry date is in the future
- ✅ Check for error messages below fields

### Duplicate medication warning?

- Product may already exist in system
- Use "Refill Inventory" instead to add more stock
- Or create with different dosage/form

### Price calculations wrong?

- Ensure you're entering PER UNIT price, not total
- Cost price should be less than selling price
- Use decimals for cents (e.g., 50.50)

---

## Quick Reference

| Field           | Required? | Type     | Example       |
| --------------- | --------- | -------- | ------------- |
| Medication Name | ✅ Yes    | Text     | Paracetamol   |
| Dosage          | ✅ Yes    | Text     | 500mg         |
| Form            | ✅ Yes    | Dropdown | Tablet        |
| Manufacturer    | ❌ No     | Text     | GSK           |
| Quantity        | ✅ Yes    | Number   | 200           |
| Reorder Level   | ❌ No     | Number   | 50            |
| Batch Number    | ❌ No     | Text     | BATCH2024-001 |
| Expiry Date     | ❌ No     | Date     | 2026-12-31    |
| Supplier        | ❌ No     | Text     | ABC Pharma    |
| Cost Price      | ❌ No     | Number   | 35.00         |
| Selling Price   | ❌ No     | Number   | 50.00         |

---

## Need Help?

- **Can't find a medication?** - Add it as a new product
- **Need to add more stock?** - Use "Refill Inventory" button instead
- **Made a mistake?** - Click "Edit" on the inventory item to fix it
- **Need to delete?** - Click "Delete" but be careful - this removes the product entirely

---

**Last Updated**: June 2, 2026
**Version**: 1.0

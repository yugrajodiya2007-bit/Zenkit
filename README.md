# 🛍️ Zenkit - Enterprise Business E-Commerce Platform

A production-grade, business-ready E-Commerce solution built in **PHP** with an **SQL Database** (zero-config SQLite fallback + full MySQL `schema.sql`), **Flipkart Classic Light Theme**, **First-Visit Split Sign-Up Modal**, and **Interactive 3D WebGL (Three.js) Product Previews**.

---

## 🚀 Live Demo & Getting Started

### 1. Start Built-in PHP Server
From the root directory (`d:\android\PHP`):
```powershell
php -S 127.0.0.1:8000
```
Then open your browser and navigate to:
**[http://127.0.0.1:8000/index.php](http://127.0.0.1:8000/index.php)**

---

## 🔑 Demo Accounts & Credentials

| Role | Email | Password | Access |
|---|---|---|---|
| **Store Administrator** | `admin@zenkit.io` | `admin123` | [Admin Portal](http://127.0.0.1:8000/admin/index.php) |
| **Demo Customer** | `demo@zenkit.io` | `demo123` | Storefront & Orders |

> ⚡ **1-Click Auto-Fill:** All registration and login forms feature 1-click quick fill buttons for instant testing.

---

## 🏢 Business-Ready & Enterprise Features

### 1. Legal Compliance & Official Corporate Identity
- **Corporate Entity**: *Zenkit Retail Technologies Pvt. Ltd.*
- **Statutory Identifiers**:
  - GSTIN: `29AABCU9603R1Z7` (Karnataka, Code 29)
  - CIN: `U51109KA2012PTC066107`
  - Toll-Free: `1800 208 9898`
- **Legal Policy Suite**:
  - [privacy.php](file:///d:/android/PHP/privacy.php) — Information Technology Act compliant Privacy Policy with Grievance Officer details.
  - [terms.php](file:///d:/android/PHP/terms.php) — Commercial Sale Agreement & Jurisdiction clauses.
  - [returns.php](file:///d:/android/PHP/returns.php) — 7-Day Doorstep Replacement & Refund Policy.
  - [about.php](file:///d:/android/PHP/about.php) — Corporate vision, 42-point QC standards, and executive certifications.

### 2. Official GST Tax Invoice Generator (`invoice.php`)
- **[invoice.php?id=1](http://127.0.0.1:8000/invoice.php?id=1)**
- Generates an official, print-ready corporate GST/VAT Tax Invoice with:
  - Billed By (Zenkit Registered Office, GSTIN, CIN, Phone, Email)
  - Billed To & Shipped To addresses
  - Itemized HSN Code classifications (e.g. `84713010` for computing)
  - 18% GST Breakdown: Taxable Value, CGST (9%), SGST (9%), Total
  - Amount in words
  - System-generated digital stamp & QR authentication seal
  - Direct 1-Click "Print / Save as PDF" button.

### 3. Live Shipment Tracking Portal (`track-order.php`)
- **[track-order.php](http://127.0.0.1:8000/track-order.php)**
- Customers can track packages with Order Number (`ZK-748921`) or Tracking Code (`TRK-DEL-994821`).
- Displays live logistics checkpoints (*Order Placed → Quality Inspection Sealed → In Transit with BlueDart Air → Out for Delivery*).

### 4. Real-Time Search Autocomplete (`api/search.php`)
- As shoppers type into the header search bar, a dynamic autocomplete dropdown appears with matching products, high-res thumbnails, category tags, and formatted pricing.

### 5. Corporate Contact & B2B Inquiry Desk (`contact.php`)
- **[contact.php](http://127.0.0.1:8000/contact.php)**
- Interactive contact form supporting Order Tracking, 1-Year Warranty Claims, Returns, and **Corporate / Bulk Procurement**.
- Inquiries are logged directly into the SQL database (`contact_inquiries`).
- View and manage tickets in **[admin/inquiries.php](http://127.0.0.1:8000/admin/inquiries.php)**.

### 6. Customer Wishlist (`wishlist.php`)
- Toggle items into wishlist via heart button on product cards or details.
- Header counter badge updates in real time.
- 1-Click Move to Cart support.

### 7. VIP Newsletter Subscription & Promo Vouchers (`api/subscribe.php`)
- Customers receive an instant 15% discount voucher code (`WELCOME15`) upon email subscription.
- Subscribers logged into `newsletter_subscribers` table.

### 8. Floating Support / WhatsApp Desk Widget
- Floating button in bottom-right corner of every page with quick links to Track Shipment, Check FAQ, or Connect to an Agent.

### 9. Business Admin Portal (`/admin/`)
- **Analytics Dashboard ([admin/index.php](file:///d:/android/PHP/admin/index.php))**: Revenue metrics, order volume, catalog size.
- **CSV Order Export**: Instant download of all orders into CSV format for accounting and logistics dispatch.
- **Product CRUD ([admin/products.php](file:///d:/android/PHP/admin/products.php))**: Inventory stock tracking, pricing, specifications, and 3D model assignments.
- **Fulfillment & Logistics ([admin/orders.php](file:///d:/android/PHP/admin/orders.php))**: Status dropdown (*Processing*, *Packed*, *Shipped*, *Delivered*, *Cancelled*) and tracking code updates.
- **Category Manager ([admin/categories.php](file:///d:/android/PHP/admin/categories.php))**: Department taxonomy management.
- **Customer Tickets ([admin/inquiries.php](file:///d:/android/PHP/admin/inquiries.php))**: Inbound support & B2B procurement inquiries inbox.

---

## 🎨 Design & Visual Features
- **Flipkart Classic Light Theme**: Royal Blue `#2874F0`, white clean cards, top circular category strip, deal rails, countdown timer.
- **First-Visit Split Sign-Up Modal**: Greets new visitors with a split branding modal and 1-click demo filler or guest skip.
- **Three.js 3D WebGL**: Hero interactive device + 360° product inspector modal with wireframe and color switcher.

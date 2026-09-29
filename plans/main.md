# Enterprise Pharmacy Management System (PMS)
## Production-Grade Architecture & Master Blueprint (Laravel + Livewire + Tailwind CSS)

---

## 1. Executive Summary & Technology Stack

### 1.1 Core Architecture
The system is engineered as a high-performance, multi-tenant capable, production-grade **Enterprise Pharmacy Management & POS System**. Built entirely on a pure **Laravel + Livewire + Alpine.js + Tailwind CSS** stack without proprietary UI wrappers (such as Flux), it provides maximum maintainability, atomic mathematical precision, and extreme reactive execution speed.

### 1.2 Target Technology Stack
- **Backend Framework:** Laravel (PHP 8.3+)
- **Reactive UI Engine:** Livewire + Alpine.js
- **Styling Framework:** Tailwind CSS v4 (Medical Enterprise Theme: Teal `#00897B` & Emerald `#059669`)
- **Database Layer:** MySQL 8.0+ / PostgreSQL 15+ (Strict `DECIMAL(12,4)` for currency & fractional math, InnoDB with row-level locking)
- **Exports & Documents:** 
  - Excel/CSV: Streamed native CSV & `maatwebsite/excel`
  - POS Thermal Print: Native CSS `@media print` (58mm & 80mm ESC/POS layout)
  - Formal Invoices/Ledgers: Barryvdh DomPDF / Snappy PDF
- **Icons:** SVG-based Lucide Icons / Heroicons via Blade UI Kit

---

## 2. Core Architectural & UI/UX Standards

### 2.1 Tab-Based View Architecture
- Every primary module container (Settings, Reports, Inventory, HR, Accounts, Purchases) utilizes a modular **Tab Navigation System**:
  - Livewire dynamic query-string sync (`#[Url(as: 'tab')] public string $activeTab = 'general';`).
  - Active indicator with Teal border-b-2 (`border-[#00897B] text-[#00897B] font-semibold bg-teal-50/50`).
  - Lazy-loaded tab content components (`wire:loading` skeletons) to maintain sub-100ms page transitions.

### 2.2 Reusable Livewire DataTable Architecture
All index screens implement standard Livewire Table traits/components:
1. **Filter & Search Bar:**
   - Real-time debounced universal search (`wire:model.live.debounce.300ms="search"`).
   - Multi-parameter filtering (Category, Manufacturer, Stock Status, Expiry Filter, Date Range).
   - Active filter count chips with single-click clear.
2. **Bulk Actions & Row Selection:**
   - Multi-checkbox selection (`wire:model.live="selectedRows"`).
   - Floating batch action toolbar (Bulk Status Update, Batch Delete, Barcode Label Generator).
3. **Data Grid Controls & Export Engine:**
   - Column sorting with ascending/descending indicators.
   - Per page selector (`[10, 25, 50, 100]`).
   - Direct CSV export, Excel download, and Print view toggle.

---

## 3. The Pharmacy Unit Conversion, Bonus & FEFO Engine

### 3.1 Mathematical Conversion Model
- **Base Atomic Unit:** `Piece` (Tablet, Capsule, Ampoule, Bottle). All calculations, batch stock, and ledger entries MUST be stored in base units to eliminate fractional rounding discrepancies.
- **Packaging Hierarchy:**
  - `Strip` = $N$ Pieces
  - `Box` = $M$ Strips = $(M \times N)$ Pieces
  - `Carton` = $K$ Boxes = $(K \times M \times N)$ Pieces
- **Formulas:**
  $$\text{Total Base Units} = (\text{Cartons} \times K \times M \times N) + (\text{Boxes} \times M \times N) + (\text{Strips} \times N) + \text{Pieces}$$
  $$\text{Price Per Piece} = \frac{\text{Box MRP}}{M \times N}$$

### 3.2 Bonus / Free Scheme & Effective Unit Cost
- When purchasing under trade schemes (e.g. *Buy 10 Boxes, Get 1 Box Free*):
  $$\text{Total Pieces Received} = (10 + 1) \times \text{Pieces Per Box}$$
  $$\text{Effective Unit Purchase Cost} = \frac{\text{Total Billed Cost}}{\text{Total Pieces Received}}$$

### 3.3 FEFO (First-Expired, First-Out) Algorithm
During POS checkout or stock deduction:
1. Query batches for `product_id` where `current_stock > 0` and `expiry_date > CURRENT_DATE`.
2. Order strictly by `expiry_date ASC, created_at ASC`.
3. Auto-allocate deductions sequentially across available batches.
4. Near-expiry warning trigger ($\le 90$ days) and locked expired batch barrier ($\le 0$ days).

---

## 4. Key Enterprise Core Mechanisms

### 4.1 Split Payment System (`sale_payments` Engine)
- Supports multi-method split settlement in a single invoice transaction:
  - Example: For a 1,500 BDT invoice $\rightarrow$ Cash: 500 BDT + bKash: 700 BDT + Customer Credit Due: 300 BDT.
  - Automatically verifies that $\sum(\text{Payments}) + \text{Due Amount} = \text{Grand Total}$.
  - Creates atomic payment receipts and assigns individual MFS/Card transaction references.

### 4.2 Doctor Directory, Prescription & Narcotic Drug Tracking
- **Doctor Master:** Stores Doctor Name, Degrees, BMDC Reg Number, and Hospital/Clinic.
- **Prescription Attachment:** Uploads image/scan (`prescription_image_path`) tied directly to the sale.
- **Controlled Substance / Narcotic Flag (`is_narcotic`):**
  - Products flagged as Schedule-H/Narcotics require a mandatory Doctor selection, Patient details, and prescription verification before checkout.
  - Generates a dedicated Narcotic Register Log for regulatory drug administration compliance.

### 4.3 Alternative Generic Substitute Finder
- When a requested branded medicine is low/out of stock in POS, the terminal executes a Livewire reactive lookup on `generics.id`.
- Instantly presents available alternative branded medicines containing the exact same generic molecule, dosage strength, and form with current in-stock counts and prices.

### 4.4 Suspended / Hold & Recall Cart (Draft POS)
- Cashiers can place a customer's active cart on **Hold** (`F4`) with a temporary draft token without losing item selections or applied discounts.
- Allows immediate processing of the next in-line customer.
- One-click **Recall** (`F7`) restores the pending order back to the active POS terminal.

### 4.5 Cash Register Shifts & Z-Report Reconciliation
- **Shift Opening:** Cashier enters starting drawer cash float (`opening_cash`).
- **Real-Time Shift Ledger:** Tracks all cash in, cash out, digital payments (bKash/Nagad/Cards), and expense payouts during the shift.
- **Shift Closing & Z-Report:** Cashier enters physical cash count. System automatically calculates expected cash vs actual physical cash, logs discrepancies (`difference`), locks the register, and prints a thermal Z-Report.

### 4.6 Real-Time Cost of Goods Sold (COGS) & Profit Analysis
- Every `sale_items` record captures the exact unit cost of the batch consumed at the time of sale (`unit_cost`).
- Provides instantaneous Gross Profit ($\text{Selling Price} - \text{Batch Purchase Cost}$) per line item, per invoice, and per cashier shift.

---

## 5. Complete Relational Database Schema (Migration Blueprint)

```
branches (Multi-Store / Multi-Branch)
 ├── id (BIGINT PK)
 ├── name, code (unique), address, phone, email, invoice_prefix
 ├── is_active (boolean default true)
 └── timestamps

users
 ├── id (BIGINT PK), branch_id (FK), role_id (FK)
 ├── name, email (unique), password, phone, status (active/inactive)
 └── timestamps

units
 ├── id (BIGINT PK)
 ├── name (Piece, Strip, Box, Carton, Bottle, Vial, Sachet)
 ├── short_code (pcs, str, bx, ctn, btl, vial, sct)
 ├── conversion_factor (relative to base piece, default 1)
 ├── is_base_unit (boolean default false)
 └── timestamps

categories
 ├── id (BIGINT PK), name, slug (unique), description, is_active (boolean)
 └── timestamps

manufacturers (Pharmaceutical Companies)
 ├── id (BIGINT PK), name, contact_person, phone, email, address, is_active
 └── timestamps

generics (Chemical Formulations)
 ├── id (BIGINT PK), name, indication, side_effects, precautions
 └── timestamps

doctors (Prescription & Medical Directory)
 ├── id (BIGINT PK), name, degrees, bmdc_reg_no, hospital_name, phone
 └── timestamps

products
 ├── id (BIGINT PK), branch_id (FK nullable - for local/global catalog)
 ├── name, generic_id (FK), category_id (FK), manufacturer_id (FK)
 ├── base_unit_id (FK - Default atomic base unit e.g. Piece)
 ├── sku (unique), barcode (unique indexed), rack_location
 ├── min_stock_alert (int default 20), max_stock_level (int default 1000)
 ├── is_narcotic (boolean default false - Schedule H / Controlled)
 ├── requires_prescription (boolean default false)
 ├── tax_rate (decimal 5,2 default 0.00), status (active/inactive)
 └── timestamps

product_units (Unit conversion & pricing hierarchy per product)
 ├── id (BIGINT PK), product_id (FK), unit_id (FK)
 ├── multiplier (int - total base pieces per this unit)
 ├── purchase_price (decimal 12,4), mrp_price (decimal 12,4)
 ├── is_default_purchase (boolean), is_default_sale (boolean)
 └── timestamps

batches (FEFO Inventory Core)
 ├── id (BIGINT PK), branch_id (FK), product_id (FK), batch_number
 ├── purchase_item_id (FK nullable)
 ├── base_unit_id (FK - Reference to atomic base unit)
 ├── initial_stock (int - in base pieces), current_stock (int - in base pieces)
 ├── purchase_rate (decimal 12,4 - per base piece), mrp_rate (decimal 12,4 - per base piece)
 ├── manufacture_date (date nullable), expiry_date (date indexed)
 ├── alert_days_before_expiry (int default 90)
 └── timestamps

customers
 ├── id (BIGINT PK), branch_id (FK), name, phone (unique indexed), email, address
 ├── opening_balance (decimal 12,2 default 0), current_due (decimal 12,2 default 0)
 ├── credit_limit (decimal 12,2 default 0), reward_points (int default 0)
 └── timestamps

suppliers
 ├── id (BIGINT PK), name, company_name, phone (unique indexed), email, address
 ├── opening_balance (decimal 12,2 default 0), current_due (decimal 12,2 default 0)
 └── timestamps

sales (POS & Invoicing Master)
 ├── id (BIGINT PK), branch_id (FK), invoice_no (unique indexed), customer_id (FK nullable)
 ├── doctor_id (FK nullable), user_id (FK - Cashier)
 ├── sale_date (timestamp indexed)
 ├── sub_total (decimal 12,2), discount_type (fixed/percentage), discount_amount (decimal 12,2)
 ├── tax_amount (decimal 12,2), round_off (decimal 12,2), grand_total (decimal 12,2)
 ├── paid_amount (decimal 12,2), due_amount (decimal 12,2), change_amount (decimal 12,2)
 ├── payment_status (PAID, PARTIAL, DUE)
 ├── status (COMPLETED, RETURNED, CANCELLED, HOLD)
 ├── notes, prescription_image_path
 └── timestamps

sale_items
 ├── id (BIGINT PK), sale_id (FK), product_id (FK), batch_id (FK)
 ├── unit_id (FK - Chosen packaging unit: Box/Strip/Piece)
 ├── quantity (int), unit_multiplier (int), total_pieces (int)
 ├── unit_price (decimal 12,4), unit_cost (decimal 12,4 - For real-time COGS profit)
 ├── sub_total (decimal 12,2), discount (decimal 12,2), total_amount (decimal 12,2)
 └── timestamps

sale_payments (Split Payment Breakdown)
 ├── id (BIGINT PK), sale_id (FK), branch_id (FK), customer_id (FK nullable)
 ├── payment_method (CASH, CARD, BKASH, NAGAD, ROCKET, BANK_TRANSFER, DUE)
 ├── amount (decimal 12,2), transaction_reference, card_last_four
 ├── received_by (FK - User), payment_date (timestamp)
 └── timestamps

sale_returns
 ├── id (BIGINT PK), branch_id (FK), return_no (unique), sale_id (FK), customer_id (FK nullable)
 ├── total_refund_amount (decimal 12,2), refund_type (CASH, DUE_ADJUSTMENT)
 ├── reason, user_id (FK)
 └── timestamps

sale_return_items
 ├── id (BIGINT PK), sale_return_id (FK), product_id (FK), batch_id (FK)
 ├── unit_id (FK - Returned unit: Box/Strip/Piece)
 ├── quantity_returned (int), unit_multiplier (int), pieces_returned (int)
 ├── refund_unit_price (decimal 12,4), total_refund (decimal 12,2)
 ├── is_damaged (boolean - if true, logged to damage ledger instead of active batch)
 └── timestamps

purchases (Procurement & GRN)
 ├── id (BIGINT PK), branch_id (FK), purchase_no (unique indexed), supplier_id (FK)
 ├── supplier_invoice_no, invoice_date (date), delivery_date (date)
 ├── sub_total (decimal 12,2), discount (decimal 12,2), tax_amount (decimal 12,2), grand_total (decimal 12,2)
 ├── paid_amount (decimal 12,2), due_amount (decimal 12,2)
 ├── payment_status (PAID, PARTIAL, DUE), status (ORDERED, RECEIVED, CANCELLED)
 └── timestamps

purchase_items
 ├── id (BIGINT PK), purchase_id (FK), product_id (FK), batch_number, expiry_date (date)
 ├── unit_id (FK - Purchased packaging unit: Carton/Box/Strip/Piece)
 ├── quantity (int), bonus_quantity (int default 0), unit_multiplier (int), total_pieces (int)
 ├── purchase_price_per_unit (decimal 12,4), mrp_per_unit (decimal 12,4), total_cost (decimal 12,2)
 └── timestamps

purchase_returns
 ├── id (BIGINT PK), branch_id (FK), return_no (unique), purchase_id (FK), supplier_id (FK)
 ├── total_refund_amount (decimal 12,2), refund_status (PENDING, ADJUSTED, REFUNDED)
 ├── reason, user_id (FK)
 └── timestamps

purchase_return_items
 ├── id (BIGINT PK), purchase_return_id (FK), product_id (FK), batch_id (FK)
 ├── unit_id (FK - Returned unit: Box/Strip/Piece)
 ├── quantity_returned (int), unit_multiplier (int), pieces_returned (int)
 ├── return_price_per_unit (decimal 12,4), total_refund (decimal 12,2)
 └── timestamps

stock_adjustments (Damage, Expiry & Audit Reconcile)
 ├── id (BIGINT PK), branch_id (FK), adjustment_no (unique), user_id (FK)
 ├── reason (DAMAGE, EXPIRED, AUDIT_DISCREPANCY, THEFT, CORRECTION)
 ├── total_loss_value (decimal 12,2), notes
 └── timestamps

stock_adjustment_items
 ├── id (BIGINT PK), stock_adjustment_id (FK), product_id (FK), batch_id (FK)
 ├── unit_id (FK), quantity (int), unit_multiplier (int), total_pieces (int)
 ├── adjustment_type (ADDITION, DEDUCTION), unit_cost (decimal 12,4)
 └── timestamps

stock_transfers (Inter-Branch Transfer)
 ├── id (BIGINT PK), transfer_no (unique), from_branch_id (FK), to_branch_id (FK)
 ├── status (PENDING, IN_TRANSIT, RECEIVED, REJECTED), sent_by (FK), received_by (FK nullable)
 ├── dispatched_at, received_at
 └── timestamps

stock_transfer_items
 ├── id (BIGINT PK), stock_transfer_id (FK), product_id (FK), batch_id (FK)
 ├── unit_id (FK), quantity (int), unit_multiplier (int), total_pieces (int)
 └── timestamps

customer_ledgers (Double-Entry Customer Ledger)
 ├── id (BIGINT PK), branch_id (FK), customer_id (FK)
 ├── transaction_type (SALE, RETURN, DUE_COLLECTION, OPENING_BALANCE, ADJUSTMENT)
 ├── reference_id (sale_id or payment_id), debit (decimal 12,2), credit (decimal 12,2), balance (decimal 12,2)
 ├── note, transaction_date (timestamp)
 └── timestamps

supplier_ledgers (Double-Entry Supplier Ledger)
 ├── id (BIGINT PK), branch_id (FK), supplier_id (FK)
 ├── transaction_type (PURCHASE, RETURN, PAYMENT, OPENING_BALANCE, ADJUSTMENT)
 ├── reference_id (purchase_id or payment_id), debit (decimal 12,2), credit (decimal 12,2), balance (decimal 12,2)
 ├── note, transaction_date (timestamp)
 └── timestamps

cash_registers (Daily Counter Shifts & Cash Float)
 ├── id (BIGINT PK), branch_id (FK), user_id (FK)
 ├── opened_at (timestamp), closed_at (timestamp nullable)
 ├── opening_cash (decimal 12,2), closing_cash (decimal 12,2 nullable)
 ├── total_cash_sales (decimal 12,2), total_card_sales (decimal 12,2), total_mfs_sales (decimal 12,2)
 ├── total_cash_expenses (decimal 12,2), expected_closing_cash (decimal 12,2), difference (decimal 12,2)
 ├── status (OPEN, CLOSED), note
 └── timestamps

expense_categories
 ├── id (BIGINT PK), branch_id (FK nullable), name, description
 └── timestamps

expenses
 ├── id (BIGINT PK), branch_id (FK), category_id (FK), user_id (FK)
 ├── title, amount (decimal 12,2), date (date), payment_method (CASH, BANK, MFS)
 ├── receipt_image_path, note
 └── timestamps
```

---

## 6. Livewire 3/4 Component Architecture & POS Design

### 6.1 POS Reactive Terminal (`App\Livewire\Pos\Terminal`)
- **Key Capabilities:**
  - Barcode scanner listener with instant item append and auto-deduction.
  - Multi-unit dropdown in cart items (`Box` $\leftrightarrow$ `Strip` $\leftrightarrow$ `Piece`) with automatic subtotal recalculation.
  - Split Payment Drawer supporting simultaneous Cash, Card, bKash/Nagad, and Due credit.
  - Instant Generic Substitute Finder (Suggests active alternatives when a requested medicine is out of stock).
  - Draft / Suspended Cart Hold-and-Recall functionality.
  - Full Keyboard Shortcut Matrix (Alpine.js driven):
    - `F1`: Focus Barcode / Search Box
    - `F2`: Add / Search Customer
    - `F4`: Hold Active Cart
    - `F7`: Recall Suspended Cart
    - `F8`: Focus Discount Input
    - `F9` or `Ctrl + Enter`: Trigger Payment Modal & ESC/POS Thermal Print
    - `Escape`: Close Modals / Clear Cart Selection

### 6.2 Thermal Receipt Engine (`resources/views/livewire/pos/thermal-receipt.blade.php`)
- Designed for standard 58mm and 80mm thermal receipt roll printers.
- Utilizes CSS `@media print` directives to automatically hide header, sidebar, footer, and navigation.
- Prints pharmacy header, VAT Reg/BIN, Cashier name, Invoice No, Doctor info, itemized packaging breakdown, batch & expiry details, and return policy.

---

## 7. Security, Performance & Concurrency Controls

1. **ACID Transaction Locks:** Every inventory modification (Sale, Purchase, Return, Adjustment) executes inside `DB::transaction()` with `lockForUpdate()` on batch records to prevent overselling race conditions.
2. **Granular RBAC (Role-Based Access Control):**
   - `Super Admin`: Global multi-branch control & system setup.
   - `Branch Manager`: Branch financials, reports, user control.
   - `Pharmacist`: Prescription validation, POS sales, generic search.
   - `Cashier`: Counter sales, split payments, shift register.
   - `Inventory Officer`: Requisition, PO, GRN receiving, batch & expiry adjustments.
   - `Accountant`: Ledgers, due recovery, expense vouchers, P&L.
3. **Audit Log Trail:** Comprehensive tracking of invoice edits, discounts exceeding thresholds, and deleted records.

---

## 8. Directory Structure & Execution Roadmap

### 8.1 Backend Core & Services Structure
```
app/
 ├── Actions/
 │    ├── Pos/
 │    │    ├── ProcessPosCheckoutAction.php
 │    │    ├── CalculatePosItemPricingAction.php
 │    │    └── ApplyPrescriptionDiscountAction.php
 │    ├── Purchases/
 │    │    ├── ReceiveGoodsNoteAction.php
 │    │    └── CreatePurchaseReturnAction.php
 │    └── Inventory/
 │         ├── AdjustBatchStockAction.php
 │         └── TransferStockAction.php
 ├── Services/
 │    ├── BatchAllocationService.php     (FEFO engine & batch splitting)
 │    ├── UnitConversionService.php      (Carton/Box/Strip/Piece conversion math)
 │    ├── LedgerAccountingService.php    (Automated double-entry posting)
 │    └── CashRegisterService.php        (Shift opening, closing, cash reconciliation)
 ├── Livewire/
 │    ├── Pos/
 │    │    ├── Terminal.php
 │    │    ├── CustomerQuickAddModal.php
 │    │    ├── GenericSubstituteModal.php
 │    │    └── SuspendedCartDrawer.php
 │    ├── Inventory/
 │    │    ├── ProductTable.php
 │    │    ├── BatchTable.php
 │    │    ├── StockAdjustmentForm.php
 │    │    └── ExpiryAlertTable.php
 │    ├── Purchases/
 │    │    ├── PurchaseTable.php
 │    │    ├── PurchaseCreateForm.php
 │    │    └── SupplierReturnTable.php
 │    ├── Accounts/
 │    │    ├── CustomerLedgerTable.php
 │    │    ├── SupplierLedgerTable.php
 │    │    ├── ExpenseManager.php
 │    │    └── DailyCashRegisterReport.php
 │    └── Components/
 │         ├── DataTableWrapper.php
 │         ├── TabNavigator.php
 │         └── ModalDialog.php
 └── Models/
      ├── Product.php, Batch.php, Unit.php, ProductUnit.php
      ├── Sale.php, SaleItem.php, SalePayment.php, SaleReturn.php
      ├── Purchase.php, PurchaseItem.php, PurchaseReturn.php
      ├── Customer.php, Supplier.php, Doctor.php
      ├── CustomerLedger.php, SupplierLedger.php
      └── CashRegister.php, Expense.php
```

### 8.2 Execution Phases & Milestones
- **Phase 1: Foundation & Database Layer**
  - Execute clean Laravel migrations for all 22+ tables with strict foreign keys, decimals, and index definitions.
  - Setup Eloquent Models with relationships, casts, and query scopes.
- **Phase 2: Core Domain Services**
  - Implement `UnitConversionService` and `BatchAllocationService` (FEFO).
  - Implement `LedgerAccountingService` for automated double-entry ledger transactions.
- **Phase 3: Base UI & Reusable Components**
  - Build standard `DataTableWrapper`, `TabNavigator`, and `ModalDialog` in Tailwind CSS v4 & Alpine.js.
- **Phase 4: POS Terminal & Livewire Components**
  - Construct reactive `Pos\Terminal` with barcode scanner, multi-unit toggles, split payments, generic substitute lookup, suspended carts, and thermal receipts.
- **Phase 5: Inventory, Procurement & Accounts**
  - Implement GRN Purchase management, Batch Expiry alerts, Stock adjustments, Cash Register shifts, and Customer/Supplier Ledgers.
- **Phase 6: Reporting & Security Hardening**
  - Add Daily Z-Reports, Profit & Loss summaries, RBAC policies, and audit logging.
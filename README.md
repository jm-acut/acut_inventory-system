# ACUT Store - Full CRUD & JOINs Application

A complete PHP/MySQL e-commerce application demonstrating full CRUD operations and SQL JOINs for managing products, categories, customers, and orders.

## Features

### 1. Category Management (CRUD)
- ✅ **Create**: Add new product categories
- ✅ **Read**: View all categories with display on product list
- ✅ **Update**: Edit existing category names
- ✅ **Delete**: Remove categories (with cascade delete of products)

### 2. Product Management (CRUD)
- ✅ **Create**: Add new products with category selection
- ✅ **Read**: Display products with category names using LEFT JOIN
- ✅ **Update**: Edit product details including category and price
- ✅ **Delete**: Remove products from inventory

### 3. Customer Management
- ✅ **Create**: Add new customers with contact information
- ✅ **Read**: View all customers with their details

### 4. Order Management (Advanced)
- ✅ **Create**: Create orders for customers with multiple products
- ✅ **Read**: 
  - List all orders with customer names (using JOIN)
  - Display total items per order (using JOIN + COUNT)
  - View detailed order information with product names and quantities

### 5. SQL Features Implemented
- **JOINs**: LEFT JOIN (products + categories), JOIN (orders + customers + order_items + products)
- **Aggregations**: SUM() for calculating order totals
- **Foreign Keys**: With ON DELETE CASCADE for data integrity
- **Prepared Statements**: To prevent SQL injection
- **Transactions**: For order creation consistency

## Installation & Setup for XAMPP

### Prerequisites
- XAMPP installed and running (Apache + MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Step 1: Copy Files to XAMPP
1. Copy all files to: `C:\xampp\htdocs\acut_site\`
2. Folder structure should look like:
   ```
   C:\xampp\htdocs\acut_site\
   ├── index.php
   ├── database.php
   ├── style.css
   ├── manage_categories.php
   ├── add_product.php
   ├── edit_product.php
   ├── delete_product.php
   ├── customers.php
   ├── create_order.php
   ├── view_order.php
   ├── view_orders.php
   ├── setup.sql
   └── README.md
   ```

### Step 2: Create Database
1. Start XAMPP Control Panel
2. Click "Start" for Apache and MySQL
3. Open browser and go to: `http://localhost/phpmyadmin/`
4. Click "New" to create a new database
   - Database name: `acut_store`
   - Click "Create"

### Step 3: Import SQL Script
1. In phpMyAdmin, select the `acut_store` database
2. Click "Import" tab
3. Click "Choose File" and select `setup.sql`
4. Click "Go" or "Import"
5. Tables and sample data will be created automatically

### Step 4: Access the Application
1. Open browser and go to: `http://localhost/acut_site/`
2. You should see the Products page with sample data

## Database Schema

### Tables Created

#### `categories`
```sql
categories_id (INT, PRIMARY KEY, AUTO_INCREMENT)
name (VARCHAR 100, UNIQUE)
created_at (TIMESTAMP)
```

#### `products`
```sql
id (INT, PRIMARY KEY, AUTO_INCREMENT)
category_id (INT, FOREIGN KEY → categories)
name (VARCHAR 150)
price (DECIMAL 10,2)
created_at (TIMESTAMP)
```

#### `customers`
```sql
id (INT, PRIMARY KEY, AUTO_INCREMENT)
name (VARCHAR 150)
email (VARCHAR 100)
phone (VARCHAR 20)
created_at (TIMESTAMP)
```

#### `orders`
```sql
id (INT, PRIMARY KEY, AUTO_INCREMENT)
customer_id (INT, FOREIGN KEY → customers)
order_date (TIMESTAMP)
status (VARCHAR 50, DEFAULT 'Pending')
total (DECIMAL 10,2)
```

#### `order_items`
```sql
id (INT, PRIMARY KEY, AUTO_INCREMENT)
order_id (INT, FOREIGN KEY → orders)
product_id (INT, FOREIGN KEY → products)
quantity (INT)
price (DECIMAL 10,2)
```

## File Descriptions

### `database.php`
- Establishes MySQLi connection to `acut_store` database
- Sets charset to UTF-8 for proper encoding
- Includes error handling for connection failures

### `index.php` (Products List - READ)
- Displays all products with category names
- Uses LEFT JOIN to display category information
- Links to Add Product, Edit, and Delete functions
- Navigation to Categories, Customers, and Orders management

### `manage_categories.php` (Categories - CRUD)
- Add new categories (CREATE)
- List all categories (READ)
- Edit category names (UPDATE)
- Delete categories (DELETE)

### `add_product.php` (Products - CREATE)
- Form to add new products
- Dropdown to select product category
- Input validation and error handling

### `edit_product.php` (Products - UPDATE)
- Form to edit existing products
- Pre-populated with current values
- Can change category and price

### `delete_product.php` (Products - DELETE)
- Removes product from database
- Redirects to product list

### `customers.php` (Customers - CREATE & READ)
- Add new customers with name, email, phone
- Display all customers in table format

### `create_order.php` (Orders - CREATE)
- Select customer from dropdown
- Display products table with quantity inputs
- Handles multiple products in single order
- Calculates order total automatically
- Uses transactions for data consistency

### `view_orders.php` (Orders - READ List)
- Displays all orders with:
  - Order ID and Date
  - Customer name (using JOIN)
  - Total items (using SUM aggregation)
  - Total amount
- Link to view detailed order

### `view_order.php` (Order Details - READ)
- Shows complete order information
- Lists all items in order with:
  - Product name (using JOIN)
  - Quantity and unit price
  - Line total calculation
- Displays order total

### `style.css`
- Professional styling with gradient background
- Responsive design (mobile-friendly)
- Color-coded buttons and alerts
- Hover effects for better UX
- Flexbox and grid layouts

### `setup.sql`
- Complete database schema creation
- Table definitions with proper constraints
- Foreign key relationships
- Sample data for testing

## Key SQL Joins Used

### 1. Products with Categories (LEFT JOIN)
```sql
SELECT p.id, p.name, p.price, c.name AS category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.categories_id
```

### 2. Orders with Customers (LEFT JOIN)
```sql
SELECT o.id, o.order_date, c.name AS customer_name
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id
```

### 3. Order Details (Multiple JOINs with Aggregation)
```sql
SELECT o.id, c.name, COUNT(oi.id) as total_items
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id
```

## Security Features

✅ **SQL Injection Prevention**: All queries use prepared statements with bound parameters
✅ **Input Validation**: Type casting and sanitization of user inputs
✅ **HTML Escaping**: htmlspecialchars() used for output
✅ **CSRF Protection Ready**: Can be enhanced with tokens
✅ **Error Handling**: Connection and query error checking

## Usage Workflow

### Adding Products
1. Go to "Manage Categories" and add categories first
2. Click "Add Product"
3. Select category from dropdown
4. Enter product name and price
5. Click "Add Product"

### Creating Orders
1. Go to "Customers" and add customers first
2. Go to "Create Order"
3. Select customer
4. Enter quantities for desired products
5. Click "Create Order"
6. View order summary

### Editing Data
- Click "Edit" next to any category or product
- Modify information and save
- Changes reflected immediately

### Deleting Data
- Click "Delete" next to any item
- Confirm deletion in popup
- Item removed and page refreshes

## Troubleshooting

### "Connection failed" Error
- Verify MySQL is running in XAMPP Control Panel
- Check database name is `acut_store`
- Ensure database credentials in `database.php` match your setup

### "Table doesn't exist" Error
- Import `setup.sql` into phpMyAdmin again
- Verify all tables created in `acut_store` database

### PHP Errors
- Check if PHP version is 7.4+
- Verify all PHP files are in correct folder
- Check Apache error log in XAMPP

### Data Not Displaying
- Verify sample data was inserted from `setup.sql`
- Check browser console for errors (F12)
- Verify all files have correct `require 'database.php'`

## Sample Data

The setup includes sample data:
- **Categories**: Electronics, Clothing, Books
- **Products**: Laptop, Mouse, T-Shirt, Programming Guide
- **Customers**: John Doe, Jane Smith

## Future Enhancements

Possible additions:
- User authentication and login
- Order status updates (Processing, Shipped, Delivered)
- Inventory management and stock tracking
- Payment gateway integration
- Search and filtering features
- Pagination for large datasets
- Admin dashboard with charts
- Email notifications for orders

## License
Free to use and modify for educational purposes.

## Support
For issues or questions, review the code comments or check XAMPP logs in `C:\xampp\apache\logs\`

# Parliament of Ghana Library System

This repository contains the complete source code for the Parliament of Ghana's library management system, including both the public-facing website and the administrative dashboard. The system is built with PHP, MySQL, HTML, CSS, and JavaScript, providing a robust and secure platform for managing the library's resources.

## Project Structure

The project is organized into the following main directories:

- `/` (root): Contains the main entry points and public-facing files
  - `index.php`: The main homepage
  - `login.php`: Admin login page
  - `admin/`: Admin dashboard and management interfaces
  - `includes/`: PHP includes and configuration files
  - `classes/`: PHP classes for database operations and business logic
  - `assets/`: Static assets (CSS, JS, images)
  - `error/`: Custom error pages

## Features

### Public-Facing Site

- **Responsive Design**: Works on all devices
- **Dynamic Content**: Pages are generated from the database
- **Search Functionality**: Search for books and resources
- **User Authentication**: Secure login for members and staff
- **Digital Library**: Access to digital resources

### Admin Dashboard

- **User Management**: Manage library staff and permissions
- **Catalog Management**: Add, edit, and remove books and resources
- **Member Management**: Handle member registrations and accounts
- **Circulation**: Manage book checkouts and returns
- **Reports**: Generate usage and inventory reports
- **System Settings**: Configure library policies and settings

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- WampServer (for Windows) or similar stack for other OS
- Modern web browser (Chrome, Firefox, Safari, Edge)

## WampServer Setup Instructions

### 1. Install WampServer

1. Download the latest version of WampServer from [wampserver.aviatechno.net](https://wampserver.aviatechno.net/)
2. Run the installer and follow the on-screen instructions
3. During installation, you'll be prompted to install additional components (like Visual C++ Redistributable) - make sure to install them
4. Once installed, launch WampServer from the Start menu

### 2. Configure WampServer

1. Click on the WampServer icon in the system tray and ensure both Apache and MySQL services are running (green icon)
2. Click on "www directory" to open the web root directory
3. Create a new folder called `parliament-library`
4. Copy all the project files into this folder

### 3. Create the Database

1. Click on the WampServer icon and go to phpMyAdmin
2. Click on "New" in the left sidebar
3. Enter `parliament_library` as the database name and click "Create"
4. Click on "Import" in the top menu
5. Click "Choose File" and select the `database/schema.sql` file from the project
6. Click "Go" to import the database structure and sample data

### 4. Configure the Application

1. Open the file `includes/config.php` in a text editor
2. Update the database credentials if necessary (default WampServer credentials are usually root with no password):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'parliament_library');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. Update the site URL if needed:
   ```php
   define('SITE_URL', 'http://localhost/parliament-library');
   ```

### 5. Set Up Virtual Host (Optional but Recommended)

1. Click on the WampServer icon and go to Apache > httpd-vhosts.conf
2. Add the following configuration at the end of the file:
   ```apache
   <VirtualHost *:80>
       ServerName parliament.library
       DocumentRoot "c:/wamp64/www/parliament-library"
       <Directory "c:/wamp64/www/parliament-library">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
3. Save the file and restart all services in WampServer
4. Open the hosts file (C:\Windows\System32\drivers\etc\hosts) as administrator
5. Add the following line:
   ```
   127.0.0.1   parliament.library
   ```
6. Save the file

## Accessing the Application

1. Open your web browser and go to:
   - `http://localhost/parliament-library` (without virtual host)
   - `http://parliament.library` (with virtual host)

2. **Admin Access**:
   - URL: `/admin`
   - Default credentials:
     - Username: admin
     - Password: admin123 (change this after first login)

## Common Issues and Troubleshooting

### 1. PHP Extensions Not Enabled
- Open the WampServer menu > PHP > PHP Extensions
- Ensure the following extensions are checked:
  - mysqli
  - pdo_mysql
  - gd
  - mbstring
  - fileinfo
  - openssl

### 2. Permission Issues
- Right-click on the project folder > Properties > Security
- Ensure the IIS_IUSRS and IUSR users have read/write permissions
- Click "Edit" and check "Full control" for these users

### 3. Apache Won't Start
- Check if port 80 is in use by another application
- Open httpd.conf and change the Listen port to 8080
- Restart WampServer

## Security Considerations

1. **Change Default Credentials**:
   - Change the default admin password immediately after first login
   - Update database credentials in `includes/config.php`

2. **File Permissions**:
   - Set appropriate file permissions (644 for files, 755 for directories)
   - Protect sensitive files using .htaccess rules

3. **Updates**:
   - Keep PHP, MySQL, and all dependencies up to date
   - Regularly backup the database

## Contributing

1. Fork the repository
2. Create a new branch for your feature
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

### Admin Access

To access the admin dashboard:

1.  Navigate to `login.html`.
2.  Use the following mock credentials:
    -   **Email:** `admin@parliament.gh`
    -   **Password:** `admin123`

## Next Steps & Backend Integration

This project is currently a **frontend-only prototype**. All dynamic functionality, such as login authentication, form submissions, and content management, is simulated using JavaScript and `localStorage`.

To make this a fully functional web application, a backend server is required. The next phase of development would involve:

1.  **Setting up a Server Environment:** Using a stack like XAMPP, MAMP, Node.js/Express, or Django/Flask.
2.  **Database Design:** Creating a database (e.g., MySQL, PostgreSQL) to store users, books, reports, etc.
3.  **API Development:** Building RESTful API endpoints for:
    -   User authentication (login, logout).
    -   CRUD (Create, Read, Update, Delete) operations for all managed content.
4.  **Connecting the Frontend:** Modifying the JavaScript files in the `/js` directory (especially `api-service.js` and `auth.js`) to make HTTP requests to the new backend API instead of using mock data. 
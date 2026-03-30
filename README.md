# Club & Society Management Portal

## About the Project

This project is a web-based system developed to manage clubs and societies in a university. It helps in handling club creation, event management, announcements, and student participation in a simple and organized way.

The system has two main roles:

* Admin: manages clubs, events, and announcements
* Student: views clubs, joins them, and participates in activities

---

## Features

### Admin Side

* Create new clubs and societies
* Manage membership requests (approve/reject)
* Create and manage events
* Post announcements
* View registered users and reports

### Student Side

* Register and login
* View available clubs and events
* Send join requests
* View announcements and updates
* Join events

---

## Requirements

Make sure you have the following installed on your system:

* XAMPP or WAMP (for Apache and MySQL)
* PHP
* MySQL
* A web browser (Chrome recommended)

---

## How to Run the Project

1. Download or clone this repository:
   git clone https://github.com/manishimage79997-bot/club-society-management-portal.git

2. Move the project folder into the "htdocs" folder (if using XAMPP)

3. Start Apache and MySQL from XAMPP control panel

4. Open phpMyAdmin and:

   * Create a database named: club_portal
   * Import the file: club_portal.sql

5. Open the file `database_connect.php` and set your MySQL password:

   ```php
   $password = "";
   ```
6. Note:
      This project uses PHPMailer for email functionality.
   
      The PHPMailer folder is not included in this repository.
      
      To run the project, download PHPMailer from the official GitHub repository:
      https://github.com/PHPMailer/PHPMailer
      
      After downloading, place the PHPMailer folder inside the project directory.
   
8. Open your browser and run:
   http://localhost/club-society-management-portal

---

## Project Demo Video

You can watch the working demo here:
https://your-youtube-link-here

---

## Notes

* Make sure Apache and MySQL are running before opening the project
* If the database is not connected, check your username and password in `database_connect.php`

---

## Developed By

Jasmine Akhtar Hussain

Manish Kumar Gupta

---

## Future Improvements

* Better UI design
* Email notifications
* Mobile-friendly layout
* More detailed reports and analytics

# Online Agenda App

A web-based agenda application that allows users to manage their appointments, events, and schedules efficiently. Built using HTML, PHP, JavaScript, MySQL, and FullCalendar.js for event management.


## Features
- User authentication (login/register)
- Create, edit, and delete events
- Add participants to events
- Store user status (attending/not attending)
- Manage appointments using FullCalendar.js
- Secure database connection with MySQL

## Installation

1) Clone the Repository   
   git clone https://github.com/GiorgosK96/Online_Agenda_App.git  
   cd Online_Agenda_App

2) Setup Database  
Open XAMPP and start Apache & MySQL.   
Open phpMyAdmin (http://localhost/phpmyadmin).   
Create a database named agenda_app.  
Import the database files provided in the repository.

3) Configure Database Connection
Navigate to app/config/   
Rename config.example.php to config.php   
Open config.php and update it with your database credentials

## Run the Project

1) Place the project inside htdocs/ (e.g., XAMPP/xamppfiles/htdocs/AgendaApp).  
2) Start Apache and MySQL from XAMPP Control Panel.  
3) Open your browser and go to: http://localhost/AgendaApp/

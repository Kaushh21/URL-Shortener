# URL Shortener

## Overview

This URL Shortener application allows users to shorten long URLs into more manageable short links. It provides features such as checking for existing URLs, generating new short URLs, deleting short URLs, and listing all shortened URLs. The application is built using PHP and utilizes MySQL for data storage.

## Features

- **Shorten URLs**: Convert a long URL into a shorter one.
- **Check Existing URLs**: If the URL has already been shortened, the app retrieves the existing short URL.
- **Delete Shortened URLs**: Remove shortened URLs from the database.
- **List All Shortened URLs**: View all shortened URLs along with their original counterparts.

## Technologies Used

  - PHP
  - MySQL
  - Bootstrap (for frontend styling)
  - jQuery (for AJAX functionality)

## Installation

To set up this URL shortener application locally, follow these steps:

1. **Clone the repository**:

       git clone https://github.com/your-username/url-shortener.git
       cd url-shortener


2. **Set up the database**:

Create a MySQL database named php-short-url.
Create a table named tbl_url with the following SQL command:

    CREATE TABLE tbl_url (
        id INT AUTO_INCREMENT PRIMARY KEY,
        short_url_hash VARCHAR(10) NOT NULL UNIQUE,
        original_url TEXT NOT NULL
    );
              
## Configure database credentials:

  Open DataSource.php and update the HOST, USERNAME, PASSWORD, and DATABASENAME constants as per your MySQL setup.
  Set the root URL:
  
  Open config.php and update the ROOT_URL constant to reflect your server setup.
  Run the application:
  
  You can run this application on a local server using XAMPP, MAMP, or any other PHP server environment. Access it through your web browser (e.g., http://localhost/url_short/).

Usage

  - Enter the long URL you wish to shorten in the input field.
  - Click on the "Shorten the URL" button.
  - The shortened URL will be displayed along with the original URL.
  - To delete a shortened URL, click the "Delete" button next to it in the list of all shortened URLs.

Acknowledgments
  - Bootstrap for the responsive design.
  - jQuery for simplifying AJAX calls.


![Screenshot (32)](https://github.com/user-attachments/assets/4f6885a6-2eb1-42e5-bc8a-377644693c27)


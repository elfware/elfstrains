# Dr Strainlove strain database / server

## Description:
A MySQL database for storing and retrieving information about bacterial strains, with a simple web interface. The database `elfstrains` contains six tables:

| Table | Columns |
| --- | --- |
| `stock_tube` | `id`, `user_id`, `creation_date` |
| `strain_information` | `tube_id`, `genotype`, `comment`, `recipient`, `donor`, `labnotes_id`, `user_id`, `date` |
| `tube_status` | `tube_id`, `category_id`, `user_id`, `comment`, `date` |
| `tube_status_categories` | `id`, `status` |
| `genbank` | `tube_id`, `filename`, `user_id`, `date` |
| `users` | `id`, `username`, `usertype`, `password`, `signature` |

- `id` in `stock_tube` is the strain number, a running number. Each new strain gets the next free number. The other tables refer to it as `tube_id`.
- `genotype` is a string of text describing the strain’s genotype.
- `recipient` and `donor` are the parental strains used for construction of the strain.
- `comment` is a string of text where you can put whatever information you like that does not belong in the genotype. Details for how the strain was constructed, who you got the strain from, references, any important information on how to handle it etc.
- `labnotes_id` is the ID of the entry in the electronic lab notebook (ELN).
- `user_id` is the `id` of whoever saved the strain. The web interface shows the user's `signature` (i.e. initials, first name etc.)
- `date` and `creation_date` are timestamps for when the row was saved.

## New features:
- Every edit of a strain is saved as a new row in `strain_information`, so the full history of each strain is kept and can be viewed as a changelog.
- Each tube has a status (`in freezer`, `discarded` or `lost`) stored in `tube_status`, also with history.
- GenBank files can be uploaded to a strain (stored in `uploads/` and listed in `genbank`) and downloaded from the strain's info page.
- A third user type, `Admin`, who can add new users.

## Licence/Warranty/Support: 
None. You are free to modify the included files in any way you want to suit your needs. If you break it, you fix it. Backup the database regularly to avoid loosing data.

## Known bugs:
?

## Files:
- actions.php
- db-example.php
- db.php
- download.php - download of GenBank files
- edit.php - contains functions for editing strains in the database
- edit_status.php - contains functions for changing the tube status
- error.php - just a silly error message. Probably useless.
- export.php - a function for exporting search results as a .csv file
- functions.php
- guidelines.htm
- images
- index.php - generates the web page
- info.php - strain info page with GenBank file upload
- insert.php - contains the functions for adding new strains
- js
- log.php, strainlog.php, statuslog.php - changelogs of strains and tube status
- login.php - A simple user verification system. Probably not very safe.
- misc.html
- phpinfo.php
- popstrains.html
- print.css - generates a clean printout of search results
- print.php
- search.php - contains all the search functions
- search1.php
- signup.php - contains functions to add new users
- scripts/import_strains.php - command line script for loading strains from a .csv file
- scripts/example_strains.csv - example .csv file for import_strains.php
- uploads - folder with the uploaded GenBank files
- variant.css




## Requirements:
- Basic Unix/Linux (MySQL experience or a good MySQL manual will help).
- A running LAMP server (Linux/Apache/MySQL/PHP 8).
- If you want to change anything in how the web interface display the data (names of fields, prefix to the strain numbers etc.) you need to edit some of the included .php files.

## Installation:
Copy the strain server files to the server’s /var/www directory (if there already are some files there, make a new sub-directory, e.g. /var/www/strains. In this case you may need to edit some paths in some of the actual .php files). Copy db-example.php to db.php and enter the database user and password. Make the uploads directory writable by the web server. Check that it’s working by browsing to the server’s (IP-) address from a web browser on another computer. If you made a subdirectory, add “/strains” after the server’s address (this may also require you to make some adjustments in some of the .php files). You should see the strain database’s web interface.

Make the new MySQL database, the tables, the tube status categories and the first user (update the user information before running)
```
mysql -u root -p
create database elfstrains;
connect elfstrains;
create table stock_tube (`id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` tinyint NOT NULL, `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) DEFAULT CHARSET=latin1;
create table strain_information (`tube_id` int unsigned NOT NULL, `genotype` varchar(999) NOT NULL, `comment` varchar(999) NOT NULL, `recipient` int unsigned DEFAULT NULL, `donor` int unsigned DEFAULT NULL, `labnotes_id` varchar(13) CHARACTER SET latin1 DEFAULT NULL, `user_id` tinyint NOT NULL, `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
create table tube_status (`tube_id` int unsigned NOT NULL, `category_id` tinyint NOT NULL DEFAULT '1', `user_id` tinyint NOT NULL, `comment` varchar(255) DEFAULT NULL, `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) DEFAULT CHARSET=latin1;
create table tube_status_categories (`id` tinyint NOT NULL PRIMARY KEY, `status` enum('in freezer','discarded','lost') DEFAULT NULL) DEFAULT CHARSET=latin1;
create table genbank (`tube_id` int unsigned NOT NULL, `filename` varchar(100) DEFAULT NULL, `user_id` tinyint NOT NULL, `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) DEFAULT CHARSET=latin1;
create table users (`id` tinyint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY, `username` varchar(20) NOT NULL UNIQUE KEY, `usertype` enum('User','Superuser','Admin') NOT NULL, `password` varchar(32) NOT NULL, `signature` varchar(20) NOT NULL UNIQUE KEY) DEFAULT CHARSET=latin1;
INSERT INTO tube_status_categories (`id`, `status`) VALUES (1, 'in freezer'), (2, 'discarded'), (3, 'lost');
INSERT INTO users (`username`, `usertype`, `password`, `signature`) VALUES ('*******', 'Admin', MD5('*******'), '********');
```
Any other users can be added through the web interface. Change the asterisks to your actual user information. Note the `usertype` parameter. An `Admin` will have full access and can add new users, a `Superuser` can create and edit strains, while a `User` can only search for strains.

## Some useful MySQL commands 
(be careful, make a backup of the existing database first):
### Load data from a .csv file 
Use the script scripts/import_strains.php from the elfstrains directory on the server. The .csv file is separated by `;` and has a header line with the columns `number;Genotype;Donor;Recipient;Comment;.gb available;labnotes`. The strains get the next free strain numbers, `Donor` and `Recipient` refer to the numbers in the file, and the given user is saved as creator:
```
php scripts/import_strains.php strains.csv username
```
See scripts/example_strains.csv for an example file. Add `--dry-run` to check the file first: the strains are shown with their new numbers, but nothing is saved.

### Delete a strain 
Strains are not deleted. Set the tube status to `discarded` or `lost` in the web interface instead.

### Lost your login password? 
I haven’t bothered to make any function for changing the password of an existing user. Use phpMyAdmin (if it’s installed on the server) to change the password, or update it as an admin in MySQL (in this case for the user with id 22):
```
mysql -u root -p
connect elfstrains
update users set password = md5('newpasswd') where id=22;
```
## Make backups. 
I use mysqldump to drop a copy of the database…

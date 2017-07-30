# Berlinger Interview Assignment
---
by:
George Georgitsis (georgegeorgitsis@gmail.com)

## Getting Started

### Introduction
This small API web service was written for the purposes of the the Interview Assignment that was given to me on 28/07/2017 by Berlinger.

The application was built in PHP using `Codeigniter v.3.1.*` through `composer` from https://github.com/kenjis/codeigniter-composer-installer and `Restserver` through `composer` from https://github.com/chriskacerguis/codeigniter-restserver and `MySQL`

For assignment purposes, no `API KEY` is required, neither any other Authentication/Authorization service to use or install the application.

The API allows 3 different operations:
* Allow the user to POST a csv file containg images
* Allow the user to get all images
* Allow the user to get a single image using the unique identifier

## The workflow
**POST Request**
1. The user POSTs a csv file with key `images` to `DOMAIN/image/insert`.
2. The application receives and saves the posted csv file locally.
3. A new request is saved into database with UUID and status `in_progress`
4. Opens the saved csv file and loops through each row/image.
5. Sanitizes and validates rows (fields + URL syntax).
6. Checks if there are duplicates in picture title fields and replaces if so.
7. Each valid row/image from csv file, is pushed into array in order to batch insert into db.
8. Insert all images into db.

```Quick Explanation```
In order to upload big data and large csv files, the application was designed in a way that could handle them. 
Instead of validating the existence and the download of each image in real-time (2 processes that need time to be executed), the application saves in db the records with a picture_title field and a valid URL by syntax. 
Each POST request has a status=0 when insert that means `in_progress`, also each saved image has a status=0 when insert that means `in_progress` too. 
In the application(not API call) there is another functionality where, retrieves the in_progress requests, for each image of the in_progress request, validates and downloads the image from the given URL if available. 
If URL is a valid image and is downloaded locally, the application updates the image status to 2 that means `completed` and updates some fields in db. 
Otherwise the image has status 1 that means `failed`.


### Installation / Configuring
1. Download or clone repository into your webserver. The domain should point to folder `public` where `index.php` will route into `Codeigniter`
2. Create a database in your server
3. Change `base_url`, `download_images_dir` and `download_csv_dir` in `applications/config/config.php`. `download_images_dir` is the directory where the images will be downloaded and must be located under `public` folder in order be web accessible. `download_csv_dir` is where the uploaded csv files will be downloaded and must be located in `root` folder.
4. Change `hostname`, `username`, `password` and `database` in `applications/config/database.php`
5. Run migration file to create the database schema. The migration URL is `YOUR_DOMAIN/migrate`

## Using the app
The user of the service can POST a csv file in key `images`. Any other key will be ignored. 
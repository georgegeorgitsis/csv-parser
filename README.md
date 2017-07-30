# Berlinger Interview Assignment
---
by:
George Georgitsis (georgegeorgitsis@gmail.com)

## Getting Started

### Introduction
This small API web service was written for the purposes of the the Interview Assignment that was given to me on 28/07/2017 by Berlinger.

The application was built in PHP using `Codeigniter v.3.1.*` through `composer` from https://github.com/kenjis/codeigniter-composer-installer and `Restserver` through `composer` from https://github.com/chriskacerguis/codeigniter-restserver

For assignment purposes, no `API KEY` is required, neither any other Authentication/Authorization service to test or install the application.

The API allows 3 different operations:
* Allow the user to POST a csv file containg images
* Allow the user to get all images
* Allow the user to get a single image using the unique identifier


### Installation / Configuring
1. Download or clone repository into your webserver. The domain should point to folder `public` where `index.php` will route into `Codeigniter`
2. Create a database in your server
3. Change `base_url`, `download_images_dir` and `download_csv_dir` in `applications/config/config.php`. `download_images_dir` is the directory where the images will be downloaded and must be located under `public` folder in order be web accessible. `download_csv_dir` is where the uploaded csv files will be downloaded and must be located in `root` folder.
4. Change `hostname`, `username`, `password` and `database` in `applications/config/database.php`
5. Run migration file to create the database schema. The migration URL is `YOUR_DOMAIN/migrate`
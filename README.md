# Berlinger Interview Assignment
---
by:
George Georgitsis (georgegeorgitsis@gmail.com)

## Getting Started

### Introduction
This small API web service was written for the purposes of the the Interview Assignment that was given to me on 28/07/2017 by Berlinger.

The application was built in PHP using `Codeigniter v.3.1` through `composer` from https://github.com/kenjis/codeigniter-composer-installer , `Restserver` through `composer` from https://github.com/chriskacerguis/codeigniter-restserver and `MySQL`

The API supports HTTP requests with methods POST and GET. One POST request to send a csv file with images and two GET requests to retrieve all or a single image based on its UUID.

For assignment purposes, no `API KEY` is required, neither any other Authentication/Authorization service to use or install the application.

The API allows 3 different operations:
* Allow the user to POST a csv file containg images
* Allow the user to get all images
* Allow the user to get a single image using the unique identifier

---

## The workflow of POST Request
1. The user POSTs a csv file with key `images` to `DOMAIN/image/insert`.
2. The application receives and saves the posted csv file locally.
3. A new request is saved into database (table `requests`) with UUID and status `in_progress`
4. The application opens the saved csv file and loops through each row/image.
5. Sanitizes and validates rows (fields + URL syntax).
6. Checks if there are duplicates in picture title fields and replaces if so.
7. Each valid row/image from csv file with status `in_progress`, is pushed into array in order to batch insert into db.
8. Inserts all `in_progress` images into db.
9. Returns all saved `in_progress` images from database.

### Quick Explanation of POST
In order to upload big data and large csv files, the application was designed in a way that could handle them. 

Instead of validating the existence and download each image in real-time-process (2 processes that need time to be executed), the application saves in db the records with a picture_title field and a valid URL by syntax. 
Each POST request is saved in database and has a status=0 when insert that means `in_progress`. Also each saved image has a status=0 when insert that means `in_progress` too. 

In the application(not API call) there is another functionality where, it retrieves the `in_progress` requests, retrieves each `in_progress` image of the request and then validates and downloads the image from the given URL if available. 

If URL is a valid image and is downloaded locally, the application updates the image status to 2 that means `completed` and updates db with new information.
Otherwise the image has status 1 that means `failed`.

When all images are parsed, the application updates the request to status 2, that means `completed`.

In that way, the application can handle millions of records and the only limit is if the server can handle big csv files.

A daemon/worker/queue/cron can be used to run the above `Download_images` functionality. Because i have only a simple shared web hosting account, i could not test any of them. 
Instead of that, there is a URL that will perform the existence and download of each image in `DOMAIN/download_images`. Check NOTES below for more information.

Until the download_images functionality is completed, the uploaded images can still be retrieved but with status `in_progress`. 

---

## The workflow of GET ALL images request
1. The user can GET all images to `DOMAIN/image/getImages`.
2. The application retrieves all images from db and returns in `JSON` format.
3. In order to retrieve images in `XML`, a parameter `?format=XML` is required.

### Quick Explanation of GET ALL images
For assignment purposes, there is no validation of the user. The `getImages` request returns all the available images from db.

---

## The workflow of GET a single image request
1. The user can GET one image using the parameter `uuid` in the request.
2. The application retrieves the image (if available by uuid) and searches the local directory to return the local image.
3. There is a requirement that a local copy of the image is always available.
4. In order to retrieve the image in `XML`, a parameter `?format=XML` is also required.

### Quick Explanation of GET one image
After POSTing the csv file, or after performing the GET ALL images request, a uuid is returned for each image. The user must use the parameter `?uuid=THE_UUID_VALUE` to retrieve the selected image.

### Installation / Configuring
1. Download or clone repository into your webserver. The domain should point to folder `public` where `index.php` will route into `Codeigniter`
2. Create a database in your server
3. Change `base_url`, `download_images_dir` and `download_csv_dir` in `applications/config/config.php`. `download_images_dir` is the directory where the images will be downloaded and must be located under `public` folder in order be web accessible. `download_csv_dir` is where the uploaded csv files will be downloaded and must be located in `root` folder.
4. Change `hostname`, `username`, `password` and `database` in `applications/config/database.php`
5. Run migration file to create the database schema. The migration URL is `YOUR_DOMAIN/migrate`

---
#### Validation of images

As you have read in the `Quick Explanation of POST`, the validation is split into 2 operations. 
The first validations of rows are performed when reading the csv file and are:
1. Trim data fields
2. Check picture_title and picture_url are empty strings.
3. Check with filter_var() if the URL is syntax correct.

The second validations are performed when `download_images` functionality runs and are:
1. Check if the URL responses.
2. Check Content-Type and pathinfo() information of the image
3. Try to download the image locally

---
#### Sample GET response of ALL images
```php
[
    {
        "uuid": "120575716597dee6bab5f5002026717",
        "title": "Bumblebee",
        "url": "https://c3.staticflickr.com/8/7350/10643721146_1a48c13161_c.jpg",
        "description": "Nice insect",
        "status": "completed"
    },
    {
        "uuid": "646531920597dee6bab61d527522840",
        "title": "Bumblebee 2",
        "url": "https://c4.staticflickr.com/9/8754/17146884707_0795be28d4_h.jpg",
        "description": "",
        "status": "completed"
    },
]
```

#### Sample GET response of a single image
```php
[
    {
        "uuid": "120575716597dee6bab5f5002026717",
        "title": "Bumblebee",
        "url": "https://localhost/images/10643721146_1a48c13161_c.jpg",
        "description": "Nice insect",
        "status": "completed"
    },
]
```

---
#### Notes
* The API is uploaded on my shared hosting account with limited recourses.
* The API URL to test the POST functionality is http://georgitsis.eu/berlinger/public/api/image/insert . You can POST a csv file with key `images` from anywhere or visit http://georgitsis.eu/berlinger/public/test/postCSV to POST the sample `images_data.csv` of the assigment. 
* After POSTing the csv file, you have to manually visit http://localhost/berlinger/public/download_images/processRequests in order to validate and download the images. Otherwise all uploaded images will be `in_progress`.
* The API URL to GET ALL images functionality is http://georgitsis.eu/berlinger/public/api/image/getImages 
* The API URL to GET a single image funcionality is http://georgitsis.eu/berlinger/public/api/image/getImage?uuid=XXX , where the uuid parameter is dynamically used for already saved images.

---
#### Files to be reviewed 
As the API is built with `Codeigniter` + `Restserver`, both of them have just been configured to run on the server.

The files that are written by me, in order to develop this API are the bellow:
* application/controllers/api/Image.php
* application/controllers/Download_images
* application/controllers/Migrate.php
* application/libraries/Image_library.php
* application/libraries/Image_library.php
* application/migrations/001_init_image | application/migrations/002_size | application/migrations/003_api_calls

---
#### TODO
There are a lot of things that would need refactoring for considering this app a full RESTful API and a well build application. I can see the following:

* Create API KEYS for each user to use the API.
* Run the `Download_images` process as a worker or as a separated thread at least, immediately after the POST request is completed, based on server load.
* A field `is_deleted` is added in `images` table, in order to let the user delete his images in the future.
* Validations and downloads of images can be performed faster and safer using raw information of the image.
* For GET requests of the image(s), there are more fields/information to be returned. Those information can be used for other purposes or can be returned back to the user.
* A PATCH method request to update the already saved images with new fields/information.
* Constants in application to control variables that are used in controllers, models and libraries.
* A cache system for both images and sql queries.
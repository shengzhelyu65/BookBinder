# BookBinder

This is a web application designed and implemented using modern web technologies, including HTML5, CSS3, PHP (Symfony Framework), and JavaScript ES6. The goal of the project was to create a healthy and thought-through code base that includes a wide set of features. The team worked collaboratively and followed an agile process, using a GitLab Issue Board and Git log as process documentation.

## Project URL's & Website credentials
Here's the [Main login page](https://a22web31.studev.groept.be/). From there you can register a new account or login with an existing one. For a full experience we recommend using the following the registration process, but you can also use the following credentials to login with one of the existing accounts:
### Regular user A
- login : vini@gmail.com
- password : secret
### Regular user B
- login : tim@gmail.com
- password : secret

This way you can navigate through the website with two different accounts and see how the user-interaction related features work.

## Minimal Requirements & Implemented Features
- [x] - MySQL database with 11 tables

    ![Software Engineering ERD.png](./Software Engineering ERD.png)   
- [x] - Fully mapped ORM
    + Created custom Enum types for the database to hold genres and languages
- [x] - User login/authentication
    + Symfony Security bundle used for authentication
    + Google Auth0 used for login and registration
- [x] - Test code coverage >95%
    + Test code automatically integrated in GitLab CI/CD pipeline (see .gitlab-ci.yml)
- [x] - Test data with >1000 database records
    + 1000+ books automatically added to the database during testing
- [x] - Use of local/remote JSON API
    + Google Books API used to fetch book information and populate the database
    + OpenAI API used to generate book descriptions
- [x] - Automatically deployed on studev.groept.be server
    + Multiple test jobs and a deploy job (see .gitlab-ci.yml) 

### User Authentication
The user authentication is handled by the Symfony Security bundle. The user can login with an existing account or register a new one. The registration process is also avaliable for Google Auth0. The user can login with their Google account or create a new account using their email address and a password.

When users register for the first time, they are redirected to a reading interests page. There, they can select their favorite genres and languages. The genres are used to recommend books to the user in the home page.

The login and regiistration forms have browser and server side validation. The user is notified if they enter invalid data.

### Home Page

### Search Bar

### OPenAI API

### Book Page

### Add review

### Meetup Page

### MyList Page

### Profile Page

### Settings Page


## Team

- Scrum Master - Arthur Tavares
- Database Maintainers - Shengzhe Lyu, Arthur Tavares
- Deployment, CI/CD Manager - Arthur Tavares
- Test Developers - Shengzhe Lyu, Thomas
- Frontend Developer - Louise Cuypers
- Backend Developers - Maarten Medaer, Louise Cuypers, Shengzhe Lyu, Arthur Tavares

## Running the Website Locally from cmd prompt (Windows)

1. Install Scoop by following the instructions on the [Scoop website](https://scoop.sh/).

2. Once installed, use Scoop to install the Symfony CLI by running the following command in your terminal:

    ```
    scoop install symfony-cli
    ```

3. After installation, use the Symfony local server to run the website. The server can be installed by following the instructions on the [Symfony website](https://symfony.com/doc/current/setup/symfony_server.html).

4. After installation, run the following command in your terminal to start the server:
    ```
    symfony server:start
    ```

5. If you are using Docker, make sure to expose the port the web server runs the application on to access it.

## Running tests Locally from cmd prompt (Windows)
First, make sure you hava a test database set up. Use MySQL Installer to install MySQL Server and MySQL Workbench.  
Then, create a new database called "bookbinder_test". Alternatively, you can repurpose the coubooks local server database that
we used in one of the Labs.

Second, create a .env.test.local file in the root of the project. Add the following code:  
```
# define your env variables for the test env here
KERNEL_CLASS='App\Kernel'
APP_SECRET='$ecretf0rt3st'
SYMFONY_DEPRECATIONS_HELPER=999999
PANTHER_APP_ENV=panther
PANTHER_ERROR_SCREENSHOT_DIR=./var/error-screenshots

###> doctrine/doctrine-bundle ###
# Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml
DATABASE_URL="mysql://<db_user_here>:<user_password_here>@127.0.0.1:3306/<db_name_here>?serverVersion=8&charset=utf8mb4"
###< doctrine/doctrine-bundle ###

OPEN_AI_KEY='sk-<your key here>'
```
Replace the values in <> with your own values.

Then, make sure that all composer dependencies are installed by running the following command in your terminal:
```
composer update vendor/package --with-dependencies
composer install
```  
If you have no dependency issues, you can run the tests by running the following command in your terminal:
```
php bin/console make:migration 
php bin/console doctrine:migrations:migrate --env=test
php bin/console doctrine:fixtures:load --env=test
```
This will create the database tables and populate them with test data. 

If you get migration errors, try running the following command in your terminal:
```
php bin/console doctrine:schema:update --force --env=test
```

If you  get a "ER_NOT_SUPPORTED_AUTH_MODE" error, follow the instructions on this [StackOverflow post](https://stackoverflow.com/questions/44946270/er-not-supported-auth-mode-mysql-server/52726522#52726522r).

Finally, run the tests by running the following command in your terminal:
```
php bin/phpunit
```

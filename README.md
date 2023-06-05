# BookBinder

This is a web application designed and implemented using modern web technologies, including HTML5, CSS3, PHP (Symfony Framework), and JavaScript ES6 and beyond. The goal of the project is to create a healthy and thought-through code base that includes a wide set of features. The team will work collaboratively and follow an agile process, using a GitLab Issue Board and Git log as process documentation.

*The subject of the project is BookBinder, which is described in detail in the HCI course introduction. The team will put their own focus on the project and select a set of features they want to implement.*

## Minimal Requirements

- [x] - MySQL database with 5 or more tables
- [x] - Fully mapped ORM
- [x] - User login/authentication
- [ ] - Test code coverage >65%
- [ ] - Test data with >1000 database records
- [x] - Use of local/remote JSON API
- [ ] - (Automatically) deployed on studev.groept.be server


## Team

- Scrum Master - *tbd*
- Database Maintainer - *tbd*
- Deployment, CI/CD Manager - *tbd*
- Test Engineer - *tbd*

## Evaluation

The project will be evaluated based on several factors, including the development process, issue board, Git log (commit messages, feature branches), testing (coverage, mocking), code base, and web app. The team's coding style should be consistent, and the code should be refactored and compliant with standards (W3C). 

During the exam period, there will be a live demo of the web app, followed by a Q&A session with all team members. Peer assessment will also be conducted.

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

### Possible issues when accessing the website admin panel locally
```
The name of the route associated to "App\Controller\Admin\DashboardController::index" cannot be determined. Clear
the application cache to run the EasyAdmin cache warmer, which generates the needed data to find this route
```
#### Solution
Clear the cache by running the following command in your terminal:  
```
php bin/console cache:clear
```  

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

If you  get a "ER_NOT_SUPPORTED_AUTH_MODE" error, follow the instructions on this [StackOverflow post](https://stackoverflow.com/questions/44946270/er-not-supported-auth-mode-mysql-server/52726522#52726522r).

Finally, run the tests by running the following command in your terminal:
```
php bin/phpunit
```
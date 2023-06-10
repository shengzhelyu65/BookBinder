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

---

## Minimal Requirements & Implemented Features
- [x] - MySQL database with 5 or more tables
    (add pictures of the database schema here)    
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

The login and registration forms have browser and server side validation. The user is notified if they enter invalid data.

### Home Page

The home page shows book recommendation for the users preffered genres and recent reviews from other users.

### Search Bar
The search-bar contains two event listeners.

1. Submit event

The submit event is used for the search itself. It checks the contents of the input
and redirects the user to ```/book-search/{query}``` where the controller uses the google-books API to list a number
of books based on the query. Each book on the search-page has a href to its book-page

2. Keyup

We utilize our cached books table to provide autosuggestions as the user is typing in the searchbar.

### OpenAI API
Another feature on the website is an AI book recommendation system.
It utilizes OpenAI's GPT-3.5-turbo Large Language Model to recommend books based on a prompt.

A prompt could be "Something about magic and wizards", after pressing the generate button
Javascript does a fetch request on one of our endpoints that uses the OpenAI API to get a response.
This response is the title of the book it recommends. After some prompt engineering this is working fairly well.
After acquiring the book title it does another fetch request to an endpoint that uses the google-books API
and returns a google-books ID and a link to the books thumbnail.

The javascript then appends this title and thumbnail to the recommendation panel with a href on the thumbnail
that leads to the book-page.


### Book Page
Upon accessing the book-page first a request will be made to the database to check if we have
a cached version of the book based on the ID and display that. If there is no entry for it a google-books API request is made
to retrieve the information and is added to the cache table in the database.

### Add review
Users can add one review to any books they want. Once the review is added they can edit and update their existing review.

### Meetup Page 
Meetups can be created by going to a book page and clicking the "host meetup" button after filling in the form you become the host of the meetup. Other user can now request to join that meetup. As host of a meetup you can accept or deny other user that requested to join your meetup.

The meetup Page displays 3 rows for meetup information and actions. The first row displays the meetups you host or have joined.
In the second row you can accept or reject requests to join your meetups.
Then the third row shows meetups you haven't joined yet.
### MyList Page
On the book page there is a dropdown list where you can select either "Want to read", "Currently reading" or "have read". These are mutally exclusive so only one can chosen but the tag can swiched with the same dropdown.

The "My list" page shows the books for which you have selected a tag in their respective row.
### Profile Page
The Profile page shows your username, full name, your language and genre preferences, your reading List and your reviews. 
Clicking on another user's username, which are on the reviews brings you to their profile page containing the same information except for their full name. 
### Settings Page
The settings page has a form with the profile settings filled in. The settings are Nickname, Name, Surname, Languages, Genres. These can be changed and them confirmed by pressing the "submit" button.

## Team

- Scrum Master - Arthur Tavares
- Database Maintainers - Shengzhe Lyu, Arthur Tavares
- Deployment, CI/CD Manager - Arthur Tavares
- Test Developers - Shengzhe Lyu, Thomas Goris
- Frontend Developer - Louise Cuypers, Thomas Goris
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

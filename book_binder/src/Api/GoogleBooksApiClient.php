<?php

namespace App;
use App\Entity\Book;
use GuzzleHttp\Client;
require_once __DIR__ . '/../Entity/Book.php';

/**
 * Class used for interacting with the google books API
 */
class GoogleBooksApiClient
{
    private string $baseUri = 'https://www.googleapis.com/books/v1/volumes';

    // TODO: REMOVE THIS KEY TO A SAFER PLACE LATER?
    private string $API_KEY = "AIzaSyDqRjtenxk_Dp8BJv6-Sqo0VvaL2y-6K2g";

    /**
     * Search for books based on query text, max amount, and start index.
     *
     * @param string $title The query text to search for.
     * @param int $maxResults  The maximum number of results to return. Default is 10.
     * @param int $startIndex The index of the first result to return. Default is 0.
     * @return array|false An array of Books, or false if the search failed.
     */
    public function searchBooks(string $title = "", int $maxResults = 10, int $startIndex = 0): bool|array {
        // Check if $title is empty
        if (empty($title)) {
            echo "No title provided";
            return false;
        }

        // Set query parameters for Google Books API
        $queryParams = [
            'q' => 'intitle:' . urlencode($title),
            'startIndex' => $startIndex,
            'maxResults' => $maxResults,
            'key' => $this->API_KEY
        ];

        // Create a new HTTP client instance
        $client = new Client([
            'verify' => false
        ]);

        // Send a GET request to the Google Books API
        $response = $client->request('GET', $this->baseUri, ['query' => $queryParams]);

        // Check if the response status code is 200 (OK)
        if ($response->getStatusCode() === 200) {
            // Parse the JSON response into an associative array
            $json = json_decode($response->getBody(), true);
            $books = [];

            // Loop through the array of book items returned by the API
            foreach ($json['items'] as $book) {
                // Create and add Book object to the array of books
                $books[] = $this->jsonToBook($book);
            }

            // Return the array of books
            return $books;
        } else {
            // Return false if the response status code is not 200
            return false;
        }
    }

    /**
     * Private function that converts a JSON object to a Book object.
     *
     * @param mixed $json The JSON object to convert.
     * @return Book The new Book object.
     */
    private function jsonToBook(mixed $json): Book {
        return new Book(
            $json['id'] ?? -1,
            $json['volumeInfo']['title'] ?? "",
            $json['volumeInfo']['authors'][0] ?? "",
            $json['volumeInfo']['description'] ?? "",
            $json['volumeInfo']['imageLinks']['thumbnail'] ?? "",
            $json['volumeInfo']['publishedDate'] ?? null,
            $json['volumeInfo']['industryIdentifiers'] ?? [],
            $json['volumeInfo']['pageCount'] ?? -1,
            $json['volumeInfo']['printType'] ?? null,
            $json['volumeInfo']['averageRating'] ?? -1,
            $json['volumeInfo']['ratingsCount'] ?? -1,
            $json['volumeInfo']['maturityRating'] ?? null,
            $json['volumeInfo']['allowAnonLogging'] ?? null,
            $json['volumeInfo']['contentVersion'] ?? null,
            $json['volumeInfo']['language'] ?? null,
            $json['volumeInfo']['previewLink'] ?? null,
            $json['volumeInfo']['infoLink'] ?? null
        );
    }

    /**
     * Search for popular or new books based on subject, max amount, and start index.
     *
     * @param string $subject The subject to search for.
     * @param int $maxResults  The maximum number of results to return. Default is 10.
     * @param int $startIndex The index of the first result to return. Default is 0.
     * @param boolean $orderBy Sorts the results by relevance if set to true. Otherwise sorts by newest.
     * @return array|false An array of Books, or false if the search failed.
     */
    public function getInterestingBooks(string $subject, int $maxResults = 1, int $startIndex = 0, bool $orderBy = false): bool|array {

        // Check if $subject is empty
        if (empty($subject)) {
            echo "No subject provided";
            return false;
        }

        // Set query parameters for Google Books API
        $queryParams = [
            'q' => 'subject:' . urlencode($subject),
            'startIndex' => $startIndex,
            'maxResults' => $maxResults,
            'orderBy' => $orderBy ? 'relevance' : 'newest',
            'key' => $this->API_KEY
        ];

        // Create a new HTTP client instance
        $client = new Client([
            'verify' => false
        ]);

        // Send a GET request to the Google Books API
        $response = $client->request('GET', $this->baseUri, ['query' => $queryParams]);

        // Check if the response status code is 200 (OK)
        if ($response->getStatusCode() === 200) {
            // Parse the JSON response into an associative array
            $json = json_decode($response->getBody(), true);
            $books = [];

            // Loop through the array of book items returned by the API
            foreach ($json['items'] as $book) {
                // Create and add Book object to the array of books
                $books[] = $this->jsonToBook($book);
            }

            // Return the array of books
            return $books;
        } else {
            // Return false if the response status code is not 200
            return false;
        }
    }


}
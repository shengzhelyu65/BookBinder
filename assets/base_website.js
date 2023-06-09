import $ from 'jquery';

// Searchbar
const form = document.getElementById('searchbar_form');
form.addEventListener('submit', searchBooks);

function searchBooks(event) {
    event.preventDefault(); // prevent form submission
    const query = event.target.querySelector('#search_bar_input').value;
    const url = `/book-search/${encodeURIComponent(query)}`;
    console.log("Searching for: " + query);
    window.location.href = url;
}

const searchInput = document.getElementById('search_bar_input');
searchInput.addEventListener('keyup', getSuggestion);

async function getSuggestion(event) {
    const input = event.target.value;
    const url = `/book-suggestion/${encodeURIComponent(input)}`;
    const response = await fetch(url);
    const suggestions = await response.json();
    const datalist = document.getElementById('book-list');
    datalist.innerHTML = ''; // clear previous suggestions
    suggestions.forEach(suggestion => {
        const option = document.createElement('option');
        option.value = suggestion.title;
        datalist.appendChild(option);
    });
}

// AI Recommendations
document.querySelector('#chatForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const inputText = document.querySelector('#inputText').value;

    // Disable the button and change its text
    const generateButton = document.querySelector('button[type="submit"]');
    generateButton.disabled = true;
    generateButton.textContent = 'Generating...';

    try {
        const response = await fetch(`/book-search/openAI/${encodeURIComponent(inputText)}`);
        console.log(response);

        if (response.ok) {
            const data = await response.json();
            const generatedText = data.text;

            const APIResponse = await fetch(`/book-search/ai/${encodeURIComponent(generatedText)}`);

            if (APIResponse.ok) {
                const data = await APIResponse.json();

                // Check if thumbnail and id exist
                if (data.thumbnail && data.id) {
                    // Display the response text (should be the title of the book)
                    const recommendationDiv = document.querySelector('#recommendations');
                    const responseDiv = document.createElement('div');
                    responseDiv.textContent = generatedText;
                    recommendationDiv.appendChild(responseDiv);

                    const thumbnail = data.thumbnail;
                    const id = data.id;

                    const thumbnailLink = document.createElement('a');
                    thumbnailLink.href = `/book-page/${id}`;
                    const thumbnailImg = document.createElement('img');
                    thumbnailImg.src = thumbnail;
                    thumbnailLink.appendChild(thumbnailImg);
                    recommendationDiv.appendChild(thumbnailLink);
                } else {
                    console.log('No thumbnail or id found.');
                }
            }
            else {
                console.log('Something went wrong with the API call.');
            }

        } else {
            console.log('Something went wrong with the API call.');
        }
    } catch (error) {
        console.log('An error occurred:', error.message);
    }

    // Enable the button and change its text back to "Generate"
    generateButton.disabled = false;
    generateButton.textContent = 'Generate';
});

$(function handleReviewsForm() {
    'use strict';

    var form = document.querySelector('.review-validation');

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);
});
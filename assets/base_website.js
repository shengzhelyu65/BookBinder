
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

// AI recommendation
async function chatWithGPT35Turbo(prompt) {
    const apiKey = "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"
    const apiUrl = 'https://api.openai.com/v1/chat/completions';

    const requestBody = {
        model: 'gpt-3.5-turbo',
        messages: [
            { role: 'system', content: 'You are a book assistant, recommend a single book to the user based on their input.' },
            { role: 'user', content: "Only respond with the title of the book, no author, no punctuation, recommend me a book about: " + prompt },
        ],
    };

    const response = await fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${apiKey}`,
        },
        body: JSON.stringify(requestBody),
    });

    const responseData = await response.json();
    return responseData.choices[0].message.content;
}

document.querySelector('#chatForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const inputText = document.querySelector('#inputText').value;

    // Disable the button and change its text
    const generateButton = document.querySelector('button[type="submit"]');
    generateButton.disabled = true;
    generateButton.textContent = 'Generating...';

    try {
        const response = await chatWithGPT35Turbo(inputText);
        console.log(response);

        // Add thumbnail with link to bookPage
        const APIResponse = await fetch(`/book-search/ai/${encodeURIComponent(response)}`);
        if (APIResponse.ok) {
            const data = await APIResponse.json();

            // Check if thumbnail and id exist
            if (data.thumbnail && data.id) {
                // Display the response text (should be the title of the book)
                const recommendationDiv = document.querySelector('#recommendations');
                const responseDiv = document.createElement('div');
                responseDiv.textContent = response;
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

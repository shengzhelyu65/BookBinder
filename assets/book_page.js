import $ from 'jquery';

const buttonToRead = document.getElementById('button-to-read');
buttonToRead.addEventListener('click', handleSelection);
const buttonCurrentlyReading = document.getElementById('button-currently-reading');
buttonCurrentlyReading.addEventListener('click', handleSelection);
const buttonHaveRead = document.getElementById('button-have-read');
buttonHaveRead.addEventListener('click', handleSelection);

function handleSelection(event) {
    var dropdownButton = document.getElementById('dropdownMenuButton1');
    var bookId = dropdownButton.dataset.bookId;
    var url = dropdownButton.dataset.url;
    var selection = event.target.dataset.selection
    dropdownButton.innerHTML = selection;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Handle the response here if needed
            console.log(xhr.responseText);
        }
    };
    xhr.send("selection=" + encodeURIComponent(selection) + "&book_id=" + encodeURIComponent(bookId));

    document.getElementById('dropdown-lists').querySelectorAll('button').forEach(function (btn) {
        btn.disabled = false;
    });
    event.target.disabled = true;
}

// Toggle between content and details
const buttonContent = document.getElementById('button-content');
buttonContent.addEventListener('click', showContent);
const buttonDetails = document.getElementById('button-details');
buttonDetails.addEventListener('click', showContent);

function showContent(event) {
    var contentElements = document.querySelectorAll('.card.card-body div');
    var buttonElements = document.querySelectorAll('button');
    var contentId = event.target.dataset.content;

    // Hide all content elements and remove active class from buttons
    contentElements.forEach(function (content) {
        content.classList.add('d-none');
        content.classList.remove('d-block');
    });
    buttonElements.forEach(function (btn) {
        btn.classList.remove('active');
    });

    // Show the clicked content and add active class to the corresponding button
    var contentElement = document.getElementById(contentId);
    if (contentElement) {
        contentElement.classList.remove('d-none');
        contentElement.classList.add('d-block');
        event.target.classList.add('active');
    }
}

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

$(function handleMeetupForm() {
    'use strict'

    // Fetch the form to apply custom Bootstrap validation styles to
    var form = document.querySelector('.meetup-validation');

    // Prevent submission if the form is not valid
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);

    // Validate datetime before submission
    form.addEventListener('submit', function (event) {
        var datetimeInput = document.getElementById("meetup_request_form_datetime");
        var datetimeValue = new Date(datetimeInput.value);

        if (datetimeValue < new Date()) {
            event.preventDefault();
            event.stopPropagation();

            datetimeInput.setCustomValidity("Invalid datetime.");
            datetimeInput.classList.add('is-invalid');
            datetimeInput.classList.remove('is-valid');
        } else {
            datetimeInput.setCustomValidity("");
            datetimeInput.classList.remove('is-invalid');
            datetimeInput.classList.add('is-valid');
        }
    }, false);
})();

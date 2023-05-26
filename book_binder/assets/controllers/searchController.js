export function searchBooks(event) {
    event.preventDefault(); // prevent form submission
    const query = event.target.querySelector('input').value;
    const url = `/bookSearch/${encodeURIComponent(query)}`;
    console.log("Searching for: " + query);
    window.location.href = url;
}
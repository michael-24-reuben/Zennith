function insertBlurBackground(glow_card_id) {
    // Get the movie card by its ID
    const movieCard = document.getElementById(glow_card_id);

    if (movieCard) {
        // Query for the movie-thumbnail class inside the movie card
        const movieThumbnail = movieCard.querySelector(".glow-target");

        if (movieThumbnail) {
            // Retrieve the src value of the movie-thumbnail
            const srcValue = movieThumbnail.getAttribute("src");

            // Build a new img element
            const newImageElement = document.createElement("img");
            newImageElement.setAttribute("src", srcValue);
            newImageElement.setAttribute("alt", "Blurred Background");
            newImageElement.classList.add("blurred-thumbnail");

            // Append-first the comment node to the movie card
            movieCard.insertBefore(newImageElement, movieCard.childNodes[1]);
        }
    } else {
        console.log("Movie card not found.");
    }
}

function updateFavorites(card_id) {
    console.log(card_id);
    // Get the movie card by its ID
    const movieCard = document.getElementById(card_id);
    if (!movieCard) {
        console.log("Movie card not found.");
        return;
    }

    // pass card ID to php

    // Simulate updating the favorites status
    const favIcon = movieCard.querySelector(".fav");
    if (favIcon.src.includes("-marked")) {
        console.log("Movie removed from favorites.");
        favIcon.src = "assets/images/icons/fav-unmarked.png";
        toggleFavorite(card_id);

    } else {
        console.log("Movie added to favorites.");
        favIcon.src = "../../assets/images/icons/fav-marked.png";
        toggleFavorite(card_id);
    }
    console.log("Favorites status updated.");
}

function toggleFavorite(card_id, toggleBool) {
    const showId = extractShowId(card_id);
    fetch("api/movies/update_fav.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `show_id=${showId}&toggle_bool=${toggleBool}`
    })
        .then(response => response.text())
        .then(data => console.log(data))
        .catch(error => console.error('Error:', error));
}

function extractShowId(str) {
    // Split the string by hyphens and return the part after the second hyphen
    const parts = str.split('-');
    if (parts.length >= 3) {
        return parts.slice(2).join('-');  // Join the parts after the second hyphen
    } else {
        return '';  // Return an empty string if there are less than 3 parts
    }
}

// Trigger the function for the specific div after the page loads
document.addEventListener("DOMContentLoaded", function () {
    const glowCards = document.querySelectorAll(".glow-card");

    glowCards.forEach((card) => {
        const id = card.id; // Get the ID of each glow card
        console.log("id: " + id);
        insertBlurBackground(id);
    });
});

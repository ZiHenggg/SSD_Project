document.addEventListener("DOMContentLoaded", function () {
    const dropdown = document.getElementById("filterType");
    const average = document.getElementById("average");
    const received = document.getElementById("receivedReviews");
    const my = document.getElementById("myReviews");

    dropdown.addEventListener("change", function () {
        if (this.value === "reviewer") {
            received.classList.add("d-none");
            average.classList.add("d-none");
            my.classList.remove("d-none");
        } else {
            my.classList.add("d-none");
            received.classList.remove("d-none");
            average.classList.remove("d-none");
        }
    });
});

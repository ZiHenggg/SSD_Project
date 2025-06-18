var group_items = document.getElementsByClassName("group-item");
var info_items = document.getElementsByClassName("info-item");

var group_items_array = Array.from(group_items);

const firstGroupItem = document.querySelector(".home .group-item");
if (firstGroupItem) {
  firstGroupItem.classList.add("selected");
}
const firstInfoItem = document.querySelector(".home .info-item");
if (firstInfoItem) {
  firstInfoItem.classList.add("showing");
}

for (var i = 0; i < group_items.length; i++) {
  group_items[i].addEventListener("click", function (e) {
    var clickedItem = e.currentTarget;
    var index = group_items_array.indexOf(clickedItem);

    // Remove existing active states
    for (var j = 0; j < group_items.length; j++) {
      group_items[j].classList.remove("selected");
      info_items[j].classList.remove("showing");
    }

    // Apply new active state
    clickedItem.classList.add("selected");
    info_items[index].classList.add("showing");
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".delete-section"); // Get all delete buttons
  const grayScreen = document.querySelector(".grayscreen"); // Get the gray screen element

  // Loop through each delete button to attach event listeners
  deleteButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const groupId = button.getAttribute("data-group-id");
      grayScreen.style.display = "block";

      if (groupId) {
        const deleteModal = document.querySelector(
          `.delete-modal[data-group-id="${groupId}"]`
        ); // Find the corresponding modal
        if (deleteModal) {
          deleteModal.style.display = "block";
        }
      }
    });
  });

  // Handle cancel button click for each modal
  const cancelButtons = document.querySelectorAll(".cancel-section");
  cancelButtons.forEach(function (cancelButton) {
    cancelButton.addEventListener("click", function () {
      const groupId = cancelButton
        .closest(".delete-modal")
        .getAttribute("data-group-id");
      const deleteModal = document.querySelector(
        `.delete-modal[data-group-id="${groupId}"]`
      );
      if (deleteModal) {
        grayScreen.style.display = "none";
        deleteModal.style.display = "none";
      }
    });
  });

  // Handle confirm button click for each modal
  const confirmButtons = document.querySelectorAll(".delete-form");
  confirmButtons.forEach(function (confirmButton) {
    confirmButton.addEventListener("submit", function (event) {
    //   event.preventDefault();

      const groupId = confirmButton
        .closest(".delete-modal")
        .getAttribute("data-group-id"); // Get the group ID
      //   const groupName = confirmButton
      //     .closest(".delete-modal")
      //     .querySelector(".delete-group-title strong")
      //     .innerText.replace(/Delete “|”\?/g, "");

      alert("Group " + groupId + " Deleted");
      //   alert("Deleting group: " + groupName);

      const deleteModal = document.querySelector(
        `.delete-modal[data-group-id="${groupId}"]`
      );

      if (deleteModal) {
        grayScreen.style.display = "none";
        deleteModal.style.display = "none";
      }
    });
  });
});

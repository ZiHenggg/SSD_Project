var group_items = document.getElementsByClassName("group-item");
var info_items = document.getElementsByClassName("info-item");

var group_items_array = Array.from(group_items);

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

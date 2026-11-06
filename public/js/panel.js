// panel.js - admin and operator portals (plain JavaScript)

// ------------------------------------------------------------
// 1. Search box + status filter for tables
// ------------------------------------------------------------
var searchInput = document.getElementById("tableSearch");
var statusSelect = document.getElementById("tableStatus");
var tableRows = document.querySelectorAll("#filterTable tbody tr");

function filterTable() {
    var text = searchInput ? searchInput.value.toLowerCase() : "";
    var status = statusSelect ? statusSelect.value : "";

    tableRows.forEach(function (row) {
        var textMatch = row.textContent.toLowerCase().indexOf(text) !== -1;

        var badge = row.querySelector(".badge");
        var statusMatch =
            status === "" ||
            (badge !== null && badge.textContent.trim() === status);

        row.classList.toggle("hidden", !(textMatch && statusMatch));
    });
}

if (searchInput) {
    searchInput.addEventListener("input", filterTable);
}
if (statusSelect) {
    statusSelect.addEventListener("change", filterTable);
}

// ------------------------------------------------------------
// 2. Notifications: "Mark all as read"
// ------------------------------------------------------------
var markReadButton = document.getElementById("markAllRead");

if (markReadButton) {
    markReadButton.addEventListener("click", function () {
        document
            .querySelectorAll("#filterTable .badge")
            .forEach(function (badge) {
                if (badge.textContent.trim() === "Unread") {
                    badge.textContent = "Read";
                    badge.className = "badge grey";
                }
            });
    });
}

// ------------------------------------------------------------
// 3. Review operator: "Approve" works only when all 3 checks are ticked
// ------------------------------------------------------------
var checkBoxes = document.querySelectorAll('input[name="checks[]"]');
var approveButton = document.getElementById("approveBtn");

function updateApproveButton() {
    var allChecked = true;
    checkBoxes.forEach(function (box) {
        if (!box.checked) {
            allChecked = false;
        }
    });
    approveButton.disabled = !allChecked;
}

if (approveButton && checkBoxes.length > 0) {
    checkBoxes.forEach(function (box) {
        box.addEventListener("change", updateApproveButton);
    });
    updateApproveButton();
}

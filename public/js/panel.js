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
    if (searchInput.value.trim() !== "") {
        filterTable();
    }
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
        var unreadStat = document.getElementById("unreadStatCount");
        if (unreadStat) {
            unreadStat.textContent = "0";
        }
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

// ------------------------------------------------------------
// 4. Toggle collapsible form boxes (Schedule crossing, Add passenger, Log cargo)
// ------------------------------------------------------------
document.querySelectorAll("[data-toggle-box]").forEach(function (btn) {
    btn.addEventListener("click", function () {
        var box = document.getElementById(btn.dataset.toggleBox);
        if (box) {
            box.classList.toggle("hidden");
            if (!box.classList.contains("hidden")) {
                var firstInput = box.querySelector("input:not([type=hidden]), select");
                if (firstInput) {
                    firstInput.focus();
                }
            }
        }
    });
});

// ------------------------------------------------------------
// 5. QR Ticket preview modal for Manifest, Check-in, and Cargo
// ------------------------------------------------------------
function drawPanelQr(box, codeText) {
    var size = 25;
    var code = codeText || "0000-00001";
    var seed = 1;
    for (var i = 0; i < code.length; i++) {
        seed = (seed * 31 + code.charCodeAt(i)) % 2147483647;
    }
    function nextRandom() {
        seed = (seed * 48271) % 2147483647;
        return seed;
    }
    function finderCell(x, y) {
        var corners = [
            [0, 0],
            [size - 7, 0],
            [0, size - 7],
        ];
        for (var c = 0; c < corners.length; c++) {
            var dx = x - corners[c][0];
            var dy = y - corners[c][1];
            if (dx >= -1 && dx <= 7 && dy >= -1 && dy <= 7) {
                if (dx < 0 || dx > 6 || dy < 0 || dy > 6) {
                    return "white";
                }
                var ring = dx === 0 || dx === 6 || dy === 0 || dy === 6;
                var center = dx >= 2 && dx <= 4 && dy >= 2 && dy <= 4;
                return ring || center ? "black" : "white";
            }
        }
        return null;
    }

    var squares = "";
    for (var y = 0; y < size; y++) {
        for (var x = 0; x < size; x++) {
            var finder = finderCell(x, y);
            var black =
                finder === null ? nextRandom() % 2 === 0 : finder === "black";
            if (black) {
                squares +=
                    '<rect x="' + x + '" y="' + y + '" width="1" height="1"/>';
            }
        }
    }

    box.innerHTML =
        '<svg viewBox="0 0 ' +
        size +
        " " +
        size +
        '" shape-rendering="crispEdges" fill="#000">' +
        squares +
        "</svg>";
}

function escapeHtml(text) {
    var div = document.createElement("div");
    div.textContent = text || "";
    return div.innerHTML;
}

var ticketModal = document.getElementById("panelTicketModal");

document.querySelectorAll("[data-preview-ticket]").forEach(function (button) {
    button.addEventListener("click", function () {
        if (!ticketModal) return;

        var type = button.dataset.previewTicket;
        var titleEl = document.getElementById("ptTitle");
        var bodyEl = document.getElementById("ptBody");
        var totalWrap = document.getElementById("ptTotalWrap");
        var totalEl = document.getElementById("ptTotal");
        var noteEl = document.getElementById("ptNote");
        var qrEl = document.getElementById("ptQr");

        var ref = button.dataset.ref || "0000-00001";

        if (type === "cargo") {
            titleEl.textContent = "Cargo Delivery Ticket";
            bodyEl.innerHTML =
                "<dt>Cargo Tracking ID:</dt><dd>" + escapeHtml(ref) + "</dd>" +
                "<dt>Sender name:</dt><dd>" + escapeHtml(button.dataset.sender) + "</dd>" +
                "<dt>Sender contact number:</dt><dd>" + escapeHtml(button.dataset.senderContact) + "</dd>" +
                "<dt>Receiver name:</dt><dd>" + escapeHtml(button.dataset.receiver) + "</dd>" +
                "<dt>Receiver contact number:</dt><dd>" + escapeHtml(button.dataset.receiverContact) + "</dd>" +
                "<dt>Cargo name:</dt><dd>" + escapeHtml(button.dataset.items) + "</dd>" +
                "<dt>Cargo quantity:</dt><dd>" + escapeHtml(button.dataset.qty) + "</dd>" +
                "<dt>Lantsa name:</dt><dd>" + escapeHtml(button.dataset.lantsa) + "</dd>";
            totalWrap.classList.add("hidden");
            noteEl.textContent = "Please take a screenshot on this ticket for verification before delivery.";
        } else {
            titleEl.textContent = "Passenger Reservation Ticket";
            bodyEl.innerHTML =
                "<dt>Reservation reference code:</dt><dd>" + escapeHtml(ref) + "</dd>" +
                "<dt>Passenger full name:</dt><dd>" + escapeHtml(button.dataset.name) + "</dd>" +
                "<dt>Number of passengers:</dt><dd>" + escapeHtml(button.dataset.passengers) + "</dd>" +
                "<dt>Cargo quantity:</dt><dd>" + escapeHtml(button.dataset.cargo || "0") + "</dd>" +
                "<dt>Route and departure date/time:</dt><dd>" + escapeHtml(button.dataset.route) + "</dd>" +
                "<dt>Lantsa name:</dt><dd>" + escapeHtml(button.dataset.lantsa) + "</dd>" +
                "<dt>Contact Number:</dt><dd>" + escapeHtml(button.dataset.contact) + "</dd>";
            totalWrap.classList.remove("hidden");
            totalEl.textContent = button.dataset.total || "150";
            noteEl.textContent = "Please take a screenshot on this ticket for verification before boarding";
        }

        drawPanelQr(qrEl, ref);
        ticketModal.classList.add("open");
    });
});

document.querySelectorAll(".modal").forEach(function (modal) {
    modal.querySelectorAll("[data-close-modal]").forEach(function (closeBtn) {
        closeBtn.addEventListener("click", function () {
            modal.classList.remove("open");
        });
    });
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.classList.remove("open");
        }
    });
});

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".modal.open").forEach(function (modal) {
            modal.classList.remove("open");
        });
    }
});

// ------------------------------------------------------------
// 6. Document preview in Operator Approval Review
// ------------------------------------------------------------
document.querySelectorAll("[data-doc-preview]").forEach(function (link) {
    link.addEventListener("click", function (event) {
        event.preventDefault();
        alert("Verified Document Preview:\n" + link.dataset.docPreview + "\n\nStatus: Verified by SURSEA Maritime Compliance");
    });
});

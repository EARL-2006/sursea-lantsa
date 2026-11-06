// passenger.js - passenger pages, login and register (plain JavaScript)

// ------------------------------------------------------------
// 1. Ticket pop-up: open / close
// ------------------------------------------------------------
document.querySelectorAll("[data-open-modal]").forEach(function (button) {
    button.addEventListener("click", function () {
        document.getElementById(button.dataset.openModal).classList.add("open");
    });
});

document.querySelectorAll(".modal").forEach(function (modal) {
    // X button
    modal
        .querySelectorAll("[data-close-modal]")
        .forEach(function (closeButton) {
            closeButton.addEventListener("click", function () {
                modal.classList.remove("open");
            });
        });
    // click on the dark background
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.classList.remove("open");
        }
    });
});

// Escape key
document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".modal.open").forEach(function (modal) {
            modal.classList.remove("open");
        });
    }
});

// ------------------------------------------------------------
// 2. Decorative QR picture (drawn from the reference code)
//    NOTE: this is only a picture. A real QR code can be added later.
// ------------------------------------------------------------
function drawQr(box) {
    var size = 25;
    var code = box.dataset.code || "0";

    // turn the code text into a number, so each ticket looks different
    var seed = 1;
    for (var i = 0; i < code.length; i++) {
        seed = (seed * 31 + code.charCodeAt(i)) % 2147483647;
    }
    function nextRandom() {
        seed = (seed * 48271) % 2147483647;
        return seed;
    }

    // is this cell part of one of the 3 big corner squares?
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

document.querySelectorAll(".qr").forEach(drawQr);

// ------------------------------------------------------------
// 3. Cargo lines: add / remove
// ------------------------------------------------------------
var cargoRows = document.getElementById("cargoRows");
var cargoTemplate = document.getElementById("cargoTemplate");
var addCargoButton = document.getElementById("addCargo");

function updateTrashButtons() {
    // when there is only one line, hide its trash button
    cargoRows.classList.toggle("single", cargoRows.children.length === 1);
}

if (cargoRows && cargoTemplate && addCargoButton) {
    addCargoButton.addEventListener("click", function () {
        cargoRows.appendChild(cargoTemplate.content.cloneNode(true));
        updateTrashButtons();
    });

    cargoRows.addEventListener("click", function (event) {
        var trash = event.target.closest("[data-remove-row]");
        if (trash && cargoRows.children.length > 1) {
            trash.closest(".cargo-row").remove();
            updateTrashButtons();
        }
    });

    updateTrashButtons();
}

// ------------------------------------------------------------
// 4. Login / Register: Passenger and Operator tabs
// ------------------------------------------------------------
var roleTabs = document.querySelectorAll("[data-role-tab]");

roleTabs.forEach(function (tab) {
    tab.addEventListener("click", function () {
        var role = tab.dataset.roleTab;

        roleTabs.forEach(function (other) {
            other.classList.toggle("active", other === tab);
        });
        document.getElementById("roleInput").value = role;

        // change the button text, e.g. "Log in as operator"
        var submitButton = document.getElementById("submitBtn");
        submitButton.textContent = submitButton.dataset[role + "Text"];

        var hint = document.getElementById("roleHint");
        if (hint) {
            hint.textContent = hint.dataset[role + "Text"];
        }

        // register page only: show the business fields for operators
        var operatorFields = document.getElementById("operatorFields");
        if (operatorFields) {
            operatorFields.classList.toggle("hidden", role !== "operator");
        }
    });
});

// ------------------------------------------------------------
// 5. Home page: "+ Add Details" for cargo
// ------------------------------------------------------------
var cargoToggle = document.getElementById("cargoToggle");
var cargoNote = document.getElementById("cargoNote");

if (cargoToggle && cargoNote) {
    cargoToggle.addEventListener("click", function () {
        cargoNote.classList.toggle("hidden");
        cargoToggle.textContent = cargoNote.classList.contains("hidden")
            ? "+ Add Details"
            : "− Hide Details";
    });
}

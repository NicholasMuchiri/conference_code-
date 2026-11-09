
var FEES = {
    registration: 1000,
    perDay: 500,
    workshop: 750,
    lunchPerDay: 300,
    accommodationPerNight: 1500   // Individual requirement (last digit 1)
};

var MAX_DAYS = 3;


function el(id) {
    return document.getElementById(id);
}

function formatKsh(amount) {
    return "KSh " + amount.toLocaleString("en-KE");
}

function getDays() {
    var days = Number(el("days").value);
    if (Number.isInteger(days) && days >= 1 && days <= MAX_DAYS) {
        return days;
    }
    return 0;
}

function getNights() {
    if (!el("accommodation").checked) {
        return 0;
    }
    var nights = Number(el("nights").value);
    var days = getDays();
    if (Number.isInteger(nights) && nights >= 1 && nights <= days) {
        return nights;
    }
    return 0;
}

function setText(id, text) {
    var node = el(id);
    if (node) {
        node.textContent = text;
    }
}

function showError(fieldId, message) {
    el(fieldId + "Error").textContent = message;
    el(fieldId).closest(".field").classList.add("invalid");
    el(fieldId).setAttribute("aria-invalid", "true");
}

function clearError(fieldId) {
    el(fieldId + "Error").textContent = "";
    el(fieldId).closest(".field").classList.remove("invalid");
    el(fieldId).removeAttribute("aria-invalid");
}

function showWorkshop() {
    var isWorkshop = el("category").value === "workshop";
    el("workshopGroup").classList.toggle("hidden", !isWorkshop);
    if (!isWorkshop) {
        el("workshop").value = "";
        clearError("workshop");
    }
}

function showNights() {
    var needsRoom = el("accommodation").checked;
    el("nightsGroup").classList.toggle("hidden", !needsRoom);

    var days = getDays() || MAX_DAYS;
    el("nights").max = days;
    el("nightsHint").textContent = "Between 1 and " + days + " night(s).";

    if (Number(el("nights").value) > days) {
        el("nights").value = days;
    }
    if (!needsRoom) {
        clearError("nights");
    }
}

function showPackageMessage() {
    var category = el("category").value;
    var days = getDays();
    var note = el("packageNote");

    if (category === "") {
        note.textContent = "Choose a category to see your package.";
        return;
    }

    var text = (category === "workshop" ? "Workshop Package" : "Conference Only Package");
    text += ": " + (days || "?") + " day(s)";
    if (category === "workshop" && el("workshop").value !== "") {
        text += ", " + el("workshop").value + " workshop";
    }
    if (el("meal").value === "lunch") {
        text += ", lunch included";
    }
    if (getNights() > 0) {
        text += ", " + getNights() + " night(s) accommodation";
    }
    note.textContent = text + ".";
}

function calculateFee() {
    var days = getDays();
    var nights = getNights();

    var registrationFee = FEES.registration;
    var attendanceFee = days * FEES.perDay;
    var workshopFee = (el("category").value === "workshop") ? FEES.workshop : 0;
    var mealFee = (el("meal").value === "lunch") ? days * FEES.lunchPerDay : 0;
    var accommodationFee = nights * FEES.accommodationPerNight;

    var total = registrationFee + attendanceFee + workshopFee + mealFee + accommodationFee;

    el("feeRegistration").textContent = formatKsh(registrationFee);
    el("feeAttendance").textContent = formatKsh(attendanceFee);
    el("feeWorkshop").textContent = formatKsh(workshopFee);
    el("feeMeals").textContent = formatKsh(mealFee);
    el("feeAccommodation").textContent = formatKsh(accommodationFee);
    el("feeTotal").textContent = formatKsh(total);
    setText("feeTotalInline", formatKsh(total));

    setText("detailAttendance", days ? days + (days === 1 ? " day" : " days") + " \u00d7 " + formatKsh(FEES.perDay) : "");
    setText("detailWorkshop", workshopFee && el("workshop").value ? el("workshop").value : "");
    setText("detailMeals", mealFee ? "Lunch, " + days + (days === 1 ? " day" : " days") + " \u00d7 " + formatKsh(FEES.lunchPerDay) : "");
    setText("detailAccommodation", nights ? nights + (nights === 1 ? " night" : " nights") + " \u00d7 " + formatKsh(FEES.accommodationPerNight) : "");

    return total;
}
function refreshForm() {
    showWorkshop();
    showNights();
    showPackageMessage();
    calculateFee();
}


function validateForm() {
    var valid = true;
    var fields = ["fullname", "admission", "email", "phone", "year", "category", "workshop", "days", "nights"];
    fields.forEach(clearError);

    // 1. Full name cannot be empty
    if (el("fullname").value.trim() === "") {
        showError("fullname", "Enter your full name.");
        valid = false;
    }

    // Admission number is required too
    if (el("admission").value.trim() === "") {
        showError("admission", "Enter your admission number.");
        valid = false;
    }

    // 2. Email must have a valid format
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(el("email").value.trim())) {
        showError("email", "Enter a valid email address, e.g. name@example.com.");
        valid = false;
    }

    // 3. Phone must contain exactly 10 digits
    if (!/^\d{10}$/.test(el("phone").value.trim())) {
        showError("phone", "Phone number must contain exactly 10 digits.");
        valid = false;
    }

    if (el("year").value === "") {
        showError("year", "Select your year of study.");
        valid = false;
    }

    // 5. A participation category must be selected
    if (el("category").value === "") {
        showError("category", "Select a participation category.");
        valid = false;
    }

    // Workshop is required for Workshop Participants
    if (el("category").value === "workshop" && el("workshop").value === "") {
        showError("workshop", "Select a workshop.");
        valid = false;
    }

    // 4. Days must be between 1 and 3
    if (getDays() === 0) {
        showError("days", "Number of days must be a whole number from 1 to 3.");
        valid = false;
    }

    // Nights must fit within the days attended
    if (el("accommodation").checked && getNights() === 0) {
        showError("nights", "Nights must be a whole number from 1 to " + (getDays() || MAX_DAYS) + ".");
        valid = false;
    }

    return valid;
}

function resetForm() {
    el("regForm").reset();
    ["fullname", "admission", "email", "phone", "year", "category", "workshop", "days", "nights"].forEach(clearError);
    refreshForm();
}

// ---------- Events ----------

document.addEventListener("DOMContentLoaded", function () {
    var form = el("regForm");

    // onsubmit: block submission until the data is valid
    form.addEventListener("submit", function (event) {
        if (!validateForm()) {
            event.preventDefault();
            var firstError = document.querySelector(".field.invalid input, .field.invalid select");
            if (firstError) {
                firstError.focus();
            }
        }
    });

    ["category", "workshop", "meal", "accommodation", "year"].forEach(function (id) {
        el(id).addEventListener("change", refreshForm);
    });

    ["days", "nights"].forEach(function (id) {
        el(id).addEventListener("input", refreshForm);
    });

    el("phone").addEventListener("input", function () {
        el("phone").value = el("phone").value.replace(/\D/g, "");
    });

    el("resetBtn").addEventListener("click", resetForm);

    refreshForm();
});

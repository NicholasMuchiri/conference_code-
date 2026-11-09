<?php
const FEE_REGISTRATION = 1000;
const FEE_PER_DAY = 500;
const FEE_WORKSHOP = 750;
const FEE_LUNCH_PER_DAY = 300;
const FEE_ACCOMMODATION_PER_NIGHT = 1500;   // Individual requirement (last digit 1)
const MAX_DAYS = 3;

const WORKSHOPS = ["Web Development", "Artificial Intelligence", "Cybersecurity"];

// Escape output so submitted text cannot inject HTML.
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function formatKsh($amount)
{
    return "KSh " . number_format($amount);
}

$errors = [];

// Only accept form submissions, not direct visits.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $errors[] = "No registration was submitted. Please use the registration form.";
} else {
    // 1. Receive the values
    $fullname      = trim($_POST["fullname"] ?? "");
    $admission     = trim($_POST["admission"] ?? "");
    $email         = trim($_POST["email"] ?? "");
    $phone         = trim($_POST["phone"] ?? "");
    $year          = trim($_POST["year"] ?? "");
    $category      = trim($_POST["category"] ?? "");
    $workshop      = trim($_POST["workshop"] ?? "");
    $days          = trim($_POST["days"] ?? "");
    $meal          = trim($_POST["meal"] ?? "none");
    $accommodation = isset($_POST["accommodation"]) && $_POST["accommodation"] === "yes";
    $nights        = trim($_POST["nights"] ?? "0");

    // 2. Check essential values were received
    if ($fullname === "" || $admission === "" || $email === "" || $phone === ""
        || $year === "" || $category === "" || $days === "") {
        $errors[] = "Some required fields are missing.";
    }

    // 3. Basic server-side validation
    if ($fullname !== "" && mb_strlen($fullname) < 2) {
        $errors[] = "Full name is too short.";
    }
    if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email address is not valid.";
    }
    if ($phone !== "" && !preg_match('/^\d{10}$/', $phone)) {
        $errors[] = "Phone number must contain exactly 10 digits.";
    }
    if ($year !== "" && !preg_match('/^[1-5]$/', $year)) {
        $errors[] = "Year of study is not valid.";
    }
    if ($category !== "" && !in_array($category, ["conference", "workshop"], true)) {
        $errors[] = "Participation category is not valid.";
    }
    if ($category === "workshop" && !in_array($workshop, WORKSHOPS, true)) {
        $errors[] = "Please select a valid workshop.";
    }
    if ($days !== "" && (!ctype_digit($days) || (int) $days < 1 || (int) $days > MAX_DAYS)) {
        $errors[] = "Number of conference days must be between 1 and " . MAX_DAYS . ".";
    }
    if (!in_array($meal, ["none", "lunch"], true)) {
        $errors[] = "Meal option is not valid.";
    }
    if ($accommodation) {
        if (!ctype_digit($nights) || (int) $nights < 1 || (int) $nights > (int) $days) {
            $errors[] = "Nights of accommodation must be from 1 up to the number of conference days.";
        }
    }
}

// 4. Calculate the total here; the amount from JavaScript is never used.
if (empty($errors)) {
    $days   = (int) $days;
    $nights = $accommodation ? (int) $nights : 0;

    $registrationFee  = FEE_REGISTRATION;
    $attendanceFee    = $days * FEE_PER_DAY;
    $workshopFee      = ($category === "workshop") ? FEE_WORKSHOP : 0;
    $mealFee          = ($meal === "lunch") ? $days * FEE_LUNCH_PER_DAY : 0;
    $accommodationFee = $nights * FEE_ACCOMMODATION_PER_NIGHT;

    $total = $registrationFee + $attendanceFee + $workshopFee + $mealFee + $accommodationFee;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration | Student Technology and Innovation Conference</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="site-nav">
        <div class="nav-inner">
            <a class="brand" href="index.html">STIC 2026</a>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="index.html#about">About</a></li>
                <li><a href="index.html#fees">Fees</a></li>
                <li><a href="register.html">Register</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">

<?php if (!empty($errors)): ?>

        <section class="panel error-banner">
            <h1>REGISTRATION NOT COMPLETED</h1>
            <p>Please fix the following and try again:</p>
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="actions">
                <a class="btn" href="register.html">Back to registration</a>
            </div>
        </section>

<?php else: ?>

        <section class="panel confirm-banner">
            <h1>REGISTRATION SUCCESSFUL</h1>

            <dl class="details">
                <dt>Participant</dt><dd><?= e($fullname) ?></dd>
                <dt>Admission Number</dt><dd><?= e($admission) ?></dd>
                <dt>Email</dt><dd><?= e($email) ?></dd>
                <dt>Phone</dt><dd><?= e($phone) ?></dd>
                <dt>Year of Study</dt><dd>Year <?= e($year) ?></dd>
                <dt>Category</dt><dd><?= $category === "workshop" ? "Workshop Participant" : "Conference Only" ?></dd>
                <dt>Conference Days</dt><dd><?= $days ?></dd>
                <dt>Workshop</dt><dd><?= $category === "workshop" ? e($workshop) : "None" ?></dd>
                <dt>Meals</dt><dd><?= $meal === "lunch" ? "Yes (lunch)" : "No" ?></dd>
                <dt>Accommodation</dt><dd><?= $nights > 0 ? "Yes (" . $nights . " night" . ($nights > 1 ? "s" : "") . ")" : "No" ?></dd>
            </dl>

            <table class="summary-table">
                <tr><td>Conference registration</td><td><?= formatKsh($registrationFee) ?></td></tr>
                <tr><td>Attendance (<?= $days ?> &times; <?= formatKsh(FEE_PER_DAY) ?>)</td><td><?= formatKsh($attendanceFee) ?></td></tr>
                <tr><td>Workshop</td><td><?= formatKsh($workshopFee) ?></td></tr>
                <tr><td>Meals</td><td><?= formatKsh($mealFee) ?></td></tr>
                <tr><td>Accommodation (<?= $nights ?> &times; <?= formatKsh(FEE_ACCOMMODATION_PER_NIGHT) ?>)</td><td><?= formatKsh($accommodationFee) ?></td></tr>
                <tr class="total-row"><td>TOTAL AMOUNT PAYABLE</td><td><?= formatKsh($total) ?></td></tr>
            </table>

            <p>Thank you for registering for the<br>
               Student Technology and Innovation Conference.</p>

            <div class="actions">
                <a class="btn" href="index.html">Back to home</a>
                <a class="btn btn-secondary" href="register.html">Register another participant</a>
            </div>
        </section>

<?php endif; ?>

    </main>

    <footer>
        Student Technology and Innovation Conference 2026
    </footer>

</body>
</html>

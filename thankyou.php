<?php
/*******w******** 
    
    Name: Rup
    Date: 2024-05-04
    Description: Website with invoice and easter egg
    EASTER EGG: Shows rickroll if total amount exceeds 20k

****************/

?>

<?php

// Items
$items = [
    ['index' => 1, 'name' => 'MacBook', 'price' => 1899.99, 'quantity' => 0],
    ['index' => 2, 'name' => 'Razer Gaming Mouse', 'price' => 79.99, 'quantity' => 0],
    ['index' => 3, 'name' => 'Portable Hard Drive', 'price' => 179.99, 'quantity' => 0],
    ['index' => 4, 'name' => 'Google Nexus 7', 'price' => 249.99, 'quantity' => 0],
    ['index' => 5, 'name' => 'Footpedal', 'price' => 119.99, 'quantity' => 0]
];


// Post Data
$email = isset($_POST['email']) ? $_POST['email'] : null;
$postalCode = isset($_POST['postal']) ? $_POST['postal'] : null;
$fullName = isset($_POST['fullname']) ? $_POST['fullname'] : null;
$address = isset($_POST['address']) ? $_POST['address'] : null;
$city = isset($_POST['city']) ? $_POST['city'] : null;
$province = isset($_POST['province']) ? $_POST['province'] : null;

// Total Amount
$totalAmount = 0;

// Update Item Quantities
foreach ($items as $key => $item) {
    $itemKey = 'qty' . $item['index'];
    $itemQty = isset($_POST[$itemKey]) ? $_POST[$itemKey] : 0;
    // add the quantities that do exist but aren't ints
    if(strlen($itemQty) > 0) {
    $items[$key]['quantity'] = $itemQty; // modify the array
    }
}

// Error Array
$errors = array();

// Checks if there are any items in the cart
function itemsExist() {
    global $items;
    $totalQuantity = 0;

    foreach($items as $key => $item) {
    $totalQuantity += $item['quantity'];
    }

    if($totalQuantity > 0) {
    return true;
    } else {
    return false;
    }
}

// Get the total amount of a single itemm (amount * qty)
function getItemTotal($item) {
    $itemTotal = $item['price'] * $item['quantity'];

    return $itemTotal;
}

// validate the POST data
function validateData() {
    global $errors;
    global $items;

    // Check if email provided exists and is valid
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $errors[] = 'Email is not valid or not provided!'; // $var[] = '' appends element to the end of the array
     }
    
    // Validate postal code
    $postalCode = filter_input(INPUT_POST, 'postal');
    if (!preg_match('/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/', $postalCode)) {
        $errors[] = 'Postal code is invalid or missing!';
    }

    // Validate credit card number
    $creditCardNumber = filter_input(INPUT_POST, 'cardnumber', FILTER_VALIDATE_INT);
    if (!$creditCardNumber || strlen((string)$creditCardNumber) !== 10) {
        $errors[] = 'Credit card number is invalid or missing!';
    }

    // Validate credit card year
    $currentYear = date('Y');
    $creditCardYear = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT, ['options' => ['min_range' => $currentYear, 'max_range' => $currentYear + 5]]);
    if (!$creditCardYear) {
        $errors[] = 'Credit card expiration year is invalid or missing!';
    }

    // Validate credit card month
    $currentMonth = date('n'); // num of month
    // Month check using current Year via Ternary operator
    $creditCardMonth = ($currentYear == $_POST['year']) ? filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT, ['options' => ['min_range' => $currentMonth, 'max_range' => 12]]) : filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
    if (!$creditCardMonth) {
        $errors[] = 'Credit card expiration month is invalid or missing!';
    }

    // Check if credit card type is provided
    $creditCardType = isset($_POST['cardtype']);
    if (!$creditCardType) {
        $errors[] = 'Credit card type is not selected!';
    }

    // Check if required fields exist
    $fieldsToCheck = ['fullname', 'cardname', 'address', 'city', 'province'];
    foreach ($fieldsToCheck as $field) {

        $value = trim(filter_input(INPUT_POST, $field));

        if (empty($value)) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is empty or missing!';
        }
    }

    // Validate quantities
    foreach ($items as $key => $item) {
        $qty = $item['quantity'];
        
        if(is_numeric($qty) && $qty >= 0) continue;
        
        $errors[] = 'Quantity for item ' . ($key + 1) . " (" . $item['name'] . ")" . ' is not an integer!';
    }

    if(!empty($errors)) {
    return false;
    } else {
    return true;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="thankyou.css">
    <title>Thanks for your order!</title>
</head>
<body>
<?php
$isDataValid = validateData();
$displayError = false;
$errorMessages = [];
$invoiceDetails = '';
$displayInvoice = false;

if (!$isDataValid) {
    $displayError = true;
    foreach ($errors as $error) {
        $errorMessages[] = htmlspecialchars($error);
    }
} else {
    if (itemsExist()) {
        $invoiceDetails = "Thanks for your order " . htmlspecialchars($fullName) . ".";
        $displayInvoice = true;
    } else {
        $invoiceDetails = "Your cart is empty.";
        $displayInvoice = true;
    }
}
?>

<?php if ($displayError): ?>
        <p>The form could not be processed due to the following errors:</p>
        <ul>
            <?php foreach ($errorMessages as $errorMessage): ?>
                <li><?= $errorMessage ?></li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($displayInvoice): ?>
    <div class="invoice">
        <h2><?= $invoiceDetails ?></h2>

    <?php if (itemsExist()): ?>
    <h3>Here's a summary of your order:</h3>
    <table>
    <colgroup>
            <col style="width: 15%">
            <col style="width: 40%">
            <col style="width: 25%">
            <col style="width: 20%">
        </colgroup>
        <tr>
            <th class="alignleft" colspan="4">Address Information</th>
        </tr>
        <tr>
            <td class="alignright bold">Address:</td>
            <td><?= $address ?></td>
            <td class="alignright bold">City:</td>
            <td><?= $city ?></td>
        </tr>
        <tr>
            <td class="alignright bold">Province:</td>
            <td ><?= $province ?></td>
            <td class="alignright bold">Postal Code:</td>
            <td><?= $postalCode ?></td>
        </tr>
        <tr>
            <td colspan="2" class="alignright bold">Email:</td>
            <td colspan="2"><?= $email ?></td>
        </tr>
    </table>

    <table>
        <colgroup>
            <col style="width: 15%">
            <col style="width: 70%">
            <col style="width: 15%">
        </colgroup>
        <tr>
            <th class="alignleft" colspan="3">Order Information</th>
        </tr>
        <tr>
            <td class="bold">Quantity</td>
            <td class="bold">Description</td>
            <td class="bold">Cost</td>
        </tr>
        <?php foreach ($items as $item): ?>
            <?php if ($item['quantity'] > 0): ?>
             <tr>
              <td><?= $item['quantity'] ?></td>
              <td><?= $item['name'] ?></td>
              <?php $totalAmount += getItemTotal($item) ?>
              <td class="alignright"><?= getItemTotal($item) ?></td>
             </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        <tr>
            <td class="alignright bold" colspan="2">Totals</td>
            <td class="alignright bold">$ <?= $totalAmount ?></td>
        </tr>
    </table>
    <?php endif; ?>
    </div>
    <?php if ($totalAmount >= 20000): ?>
             <h1>Congrats on the big order. Rick Astley congratulates you.</h1>
             <iframe id='rollingrick' src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Rick Astley - Never Gonna Give You Up (Official Music Video)" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            <?php endif; ?>

    <?php endif; ?>

</body>
</html>
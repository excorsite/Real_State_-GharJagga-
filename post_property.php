<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/connect.php';

// AUTH CHECK — redirect to login if not logged in
if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    header('location:login.php');
    exit();
}


$success_msg = [];
$warning_msg = [];

// FORM SUBMISSION

if (isset($_POST['post'])) {
    //Server side validation
    $errors = [];

    if (empty(trim($_POST['property_name'] ?? ''))) $errors[] = 'Property name is required.';
    if (!isset($_POST['price']) || $_POST['price'] === '')  $errors[] = 'Property price is required.';
    if (empty(trim($_POST['address'] ?? '')))        $errors[] = 'Property address is required.';
    if (empty(trim($_POST['type'] ?? '')))           $errors[] = 'Property type is required.';
    if (empty(trim($_POST['description'] ?? '')))    $errors[] = 'Property description is required.';
    if (empty($_FILES['image_01']['name']))          $errors[] = 'Main image is required.';

    // Validate property name - only letters and spaces
    $property_name_temp = trim($_POST['property_name'] ?? '');
    if (!preg_match('/^[a-zA-Z\s]+$/', $property_name_temp) && !empty($property_name_temp)) {
        $errors[] = 'Property name should contain only letters.';
    }

    // Validate address - only letters, numbers, spaces, commas, and hyphens
    $address_temp = trim($_POST['address'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9\s,\-]+$/', $address_temp) && !empty($address_temp)) {
        $errors[] = 'Address should contain only letters, numbers, spaces, commas, and hyphens.';
    }

    // Validate price - must be selected (minimum 1 Crore)
    $price_temp = isset($_POST['price']) ? (int)$_POST['price'] : 0;
    if (!$price_temp) {
        $errors[] = 'Please select a price.';
    }

    if (!empty($errors)) {
        // Show all validation errors and stop
        foreach ($errors as $e) {
            $warning_msg[] = $e;
        }

    } else {
        // STEP 2 — SANITIZE TEXT FIELDS
        $id               = create_unique_id();
        $property_name    = htmlspecialchars(strip_tags(trim($_POST['property_name'])));
        $address          = htmlspecialchars(strip_tags(trim($_POST['address'])));
        $offer            = htmlspecialchars(strip_tags(trim($_POST['offer'])));
        $type             = htmlspecialchars(strip_tags(trim($_POST['type'])));
        $property_condition = htmlspecialchars(strip_tags(trim($_POST['property_condition'])));
        $furnished        = htmlspecialchars(strip_tags(trim($_POST['furnished'] ?? '')));
        $loan             = htmlspecialchars(strip_tags(trim($_POST['loan'])));
        $description      = htmlspecialchars(strip_tags(trim($_POST['description'])));
        $road_access      = htmlspecialchars(strip_tags(trim($_POST['road_access'] ?? '')));
        $facing           = htmlspecialchars(strip_tags(trim($_POST['facing'] ?? '')));
        $plot_shape       = htmlspecialchars(strip_tags(trim($_POST['plot_shape'] ?? '')));
        $ownership        = htmlspecialchars(strip_tags(trim($_POST['ownership'] ?? '')));

        // STEP 3 — SANITIZE NUMERIC FIELDS
        // Empty string becomes NULL so MySQL integer columns don't error

        $price      = isset($_POST['price'])       && $_POST['price'] !== ''       ? (int)$_POST['price']       : 0;
        $deposite   = isset($_POST['deposite'])     && $_POST['deposite'] !== ''    ? (int)$_POST['deposite']    : 0;
        $bhk        = isset($_POST['bhk'])          && $_POST['bhk'] !== ''         ? (int)$_POST['bhk']         : null;
        $bedroom    = isset($_POST['bedroom'])      && $_POST['bedroom'] !== ''     ? (int)$_POST['bedroom']     : null;
        $bathroom   = isset($_POST['bathroom'])     && $_POST['bathroom'] !== ''    ? (int)$_POST['bathroom']    : null;
        $balcony    = isset($_POST['balcony'])      && $_POST['balcony'] !== ''     ? (int)$_POST['balcony']     : null;
        $carpet     = isset($_POST['carpet'])       && $_POST['carpet'] !== ''      ? (int)$_POST['carpet']      : null;
        $age        = isset($_POST['age'])          && $_POST['age'] !== ''         ? (int)$_POST['age']         : null;
        $total_floors = isset($_POST['total_floors']) && $_POST['total_floors'] !== '' ? (int)$_POST['total_floors'] : null;
        $room_floor = isset($_POST['room_floor'])   && $_POST['room_floor'] !== ''  ? (int)$_POST['room_floor']  : null;
        $total_area = isset($_POST['total_area'])   && $_POST['total_area'] !== ''  ? (int)$_POST['total_area']  : null;
        $ana        = isset($_POST['ana'])          && $_POST['ana'] !== ''         ? (float)$_POST['ana']       : null;


        // STEP 4 — AMENITIES (checkboxes — default to 'no')

        $lift           = isset($_POST['lift'])           ? 'yes' : 'no';
        $security_guard = isset($_POST['security_guard']) ? 'yes' : 'no';
        $play_ground    = isset($_POST['play_ground'])    ? 'yes' : 'no';
        $garden         = isset($_POST['garden'])         ? 'yes' : 'no';
        $water_supply   = isset($_POST['water_supply'])   ? 'yes' : 'no';
        $power_backup   = isset($_POST['power_backup'])   ? 'yes' : 'no';
        $parking_area   = isset($_POST['parking_area'])   ? 'yes' : 'no';
        $gym            = isset($_POST['gym'])            ? 'yes' : 'no';
        $shopping_mall  = isset($_POST['shopping_mall'])  ? 'yes' : 'no';
        $hospital       = isset($_POST['hospital'])       ? 'yes' : 'no';
        $school         = isset($_POST['school'])         ? 'yes' : 'no';
        $market_area    = isset($_POST['market_area'])    ? 'yes' : 'no';


        // STEP 5 — HANDLE IMAGE UPLOADS
        // Max file size: 2MB per image

        // Helper function to handle an optional image upload
        // Returns the new filename on success, or '' if no file was uploaded
        function handle_optional_image($file_key) {
            $file     = $_FILES[$file_key];
            $name     = trim($file['name']);

            if (empty($name)) {
                return ''; 
            }

            if ($file['size'] > 2000000) {
                return 'TOO_LARGE'; 
            }

            $ext         = pathinfo($name, PATHINFO_EXTENSION);
            $new_name    = create_unique_id() . '.' . $ext;
            $destination = 'uploaded_files/' . $new_name;
            move_uploaded_file($file['tmp_name'], $destination);

            return $new_name;
        }

        // Process optional images 02–05
        $rename_image_02 = handle_optional_image('image_02');
        $rename_image_03 = handle_optional_image('image_03');
        $rename_image_04 = handle_optional_image('image_04');
        $rename_image_05 = handle_optional_image('image_05');

        // Check if any optional image was too large
        $image_size_error = false;
        foreach (['image_02' => $rename_image_02, 'image_03' => $rename_image_03,
                  'image_04' => $rename_image_04, 'image_05' => $rename_image_05] as $key => $result) {
            if ($result === 'TOO_LARGE') {
                $warning_msg[] = $key . ' is too large (max 2MB).';
                $$key = ''; // Reset to empty
                $image_size_error = true;
            }
        }
        // Clean up TOO_LARGE markers
        if ($rename_image_02 === 'TOO_LARGE') $rename_image_02 = '';
        if ($rename_image_03 === 'TOO_LARGE') $rename_image_03 = '';
        if ($rename_image_04 === 'TOO_LARGE') $rename_image_04 = '';
        if ($rename_image_05 === 'TOO_LARGE') $rename_image_05 = '';

        // Process required main image (image_01)
        $image_01      = $_FILES['image_01'];
        $image_01_name = trim($image_01['name']);
        $image_01_size = $image_01['size'];
        $image_01_ext  = pathinfo($image_01_name, PATHINFO_EXTENSION);
        $rename_image_01 = create_unique_id() . '.' . $image_01_ext;

        if ($image_01_size > 2000000) {

            // Main image too large — stop everything
            $warning_msg[] = 'Main image is too large (max 2MB).';

        } else {

            // STEP 6 — INSERT INTO DATABASE
            $insert = $conn->prepare("
                INSERT INTO `property` (
                    id, user_id, property_name, address, price, type, offer,
                    property_condition, furnished, bhk, deposite, bedroom, bathroom,
                    balcony, carpet, age, total_floors, room_floor, loan, lift,
                    security_guard, play_ground, garden, water_supply, power_backup,
                    parking_area, gym, shopping_mall, hospital, school, market_area,
                    image_01, image_02, image_03, image_04, image_05, description
                ) VALUES (
                    ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
                )
            ");

            $insert->execute([
                $id, $user_id, $property_name, $address, $price, $type, $offer,
                $property_condition, $furnished, $bhk, $deposite, $bedroom, $bathroom,
                $balcony, $carpet, $age, $total_floors, $room_floor, $loan, $lift,
                $security_guard, $play_ground, $garden, $water_supply, $power_backup,
                $parking_area, $gym, $shopping_mall, $hospital, $school, $market_area,
                $rename_image_01, $rename_image_02, $rename_image_03, $rename_image_04,
                $rename_image_05, $description
            ]);

            // Move main image to uploads folder after successful DB insert
            move_uploaded_file($image_01['tmp_name'], 'uploaded_files/' . $rename_image_01);

            $success_msg[] = 'Property posted successfully!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Property</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hidden { display: none !important; }

        .form-error-msg {
            background: #ffe0e0;
            color: #c0392b;
            border: 1px solid #e74c3c;
            border-radius: 5px;
            padding: 12px 20px;
            margin-bottom: 15px;
            font-size: 15px;
            display: none;
        }

        .field-error {
            border: 2px solid #e74c3c !important;
            background: #ffe0e0 !important;
        }

        .error-text {
            color: #c0392b;
            font-size: 13px;
            margin-top: 5px;
            display: none;
        }

        .error-text.show {
            display: block;
        }
    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="property-form">

    <form action="" method="POST" enctype="multipart/form-data" novalidate>

        <div class="form-error-msg" id="form-error-msg">
            Please fill in all required fields before submitting.
        </div>

        <h3>Property Details</h3>

        <div class="flex">
            <div class="box">
                <p>Property name <span>*</span></p>
                <input type="text" inputmode="text" name="property_name" id="property_name" required maxlength="50"
                    placeholder="Enter property name" class="input">
                <div class="error-text" id="property_name_error"></div>
            </div>
            <div class="box">
                <p>Property price <span>*</span></p>
                <select name="price" id="price" required class="input">
                    <option value="">-- Select Price --</option>
                    <option value="10000000">Rs1 Crore (1,00,00,000)</option>
                    <option value="15000000">Rs1.5 Crore (1,50,00,000)</option>
                    <option value="20000000">Rs2 Crore (2,00,00,000)</option>
                    <option value="25000000">Rs2.5 Crore (2,50,00,000)</option>
                    <option value="30000000">Rs3 Crore (3,00,00,000)</option>
                    <option value="35000000">Rs3.5 Crore (3,50,00,000)</option>
                    <option value="40000000">Rs4 Crore (4,00,00,000)</option>
                    <option value="45000000">Rs4.5 Crore (4,50,00,000)</option>
                    <option value="50000000">Rs5 Crore (5,00,00,000)</option>
                    
                
                </select>
                <div class="error-text" id="price_error"></div>
            </div>
            <div class="box">
                <p>Deposit amount</p>
                <input type="number" inputmode="numeric" name="deposite" min="0" 
                    placeholder="Enter deposit (optional)" class="input">
            </div>
            <div class="box">
                <p>Property address <span>*</span></p>
                <input type="text" name="address" id="address" required maxlength="100"
                    placeholder="Full address" class="input">
                <div class="error-text" id="address_error"></div>
            </div>
            <div class="box">
                <p>Offer type <span>*</span></p>
                <select name="offer" required class="input">
                    <option value="sale">Sale</option>
                    <option value="resale">Resale</option>
                </select>
            </div>
            <div class="box">
                <p>Property type <span>*</span></p>
                <select name="type" id="property_type" required class="input">
                    <option value="">-- Select Type --</option>
                    <option value="home">Home</option>
                    <option value="land">Land</option>
                </select>
            </div>
            <div class="box">
                <p>Property status <span>*</span></p>
                <select name="property_condition" required class="input">
                    <option value="ready to move">Ready to move</option>
                    <option value="under construction">Under construction</option>
                </select>
            </div>
        </div>

        <!-- HOME FIELDS — shown only when Home is selected -->
        <div id="home_fields" class="hidden">
            <div class="flex">
                <div class="box">
                    <p>Furnished status</p>
                    <select name="furnished" class="input">
                        <option value="unfurnished">Unfurnished</option>
                        <option value="semi-furnished">Semi-furnished</option>
                        <option value="furnished">Furnished</option>
                    </select>
                </div>
                <div class="box">
                    <p>BHK</p>
                    <select name="bhk" class="input">
                        <option value="1">1 BHK</option>
                        <option value="2">2 BHK</option>
                        <option value="3">3 BHK</option>
                        <option value="4">4 BHK</option>
                        <option value="5">5+ BHK</option>
                    </select>
                </div>
                <div class="box">
                    <p>Bedrooms</p>
                    <input type="number" inputmode="numeric" name="bedroom" min="0" placeholder="No. of bedrooms" class="input">
                </div>
                <div class="box">
                    <p>Bathrooms</p>
                    <input type="number" inputmode="numeric" name="bathroom" min="0" placeholder="No. of bathrooms" class="input">
                </div>
                <div class="box">
                    <p>Balconies</p>
                    <input type="number" inputmode="numeric" name="balcony" min="0" placeholder="No. of balconies" class="input">
                </div>
                <div class="box">
                    <p>Carpet area (sqft)</p>
                    <input type="number" inputmode="numeric" name="carpet" min="0" placeholder="Carpet area in sqft" class="input">
                </div>
                <div class="box">
                    <p>Age of property (years)</p>
                    <input type="number" inputmode="numeric" name="age" min="0" placeholder="How old?" class="input">
                </div>
                <div class="box">
                    <p>Total floors in building</p>
                    <input type="number" inputmode="numeric" name="total_floors" min="0" placeholder="Total floors" class="input">
                </div>
                <div class="box">
                    <p>Property on floor</p>
                    <input type="number" inputmode="numeric" name="room_floor" min="0" placeholder="e.g. 3rd floor" class="input">
                </div>
            </div>
        </div>

        <!-- LAND FIELDS — shown only when Land is selected -->
        <div id="land_fields" class="hidden">
            <div class="flex">
                <div class="box">
                    <p>Total area (sqft) <span>*</span></p>
                    <input type="number" inputmode="numeric" name="total_area" min="0" placeholder="e.g. 1369 sqft" class="input">
                </div>
                <div class="box">
                    <p>Road access (ft)</p>
                    <input type="text" inputmode="text" name="road_access" maxlength="50"
                        placeholder="e.g. 20 ft pitched road" class="input">
                </div>
                <div class="box">
                    <p>Facing direction</p>
                    <select name="facing" class="input">
                        <option value="East">East</option>
                        <option value="West">West</option>
                        <option value="North">North</option>
                        <option value="South">South</option>
                        <option value="North-East">North-East</option>
                        <option value="North-West">North-West</option>
                        <option value="South-East">South-East</option>
                        <option value="South-West">South-West</option>
                    </select>
                </div>
                <div class="box">
                    <p>Plot shape</p>
                    <select name="plot_shape" class="input">
                        <option value="Rectangular">Rectangular</option>
                        <option value="Square">Square</option>
                        <option value="Irregular">Irregular</option>
                    </select>
                </div>
                <div class="box">
                    <p>Area in Ana (if applicable)</p>
                    <input type="number" step="0.1" name="ana" min="0" placeholder="e.g. 4 Ana" class="input">
                </div>
                <div class="box">
                    <p>Ownership type</p>
                    <select name="ownership" class="input">
                        <option value="Individual">Individual</option>
                        <option value="Company">Company</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- COMMON FIELDS -->
        <div class="box">
            <p>Loan available?</p>
            <select name="loan" class="input">
                <option value="available">Available</option>
                <option value="not available">Not available</option>
            </select>
        </div>

        <div class="box">
            <p>Property description <span>*</span></p>
            <textarea name="description" maxlength="2000" cols="30" rows="10"
                placeholder="Write about the property..." class="input" required></textarea>
        </div>

        <!-- AMENITIES -->
        <div class="checkbox">
            <p>Amenities (optional)</p>
            <div class="flex">
                <div>
                    <label><input type="checkbox" name="play_ground" value="yes"> Play ground</label><br>
                    <label><input type="checkbox" name="garden" value="yes"> Garden</label><br>
                    <label><input type="checkbox" name="water_supply" value="yes"> Water supply</label><br>
                    <label><input type="checkbox" name="power_backup" value="yes"> Power backup</label>
                </div>
                <div>
                    <label><input type="checkbox" name="parking_area" value="yes"> Parking area</label><br>
                    <label><input type="checkbox" name="hospital" value="yes"> Hospital nearby</label><br>
                    <label><input type="checkbox" name="school" value="yes"> School nearby</label><br>
                    <label><input type="checkbox" name="market_area" value="yes"> Market nearby</label>
                </div>
            </div>
        </div>

        <!-- IMAGES -->
        <div class="box">
            <p>Main image <span>*</span></p>
            <input type="file" name="image_01" accept="image/*" required class="input">
        </div>
        <div class="flex">
            <div class="box"><p>Image 02</p><input type="file" name="image_02" accept="image/*"></div>
            <div class="box"><p>Image 03</p><input type="file" name="image_03" accept="image/*"></div>
            <div class="box"><p>Image 04</p><input type="file" name="image_04" accept="image/*"></div>
            <div class="box"><p>Image 05</p><input type="file" name="image_05" accept="image/*"></div>
        </div>

        <input type="submit" value="Post Property" name="post" id="submit_btn" class="btn">

    </form>

</section>

<script>
// Show/hide Home or Land fields based on selected property type
const propertyTypeSelect = document.getElementById('property_type');

function toggleFields() {
    const selected = propertyTypeSelect.value;
    document.getElementById('home_fields').classList.add('hidden');
    document.getElementById('land_fields').classList.add('hidden');

    if (selected === 'home') document.getElementById('home_fields').classList.remove('hidden');
    if (selected === 'land') document.getElementById('land_fields').classList.remove('hidden');
}

propertyTypeSelect.addEventListener('change', toggleFields);
window.addEventListener('load', toggleFields); // Run on page load too

// ============================================================
// LIVE VALIDATION FOR PROPERTY NAME AND PRICE
// ============================================================

const propertyNameInput = document.getElementById('property_name');
const priceInput = document.getElementById('price');
const addressInput = document.getElementById('address');
const propertyNameError = document.getElementById('property_name_error');
const priceError = document.getElementById('price_error');
const addressError = document.getElementById('address_error');
const submitBtn = document.getElementById('submit_btn');

// Minimum price: 1 Crore (1,00,00,000)
const MIN_PRICE = 10000000;

// Validate property name - only letters and spaces allowed
function validatePropertyName() {
    const value = propertyNameInput.value.trim();
    const letterOnly = /^[a-zA-Z\s]*$/;
    
    if (value === '') {
        propertyNameError.style.display = 'none';
        propertyNameError.classList.remove('show');
        propertyNameInput.classList.remove('field-error');
        return true;
    }
    
    if (!letterOnly.test(value)) {
        propertyNameError.textContent = '❌ Property name should contain only letters';
        propertyNameError.classList.add('show');
        propertyNameInput.classList.add('field-error');
        return false;
    } else {
        propertyNameError.classList.remove('show');
        propertyNameInput.classList.remove('field-error');
        return true;
    }
}

// Validate address - only letters, spaces, numbers, commas, and hyphens allowed
function validateAddress() {
    const value = addressInput.value.trim();
    const addressPattern = /^[a-zA-Z0-9\s,\-]*$/;
    
    if (value === '') {
        addressError.style.display = 'none';
        addressError.classList.remove('show');
        addressInput.classList.remove('field-error');
        return true;
    }
    
    if (!addressPattern.test(value)) {
        addressError.textContent = '❌ Address should contain only letters, numbers, spaces, commas, and hyphens';
        addressError.classList.add('show');
        addressInput.classList.add('field-error');
        return false;
    } else {
        addressError.classList.remove('show');
        addressInput.classList.remove('field-error');
        return true;
    }
}

// Validate price - minimum 1 Crore
function validatePrice() {
    const value = priceInput.value.trim();
    
    if (value === '' || value === '0') {
        priceError.style.display = 'none';
        priceError.classList.remove('show');
        priceInput.classList.remove('field-error');
        return true;
    }
    
    const priceNum = parseInt(value);
    
    if (isNaN(priceNum) || priceNum === 0) {
        priceError.textContent = '❌ Please select a valid price';
        priceError.classList.add('show');
        priceInput.classList.add('field-error');
        return false;
    } else {
        priceError.classList.remove('show');
        priceInput.classList.remove('field-error');
        return true;
    }
}

// Check form validity
function checkFormValidity() {
    const isNameValid = validatePropertyName();
    const isPriceValid = validatePrice();
    const isAddressValid = validateAddress();
    
    if (!isNameValid || !isPriceValid || !isAddressValid) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    } else {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }
}

// Add event listeners for real-time validation
propertyNameInput.addEventListener('input', checkFormValidity);
propertyNameInput.addEventListener('blur', validatePropertyName);
priceInput.addEventListener('input', checkFormValidity);
priceInput.addEventListener('blur', validatePrice);
addressInput.addEventListener('input', checkFormValidity);
addressInput.addEventListener('blur', validateAddress);

// Check validity on page load
window.addEventListener('load', checkFormValidity);
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>
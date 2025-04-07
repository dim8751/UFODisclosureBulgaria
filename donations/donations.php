<?php
require_once('../vendor/autoload.php');
require_once('../inc/db_connect.php');

$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$_SESSION['lang'] = $lang;

// Translations
$translations = [
    'en' => [
        'title' => 'Donations - UFO Disclosure Bulgaria',
        'make_donation' => 'MAKE A DONATION',
        'thank_you_note' => 'We sincerely appreciate your generous donation! As a token of our gratitude, your name will be displayed in our donors list unless you prefer to remain anonymous.',
        'dashboard' => 'Dashboard',
        'personal_info' => 'Personal Information:',
        'email' => 'Email',
        'donation_amount' => 'Donation Amount:',
        'anonymous_label' => 'Make this donation anonymous',
        'donate_btn' => 'DONATE',
        'processing' => 'PROCESSING...',
        'err_login' => 'Please log in to make a donation',
        'err_amount_empty' => 'Please enter a valid donation amount',
        'err_name_empty' => 'Please provide both your first and last name',
        'err_amount_invalid' => 'Please enter a donation amount of $1.00 or more',
        'err_minimum_amount' => 'Minimum donation amount is $1.00',
        'err_token_missing' => 'Payment token not received. Please try again.',
        'err_card' => 'Card error: ',
        'err_invalid_request' => 'Invalid request. Please try again.',
        'err_auth' => 'Authentication error. Please contact support.',
        'err_network' => 'Network error. Please try again.',
        'err_payment' => 'Payment error. Please try again later.',
        'err_general' => 'An error occurred. Please try again.',
        'err_db' => 'Database error occurred. Please try again later.',
        'err_csrf' => 'Invalid security token. Please try again.',
        'err_card_incomplete' => 'Your card number is incomplete',
        'err_expiry_incomplete' => 'Your card expiration date is incomplete',
        'err_expiry_past' => 'Your card expiration year is in the past',
        'err_cvc_incomplete' => 'Your card CVC is incomplete',
        'err_zip_incomplete' => 'Your card ZIP code is incomplete',
        'err_card_invalid' => 'Your card number is invalid',
        'err_expiry_invalid' => 'Your card expiration date is invalid',
        'err_cvc_invalid' => 'Your card CVC is invalid',
        'err_zip_invalid' => 'Your card ZIP code is invalid',
        'success_donation' => 'Thank you for your %s donation of $%s!'
    ],
    'bg' => [
        'title' => 'Дарения - НЛО Разкритие България',
        'make_donation' => 'НАПРАВЕТЕ ДАРЕНИЕ',
        'thank_you_note' => 'Искрено ценим вашето щедро дарение! Като знак на благодарност, името ви ще бъде показано в списъка на дарителите, освен ако не пожелаете да останете анонимниa.',
        'dashboard' => 'Начален Панел',
        'personal_info' => 'Лична Информация:',
        'email' => 'Имейл',
        'donation_amount' => 'Сума на Дарението:',
        'anonymous_label' => 'Направете това дарение анонимно',
        'donate_btn' => 'ДАРИ',
        'processing' => 'ОБРАБОТВАНЕ...',
        'err_login' => 'Моля, влезте, за да направите дарение',
        'err_amount_empty' => 'Моля, въведете валидна сума за дарение',
        'err_name_empty' => 'Моля, предоставете вашето име и фамилия',
        'err_amount_invalid' => 'Моля, въведете сума за дарение от $1.00 или повече',
        'err_minimum_amount' => 'Минималната сума за дарение е $1.00',
        'err_token_missing' => 'Токенът за плащане не е получен. Моля, опитайте отново.',
        'err_card' => 'Грешка с картата: ',
        'err_invalid_request' => 'Невалидна заявка. Моля, опитайте отново.',
        'err_auth' => 'Грешка при удостоверяване. Моля, свържете се с поддръжката.',
        'err_network' => 'Мрежова грешка. Моля, опитайте отново.',
        'err_payment' => 'Грешка при плащане. Моля, опитайте отново по-късно.',
        'err_general' => 'Възникна грешка. Моля, опитайте отново.',
        'err_db' => 'Възникна грешка в базата данни. Моля, опитайте отново по-късно.',
        'err_csrf' => 'Невалиден защитен токен. Моля, опитайте отново.',
        'err_card_incomplete' => 'Номерът на вашата карта е непълен',
        'err_expiry_incomplete' => 'Датата на изтичане на вашата карта е непълна',
        'err_expiry_past' => 'Годината на изтичане на вашата карта е в миналото',
        'err_cvc_incomplete' => 'CVC кодът на вашата карта е непълен',
        'err_zip_incomplete' => 'ZIP кодът на вашата карта е непълен',
        'err_card_invalid' => 'Номерът на вашата карта е невалиден',
        'err_expiry_invalid' => 'Датата на изтичане на вашата карта е невалидна',
        'err_cvc_invalid' => 'CVC кодът на вашата карта е невалиден',
        'err_zip_invalid' => 'ZIP кодът на вашата карта е невалиден',
        'success_donation' => 'Благодарим ви за вашето %s дарение от $%s!'
    ]
];

$t = $translations[$lang] ?? $translations['en'];

$successful_donation = '';
$error_msg = '';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['userID'])) {
    $error_msg = $t['err_login'];
    header("Location: login.php?lang=$lang");
    exit();
}

$userID = $_SESSION['user']['userID'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_msg = $t['err_csrf'];
    } else {
        if (!isset($_POST['donationAmount']) || empty($_POST['donationAmount'])) {
            $error_msg = $t['err_amount_empty'];
        } elseif (!isset($_POST['firstName']) || empty(trim($_POST['firstName'])) || !isset($_POST['lastName']) || empty(trim($_POST['lastName']))) {
            $error_msg = $t['err_name_empty'];
        } else {
            $donationAmount = str_replace(['$', ','], '', $_POST['donationAmount']);
            $donationAmount = (float) $donationAmount;
            $isAnonymous = isset($_POST['anonymous']) && $_POST['anonymous'] === 'on' ? 1 : 0;

            if (!is_numeric($donationAmount) || $donationAmount < 1) {
                $error_msg = $t['err_amount_invalid'];
            } else {
                try {
                    \Stripe\Stripe::setApiKey('sk_test_51QnskfR2TFgANVZJDtqg5gK37iEOJfAvScTTeTg9XIzR7z59zhpTyW4sOtTu75oRW4WC44s2YnRq3PjgSQd4IBWj00iuRjJkTT');

                    if (!isset($_POST['stripeToken'])) {
                        throw new Exception($t['err_token_missing']);
                    }

                    $charge = \Stripe\Charge::create([
                        'amount' => (int)$donationAmount,
                        'currency' => 'usd',
                        'description' => 'Donation to UfoDisclosureBulgaria' . ($isAnonymous ? ' (Anonymous)' : ''),
                        'source' => $_POST['stripeToken'],
                        'shipping' => [
                            'name' => $isAnonymous ? 'Anonymous Donor' : ($_POST['firstName'] . ' ' . $_POST['lastName']),
                            'address' => [
                                'line1' => 'Address Line 1',
                            ],
                        ]
                    ]);

                    try {
                        $db->beginTransaction();
                        
                        $stmt = $db->prepare("INSERT INTO DONATIONS (userID, donationAmount, donationStatus, isAnonymous) VALUES (:userID, :donationAmount, 'Completed', :isAnonymous)");
                        $stmt->bindParam(':userID', $userID);
                        $stmt->bindParam(':donationAmount', $donationAmount);
                        $stmt->bindParam(':isAnonymous', $isAnonymous, PDO::PARAM_INT);
                        $stmt->execute();
                        
                        $db->commit();
                        $anonymousText = $isAnonymous ? ($lang === 'bg' ? 'анонимно' : 'anonymous') : '';
                        $successful_donation = sprintf($t['success_donation'], $anonymousText, number_format($donationAmount / 100, 2));
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        
                    } catch (PDOException $e) {
                        $db->rollBack();
                        $error_msg = $t['err_db'];
                        error_log("Database Error: " . $e->getMessage());
                    }

                } catch (\Stripe\Exception\CardException $e) {
                    $error_msg = $t['err_card'];
                    if ($e->getStripeCode() === 'card_declined') {
                        $error_msg .= $lang === 'bg' ? 'Картата е отхвърлена' : 'The card was declined';
                    } else {
                        $error_msg .= $e->getMessage();
                    }
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    $error_msg = $t['err_invalid_request'];
                } catch (\Stripe\Exception\AuthenticationException $e) {
                    $error_msg = $t['err_auth'];
                    error_log("Stripe Authentication Error: " . $e->getMessage());
                } catch (\Stripe\Exception\ApiConnectionException $e) {
                    $error_msg = $t['err_network'];
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    $error_msg = $t['err_payment'];
                    error_log("Stripe API Error: " . $e->getMessage());
                } catch (Exception $e) {
                    $error_msg = $t['err_general'];
                    error_log("General Error: " . $e->getMessage());
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang === 'bg' ? 'bg' : 'en'; ?>">
<head>
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title><?php echo $t['title']; ?></title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>   
    <form class="payment-form" action="donations.php?lang=<?php echo $lang; ?>" method="POST" id="payment-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="input-container">
            <button type="button" onclick="window.location.href='../dashboard/index.php?lang=<?php echo $lang; ?>'" class="back-btn"><?php echo $t['dashboard']; ?></button>
            <div class="language-switch">
                <a href="?lang=en">EN</a>
                <a> / </a>
                <a href="?lang=bg">BG</a>
            </div>
            <h1><?php echo $t['make_donation']; ?></h1>
            <h2 class="thank-you-note"><?php echo $t['thank_you_note']; ?></h2>
            <?php if (!empty($error_msg)): ?>
                <div class="error-message" id="card-errors"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>
            <?php if (!empty($successful_donation)): ?>
                <div id="success-donation"><?php echo htmlspecialchars($successful_donation); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label><?php echo $t['personal_info']; ?></label>
                <input type="text" id="firstName" name="firstName" maxlength="25" placeholder="First Name:" value="<?php echo htmlspecialchars($_SESSION['user']['userFirstName']); ?>">
                <input type="text" id="lastName" name="lastName" maxlength="25" placeholder="Last Name:" value="<?php echo htmlspecialchars($_SESSION['user']['userLastName']); ?>">
                <input type="email" name="emailAddress" value="<?php echo htmlspecialchars($_SESSION['user']['userEmailAddress']); ?>" style="cursor: not-allowed !important; background-color: #f0f0f0 !important;" readonly>
                <div id="card-element">
                    <!-- Stripe Element will be inserted here -->
                </div>
            </div>
            
            <div class="form-group">
                <label><?php echo $t['donation_amount']; ?></label>
                <input type="text" id="donationAmount" name="donationAmount" class="donation-amount" maxlength="10" value="$0.00" style="caret-color: transparent">
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="anonymous" id="anonymous">
                    <?php echo $t['anonymous_label']; ?>
                </label>
            </div>

            <button type="submit" class="donate-btn"><?php echo $t['donate_btn']; ?></button>
        </div>
    </form>

    <script>
    // Store translations in JavaScript variables for proper access
    var jsTranslations = {
        err_card_incomplete: '<?php echo $t['err_card_incomplete']; ?>',
        err_expiry_incomplete: '<?php echo $t['err_expiry_incomplete']; ?>',
        err_expiry_past: '<?php echo $t['err_expiry_past']; ?>',
        err_cvc_incomplete: '<?php echo $t['err_cvc_incomplete']; ?>',
        err_zip_incomplete: '<?php echo $t['err_zip_incomplete']; ?>',
        err_card_invalid: '<?php echo $t['err_card_invalid']; ?>',
        err_expiry_invalid: '<?php echo $t['err_expiry_invalid']; ?>',
        err_cvc_invalid: '<?php echo $t['err_cvc_invalid']; ?>',
        err_zip_invalid: '<?php echo $t['err_zip_invalid']; ?>',
        donate_btn: '<?php echo $t['donate_btn']; ?>',
        processing: '<?php echo $t['processing']; ?>',
        err_minimum_amount: '<?php echo $t['err_minimum_amount']; ?>'
    };

    // Initialize Stripe
    var stripe = Stripe('pk_test_51QnskfR2TFgANVZJDbrnOW6HWrKzUHAF16JP0r4GHFsbyCYXAvLDZ6NT2xwbANJz7F46DyLkBP8kEGDqM5GHg4i800ZapE3A0e');
    var elements = stripe.elements();

    // Create card element
    var card = elements.create('card', {
    hidePostalCode: false, // Enable postal code by default
    style: {
        base: {
            fontFamily: "'Jura', sans-serif",              
            '::placeholder': {               
                fontFamily: "'Jura', sans-serif"
            }
        },
        invalid: {
            color: '#d23100',
        }
    }
});
    card.mount('#card-element');

    // Track card country and adjust postal code requirement
    var requiresPostalCode = true; // Default to true until country is detected

    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        
        // Detect card country
        if (event.brand && event.country) {
            requiresPostalCode = event.country === 'US'; // Require postal code only for US cards
            if (!requiresPostalCode) {
                card.update({ hidePostalCode: true }); // Hide postal code for non-US cards
            } else {
                card.update({ hidePostalCode: false }); // Show postal code for US cards
            }
        }

        if (event.error) {
            var errorMessage;
            switch (event.error.code) {
                case 'incomplete_number':
                    errorMessage = jsTranslations.err_card_incomplete;
                    break;
                case 'incomplete_expiry':
                    errorMessage = jsTranslations.err_expiry_incomplete;
                    break;
                case 'invalid_expiry_year_past':
                case 'invalid_expiry_year':
                    errorMessage = jsTranslations.err_expiry_past;
                    break;
                case 'invalid_number':
                    errorMessage = jsTranslations.err_card_invalid;
                    break;
                case 'invalid_expiry':
                    errorMessage = jsTranslations.err_expiry_invalid;
                    break;
                case 'incomplete_cvc':
                    errorMessage = jsTranslations.err_cvc_incomplete;
                    break;
                case 'invalid_cvc':
                    errorMessage = jsTranslations.err_cvc_invalid;
                    break;
                case 'incomplete_zip':
                    errorMessage = requiresPostalCode ? jsTranslations.err_zip_incomplete : '';
                    break;
                case 'invalid_zip':
                    errorMessage = requiresPostalCode ? jsTranslations.err_zip_invalid : '';
                    break;
                default:
                    errorMessage = event.error.message;
            }
            displayError.textContent = errorMessage;
        } else {
            displayError.textContent = '';
        }
    });

    // Format donation amount
    function formatAmount(value) {
        let num = value.replace(/[^0-9]/g, '');
        if (num) {
            while (num.length < 3) {
                num = '0' + num;
            }
            num = num.slice(0, -2) + '.' + num.slice(-2);
            return num.replace(/^0+(?=\d)/, '');
        }
        return '0.00';
    }

    // Handle donation amount input
    var donationAmountInput = document.getElementById('donationAmount');

    donationAmountInput.addEventListener('click', function(event) {
        event.preventDefault();
        this.setSelectionRange(this.value.length, this.value.length);
    });

    donationAmountInput.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight' || 
            event.key === 'Home' || event.key === 'End') {
            event.preventDefault();
            this.setSelectionRange(this.value.length, this.value.length);
        }
    });

    donationAmountInput.addEventListener('input', function(event) {
        let value = this.value.replace(/[^0-9]/g, '');
        let formattedValue = formatAmount(value);
        this.value = '$' + formattedValue;
        this.setSelectionRange(this.value.length, this.value.length);
    });

    donationAmountInput.addEventListener('focus', function(event) {
        this.value = '$0.00';
        this.setSelectionRange(this.value.length, this.value.length);
    });

    // Handle form submission
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        var errorElement = document.getElementById('card-errors');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.id = 'card-errors';
            errorElement.className = 'error-message';
            const h1Element = form.querySelector('h1');
            h1Element.parentNode.insertBefore(errorElement, h1Element.nextSibling);
        }

        errorElement.textContent = '';
        errorElement.style.display = 'none';

        var donationAmount = document.getElementById('donationAmount').value.replace(/[\$,]/g, '');
        if (!donationAmount || donationAmount === '0.00' || parseFloat(donationAmount) < 1.00) {
            errorElement.textContent = jsTranslations.err_minimum_amount;
            errorElement.style.display = 'block';
            return;
        }

        var submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = jsTranslations.processing;

        stripe.createToken(card).then(function(result) {
            if (result.error) {
                var errorMessage;
                switch (result.error.code) {
                    case 'incomplete_number':
                        errorMessage = jsTranslations.err_card_incomplete;
                        break;
                    case 'incomplete_expiry':
                        errorMessage = jsTranslations.err_expiry_incomplete;
                        break;
                    case 'invalid_expiry_year_past':
                    case 'invalid_expiry_year':
                        errorMessage = jsTranslations.err_expiry_past;
                        break;
                    case 'invalid_number':
                        errorMessage = jsTranslations.err_card_invalid;
                        break;
                    case 'invalid_expiry':
                        errorMessage = jsTranslations.err_expiry_invalid;
                        break;
                    case 'incomplete_cvc':
                        errorMessage = jsTranslations.err_cvc_incomplete;
                        break;
                    case 'invalid_cvc':
                        errorMessage = jsTranslations.err_cvc_invalid;
                        break;
                    case 'incomplete_zip':
                        errorMessage = requiresPostalCode ? jsTranslations.err_zip_incomplete : '';
                        break;
                    case 'invalid_zip':
                        errorMessage = requiresPostalCode ? jsTranslations.err_zip_invalid : '';
                        break;
                    default:
                        errorMessage = result.error.message;
                }
                errorElement.textContent = errorMessage;
                errorElement.style.display = errorMessage ? 'block' : 'none';
                submitButton.disabled = false;
                submitButton.textContent = jsTranslations.donate_btn;
            } else {
                var tokenInput = document.createElement('input');
                tokenInput.setAttribute('type', 'hidden');
                tokenInput.setAttribute('name', 'stripeToken');
                tokenInput.setAttribute('value', result.token.id);
                form.appendChild(tokenInput);

                var amountInCents = Math.round(parseFloat(donationAmount) * 100);
                var amountInput = document.createElement('input');
                amountInput.setAttribute('type', 'hidden');
                amountInput.setAttribute('name', 'donationAmount');
                amountInput.setAttribute('value', amountInCents);
                form.appendChild(amountInput);

                form.submit();
            }
        });
    });
    </script>
</body>
</html>
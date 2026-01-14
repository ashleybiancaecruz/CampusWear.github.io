<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: auth.php');
    exit;
}

$pageTitle = 'Checkout';
include 'includes/header.php';
include 'includes/navbar.php';

$user_id = $_SESSION['user_id'];

$whereClause = "c.user_id = $user_id";
if (isset($_GET['items']) && !empty($_GET['items'])) {
    $itemIds = array_map('intval', explode(',', $_GET['items']));
    $ids = implode(',', $itemIds);
    $whereClause .= " AND c.cart_id IN ($ids)";
}

$sql = "SELECT c.*, m.name, m.description, m.price, m.stock, m.size, m.color, m.image, o.name as org_name, o.type as org_type
        FROM cart c
        JOIN merchandise m ON c.merchandise_id = m.merchandise_id
        JOIN organizations o ON m.org_id = o.organizations_id
        WHERE $whereClause
        ORDER BY c.created_at DESC";

$result = $conn->query($sql);
$cartItems = [];
$subtotal = 0;
$hasISCItem = false;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $subtotal += $row['price'] * $row['quantity'];
        if (strtolower($row['org_name']) === 'isc') {
            $hasISCItem = true;
        }
    }
}

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$discount = 0;
if ($hasISCItem && isset($_SESSION['email'])) {
    require_once __DIR__ . '/config/database_isc.php';
    $member = getISCMemberByEmail($_SESSION['email'], $conn, isset($isc_conn) ? $isc_conn : null);
    if ($member && $member['status'] === 'active') {
        foreach ($cartItems as $item) {
            if (strtolower($item['org_name']) === 'isc') {
                $discount += ($item['price'] * $item['quantity']) * 0.10;
            }
        }
    }
}

$total = $subtotal - $discount;

function getEstimatedDate($cartItems)
{
    $maxMin = 7;
    foreach ($cartItems as $item) {
        $n = strtolower($item['name']);
        if (strpos($n, 'hoodie') !== false || strpos($n, 'backpack') !== false)
            $maxMin = max($maxMin, 30);
        else if (strpos($n, 't-shirt') !== false)
            $maxMin = max($maxMin, 14);
    }
    return (new DateTime())->modify("+$maxMin days")->format('F j, Y');
}
$estimatedDate = getEstimatedDate($cartItems);
?>

<main class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <h4 class="fw-bold mb-4">Pick-Up & Verification</h4>

            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-primary text-white py-3 rounded-top-4">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2"></i>Personal Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" id="fullName" class="form-control"
                            value="<?php echo htmlspecialchars($_SESSION['user'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pickup Location</label>
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-primary">
                            <strong>COMSOC Office</strong><br>
                            <small class="text-muted">New Building, 3rd Floor, Room 304</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">+63</span>
                            <input type="tel" id="checkoutPhone" class="form-control" placeholder="9123456789">
                            <button class="btn btn-primary" type="button" id="sendOtpBtn" onclick="sendOTP()">Send OTP</button>
                        </div>
                        <div id="phoneStatus" class="mt-1 small"></div>
                    </div>

                    <div id="otpSection" class="d-none mt-3 p-3 border rounded bg-light">
                        <label class="form-label fw-bold text-warning">6-Digit Verification Code</label>
                        <div class="input-group">
                            <input type="text" id="otpCode" class="form-control text-center fw-bold" maxlength="6"
                                placeholder="000000">
                            <button class="btn btn-warning" id="verifyOtpBtn" type="button"
                                onclick="verifyOTP()">Verify</button>
                        </div>
                        <div id="otpStatus" class="mt-2 small"></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-dark text-white py-3 rounded-top-4">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>
                </div>
                <div class="card-body p-4 position-relative">
                    <div id="paymentOverlay"
                        class="position-absolute top-0 start-0 w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                        style="z-index: 100; pointer-events: all;">
                        <div class="text-center">
                            <i class="fas fa-lock fa-2x mb-2 text-muted"></i>
                            <p class="fw-bold text-muted small">Verify your phone to unlock payment</p>
                        </div>
                    </div>
                    <div id="paypal-button-container"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <h4 class="fw-bold mb-4">Order Summary</h4>
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <div>
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?> x
                                    ₱<?php echo number_format($item['price'], 2); ?></small>
                            </div>
                            <span
                                class="fw-bold">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span class="fw-bold">₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between text-success mb-2">
                            <span>ISC Discount (10%)</span>
                            <span class="fw-bold">-₱<?php echo number_format($discount, 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 fw-bold">Total</span>
                        <span class="h4 fw-bold text-primary"
                            id="finalPriceDisplay">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script
    src="https://www.paypal.com/sdk/js?client-id=AYLQHONCJFv-DSR1zoUP7YiiYG0FOmq_jOJcbN2JppMvsQJx-OvygnQmnGeFt5rNcR7zin5bPsVWwggX&currency=PHP"></script>

<script>
    window.isVerified = false;
    const cleanTotal = "<?php echo number_format($total, 2, '.', ''); ?>";

    function sendOTP() {
        const phone = document.getElementById('checkoutPhone').value;
        const phoneStatus = document.getElementById('phoneStatus');

        if (!phone) {
            alert("Please enter your phone number.");
            return;
        }

        phoneStatus.innerHTML = "Sending...";

        fetch('api/otp.php?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'phone': phone })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('otpSection').classList.remove('d-none');
                    phoneStatus.innerHTML = "<span class='text-success'>Code sent successfully!</span>";
                } else {
                    phoneStatus.innerHTML = `<span class='text-danger'>${data.message}</span>`;
                }
            })
            .catch(err => {
                phoneStatus.innerHTML = "<span class='text-danger'>Server Error. Check Console.</span>";
            });
    }

    function verifyOTP() {
        const otp = document.getElementById('otpCode').value;
        const phone = document.getElementById('checkoutPhone').value;
        const statusEl = document.getElementById('otpStatus');

        if (otp.length < 6) {
            alert("Please enter the 6-digit code.");
            return;
        }

        fetch('api/otp.php?action=verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'otp': otp, 'phone': phone })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.isVerified = true;
                    statusEl.innerHTML = "<span class='text-success fw-bold'>✓ OTP verified successfully!</span>";

                    if (typeof window.unlockPaypal === 'function') {
                        window.unlockPaypal();
                    }
                    const overlay = document.getElementById('paymentOverlay');
                    if (overlay) overlay.style.display = 'none';

                    ['otpCode', 'checkoutPhone', 'verifyOtpBtn', 'sendOtpBtn'].forEach(id => {
                        document.getElementById(id).disabled = true;
                    });
                } else {
                    window.isVerified = false;
                    statusEl.innerHTML = `<span class='text-danger'>${data.message || 'Invalid code.'}</span>`;
                }
            })
            .catch(err => console.error("Verify Error:", err));
    }

    paypal.Buttons({
        onInit: function (data, actions) {
            actions.disable();
            window.unlockPaypal = () => actions.enable();
        },
        onClick: (data, actions) => {
            if (!window.isVerified) {
                alert("You must verify your phone number before paying.");
                return actions.reject();
            }
        },
        createOrder: (data, actions) => {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        currency_code: 'PHP',
                        value: cleanTotal
                    }
                }]
            });
        },
        onApprove: (data, actions) => {
            return actions.order.capture().then(details => {
                const formData = new FormData();
                formData.append('orderID', data.orderID);
                formData.append('total_amount', details.purchase_units[0].amount.value);
                formData.append('discount', "<?php echo $discount; ?>");

                return fetch('api/paypal_verify.php?action=verify_cart', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    try {
                        const result = JSON.parse(text);
                        if (result.status === 'success') {
                            window.location.href = 'purchases.php';
                        } else {
                            alert("Error: " + result.message);
                        }
                    } catch (e) {
                        alert("Server Error! Check the Console (F12) for the raw response.");
                    }
                });
            });
        }
    }).render('#paypal-button-container');
</script>

<?php include 'includes/footer.php'; ?>
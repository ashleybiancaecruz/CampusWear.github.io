<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/config.php';

if (isset($_SESSION['user']) && isset($_SESSION['role'])) {
    $userRole = strtolower(trim($_SESSION['role']));
    if ($userRole === 'admin') {
        header('Location: admin_dashboard.php');
        exit;
    }
}

$pageTitle = 'Home';
include 'includes/header.php';
include 'includes/navbar.php';

$orgs = $conn->query("SELECT * FROM organizations ORDER BY type, name")->fetch_all(MYSQLI_ASSOC);
$merchandise = $conn->query("SELECT m.*, o.name as org_name, o.type as org_type 
                            FROM merchandise m 
                            JOIN organizations o ON m.org_id = o.organizations_id 
                            ORDER BY o.name, m.name")->fetch_all(MYSQLI_ASSOC);

$isLoggedIn = isset($_SESSION['user']) && isset($_SESSION['user_id']);
$currentUserName = isset($_SESSION['user']) ? $_SESSION['user'] : '';

$showISCBanner = true;
if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
    require_once __DIR__ . '/config/database_isc.php';
    if (function_exists('getISCMemberByEmail')) {
        $member = getISCMemberByEmail($_SESSION['email'], $conn, isset($isc_conn) ? $isc_conn : null);
        $showISCBanner = ($member === null);
    }
}
?>

<main class="container py-5">
    <?php if ($showISCBanner): ?>
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="alert alert-info border-0 shadow-sm rounded-4 p-4"
                    style="background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%); color: white;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap flex-md-nowrap">
                        <div class="mb-3 mb-md-0 me-md-4">
                            <h5 class="mb-2 fw-bold"><i class="fas fa-id-card me-2"></i>Join ISC and Get 10% Off!</h5>
                            <p class="mb-0 opacity-90">Apply for ISC membership to enjoy exclusive discounts on ISC merchandise.</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="isc_application.php?source=discount_notice"
                                class="btn btn-light rounded-pill px-4 fw-bold shadow-sm text-nowrap">
                                <i class="fas fa-file-alt me-2"></i>Apply Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center mb-5">
        <div class="col-lg-10">
            <div class="search-container mb-4 d-flex align-items-center bg-white p-2 rounded-pill shadow-sm border">
                <i class="fas fa-search text-muted ms-3 me-2"></i>
                <input type="text" id="merchSearch" class="form-control border-0 shadow-none"
                    onkeyup="filterMerchandise()" placeholder="Search merchandise...">
                <div class="filter-box border-start ps-3 ms-2 pe-3">
                    <select onchange="applyCategory(this.value)" class="form-select border-0 shadow-none cat-select">
                        <option value="all">All Organizations</option>
                        <?php
                        $types = ['academic', 'non-academic', 'isc'];
                        foreach ($types as $type): ?>
                            <optgroup label="<?= ucfirst($type) ?>">
                                <?php foreach ($orgs as $org):
                                    if ($org['type'] === $type): ?>
                                        <option value="<?= htmlspecialchars($org['name']) ?>"><?= htmlspecialchars($org['name']) ?></option>
                                    <?php endif; endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="user-nav-pills d-flex justify-content-center gap-2 flex-wrap">
                <div onclick="applyCategory('all', this)" class="pill-btn active border px-3 py-1 rounded-pill cursor-pointer">All Items</div>
                <div onclick="applyType('academic', this)" class="pill-btn border px-3 py-1 rounded-pill cursor-pointer">Academic</div>
                <div onclick="applyType('non-academic', this)" class="pill-btn border px-3 py-1 rounded-pill cursor-pointer">Non-Academic</div>
                <div onclick="applyType('isc', this)" class="pill-btn border px-3 py-1 rounded-pill cursor-pointer">ISC</div>
            </div>
        </div>
    </div>

    <div id="merchGrid" class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 mb-5">
        <?php foreach ($merchandise as $merch): ?>
            <div class="col merch-item" data-category="<?= htmlspecialchars($merch['org_name']) ?>"
                data-type="<?= htmlspecialchars($merch['org_type']) ?>"
                data-name="<?= strtolower(htmlspecialchars($merch['name'])) ?>">
                <div class="merch-card p-3 border rounded-4 bg-white h-100 d-flex flex-column shadow-sm">
                    <div class="merch-image mb-3 text-center bg-light rounded-3 overflow-hidden" style="height: 180px;">
                        <?php
                        $rawImageName = (!empty($merch['image']) && $merch['image'] !== 'default.jpg') ? $merch['image'] : 'default.png';
                        $imageName = htmlspecialchars($rawImageName, ENT_QUOTES, 'UTF-8');
                        $primaryPath = 'assets/images/merchandise/' . $imageName;
                        $fallbackPath = 'Merch/' . $imageName;
                        $finalPath = file_exists(__DIR__ . '/' . $primaryPath) ? $primaryPath : (file_exists(__DIR__ . '/' . $fallbackPath) ? $fallbackPath : 'assets/images/logo.png');
                        ?>
                        <img src="<?= $finalPath ?>" class="img-fluid h-100 object-fit-contain"
                            alt="<?= htmlspecialchars($merch['name']) ?>"
                            onerror="this.onerror=null; this.src='assets/images/logo.png';">
                    </div>

                    <span class="badge bg-secondary mb-2 align-self-start"><?= htmlspecialchars($merch['org_name']) ?></span>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($merch['name']) ?></h6>
                    <p class="text-muted small flex-grow-1"><?= htmlspecialchars(substr($merch['description'], 0, 50)) ?>...</p>
                    <div class="fw-bold mb-2 text-danger">₱<?= number_format($merch['price'], 2) ?></div>

                    <?php if ($merch['stock'] > 0): ?>
                        <?php if ($isLoggedIn): ?>
                            <div class="d-flex flex-column gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-pill"
                                    onclick="addToCart(<?= (int) $merch['merchandise_id'] ?>, 1)">
                                    <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                </button>
                                <button type="button" class="btn btn-primary btn-sm w-100 rounded-pill"
                                    onclick="openPurchaseModal(<?= (int) $merch['merchandise_id'] ?>, '<?= htmlspecialchars($merch['name'], ENT_QUOTES) ?>', <?= (float) $merch['price'] ?>, <?= (int) $merch['stock'] ?>, '<?= htmlspecialchars($merch['org_name'], ENT_QUOTES) ?>')">
                                    <i class="fas fa-bolt me-1"></i>Buy Now
                                </button>
                            </div>
                        <?php else: ?>
                            <a href="auth.php" class="btn btn-primary btn-sm w-100 rounded-pill"><i class="fas fa-sign-in-alt me-1"></i>Login to Purchase</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-sm w-100 rounded-pill" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 border-0 shadow-lg rounded-4">
            <h5 class="fw-bold mb-4" id="purchaseMerchName"></h5>
            <div class="mb-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Quantity</label>
                <div class="d-flex align-items-center gap-3">
                    <div class="input-group" style="width: 140px;">
                        <button type="button" class="btn btn-outline-secondary px-3" onclick="updatePurchaseQty(-1)">-</button>
                        <input type="text" class="form-control text-center bg-white fw-bold" id="purchaseQuantity" value="1" readonly>
                        <button type="button" class="btn btn-outline-secondary px-3" onclick="updatePurchaseQty(1)">+</button>
                    </div>
                    <span class="badge bg-light text-dark border fw-normal py-2 px-3" id="stockInfo"></span>
                </div>
            </div>
            <div class="p-3 rounded-3 mb-4" style="background-color: #f8f9fa; border-left: 5px solid #8B0000;">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted fw-bold">Total Amount:</span>
                    <strong class="h4 mb-0 text-danger" id="purchaseTotal">₱0.00</strong>
                </div>
            </div>
            <div class="d-grid">
                <button type="button" onclick="directPurchase()" class="btn btn-primary btn-lg rounded-pill fw-bold" style="background-color: #8B0000; border: none;">
                    Confirm & Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var merchandiseData = <?php echo json_encode($merchandise); ?>;
    var currentPurchaseData = {};
    var currentTypeFilter = 'all';
    var currentOrgFilter = 'all';

    function addToCart(id, qty) {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('merchandise_id', id);
        formData.append('quantity', qty);

        fetch('api/cart.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Item added to cart!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }

    function openPurchaseModal(id, name, price, stock, org) {
        currentPurchaseData = { id, name, price, stock, org };
        document.getElementById('purchaseMerchName').innerText = name;
        document.getElementById('purchaseQuantity').value = 1;
        document.getElementById('stockInfo').innerText = "Stock: " + stock;
        updateTotal();
        new bootstrap.Modal(document.getElementById('purchaseModal')).show();
    }

    function updatePurchaseQty(val) {
        let qtyInput = document.getElementById('purchaseQuantity');
        let newQty = (parseInt(qtyInput.value) || 1) + val;
        if (newQty >= 1 && newQty <= currentPurchaseData.stock) {
            qtyInput.value = newQty;
            updateTotal();
        }
    }

    function updateTotal() {
        let qty = parseInt(document.getElementById('purchaseQuantity').value) || 1;
        document.getElementById('purchaseTotal').innerText = "₱" + (qty * currentPurchaseData.price).toLocaleString(undefined, { minimumFractionDigits: 2 });
    }

    function directPurchase() {
        const qty = document.getElementById('purchaseQuantity').value;
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('merchandise_id', currentPurchaseData.id);
        formData.append('quantity', qty);

        fetch('api/cart.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'checkout.php?items=' + data.cart_id;
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }

    function filterMerchandise() {
        let input = document.getElementById('merchSearch').value.toLowerCase();
        let items = document.getElementsByClassName('merch-item');
        for (let item of items) {
            let name = item.getAttribute('data-name');
            item.style.display = name.includes(input) ? "block" : "none";
        }
    }

    function applyCategory(cat, element = null) {
        currentOrgFilter = cat || 'all';
        currentTypeFilter = 'all';
        document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
        if (element) element.classList.add('active');
        applyFilters();
    }

    function applyType(type, element = null) {
        currentTypeFilter = type || 'all';
        currentOrgFilter = 'all';
        document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
        if (element) element.classList.add('active');
        applyFilters();
    }

    function applyFilters() {
        const items = document.getElementsByClassName('merch-item');
        for (let item of items) {
            const itemType = (item.getAttribute('data-type') || '').toLowerCase();
            const itemOrg = item.getAttribute('data-category') || '';
            const matchesType = (currentTypeFilter === 'all' || itemType === currentTypeFilter);
            const matchesOrg = (currentOrgFilter === 'all' || itemOrg === currentOrgFilter);
            item.style.display = (matchesType && matchesOrg) ? "block" : "none";
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
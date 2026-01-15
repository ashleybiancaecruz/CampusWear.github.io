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
$currentUserName = isset($_SESSION['user']) ? $_SESSION['user'] : 'Guest';

$showISCBanner = true;
if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
    require_once __DIR__ . '/config/database_isc.php';
    if (function_exists('getISCMemberByEmail')) {
        $member = getISCMemberByEmail($_SESSION['email'], $conn, isset($isc_conn) ? $isc_conn : null);
        $showISCBanner = ($member === null);
    }
}
?>

<style>
    #aiButton {
        position: fixed; bottom: 30px; right: 30px; z-index: 9999;
        width: 65px; height: 65px; border-radius: 50%;
        background: #8B0000; color: white; display: flex !important;
        align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 5px 20px rgba(0,0,0,0.4);
        transition: transform 0.3s ease;
    }
    #aiButton:hover { transform: scale(1.1) rotate(5deg); }
    #aiWindow {
        position: fixed; bottom: 110px; right: 30px;
        width: 360px; height: 520px; background: white;
        z-index: 9999; display: none; flex-direction: column;
        border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        overflow: hidden; border: 1px solid rgba(0,0,0,0.1);
    }
    #aiWindow.active { display: flex !important; }
    #chatContent { flex-grow: 1; overflow-y: auto; padding: 15px; background: #fdfdfd; display: flex; flex-direction: column; gap: 12px; }
    .user-msg { background: #8B0000; color: white; padding: 10px 14px; border-radius: 18px 18px 0 18px; align-self: flex-end; max-width: 85%; font-size: 0.9rem; }
    .ai-msg { background: #f0f0f0; color: #333; padding: 10px 14px; border-radius: 18px 18px 18px 0; align-self: flex-start; max-width: 85%; font-size: 0.9rem; border: 1px solid #e0e0e0; }

    .merch-card { transition: all 0.3s ease; border: 1px solid #eee; background: #fff; }
    .merch-card:hover { transform: translateY(-8px); box-shadow: 0 12px 25px rgba(0,0,0,0.12); }
    .pill-btn { cursor: pointer; border: 1px solid #ddd; padding: 6px 18px; border-radius: 25px; transition: 0.3s; font-weight: 500; }
    .pill-btn.active { background-color: #8B0000; color: white; border-color: #8B0000; }
    
    .cart-badge-pop { animation: cartPop 0.4s ease; }
    @keyframes cartPop {
        0% { transform: scale(1); }
        50% { transform: scale(1.6); }
        100% { transform: scale(1); }
    }
</style>

<main class="container py-5">
    <?php if ($showISCBanner): ?>
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="alert border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #8B0000 0%, #d42a2a 100%); color: white;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h5 class="mb-2 fw-bold"><i class="fas fa-id-card me-2"></i>Join ISC and Get 10% Off!</h5>
                            <p class="mb-0 opacity-90">Verify your membership to unlock student discounts on all gear.</p>
                        </div>
                        <a href="isc_application.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm">Apply Now</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center mb-5">
        <div class="col-lg-10 text-center">
            <div class="input-group shadow-sm rounded-pill overflow-hidden border mb-4">
                <span class="input-group-text bg-white border-0 ps-4"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="merchSearch" class="form-control border-0 py-3 shadow-none" onkeyup="filterMerchandise()" placeholder="Search products, departments, or gear...">
            </div>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <div onclick="applyCategory('all', this)" class="pill-btn active">All Items</div>
                <div onclick="applyCategory('academic', this)" class="pill-btn">Academic</div>
                <div onclick="applyCategory('non-academic', this)" class="pill-btn">Non-Academic</div>
                <div onclick="applyCategory('isc', this)" class="pill-btn">ISC Special</div>
            </div>
        </div>
    </div>

    <div id="merchGrid" class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
        <?php foreach ($merchandise as $merch): ?>
            <div class="col merch-item" data-type="<?= htmlspecialchars($merch['org_type']) ?>" data-name="<?= strtolower(htmlspecialchars($merch['name'])) ?>">
                <div class="merch-card p-3 rounded-4 h-100 d-flex flex-column">
                    <div class="text-center mb-3 bg-light rounded-3 overflow-hidden" style="height: 180px;">
                        <img src="assets/images/merchandise/<?= htmlspecialchars($merch['image']) ?>" class="img-fluid h-100 object-fit-contain" onerror="this.src='assets/images/logo.png';">
                    </div>
                    <span class="text-muted extra-small text-uppercase fw-bold"><?= htmlspecialchars($merch['org_name']) ?></span>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($merch['name']) ?></h6>
                    <div class="fw-bold mb-3 text-danger mt-auto">₱<?= number_format($merch['price'], 2) ?></div>
                    
                    <div class="d-flex flex-column gap-2">
                        <?php if ($isLoggedIn): ?>
                            <button class="btn btn-outline-dark btn-sm rounded-pill" onclick="addToCart(<?= $merch['merchandise_id'] ?>)">
                                <i class="fas fa-cart-plus me-1"></i> Add to Cart
                            </button>
                            <button class="btn btn-danger btn-sm rounded-pill" onclick="openPurchaseModal(<?= $merch['merchandise_id'] ?>, '<?= addslashes($merch['name']) ?>', <?= $merch['price'] ?>, <?= $merch['stock'] ?>)">
                                Buy Now
                            </button>
                        <?php else: ?>
                            <a href="auth.php" class="btn btn-danger btn-sm rounded-pill text-center">Buy Now</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<div id="aiButton" onclick="toggleAI()"><i class="fas fa-robot fa-2x"></i></div>
<div id="aiWindow">
    <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: #8B0000;">
        <span class="fw-bold"><i class="fas fa-comment-dots me-2"></i>CampusSupport AI</span>
        <button onclick="toggleAI()" class="btn-close btn-close-white shadow-none"></button>
    </div>
    <div id="chatContent"></div>
    <div class="p-3 bg-white border-top">
        <div class="input-group">
            <input type="text" id="aiInput" class="form-control rounded-pill-start shadow-none" placeholder="Ask about products or discounts..." onkeypress="if(event.key === 'Enter') askAIEnglish()">
            <button class="btn btn-dark rounded-pill-end px-3" onclick="askAIEnglish()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 border-0 shadow-lg rounded-4">
            <h5 class="fw-bold mb-4" id="purchaseMerchName"></h5>
            <div class="mb-4">
                <label class="form-label fw-bold">Quantity</label>
                <div class="input-group" style="width: 150px;">
                    <button class="btn btn-outline-secondary" onclick="updateModalQty(-1)">-</button>
                    <input type="text" class="form-control text-center" id="purchaseQuantity" value="1" readonly>
                    <button class="btn btn-outline-secondary" onclick="updateModalQty(1)">+</button>
                </div>
                <small id="stockInfo" class="text-muted mt-2 d-block"></small>
            </div>
            <div class="p-3 rounded-3 mb-4 bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold text-secondary">Total:</span>
                <strong class="h4 mb-0 text-danger" id="purchaseTotal">₱0.00</strong>
            </div>
            <button onclick="confirmDirectPurchase()" class="btn btn-danger btn-lg rounded-pill w-100">Check Out Now</button>
        </div>
    </div>
</div>

<script>
    const merchData = <?php echo json_encode($merchandise); ?>;
    const currentUserName = "<?php echo htmlspecialchars($currentUserName); ?>";
    let activeItem = {};

    function toggleAI() {
        const win = document.getElementById('aiWindow');
        win.classList.toggle('active');
        if (win.classList.contains('active') && document.getElementById('chatContent').innerHTML === "") {
            addMsg("ai", `Hi <b>${currentUserName}</b>! Welcome to CampusWear. How can I assist you today?`);
        }
    }

    function askAIEnglish() {
        const input = document.getElementById('aiInput');
        const query = input.value.trim().toLowerCase();
        if (!query) return;

        addMsg("user", input.value);
        input.value = "";

        setTimeout(() => {
            if (["hi", "hello", "hey", "morning"].some(g => query.includes(g))) {
                addMsg("ai", "Hello! I can help you find specific department shirts or explain our ISC discounts.");
                return;
            }
            if (query.includes("discount") || query.includes("off") || query.includes("promo")) {
                addMsg("ai", "ISC members enjoy a 10% discount on all official ISC merchandise! Apply via the banner on top.");
                return;
            }
            const matches = merchData.filter(m => m.name.toLowerCase().includes(query) || m.org_name.toLowerCase().includes(query));
            if (matches.length > 0) {
                let list = `I found ${matches.length} items:<br><ul class="mt-2" style="padding-left:15px;">`;
                matches.forEach(m => list += `<li><b>${m.name}</b> - ₱${parseFloat(m.price).toLocaleString()}</li>`);
                addMsg("ai", list + "</ul>");
            } else {
                addMsg("ai", "I'm sorry, I couldn't find any merchandise matching that. Try searching for 'shirt' or 'CICS'.");
            }
        }, 600);
    }

    function addMsg(sender, text) {
        const chat = document.getElementById('chatContent');
        const d = document.createElement('div');
        d.className = sender === "user" ? "user-msg" : "ai-msg";
        d.innerHTML = text;
        chat.appendChild(d);
        chat.scrollTop = chat.scrollHeight;
    }

    function addToCart(id) {
        const f = new FormData();
        f.append('action', 'add');
        f.append('merchandise_id', id);
        f.append('quantity', 1);

        fetch('api/cart.php', { method: 'POST', body: f })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                updateBadge(); 
                alert('Item added to cart!');
            }
        });
    }

    function updateBadge() {
        fetch('api/get_cart_count.php')
        .then(res => res.json())
        .then(data => {
            const badge = document.querySelector('.cart-count, .badge-danger, .badge-pill');
            if (badge) {
                badge.innerText = data.count;
                badge.classList.remove('cart-badge-pop');
                void badge.offsetWidth; 
                badge.classList.add('cart-badge-pop');
                badge.style.display = data.count > 0 ? 'inline-block' : 'none';
            }
        });
    }

    function openPurchaseModal(id, name, price, stock) {
        activeItem = { id, name, price, stock };
        document.getElementById('purchaseMerchName').innerText = name;
        document.getElementById('purchaseQuantity').value = 1;
        document.getElementById('stockInfo').innerText = `Stock Available: ${stock}`;
        updateModalTotal();
        new bootstrap.Modal(document.getElementById('purchaseModal')).show();
    }

    function updateModalQty(v) {
        const input = document.getElementById('purchaseQuantity');
        let val = parseInt(input.value) + v;
        if (val >= 1 && val <= activeItem.stock) {
            input.value = val;
            updateModalTotal();
        }
    }

    function updateModalTotal() {
        const qty = parseInt(document.getElementById('purchaseQuantity').value);
        document.getElementById('purchaseTotal').innerText = "₱" + (qty * activeItem.price).toLocaleString();
    }

    function confirmDirectPurchase() {
        const qty = document.getElementById('purchaseQuantity').value;
        window.location.href = `checkout.php?merchandise_id=${activeItem.id}&qty=${qty}`;
    }

    function filterMerchandise() {
        const query = document.getElementById('merchSearch').value.toLowerCase();
        document.querySelectorAll('.merch-item').forEach(item => {
            item.style.display = item.dataset.name.includes(query) ? "block" : "none";
        });
    }

    function applyCategory(type, el) {
        document.querySelectorAll('.pill-btn').forEach(btn => btn.classList.remove('active'));
        el.classList.add('active');
        document.querySelectorAll('.merch-item').forEach(item => {
            item.style.display = (type === 'all' || item.dataset.type === type) ? "block" : "none";
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: auth.php');
    exit;
}

$pageTitle = 'My Cart';
include 'includes/header.php';
include 'includes/navbar.php';
<<<<<<< HEAD
?>

<div class="container py-4">
    <button onclick="history.back()" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Back
    </button>
<?php

$user_id = $_SESSION['user_id'];

=======

$user_id = $_SESSION['user_id'];
>>>>>>> ada1589 (changed some parts to make some clearer)
$sql = "SELECT c.*, m.name, m.description, m.price, m.stock, m.size, m.color, m.image, o.name as org_name, o.type as org_type
        FROM cart c
        JOIN merchandise m ON c.merchandise_id = m.merchandise_id
        JOIN organizations o ON m.org_id = o.organizations_id
        WHERE c.user_id = $user_id
        ORDER BY c.created_at DESC";

$result = $conn->query($sql);
$cartItems = [];
$total = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}
?>
<<<<<<< HEAD
<main class="container py-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart me-2"></i>My Shopping Cart</h2>

    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-shopping-basket fa-4x text-muted"></i>
            </div>
            <h4 class="text-muted">Your cart is empty</h4>
            <p class="text-muted mb-4">Looks like you haven't added any items yet.</p>
            <a href="index.php" class="btn btn-primary rounded-pill px-4 fw-bold">Start Shopping</a>
        </div>
    <?php else: ?>
     
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3" style="width: 50px;">
                                            <input type="checkbox" class="form-check-input" id="check-all" onchange="toggleAllItems(this)">
                                        </th>
                                        <th class="py-3">Product</th>
                                        <th class="py-3">Price</th>
                                        <th class="py-3">Quantity</th>
                                        <th class="py-3">Total</th>
                                        <th class="pe-4 py-3 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr id="cart-row-<?php echo $item['cart_id']; ?>">
                                            <td class="ps-4 py-3">
                                                <input type="checkbox" class="form-check-input cart-item-check" 
                                                       value="<?php echo $item['cart_id']; ?>" 
                                                       data-price="<?php echo $item['price']; ?>"
                                                       data-qty="<?php echo $item['quantity']; ?>"
                                                       onchange="recalculateTotal()">
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($item['org_name']); ?></small>
                                                        <?php if ($item['size']): ?>
                                                            <br><small class="text-muted">Size: <?php echo htmlspecialchars($item['size']); ?></small>
                                                        <?php endif; ?>
                                                        <?php if ($item['color']): ?>
                                                            <span class="text-muted"> | Color: <?php echo htmlspecialchars($item['color']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3">₱<?php echo number_format($item['price'], 2); ?></td>
                                            <td class="py-3">
                                                <div class="input-group input-group-sm" style="width: 120px;">
                                                    <button class="btn btn-qty btn-outline-secondary" type="button" 
                                                            onclick="updateQty(<?php echo $item['cart_id']; ?>, -1, <?php echo $item['stock']; ?>, <?php echo $item['price']; ?>)">
                                                        <span class="fw-bold">-</span>
                                                    </button>
                                                    <input type="text" class="form-control text-center" id="qty-<?php echo $item['cart_id']; ?>" 
                                                           value="<?php echo $item['quantity']; ?>" readonly>
                                                    <button class="btn btn-qty btn-outline-secondary" type="button" 
                                                            onclick="updateQty(<?php echo $item['cart_id']; ?>, 1, <?php echo $item['stock']; ?>, <?php echo $item['price']; ?>)">
                                                        <span class="fw-bold">+</span>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="py-3 fw-bold" id="item-total-<?php echo $item['cart_id']; ?>">
                                                ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </td>
                                            <td class="pe-4 py-3 text-end">
                                                <button class="btn btn-sm btn-outline-danger rounded-pill" 
                                                        onclick="removeCartItemPage(<?php echo $item['cart_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
=======

<div class="d-flex flex-column min-vh-100">
    <main class="container py-5 flex-grow-1">
        <button onclick="history.back()" class="btn btn-outline-secondary mb-4">
            <i class="fas fa-arrow-left me-2"></i>Back
        </button>

        <h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart me-2"></i>My Shopping Cart</h2>

        <?php if (empty($cartItems)): ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-shopping-basket fa-4x text-muted"></i>
                </div>
                <h4 class="text-muted">Your cart is empty</h4>
                <p class="text-muted mb-4">Looks like you haven't added any items yet.</p>
                <a href="index.php" class="btn btn-primary rounded-pill px-4 fw-bold">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3" style="width: 50px;">
                                                <input type="checkbox" class="form-check-input" id="check-all" onchange="toggleAllItems(this)">
                                            </th>
                                            <th class="py-3">Product</th>
                                            <th class="py-3">Price</th>
                                            <th class="py-3 text-center">Quantity</th>
                                            <th class="py-3">Total</th>
                                            <th class="pe-4 py-3 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $item): ?>
                                            <tr id="cart-row-<?php echo $item['cart_id']; ?>">
                                                <td class="ps-4 py-3">
                                                    <input type="checkbox" class="form-check-input cart-item-check" 
                                                           value="<?php echo $item['cart_id']; ?>" 
                                                           data-price="<?php echo $item['price']; ?>"
                                                           data-qty="<?php echo $item['quantity']; ?>"
                                                           onchange="recalculateTotal()">
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                            <small class="text-muted d-block"><?php echo htmlspecialchars($item['org_name']); ?></small>
                                                            <small class="text-muted">
                                                                <?php if ($item['size']) echo "Size: " . htmlspecialchars($item['size']); ?>
                                                                <?php if ($item['size'] && $item['color']) echo " | "; ?>
                                                                <?php if ($item['color']) echo "Color: " . htmlspecialchars($item['color']); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">₱<?php echo number_format($item['price'], 2); ?></td>
                                                <td class="py-3">
                                                    <div class="input-group input-group-sm mx-auto" style="width: 100px;">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="updateQty(<?php echo $item['cart_id']; ?>, -1, <?php echo $item['stock']; ?>, <?php echo $item['price']; ?>)">-</button>
                                                        <input type="text" class="form-control text-center" id="qty-<?php echo $item['cart_id']; ?>" value="<?php echo $item['quantity']; ?>" readonly>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="updateQty(<?php echo $item['cart_id']; ?>, 1, <?php echo $item['stock']; ?>, <?php echo $item['price']; ?>)">+</button>
                                                    </div>
                                                </td>
                                                <td class="py-3 fw-bold text-primary" id="item-total-<?php echo $item['cart_id']; ?>">
                                                    ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                                </td>
                                                <td class="pe-4 py-3 text-end">
                                                    <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="removeCartItemPage(<?php echo $item['cart_id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-dark text-white rounded-top-4 py-3">
                            <h5 class="fw-bold mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold" id="cart-subtotal">₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5 fw-bold">Total</span>
                                <span class="h5 fw-bold text-danger" id="cart-total">₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            <button onclick="proceedToCheckout()" class="btn btn-danger w-100 py-3 rounded-pill fw-bold shadow-sm">
                                Proceed to Checkout
                            </button>
>>>>>>> ada1589 (changed some parts to make some clearer)
                        </div>
                    </div>
                </div>
            </div>
<<<<<<< HEAD
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-gradient text-white rounded-top-4 py-3" style="background: var(--gradient-1);">
                        <h5 class="fw-bold mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold" id="cart-subtotal">₱<?php echo number_format($total, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="h5 fw-bold">Total</span>
                            <span class="h5 fw-bold" style="color: var(--primary-color);" id="cart-total">₱<?php echo number_format($total, 2); ?></span>
                        </div>
                        <a href="javascript:void(0)" onclick="proceedToCheckout()" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
=======
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</div>
>>>>>>> ada1589 (changed some parts to make some clearer)

<script>
function toggleAllItems(source) {
    const checkboxes = document.querySelectorAll('.cart-item-check');
    checkboxes.forEach(cb => cb.checked = source.checked);
    recalculateTotal();
}

function updateQty(cartId, change, maxStock, price) {
    const qtyInput = document.getElementById('qty-' + cartId);
<<<<<<< HEAD
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty + change;
    
    if (newQty < 1) return;
    if (newQty > maxStock) {
        alert('Max stock reached');
        return;
    }
    
=======
    let newQty = parseInt(qtyInput.value) + change;
    if (newQty < 1 || newQty > maxStock) return;
>>>>>>> ada1589 (changed some parts to make some clearer)
    updateCartItemPage(cartId, newQty, price);
}

function updateCartItemPage(cartId, quantity, price) {
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('quantity', quantity);
    
<<<<<<< HEAD
    fetch('api/cart.php?action=update', {
        method: 'POST',
        body: formData
    })
=======
    fetch('api/cart.php?action=update', { method: 'POST', body: formData })
>>>>>>> ada1589 (changed some parts to make some clearer)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('qty-' + cartId).value = quantity;
<<<<<<< HEAD
            
            const checkbox = document.querySelector(`.cart-item-check[value="${cartId}"]`);
            if (checkbox) checkbox.dataset.qty = quantity;
            
            const itemTotal = (price * quantity).toFixed(2);
            document.getElementById('item-total-' + cartId).innerText = '₱' + itemTotal;
            
            recalculateTotal();
            
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
        } else {
            alert(data.message);
=======
            const checkbox = document.querySelector(`.cart-item-check[value="${cartId}"]`);
            if (checkbox) checkbox.dataset.qty = quantity;
            document.getElementById('item-total-' + cartId).innerText = '₱' + (price * quantity).toFixed(2);
            recalculateTotal();
>>>>>>> ada1589 (changed some parts to make some clearer)
        }
    });
}

function removeCartItemPage(cartId) {
<<<<<<< HEAD
    if (!confirm('Are you sure you want to remove this item?')) return;
    
    const formData = new FormData();
    formData.append('cart_id', cartId);
    
    fetch('api/cart.php?action=delete', {
        method: 'POST',
        body: formData
    })
=======
    if (!confirm('Remove this item?')) return;
    const formData = new FormData();
    formData.append('cart_id', cartId);
    fetch('api/cart.php?action=delete', { method: 'POST', body: formData })
>>>>>>> ada1589 (changed some parts to make some clearer)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('cart-row-' + cartId).remove();
            recalculateTotal();
<<<<<<< HEAD
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            
            const rows = document.querySelectorAll('tbody tr');
            if (rows.length === 0) {
                location.reload();
            }
        } else {
            alert(data.message);
=======
            if (document.querySelectorAll('tbody tr').length === 0) location.reload();
>>>>>>> ada1589 (changed some parts to make some clearer)
        }
    });
}

function recalculateTotal() {
    let total = 0;
<<<<<<< HEAD
    const checkboxes = document.querySelectorAll('.cart-item-check:checked');
    
    checkboxes.forEach(cb => {
        const price = parseFloat(cb.dataset.price);
        const quantity = parseInt(cb.dataset.qty);
        total += price * quantity;
    });
    
    const formattedTotal = '₱' + total.toFixed(2);
    document.getElementById('cart-subtotal').innerText = formattedTotal;
    document.getElementById('cart-total').innerText = formattedTotal;
}

document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('check-all');
    if (checkAll) {
        checkAll.checked = true;
        toggleAllItems(checkAll);
    }
});

function proceedToCheckout() {
    const checkboxes = document.querySelectorAll('.cart-item-check:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one item to checkout.');
        return;
    }
    
    const selectedIds = Array.from(checkboxes).map(cb => cb.value).join(',');
    window.location.href = 'checkout.php?items=' + selectedIds;
}
</script>
<script src="assets/js/main.js"></script>
<?php include 'includes/footer.php'; ?>
=======
    document.querySelectorAll('.cart-item-check:checked').forEach(cb => {
        total += parseFloat(cb.dataset.price) * parseInt(cb.dataset.qty);
    });
    const formatted = '₱' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    document.getElementById('cart-subtotal').innerText = formatted;
    document.getElementById('cart-total').innerText = formatted;
}

function proceedToCheckout() {
    const selected = Array.from(document.querySelectorAll('.cart-item-check:checked')).map(cb => cb.value);
    if (selected.length === 0) return alert('Select items first.');
    window.location.href = 'checkout.php?items=' + selected.join(',');
}

document.addEventListener('DOMContentLoaded', () => {
    const all = document.getElementById('check-all');
    if (all) { all.checked = true; toggleAllItems(all); }
});
</script>
<script src="assets/js/main.js"></script>
</body>
</html>
>>>>>>> ada1589 (changed some parts to make some clearer)

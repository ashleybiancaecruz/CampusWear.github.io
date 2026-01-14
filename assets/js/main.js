function filterMerchandise() {
    const val = document.getElementById('merchSearch').value.toLowerCase();
    document.querySelectorAll('.merch-item').forEach(el => {
        const name = el.querySelector('.merch-name').innerText.toLowerCase();
        el.classList.toggle('hidden', !name.includes(val));
    });
}

const merchandiseData = [
    { id: 1, name: "AFBA Classic Polo Shirt", org_name: "AFBA", price: 350.00, stock: 15, description: "Official AFBA Polo", type: "polo", category: "apparel" },
    { id: 2, name: "AFBA Tote Bag", org_name: "AFBA", price: 150.00, stock: 20, description: "Durable AFBA Tote", type: "tote bag", category: "accessories" },
    { id: 3, name: "Arise and Shine Devotional Journal", org_name: "Arise", price: 250.00, stock: 10, description: "Daily Devotional", type: "journal", category: "stationery" },
    { id: 4, name: "Arise and Shine T-Shirt", org_name: "Arise", price: 300.00, stock: 25, description: "Inspirational Shirt", type: "t-shirt", category: "apparel" },
    { id: 5, name: "BHB Brotherhood Cup", org_name: "BHB", price: 180.00, stock: 12, description: "BHB Ceramic Cup", type: "cup", category: "accessories" },
    { id: 6, name: "BHB Brotherhood Hoodie", org_name: "BHB", price: 750.00, stock: 8, description: "Warm BHB Hoodie", type: "hoodie", category: "apparel" },
    { id: 7, name: "COMSOC Laptop Sticker Pack", org_name: "COMSOC", price: 50.00, stock: 100, description: "Tech Stickers", type: "laptop stickers", category: "accessories" },
    { id: 8, name: "COMSOC Tech Hoodie", org_name: "COMSOC", price: 800.00, stock: 5, description: "Premium Tech Hoodie", type: "hoodie", category: "apparel" },
    { id: 9, name: "CSC Cultural Tote Bag", org_name: "CSC", price: 150.00, stock: 30, description: "Cultural Print Tote", type: "tote bag", category: "accessories" },
    { id: 10, name: "CSC Cultural T-Shirt", org_name: "CSC", price: 320.00, stock: 20, description: "Official Cultural Shirt", type: "t-shirt", category: "apparel" },
    { id: 11, name: "DAS Drama T-Shirt", org_name: "DAS", price: 300.00, stock: 15, description: "Theatrical Shirt", type: "t-shirt", category: "apparel" },
    { id: 12, name: "DAS Script Notebook", org_name: "DAS", price: 120.00, stock: 40, description: "Journal for Scripts", type: "notebook", category: "stationery" },
    { id: 13, name: "Indak Batangan Dance Bag", org_name: "Indak", price: 450.00, stock: 10, description: "Durable Dance Bag", type: "dance bag", category: "accessories" },
    { id: 14, name: "Indak Batangan Dance T-Shirt", org_name: "Indak", price: 300.00, stock: 25, description: "Official Dance Shirt", type: "t-shirt", category: "apparel" },
    { id: 15, name: "ISC Executive Backpack", org_name: "ISC", price: 1200.00, stock: 5, description: "Professional Backpack", type: "backpack", category: "accessories" },
    { id: 16, name: "ISC Leadership Hoodie", org_name: "ISC", price: 850.00, stock: 10, description: "ISC Leader Hoodie", type: "hoodie", category: "apparel" },
    { id: 17, name: "ISC Leadership Pin Set", org_name: "ISC", price: 100.00, stock: 50, description: "Collector Pins", type: "pin set", category: "accessories" },
    { id: 18, name: "ISC Official Cap", org_name: "ISC", price: 250.00, stock: 20, description: "Classic ISC Cap", type: "cap", category: "accessories" },
    { id: 19, name: "ISC Official Polo Shirt", org_name: "ISC", price: 400.00, stock: 15, description: "ISC Formal Polo", type: "polo shirt", category: "apparel" },
    { id: 20, name: "ITECH Flash Drive", org_name: "ITECH", price: 450.00, stock: 30, description: "32GB Flash Drive", type: "flash drive", category: "tech" },
    { id: 21, name: "ITECH Programmer T-Shirt", org_name: "ITECH", price: 350.00, stock: 20, description: "Code-themed Shirt", type: "t-shirt", category: "apparel" },
    { id: 22, name: "JPIIE Educator Sweatshirts", org_name: "JPIIE", price: 650.00, stock: 12, description: "Cozy Sweatshirt", type: "sweatshirt", category: "apparel" },
    { id: 23, name: "JPIIE Notebook Set", org_name: "JPIIE", price: 200.00, stock: 25, description: "Study Notebooks", type: "notebook set", category: "stationery" },
    { id: 24, name: "Kataga Literacy T-Shirt", org_name: "Kataga", price: 300.00, stock: 18, description: "Literacy Advocate Shirt", type: "t-shirt", category: "apparel" },
    { id: 25, name: "KATAGA Writing Set", org_name: "Kataga", price: 150.00, stock: 30, description: "Pen and Paper Set", type: "writing set", category: "stationery" },
    { id: 26, name: "MOZART_S GUILD Music Note Cap", org_name: "Mozart", price: 280.00, stock: 15, description: "Musical Cap", type: "cap", category: "accessories" },
    { id: 27, name: "Mozart_s Guild Music T-Shirt", org_name: "Mozart", price: 320.00, stock: 22, description: "Music Lover Shirt", type: "t-shirt", category: "apparel" },
    { id: 28, name: "OECES Circuit Design T-Shirt", org_name: "OECES", price: 350.00, stock: 20, description: "Engineering Shirt", type: "t-shirt", category: "apparel" },
    { id: 29, name: "OECES Engineering Cap", org_name: "OECES", price: 250.00, stock: 15, description: "OECES Official Cap", type: "cap", category: "accessories" },
    { id: 30, name: "PSTO Tech Backpack", org_name: "PSTO", price: 950.00, stock: 8, description: "Tech-ready Backpack", type: "backpack", category: "accessories" },
    { id: 31, name: "PSTO Technical Polo", org_name: "PSTO", price: 420.00, stock: 12, description: "Official Technical Polo", type: "polo", category: "apparel" },
    { id: 32, name: "PSYCHSOC Mindful Hoodie", org_name: "PsychSoc", price: 780.00, stock: 10, description: "Comfort Hoodie", type: "hoodie", category: "apparel" },
    { id: 33, name: "PSYCHSOC Psychology Journal", org_name: "PsychSoc", price: 280.00, stock: 25, description: "Reflective Journal", type: "journal", category: "stationery" },
    { id: 34, name: "RESEARCHER REPORTER Notebook", org_name: "Research", price: 130.00, stock: 50, description: "Field Notebook", type: "notebook", category: "stationery" },
    { id: 35, name: "SDSS Leadership Polo", org_name: "SDSS", price: 380.00, stock: 15, description: "SDSS Official Polo", type: "polo", category: "apparel" },
    { id: 36, name: "SDSS Planner 2026", org_name: "SDSS", price: 350.00, stock: 60, description: "Yearly Planner", type: "planner", category: "stationery" },
    { id: 37, name: "SEES Engineering Tool Kit", org_name: "SEES", price: 550.00, stock: 10, description: "Essential Tools", type: "tool kit", category: "tech" },
    { id: 38, name: "SEES Power Up T-Shirt", org_name: "SEES", price: 300.00, stock: 25, description: "Energy-themed Shirt", type: "t-shirt", category: "apparel" },
    { id: 39, name: "Teatro Batangan Theater T-Shirt", org_name: "Teatro", price: 320.00, stock: 20, description: "Drama T-Shirt", type: "t-shirt", category: "apparel" },
    { id: 40, name: "Teatro Batangan Theatrical Mask", org_name: "Teatro", price: 200.00, stock: 15, description: "Stage Mask", type: "theatrical mask", category: "accessories" },
    { id: 41, name: "The Searcher Media T-Shirt", org_name: "Searcher", price: 300.00, stock: 18, description: "Media Team Shirt", type: "t-shirt", category: "apparel" },
    { id: 42, name: "YSFPA Lab Coat", org_name: "YSFPA", price: 500.00, stock: 30, description: "White Lab Coat", type: "lab coat", category: "apparel" },
    { id: 43, name: "YSFPA Scientific Calculator", org_name: "YSFPA", price: 850.00, stock: 12, description: "Calculated Precision", type: "calculator", category: "tech" },
    { id: 44, name: "YSLM Leadership Polo", org_name: "YSLM", price: 380.00, stock: 15, description: "YSLM Official Polo", type: "polo", category: "apparel" },
    { id: 45, name: "YSLM Service Badge Set", org_name: "YSLM", price: 120.00, stock: 40, description: "Official Badges", type: "pin set", category: "accessories" }
];

function applyCategory(cat, element = null) {
    document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
    
    if (element) {
        element.classList.add('active');
    } else if (event && event.target) {
        event.target.classList.add('active');
    }
    
    document.querySelectorAll('.merch-item').forEach(el => {
        if (cat === 'all') {
            el.classList.remove('hidden');
        } else if (cat === 'fav') {
            el.classList.toggle('hidden', el.getAttribute('data-fav') !== 'true');
        } else {
            const type = el.getAttribute('data-type');
            const category = el.getAttribute('data-category');
            el.classList.toggle('hidden', type !== cat && category !== cat);
        }
    });
}

function toggleFavorite(merchandiseId, btn) {
    const formData = new FormData();
    formData.append('merchandise_id', merchandiseId);
    
    fetch('api/favorites.php?action=toggle', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const item = btn.closest('.merch-item');
            item.setAttribute('data-fav', data.favorited);
            btn.classList.toggle('active', data.favorited);
        }
    });
}

function loadFavorites() {
    fetch('api/favorites.php?action=list')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            data.data.forEach(fav => {
                const item = document.querySelector(`[data-merch-id="${fav.merchandise_id}"]`);
                if (item) {
                    item.setAttribute('data-fav', 'true');
                    const btn = item.querySelector('.star-fav');
                    if (btn) btn.classList.add('active');
                }
            });
        }
    });
}

let currentPurchaseData = { merchandise_id: null, name: '', price: 0, stock: 0, orgName: '', isISCMember: false, discount: 0 };
let currentPriceNumeric = 0;

const leadTimes = [
    { key: 'tote bag', minDays: 7, maxDays: 7 },
    { key: 't-shirt', minDays: 14, maxDays: 14 },
    { key: 'backpack', minDays: 30, maxDays: 30 },
    { key: 'laptop stickers', minDays: 3, maxDays: 3 },
    { key: 'polo shirt', minDays: 7, maxDays: 7 },
    { key: 'journal', minDays: 7, maxDays: 7 },
    { key: 'cap', minDays: 14, maxDays: 14 },
    { key: 'hoodie', minDays: 1, maxDays: 30 },
    { key: 'notebook set', minDays: 14, maxDays: 14 },
    { key: 'notebook', minDays: 7, maxDays: 7 },
    { key: 'dance bag', minDays: 1, maxDays: 30 },
    { key: 'pin set', minDays: 21, maxDays: 21 },
    { key: 'flash drive', minDays: 14, maxDays: 21 },
    { key: 'sweatshirt', minDays: 14, maxDays: 14 },
    { key: 'writing set', minDays: 14, maxDays: 14 },
    { key: 'polo', minDays: 14, maxDays: 14 },
    { key: 'planner', minDays: 14, maxDays: 14 },
    { key: 'tool kit', minDays: 14, maxDays: 21 },
    { key: 'theatrical mask', minDays: 14, maxDays: 14 }
];

function getLeadTime(name) {
    const lowerName = name.toLowerCase();
    for (const item of leadTimes) {
        if (lowerName.includes(item.key)) return item;
    }
    return { minDays: 7, maxDays: 7 };
}

function updatePickupEstimate() {
    const estimateEl = document.getElementById('pickupEstimate');
    if (!estimateEl || !currentPurchaseData || !currentPurchaseData.name) return;

    const lead = getLeadTime(currentPurchaseData.name);
    const now = new Date();
    const minDate = new Date(now.getTime() + lead.minDays * 24 * 60 * 60 * 1000);
    const maxDate = new Date(now.getTime() + lead.maxDays * 24 * 60 * 60 * 1000);

    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    if (lead.minDays === lead.maxDays) {
        estimateEl.innerText = `Estimated pick-up: ${minDate.toLocaleDateString(undefined, options)} at COMSOC Office, New Building, 3rd Floor, Room 304.`;
    } else {
        estimateEl.innerText = `Estimated pick-up: between ${minDate.toLocaleDateString(undefined, options)} and ${maxDate.toLocaleDateString(undefined, options)} at COMSOC Office, New Building, 3rd Floor, Room 304.`;
    }
}

function openPurchaseModal(merchandiseId, name, price, stock, orgName) {
    currentPurchaseData = { merchandise_id: merchandiseId, name, price, stock, orgName: orgName || '', isISCMember: false };
    document.getElementById('purchaseMerchName').innerText = name;
    document.getElementById('purchasePrice').innerText = '₱' + price.toFixed(2);
    const qtyInput = document.getElementById('purchaseQuantity');
    qtyInput.value = 1;
    qtyInput.setAttribute('data-max', stock);
    document.getElementById('stockInfo').innerText = stock + ' available';
    document.getElementById('discountRow').classList.add('d-none');

    const pickupSelect = document.getElementById('pickupLocation');
    if (pickupSelect) pickupSelect.value = 'COMSOC Office, New Building, 3rd Floor, Room 304';
    updatePickupEstimate();
    
    const iscSection = document.getElementById('iscDiscountSection');
    const iscApplied = document.getElementById('iscDiscountApplied');
    const iscNotMember = document.getElementById('iscNotMember');

    if (iscApplied) iscApplied.classList.add('d-none');
    if (iscNotMember) iscNotMember.classList.add('d-none');

    if (orgName && orgName.toLowerCase() === 'isc') {
        if (iscSection) iscSection.classList.remove('d-none');
        checkISCMembership();
    } else {
        if (iscSection) iscSection.classList.add('d-none');
        currentPurchaseData.isISCMember = false;
        updatePurchaseTotal();
    }
    
    document.getElementById('proceedToPayBtn').classList.remove('hidden');
    document.getElementById('paypal-button-container').classList.add('hidden');
    document.getElementById('paypal-button-container').innerHTML = '';
    
    new bootstrap.Modal(document.getElementById('purchaseModal')).show();
}

function checkISCMembership() {
    const iscSection = document.getElementById('iscDiscountSection');
    const iscApplied = document.getElementById('iscDiscountApplied');
    const iscNotMember = document.getElementById('iscNotMember');

    if (!userEmail || typeof userEmail === 'undefined') {
        currentPurchaseData.isISCMember = false;
        if (iscSection) iscSection.classList.remove('d-none');
        if (iscApplied) iscApplied.classList.add('d-none');
        if (iscNotMember) iscNotMember.classList.remove('d-none');
        updatePurchaseTotal();
        return;
    }

    fetch('api/isc.php?action=check_member&email=' + encodeURIComponent(userEmail))
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.is_member) {
            currentPurchaseData.isISCMember = true;
            if (iscSection) iscSection.classList.remove('d-none');
            if (iscApplied) iscApplied.classList.remove('d-none');
            if (iscNotMember) iscNotMember.classList.add('d-none');
        } else {
            currentPurchaseData.isISCMember = false;
            if (iscSection) iscSection.classList.remove('d-none');
            if (iscApplied) iscApplied.classList.add('d-none');
            if (iscNotMember) iscNotMember.classList.remove('d-none');
        }
        updatePurchaseTotal();
    })
    .catch(error => {
        console.error('Error checking ISC membership:', error);
        currentPurchaseData.isISCMember = false;
        if (iscSection) iscSection.classList.remove('d-none');
        if (iscApplied) iscApplied.classList.add('d-none');
        if (iscNotMember) iscNotMember.classList.remove('d-none');
        updatePurchaseTotal();
    });
}

function closePurchaseModal() {
    bootstrap.Modal.getInstance(document.getElementById('purchaseModal')).hide();
}

function updatePurchaseQty(change) {
    const qtyInput = document.getElementById('purchaseQuantity');
    if (!qtyInput) {
        console.error('Quantity input not found');
        return;
    }
    
    const maxStock = parseInt(qtyInput.getAttribute('data-max')) || 999;
    let currentQty = parseInt(qtyInput.value) || 1;
    let newQty = currentQty + change;
    
    if (newQty < 1) {
        newQty = 1;
    }
    if (newQty > maxStock) {
        alert('Max stock reached (' + maxStock + ' available)');
        return;
    }
    
    qtyInput.value = newQty;
    updatePurchaseTotal();
}

function updatePurchaseTotal() {
    const qty = parseInt(document.getElementById('purchaseQuantity').value) || 1;
    const price = currentPurchaseData.price;
    let total = qty * price;
    let discount = 0;
    
    if (currentPurchaseData.isISCMember && 
        currentPurchaseData.orgName && 
        currentPurchaseData.orgName.toLowerCase() === 'isc') {
        discount = total * 0.10;
        total = total - discount;
        document.getElementById('discountRow').classList.remove('d-none');
        document.getElementById('discountAmount').innerText = '-₱' + discount.toFixed(2);
    } else {
        document.getElementById('discountRow').classList.add('d-none');
    }
    
    document.getElementById('purchaseQtyDisplay').innerText = qty;
    document.getElementById('purchaseTotal').innerText = '₱' + total.toFixed(2);
    currentPriceNumeric = total;
    currentPurchaseData.discount = discount;
}

let otpVerified = false;
let selectedPaymentMethod = '';

function sendOTP() {
    const phone = document.getElementById('checkoutPhone').value;
    if (!phone) {
        alert('Please enter your phone number');
        return;
    }
    
    const formData = new FormData();
    formData.append('phone', phone);
    
    fetch('api/otp.php?action=send', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const statusEl = document.getElementById('otpStatus');
        if (data.status === 'success') {
            statusEl.textContent = 'OTP sent successfully';
            statusEl.className = 'text-success';
            document.getElementById('otpSection').classList.remove('d-none');
        } else {
            statusEl.textContent = data.message || 'Failed to send OTP';
            statusEl.className = 'text-danger';
        }
    });
}

function verifyOTP() {
    const phone = document.getElementById('checkoutPhone').value;
    const otp = document.getElementById('otpCode').value;
    
    if (!phone || !otp) {
        alert('Please enter phone number and OTP');
        return;
    }
    
    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('otp', otp);
    
    fetch('api/otp.php?action=verify', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const statusEl = document.getElementById('otpStatus');
        if (data.status === 'success') {
            otpVerified = true;
            statusEl.textContent = 'OTP verified successfully';
            statusEl.className = 'text-success';
        } else {
            otpVerified = false;
            statusEl.textContent = data.message || 'Invalid OTP';
            statusEl.className = 'text-danger';
        }
    });
}

function unlockPayment() {
    if (!otpVerified) {
        alert('Please verify your phone number with OTP first');
        return;
    }
    
    document.getElementById('proceedToPayBtn').classList.add('hidden');
    document.getElementById('payment-methods').classList.remove('d-none');
}

function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    
    if (method === 'paypal') {
        document.getElementById('paypal-button-container').classList.remove('hidden');
        renderPayPalButtons();
    }
}

function unlockPayPal() {
    unlockPayment();
    selectPaymentMethod('paypal');
}

function renderPayPalButtons() {
    document.getElementById('paypal-button-container').innerHTML = '';
    
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: currentPriceNumeric.toFixed(2),
                        currency_code: 'PHP'
                    },
                    description: 'CampusWear - ' + currentPurchaseData.name
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                const orderID = data.orderID;
                const payer = details.payer;
                const transaction = details.purchase_units[0].payments.captures[0];
                finalizePurchaseWithPayPal(orderID, transaction.id, details);
            });
        },
        onCancel: function(data) {
            alert('Payment cancelled. You can try again.');
        },
        onError: function(err) {
            console.error('PayPal Error:', err);
            alert('Payment error. Please try again.');
            document.getElementById('proceedToPayBtn').classList.remove('hidden');
            document.getElementById('paypal-button-container').classList.add('hidden');
        }
    }).render('#paypal-button-container');
}

function finalizePurchaseWithPayPal(orderID, transactionID, paypalDetails) {
    if (!currentPurchaseData || !currentPurchaseData.merchandise_id) {
        alert('Error: Purchase data not found. Please refresh and try again.');
        console.error('currentPurchaseData:', currentPurchaseData);
        return;
    }
    
    const qty = parseInt(document.getElementById('purchaseQuantity').value) || 1;
    const pickup = document.getElementById('pickupLocation').value || 'COMSOC Office, New Building, 3rd Floor, Room 304';
    const discount = currentPurchaseData.discount || 0;
    
    const formData = new FormData();
    formData.append('orderID', orderID);
    formData.append('merchandise_id', currentPurchaseData.merchandise_id);
    formData.append('quantity', qty);
    formData.append('shipping_address', pickup);
    formData.append('total_amount', currentPriceNumeric);
    formData.append('discount', discount);
    
    const paypalContainer = document.getElementById('paypal-button-container');
    paypalContainer.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--primary-color);"></i><p class="mt-2">Processing payment...</p></div>';
    
    fetch('api/paypal_verify.php?action=verify', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            paypalContainer.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Payment successful! Your order has been placed.</div>';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('purchaseModal')).hide();
                window.location.reload();
            }, 2000);
        } else {
            paypalContainer.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + data.message + '</div>';
            setTimeout(() => {
                document.getElementById('proceedToPayBtn').classList.remove('hidden');
                document.getElementById('paypal-button-container').classList.add('hidden');
                document.getElementById('paypal-button-container').innerHTML = '';
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        paypalContainer.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>An error occurred. Please try again.</div>';
        setTimeout(() => {
            document.getElementById('proceedToPayBtn').classList.remove('hidden');
            document.getElementById('paypal-button-container').classList.add('hidden');
            document.getElementById('paypal-button-container').innerHTML = '';
        }, 3000);
    });
}

function deleteHistory(id) {
    if (!confirm('Remove this purchase from your history?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('api/purchases.php?action=delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('card-' + id).remove();
            if (document.querySelectorAll('.history-card').length === 0) {
                document.getElementById('histBody').innerHTML = '<div class="text-center py-5 text-muted">No purchases found.</div>';
            }
        }
    });
}

function openHistory() {
    new bootstrap.Modal(document.getElementById('historyModal')).show();
}

function addToCart(merchandiseId, quantity, redirectOnSuccess) {
    const formData = new FormData();
    formData.append('merchandise_id', merchandiseId);
    formData.append('quantity', quantity);

    fetch('api/cart.php?action=add', {
        method: 'POST',
        body: formData
    })
        .then(async (response) => {
            if (response.status === 401) {
                alert('Please log in to add items to your cart.');
                window.location.href = 'auth.php';
                return null;
            }
            return response.json();
        })
        .then((data) => {
            if (!data) return;
            if (data.status === 'success') {
                updateCartCount();
                if (redirectOnSuccess) {
                    if (data.cart_id) {
                        window.location.href = 'checkout.php?items=' + data.cart_id;
                    } else {
                        window.location.href = 'cart.php';
                    }
                } else {
                    alert('Item added to cart');
                }
            } else {
                alert(data.message || 'Failed to add item to cart');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('An error occurred while adding the item to your cart.');
        });
}

function updateCartCount() {
    fetch('api/cart.php')
        .then(async (response) => {
            if (response.status === 401) {
                const countEl = document.getElementById('cartCount');
                if (countEl) {
                    countEl.textContent = '0';
                    countEl.style.display = 'none';
                }
                return null;
            }
            if (!response.ok) {
                return null;
            }
            return response.json();
        })
        .then((data) => {
            if (!data || data.status !== 'success' || !Array.isArray(data.data)) return;
            const count = data.data.length;
            const countEl = document.getElementById('cartCount');
            if (countEl) {
                countEl.textContent = count;
                countEl.style.display = count > 0 ? 'inline' : 'none';
            }
        })
        .catch((error) => {
            console.error('Error updating cart count:', error);
        });
}

function openCart() {
    window.location.href = 'cart.php';
}


function removeCartItem(cartId) {
    if (!confirm('Remove this item from cart?')) return;
    
    const formData = new FormData();
    formData.append('cart_id', cartId);
    
    fetch('api/cart.php?action=delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('cart-item-' + cartId).remove();
            updateCartCount();
            openCart();
        } else {
            alert(data.message || 'Failed to remove item');
        }
    });
}

function checkoutFromCart() {
    window.location.href = 'checkout.php';
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof updateCartCount === 'function') {
            updateCartCount();
            setInterval(updateCartCount, 30000);
        }
        
        var aiInput = document.getElementById('aiInput');
        if (aiInput) {
            aiInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    if (typeof askAI === 'function') {
                        askAI();
                    } else if (typeof askAIEnhanced === 'function') {
                        askAIEnhanced();
                    }
                }
            });
            var chat = document.getElementById('chatContent');
            if (chat && !chat.dataset.greeted) {
                var name = (typeof currentUserName !== 'undefined' && currentUserName) ? currentUserName : 'Guest';
                chat.innerHTML += '<div class="bg-white p-3 rounded-4 border small shadow-sm me-auto mb-2" style="max-width:85%">Hi ' + name + '! I can help you find merchandise by name, organization, or type. Try asking for ISC hoodie or BHB cap.</div>';
                chat.dataset.greeted = 'true';
            }
        }
    });
}

function toggleAI() {
    document.getElementById('aiWindow').classList.toggle('active');
}

function askAI() {
    if (typeof askAIEnhanced === 'function' && typeof merchandiseData !== 'undefined') {
        askAIEnhanced();
    } else {
        const input = document.getElementById('aiInput');
        const chat = document.getElementById('chatContent');
        const userText = input.value.trim();
        
        if (!userText) return;
        
        chat.innerHTML += `<div style="background: var(--primary-color); color: white; padding: 0.75rem; border-radius: 1rem; margin-left: auto; font-size: 0.875rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 0.5rem; max-width:85%">${userText}</div>`;
        input.value = '';
        
        setTimeout(() => {
            let response = '';
            const query = userText.toLowerCase();
            
            const matches = merchandiseData.filter(m => 
                m.name.toLowerCase().includes(query) || 
                m.description.toLowerCase().includes(query) ||
                m.org_name.toLowerCase().includes(query)
            );

            if ((query.includes('cap') || query.includes('caps')) && merchandiseData.length) {
                const capItems = merchandiseData.filter(m => m.name.toLowerCase().includes('cap'));
                if (capItems.length > 0) {
                    response = 'Available caps:\n' + capItems.map(item => {
                        return `• <strong>${item.name}</strong> (${item.org_name}) - ₱${item.price} | Stock: ${item.stock}`;
                    }).join('<br>');
                }
            }
            
            if (!response) {
                const isHoodieQuery = query.includes('hoodie') && !query.match(/\b(isc|afba|comsoc|itech|jpii|oeces|psychsoc|sees|psto|ysfpa|csc|sdss|arise|bhb|indak|das|kataga|mozart|teatro|searcher|yslm)\b/i);
                
                if (isHoodieQuery) {
                    const hoodies = merchandiseData.filter(m => m.name.toLowerCase().includes('hoodie'));
                    if (hoodies.length > 0) {
                        response = 'Available hoodies: ' + hoodies.map(m => `${m.name} (${m.org_name}) - ₱${m.price}`).join(', ');
                    } else {
                        response = 'No hoodies found in our inventory.';
                    }
                } else if (matches.length === 1) {
                    const m = matches[0];
                    response = `${m.name} from ${m.org_name} - ${m.description} Price: ₱${m.price}. Stock: ${m.stock} available.`;
                } else if (matches.length > 1) {
                    response = 'I found these items: ' + matches.slice(0, 6).map(m => `${m.name} (${m.org_name}) - ₱${m.price} | Stock: ${m.stock}`).join(', ');
                } else if (query.includes('organization') || query.includes('org')) {
                    const orgs = [...new Set(merchandiseData.map(m => m.org_name))];
                    response = `We have merchandise from ${orgs.length} organizations: ${orgs.slice(0, 8).join(', ')} and more.`;
                } else if (query.includes('price') || query.includes('cost')) {
                    response = 'Our merchandise prices range from ₱150 to ₱850. You can filter by organization to see specific items and prices.';
                } else {
                    response = 'I can help you find merchandise by name, organization, or price. Try asking about specific items like AFBA polo, ISC hoodie, or just type the item name like cap.';
                }
            }
            
            chat.innerHTML += `<div class="bg-white p-3 rounded-4 border small shadow-sm me-auto mb-2" style="max-width:85%">${response}</div>`;
            chat.scrollTop = chat.scrollHeight;
        }, 400);
    }
}

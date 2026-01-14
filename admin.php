<?php
require_once 'config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: auth.php');
    exit;
}

$userRole = strtolower(trim($_SESSION['role'] ?? 'user'));
if ($userRole !== 'admin') {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Admin Panel - Merchandise Management';
include 'includes/header.php';
include 'includes/admin_navbar.php';

$orgs = $conn->query("SELECT * FROM organizations ORDER BY type, name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-5">
    <a href="javascript:history.back()" class="btn-back mb-3">
        <i class="fas fa-arrow-left"></i>Back
    </a>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="fw-bold" style="color: var(--primary-color);">Admin Panel - Merchandise Management</h2>
        <button class="btn btn-primary rounded-pill" onclick="showAddForm()">
            <i class="fas fa-plus me-2"></i>Add New Merchandise
        </button>
    </div>

    <div id="merchForm" class="admin-card hidden mb-4">
        <h4 class="fw-bold mb-3" id="formTitle">Add New Merchandise</h4>
        <form id="merchFormData" onsubmit="saveMerchandise(event)">
            <input type="hidden" id="formMerchId" name="id">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Organization</label>
                    <select class="form-select" id="formOrgId" name="org_id" required>
                        <option value="">Select Organization</option>
                        <?php foreach ($orgs as $org): ?>
                            <option value="<?php echo $org['organizations_id']; ?>"><?php echo htmlspecialchars($org['name']); ?> (<?php echo $org['type']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Name</label>
                    <input type="text" class="form-control" id="formName" name="name" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Description</label>
                <textarea class="form-control" id="formDescription" name="description" rows="3" required></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Price (₱)</label>
                    <input type="number" class="form-control" id="formPrice" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Stock</label>
                    <input type="number" class="form-control" id="formStock" name="stock" min="0" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Size</label>
                    <input type="text" class="form-control" id="formSize" name="size" placeholder="e.g. S, M, L, XL">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Color</label>
                    <input type="text" class="form-control" id="formColor" name="color" placeholder="e.g. Black, White, Navy">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Image</label>
                <input type="file" class="form-control" id="formImage" name="image" accept="image/*">
                <small class="text-muted">Upload product image (PNG, JPG). Leave empty to keep existing image.</small>
                <div id="imagePreview" class="mt-2"></div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill">
                    <i class="fas fa-save me-2"></i>Save
                </button>
                <button type="button" class="btn btn-secondary rounded-pill" onclick="cancelForm()">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-white" style="background: var(--primary-color);">
                    <h5 class="mb-0 fw-bold">All Merchandise</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Organization</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="merchTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadMerchandise();
    });

    function loadMerchandise() {
        fetch('api/merchandise.php?action=list')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayMerchandise(data.data);
            }
        });
    }

    function displayMerchandise(merchandise) {
        const tbody = document.getElementById('merchTableBody');
        
        if (merchandise.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No merchandise found.</td></tr>';
            return;
        }
        
        tbody.innerHTML = merchandise.map(merch => `
            <tr>
                <td>${merch.merchandise_id}</td>
                <td>
                    <span class="org-badge ${merch.org_type}">${merch.org_name}</span>
                </td>
                <td>${merch.name}</td>
                <td>₱${parseFloat(merch.price).toFixed(2)}</td>
                <td>
                    <span class="stock-badge ${merch.stock > 10 ? 'in-stock' : merch.stock > 0 ? 'low-stock' : 'out-of-stock'}">
                        ${merch.stock}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning me-2" onclick="editMerchandise(${merch.merchandise_id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteMerchandise(${merch.merchandise_id}, '${merch.name.replace(/'/g, "\\'")}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function showAddForm() {
        document.getElementById('formTitle').innerText = 'Add New Merchandise';
        document.getElementById('merchFormData').reset();
        document.getElementById('formMerchId').value = '';
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('merchForm').classList.remove('hidden');
        document.getElementById('merchForm').scrollIntoView({ behavior: 'smooth' });
    }

    function editMerchandise(id) {
        fetch(`api/merchandise.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const merch = data.data;
                document.getElementById('formTitle').innerText = 'Edit Merchandise';
                document.getElementById('formMerchId').value = merch.merchandise_id;
                document.getElementById('formOrgId').value = merch.org_id;
                document.getElementById('formName').value = merch.name;
                document.getElementById('formDescription').value = merch.description;
                document.getElementById('formPrice').value = merch.price;
                document.getElementById('formStock').value = merch.stock;
                document.getElementById('formSize').value = merch.size || '';
                document.getElementById('formColor').value = merch.color || '';
                if (merch.image && merch.image !== 'default.jpg') {
                    document.getElementById('imagePreview').innerHTML = '<img src="assets/images/merchandise/' + merch.image + '" class="img-thumbnail" style="max-height: 150px;">';
                } else {
                    document.getElementById('imagePreview').innerHTML = '';
                }
                document.getElementById('merchForm').classList.remove('hidden');
                document.getElementById('merchForm').scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    function saveMerchandise(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const imageFile = document.getElementById('formImage').files[0];
        if (imageFile) {
            formData.append('image_file', imageFile);
        }
        
        const action = formData.get('id') ? 'update' : 'create';
        
        fetch(`api/merchandise.php?action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Merchandise saved successfully!');
                cancelForm();
                loadMerchandise();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving merchandise.');
        });
    }
    
    document.getElementById('formImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 150px;">';
            };
            reader.readAsDataURL(file);
        }
    });

    function deleteMerchandise(id, name) {
        if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            return;
        }
        
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('api/merchandise.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Merchandise deleted successfully!');
                loadMerchandise();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }

    function cancelForm() {
        document.getElementById('merchForm').classList.add('hidden');
        document.getElementById('merchFormData').reset();
        document.getElementById('imagePreview').innerHTML = '';
    }
</script>

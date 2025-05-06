<?php
// ../noblehome_quotation/order.php
include '../connection/connection.php';

$product       = null;
$query         = '';
$error         = '';
$defaultImage  = '../img/image_alt.jpg';
$productImage  = $defaultImage;

if (!empty($_GET['query'])) {
    $query = $conn->real_escape_string(trim($_GET['query']));
    $sql = "
        SELECT * FROM noble_products
        WHERE code LIKE '%{$query}%'
           OR product_name LIKE '%{$query}%'
        LIMIT 1
    ";
    $res = $conn->query($sql);
    if ($res && $res->num_rows) {
        $product = $res->fetch_assoc();
        if (!empty($product['product_image'])) {
            $productImage = 'data:image/jpeg;base64,' .
                             base64_encode($product['product_image']);
        }
    } else {
        $error = "No product found matching '{$query}'.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Search and Add Product – NobleHome</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-black font-sans">
  <header class="bg-black text-orange-500 py-4 shadow-md">
    <div class="container mx-auto text-center">
      <h1 class="text-3xl font-bold">NobleHome</h1>
    </div>
  </header>

  <main class="container mx-auto p-6 space-y-6">
    <!-- Modal Trigger -->
    <button id="openModal"
            class="px-4 py-2 bg-orange-500 text-black rounded-lg font-semibold shadow hover:opacity-90">
      Add Product
    </button>

    <!-- Search Product -->
    <section class="space-y-4">
      <h2 class="text-2xl font-semibold">Search Product</h2>
      <form method="GET" action=""
            class="flex flex-col sm:flex-row sm:items-center gap-4">
        <input type="text" name="query" id="searchInput"
               placeholder="Enter code or name" list="product_suggestions"
               value="<?= htmlspecialchars($query) ?>" required
               class="flex-grow px-4 py-2 border border-black rounded-lg focus:ring-2 focus:ring-orange-500">
        <datalist id="product_suggestions"></datalist>
        <button type="submit"
                class="px-6 py-2 bg-orange-500 text-black rounded-lg font-bold hover:opacity-90">
          Search
        </button>
      </form>
      <?php if ($error): ?>
        <p class="text-red-600"><?= $error ?></p>
      <?php endif; ?>
    </section>

    <!-- Product Display -->
    <?php if ($product): ?>
    <section class="bg-gray-100 rounded-xl shadow-md p-6 flex flex-col md:flex-row gap-6">
      <img src="<?= $productImage ?>" alt="Product Image"
           class="w-full md:w-40 rounded-lg object-cover">
      <div class="flex-1 space-y-2">
        <p><strong>Code:</strong> <?= $product['code'] ?></p>
        <p><strong>Name:</strong> <?= $product['product_name'] ?></p>
        <p><strong>Description:</strong> <?= $product['description'] ?></p>
        <p><strong>Unit:</strong> <?= $product['unit'] ?? '—' ?></p>
        <p><strong>Price:</strong> ₱<?= number_format($product['product_price'],2) ?></p>
        <p><strong>Category:</strong> <?= $product['category'] ?></p>
        <hr class="my-4" />
        <div class="flex items-center gap-4">
          <label for="qty" class="font-medium">Quantity:</label>
          <input type="number" id="qty" name="qty" value="1" min="1"
                 class="w-20 px-2 py-1 border border-black rounded-lg focus:ring-2 focus:ring-orange-500">
          <button type="button" id="addToCart"
                  data-name="<?= htmlspecialchars($product['product_name']) ?>"
                  data-description="<?= htmlspecialchars($product['description']) ?>"
                  data-unit="<?= htmlspecialchars($product['unit'] ?? '') ?>"
                  data-price="<?= $product['product_price'] ?>"
                  data-img="<?= $productImage ?>"
                  class="px-4 py-2 bg-orange-500 text-black rounded-lg font-semibold hover:opacity-90">
            Add to Cart
          </button>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- Quotation Form -->
    <form method="POST" action="generate_excel.php" id="quotationForm" class="space-y-8">
      <!-- Client & Project Details -->
      <section class="space-y-4 bg-gray-50 p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold">Client &amp; Project Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-sm font-medium text-gray-700">Client Name</span>
            <input type="text" name="client_name" placeholder="Client Name"
                   class="w-full mt-1 px-3 py-2 border border-black rounded-lg focus:outline-none" />
          </label>
          <label class="block">
            <span class="text-sm font-medium text-gray-700">Project Scope</span>
            <input type="text" name="project_scope" placeholder="Project Scope"
                   class="w-full mt-1 px-3 py-2 border border-black rounded-lg focus:outline-none" />
          </label>
          <label class="block">
            <span class="text-sm font-medium text-gray-700">Order Date</span>
            <input type="date" name="order_date" value="<?= date('Y-m-d') ?>"
                   class="w-full mt-1 px-3 py-2 border border-black rounded-lg focus:outline-none" />
          </label>
          <label class="block">
            <span class="text-sm font-medium text-gray-700">Contact No.</span>
            <input type="text" name="contact_no" placeholder="Contact No."
                   class="w-full mt-1 px-3 py-2 border border-black rounded-lg focus:outline-none" />
          </label>
          <label class="block">
            <span class="text-sm font-medium text-gray-700">Address</span>
            <input type="text" name="address" placeholder="Address"
                   class="w-full mt-1 px-3 py-2 border border-black rounded-lg focus:outline-none" />
          </label>
          <label class="block">
            <span class="text-sm font-medium text-gray-700">Type of Installation</span>
            <input type="text" name="install_type" placeholder="Type of Installation"
                   class="w-full mt-1 px-3 py-2 border border-black rounded-lg focus:outline-none" />
          </label>
        </div>
      </section>

      <!-- Cart Section -->
      <section class="space-y-4">
        <h2 class="text-2xl font-semibold">Cart</h2>
        <div class="overflow-x-auto">
          <table id="cartTable" class="min-w-full table-auto border-collapse">
            <thead class="bg-black text-orange-500">
              <tr>
                <th class="px-6 py-3 text-left">Image</th>
                <th class="px-6 py-3 text-left">Name</th>
                <th class="px-6 py-3 text-left">Description</th>
                <th class="px-6 py-3 text-right">Unit Price</th>
                <th class="px-6 py-3 text-center">Qty</th>
                <th class="px-6 py-3 text-center">Unit</th>
                <th class="px-6 py-3 text-right">Subtotal</th>
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y"></tbody>
          </table>
        </div>

        <!-- Totals & Employee & Download -->
        <div class="space-y-4">
          <div class="text-right space-y-2">
            <p><strong>Total:</strong> ₱<span id="total">0.00</span></p>
            <div class="flex justify-end items-center gap-4">
              <label class="font-medium">Discount (%):</label>
              <input type="number" id="discount" name="discount" value="0" min="0" max="100"
                     class="w-24 px-2 py-1 border border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>
            <p><strong>Final Total:</strong> ₱<span id="finalTotal">0.00</span></p>
          </div>
          <div class="bg-gray-50 p-6 rounded-lg shadow space-y-4">
            <label class="block">
              <span class="text-sm font-medium text-gray-700">Employee Name</span>
              <input type="text" name="employee_name" id="employee_name"
                     placeholder="Enter your name"
                     class="w-full mt-1 px-3 py-2 border border-black rounded-lg focus:outline-none" />
            </label>
            <div class="flex justify-end">
              <button type="submit"
                      class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                Download Quotation Excel
              </button>
            </div>
            <input type="hidden" name="cart_data" id="cart_data" />
            <input type="hidden" name="quotation_no" value="<?= date('YmdHis') ?>" />
          </div>
        </div>
      </section>
    </form>
  </main>

  <!-- Modal -->
  <div id="modal"
       class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-11/12 max-w-2xl max-h-[85vh]
                overflow-y-auto flex flex-col">
      <div class="flex justify-between items-center bg-black text-orange-500 px-6 py-4 rounded-t-2xl">
        <h2 class="text-2xl font-semibold">Add Product</h2>
        <button id="closeModal" class="text-3xl leading-none">&times;</button>
      </div>
      <div id="modalBody" class="p-6 flex-1">
        <p class="text-center">Loading...</p>
      </div>
    </div>
  </div>

  <script>
    // --- Cart & Totals Logic ---
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    function updateCartTable() {
      const tbody = document.querySelector('#cartTable tbody');
      tbody.innerHTML = '';
      let total = 0;

      cart.forEach((item, idx) => {
        const row = document.createElement('tr');

        // Image
        const tdImg = document.createElement('td');
        tdImg.className = 'px-6 py-3';
        const img = document.createElement('img');
        img.src = item.img;
        img.className = 'w-16 h-16 object-cover rounded';
        tdImg.append(img);
        row.append(tdImg);

        // Name & Description
        ['name','description'].forEach(key => {
          const td = document.createElement('td');
          td.className = 'px-6 py-3';
          td.innerText = item[key];
          row.append(td);
        });

        // Unit Price
        const tdPrice = document.createElement('td');
        tdPrice.className = 'px-6 py-3 text-right';
        tdPrice.innerText = parseFloat(item.price).toFixed(2);
        row.append(tdPrice);

        // Quantity
        const tdQty = document.createElement('td');
        tdQty.className = 'px-6 py-3 text-center';
        const inpQty = document.createElement('input');
        inpQty.type = 'number'; inpQty.min = 1; inpQty.value = item.quantity;
        inpQty.className = 'w-16 px-2 py-1 border rounded-lg';
        inpQty.onchange = () => {
          item.quantity = parseInt(inpQty.value) || 1;
          updateCartTable();
        };
        tdQty.append(inpQty);
        row.append(tdQty);

        // Unit
        const tdUnit = document.createElement('td');
        tdUnit.className = 'px-6 py-3 text-center';
        tdUnit.innerText = item.unit || '';
        row.append(tdUnit);

        // Subtotal
        const subtotal = item.quantity * parseFloat(item.price);
        total += subtotal;
        const tdSub = document.createElement('td');
        tdSub.className = 'px-6 py-3 text-right';
        tdSub.innerText = subtotal.toFixed(2);
        row.append(tdSub);

        // Actions
        const tdAct = document.createElement('td');
        tdAct.className = 'px-6 py-3 text-center';
        const btnRm = document.createElement('button');
        btnRm.innerText = 'Remove';
        btnRm.className = 'px-3 py-1 bg-red-500 text-white rounded-lg';
        btnRm.onclick = () => {
          cart.splice(idx, 1);
          updateCartTable();
        };
        tdAct.append(btnRm);
        row.append(tdAct);

        tbody.append(row);
      });

      document.getElementById('total').innerText = total.toFixed(2);
      updateFinalTotal();
      localStorage.setItem('cart', JSON.stringify(cart));
    }

    function updateFinalTotal() {
      const total = parseFloat(document.getElementById('total').innerText) || 0;
      const disc  = parseFloat(document.getElementById('discount').value) || 0;
      const final = total - total * disc / 100;
      document.getElementById('finalTotal').innerText = final.toFixed(2);
    }

    // Add From Search
    document.getElementById('addToCart')?.addEventListener('click', function(){
      const qty = parseInt(document.getElementById('qty').value) || 1;
      const item = {
        name:        this.dataset.name,
        description: this.dataset.description,
        unit:        this.dataset.unit,
        price:       this.dataset.price,
        img:         this.dataset.img,
        quantity:    qty
      };
      cart.push(item);
      updateCartTable();
    });

    // Serialize cart before form submit
    document.getElementById('quotationForm')
      .addEventListener('submit', () => {
        document.getElementById('cart_data').value = JSON.stringify(cart);
      });

    // Discount input listener
    document.getElementById('discount')
      .addEventListener('input', updateFinalTotal);

    // Initial render
    updateCartTable();

    // Fetch suggestions (unchanged)
    const searchInput = document.getElementById('searchInput');
    const dataList    = document.getElementById('product_suggestions');
    searchInput?.addEventListener('input', () => {
      const q = searchInput.value.trim();
      dataList.innerHTML = '';
      if (!q) return;
      fetch(`product_suggestions.php?query=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(list => {
          list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            dataList.append(opt);
          });
        });
    });
    searchInput?.addEventListener('focus', () => searchInput.dispatchEvent(new Event('input')));

    // Modal logic (unchanged)
    const openModal  = document.getElementById('openModal');
    const closeModal = document.getElementById('closeModal');
    const modal      = document.getElementById('modal');
    const body       = document.getElementById('modalBody');

    openModal.addEventListener('click', () => {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      fetch('../noblehome_insert/show.php')
        .then(r => r.text())
        .then(html => {
          body.innerHTML = html;
          attachImagePreview();
          attachFormSubmission();
        });
    });

    closeModal.addEventListener('click', () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    });
    window.addEventListener('click', e => {
      if (e.target === modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    });

    function attachImagePreview() {
      const img     = body.querySelector('#imagePreview');
      const inp     = body.querySelector('#productImageFile');
      const wrapper = body.querySelector('#imgUpload');
      if (!img || !inp || !wrapper) return;
      wrapper.addEventListener('click', () => inp.click());
      inp.addEventListener('change', () => {
        const file = inp.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => img.src = e.target.result;
        reader.readAsDataURL(file);
      });
    }

    function attachFormSubmission() {
      const form = body.querySelector('#addProductForm');
      const errP = body.querySelector('.form-error');
      if (!form) return;
      form.addEventListener('submit', async e => {
        e.preventDefault();
        errP.classList.add('hidden');
        const fd = new FormData(form);
        const res = await fetch('../noblehome_insert/show.php', {
          method: 'POST', body: fd
        });
        const json = await res.json();
        if (json.success) {
          alert(json.message);
          modal.classList.add('hidden');
          updateCartTable();
        } else {
          errP.textContent = json.message;
          errP.classList.remove('hidden');
        }
      });
    }
  </script>
</body>
</html>

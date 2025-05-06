<?php
// ../order/order.php
include '../config/db_conn.php';

// Client/project defaults
$clientName   = '';
$projectScope = '';
$orderDate    = date('Y-m-d');
$contactNo    = '';
$address      = '';
$installType  = '';

// Search/item vars
$item       = null;
$query      = '';
$error      = '';
$defaultImg = '../img/image_alt.jpg';
$itemImage  = $defaultImg;

// Handle search
if (!empty($_GET['query'])) {
    $query = $conn->real_escape_string(trim($_GET['query']));
    $stmt  = $conn->prepare("
      SELECT * FROM items
       WHERE item_code LIKE CONCAT('%', ?, '%')
          OR item_name LIKE CONCAT('%', ?, '%')
       LIMIT 1
    ");
    $stmt->bind_param('ss', $query, $query);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows) {
        $item = $res->fetch_assoc();

        // *** NEW: sniff real MIME from the blob ***
        if (!empty($item['item_image'])) {
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($item['item_image']);
            $b64      = base64_encode($item['item_image']);
            $itemImage = "data:{$mimeType};base64,{$b64}";
        }
    } else {
        $error = "No item found matching '{$query}'.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Order – Modular Cabinets</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">
  <header class="bg-blue-900 text-yellow-400 py-4 text-center text-2xl font-bold">
    Real<span class="text-blue-300">iving</span>
  </header>

  <div class="max-w-7xl mx-auto mt-6 bg-white p-6 rounded-xl shadow-lg space-y-8">
    <!-- Add Product Button -->
    <div class="flex justify-end">
      <button id="openModal" class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-2 px-4 rounded-md">
        Add Product
      </button>
    </div>
    <!-- Search Form -->
    <section class="space-y-2">
      <form method="GET" class="flex gap-2 items-center">
        <input
          type="text"
          name="query"
          id="searchInput"
          list="item_suggestions"
          value="<?= htmlspecialchars($query) ?>"
          placeholder="Enter item code or name"
          required
          class="flex-grow px-3 py-2 border border-blue-900 rounded-md focus:outline-none" />
        <datalist id="item_suggestions"></datalist>
        <button
          type="submit"
          class="bg-blue-900 hover:bg-blue-800 text-yellow-400 font-bold py-2 px-4 rounded-md">
          Search
        </button>
      </form>
      <?php if ($error): ?>
        <p class="text-red-600"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
    </section>

    <!-- Item Card (only shows if $item is found) -->
    <?php if ($item): ?>
      <div id="itemCard" class="flex flex-col md:flex-row bg-gray-100 rounded-lg shadow p-4">
        <img src="<?= $itemImage ?>" alt="Item Image"
             class="w-full md:w-48 h-48 object-cover rounded-md mb-4 md:mb-0"/>
        <div class="md:ml-6 flex-1 space-y-4">
          <p><strong>Code:</strong> <?= htmlspecialchars($item['item_code']) ?></p>
          <p><strong>Name:</strong> <?= htmlspecialchars($item['item_name']) ?></p>
          <p><strong>Description:</strong> <?= htmlspecialchars($item['item_description']) ?></p>

          <!-- Editable Area -->
          <label class="block">
            <span class="text-sm font-medium text-gray-700">Area (Lm./Sqm.)</span>
            <input
              type="number"
              id="areaInput"
              step="0.01"
              placeholder="Enter area"
              class="w-full mt-1 px-3 py-2 border border-blue-900 rounded-md focus:outline-none" />
          </label>

          <p><strong>Unit:</strong> <?= htmlspecialchars($item['item_unit']) ?></p>
          <p><strong>Category:</strong> <?= htmlspecialchars($item['item_category']) ?></p>
          <hr class="my-2"/>

          <div class="flex items-center space-x-2">
            <input
              type="number"
              id="qty"
              min="1"
              value="1"
              class="w-20 px-2 py-1 border border-blue-900 rounded-md focus:outline-none" />
            <button
              id="addToCart"
              data-code="<?= htmlspecialchars($item['item_code']) ?>"
              data-name="<?= htmlspecialchars($item['item_name']) ?>"
              data-desc="<?= htmlspecialchars($item['item_description']) ?>"
              data-unit="<?= htmlspecialchars($item['item_unit']) ?>"
              data-category="<?= htmlspecialchars($item['item_category']) ?>"
              data-price="<?= $item['item_price'] ?>"
              data-labor="<?= $item['item_labor_cost'] ?>"
              data-img="<?= $itemImage ?>"
              class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-2 px-4 rounded-md">
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  <form method="POST" action="generate_pdf.php" id="quotationForm">
    <!-- Client & Project Details -->
    <section class="space-y-4">
      <h1 class="text-3xl font-bold text-blue-900">Client &amp; Project Details</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="block">
          <span class="text-sm font-medium text-gray-700">Client Name</span>
          <input type="text" name="client_name" placeholder="Client Name"
                 class="w-full mt-1 px-3 py-2 border border-blue-900 rounded-md focus:outline-none" />
        </label>
        <label class="block">
          <span class="text-sm font-medium text-gray-700">Project Scope</span>
          <input type="text" name="project_scope" placeholder="Project Scope"
                 class="w-full mt-1 px-3 py-2 border border-blue-900 rounded-md focus:outline-none" />
        </label>
        <label class="block">
          <span class="text-sm font-medium text-gray-700">Order Date</span>
          <input type="date" name="order_date" value="<?= $orderDate ?>"
                 class="w-full mt-1 px-3 py-2 border border-blue-900 rounded-md focus:outline-none" />
        </label>
        <label class="block">
          <span class="text-sm font-medium text-gray-700">Contact No.</span>
          <input type="text" name="contact_no" placeholder="Contact No."
                 class="w-full mt-1 px-3 py-2 border border-blue-900 rounded-md focus:outline-none" />
        </label>
        <label class="block">
          <span class="text-sm font-medium text-gray-700">Address</span>
          <input type="text" name="address" placeholder="Address"
                 class="w-full mt-1 px-3 py-2 border border-blue-900 rounded-md focus:outline-none" />
        </label>
        <label class="block">
          <span class="text-sm font-medium text-gray-700">Type of Installation</span>
          <input type="text" name="install_type" placeholder="Type of Installation"
                 class="w-full mt-1 px-3 py-2 border border-blue-900 rounded-md focus:outline-none" />
        </label>
      </div>
    </section>

    

    

    <!-- Cart Table -->
    <section>
      <h2 class="text-2xl font-bold text-blue-900 mb-4">Cart</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-blue-900 text-yellow-400">
            <tr>
              <th class="px-3 py-2">Image</th>
              <th class="px-3 py-2">No.</th>
              <th class="px-3 py-2">Item</th>
              <th class="px-3 py-2">Description</th>
              <th class="px-3 py-2">Area</th>
              <th class="px-3 py-2">Unit</th>
              <th class="px-3 py-2">Qty</th>
              <th class="px-3 py-2">Unit Price</th>
              <th class="px-3 py-2">Cabinet Cost</th>
              <th class="px-3 py-2">Labor Cost/Unit</th>
              <th class="px-3 py-2">Total Labor</th>
              <th class="px-3 py-2">Total Amount</th>
              <th class="px-3 py-2">Actions</th>
            </tr>
          </thead>
          <tbody id="cartTable" class="bg-white divide-y divide-gray-200"></tbody>
        </table>
      </div>

      <!-- Totals -->
      <div class="mt-6 space-y-3 max-w-md ml-auto">
        <div class="flex justify-between">
          <span class="font-semibold">Grand Total:</span>
          <span>₱<span id="grandTotal">0.00</span></span>
        </div>
        <div class="flex justify-between items-center">
          <label class="font-semibold" for="discount">Discount (%):</label>
          <input
            type="number"
            id="discount"
            name="discount" 
            value="0"
            min="0"
            max="100"
            class="w-20 px-2 py-1 border border-blue-900 rounded-md focus:outline-none" />
        </div>
        <div class="flex justify-between">
          <span class="font-semibold">Total Labor Cost:</span>
          <span>₱<span id="laborTotal">0.00</span></span>
        </div>
        <div class="flex justify-between">
          <span class="font-semibold">Final Total:</span>
          <span class="text-xl font-bold">₱<span id="finalTotal">0.00</span></span>
        </div>
      </div>
    </section>

<!-- Employee Name -->
<div class="mt-6 max-w-md ml-auto">
    <label class="block mb-2">
      <span class="text-sm font-medium text-gray-700">Employee Name</span>
      <input type="text" name="employee_name" id="employee_name"
             class="w-full mt-1 px-3 py-2 border border-blue-900 rounded-md focus:outline-none"
             placeholder="Enter your name" />
    </label>
  </div>

  <div class="flex justify-end mt-4">
    <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md">
      Download Quotation PDF
    </button>
  </div>
  <input type="hidden" name="cart_data" id="cart_data" />
</form>

  <!-- Modal -->
  <div id="modal"
       class="fixed inset-0 bg-black bg-opacity-60 hidden items-start justify-center overflow-auto p-6">
    <div class="bg-white w-full max-w-4xl rounded-xl overflow-hidden shadow-lg">
      <div class="flex justify-between bg-blue-900 text-yellow-400 px-4 py-2">
        <h3 class="text-lg">Add New Item</h3>
        <button id="closeModal" class="text-2xl text-white">&times;</button>
      </div>
      <div id="modalBody" class="p-4 max-h-[80vh] overflow-y-auto">
        <p class="text-center text-gray-500">Loading…</p>
      </div>
    </div>
  </div>

  <script>
    const defaultImg = '<?= $defaultImg ?>';
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Always push a new entry, never merge
    document.getElementById('addToCart')?.addEventListener('click', function(){
      const qty     = parseInt(document.getElementById('qty').value) || 1;
      const areaVal = parseFloat(document.getElementById('areaInput').value) || 0;
      const itm = {
        code:     this.dataset.code,
        name:     this.dataset.name,
        desc:     this.dataset.desc,
        area:     areaVal,
        unit:     this.dataset.unit,
        category: this.dataset.category,
        price:    parseFloat(this.dataset.price),
        labor:    parseFloat(this.dataset.labor),
        img:      this.dataset.img,
        quantity: qty
      };
      // Always add a new line, even if same code
      cart.push(itm);
      updateCartTable();
    });

    // Renders the cart, grouped by category
    function updateCartTable() {
  const tbody = document.getElementById('cartTable');
  tbody.innerHTML = '';

  // Build category groups with original indexes
  const groups = {};
  const order = [];
  cart.forEach((it, idx) => {
    if (!(it.category in groups)) {
      groups[it.category] = [];
      order.push(it.category);
    }
    groups[it.category].push({ item: it, idx });
  });

  let grand = 0, laborSum = 0;
  let rowCount = 0;

  order.forEach(cat => {
    // Category header
    const hdr = document.createElement('tr');
    hdr.innerHTML = `
      <th colspan="13"
          class="bg-blue-900 text-yellow-400 px-3 py-2 text-left uppercase font-semibold">
        ${cat}
      </th>`;
    tbody.appendChild(hdr);

    // Items in this category
    groups[cat].forEach(({ item: it, idx }) => {
      rowCount++;
      const cab = it.area * it.price * it.quantity;
      const lab = it.area * it.labor * it.quantity;
      const line = cab + lab;
      grand += line;
      laborSum += lab;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-3 py-2 text-center">
          <img src="${it.img||defaultImg}" class="w-12 h-12 object-cover rounded"/>
        </td>
        <td class="px-3 py-2 text-center">${rowCount}</td>
        <td class="px-3 py-2">${it.name}</td>
        <td class="px-3 py-2">${it.desc}</td>
        <td class="px-3 py-2 text-center">
          <input type="number" step="0.01" value="${it.area.toFixed(3)}"
                 class="w-20 px-1 py-0.5 border border-blue-900 rounded"
                 onchange="updateArea(${idx}, this.value)"/>
        </td>
        <td class="px-3 py-2 text-center">${it.unit}</td>
        <td class="px-3 py-2 text-center">
          <input type="number" min="1" value="${it.quantity}"
                 class="w-16 px-1 py-0.5 border border-blue-900 rounded"
                 onchange="changeQty(${idx}, this.value)"/>
        </td>
        <td class="px-3 py-2 text-right">
          <input type="number" step="0.01" value="${it.price.toFixed(2)}"
                 class="w-20 px-1 py-0.5 border border-blue-900 rounded text-right"
                 onchange="updatePrice(${idx}, this.value)"/>
        </td>
        <td class="px-3 py-2 text-right">${cab.toFixed(2)}</td>
        <td class="px-3 py-2 text-right">
          <input type="number" step="0.01" value="${it.labor.toFixed(2)}"
                 class="w-20 px-1 py-0.5 border border-blue-900 rounded text-right"
                 onchange="updateLabor(${idx}, this.value)"/>
        </td>
        <td class="px-3 py-2 text-right">${lab.toFixed(2)}</td>
        <td class="px-3 py-2 text-right font-semibold">${line.toFixed(2)}</td>
        <td class="px-3 py-2 text-center">
          <button onclick="removeItem(${idx})"
                  class="text-red-600 hover:underline">
            Remove
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  });

  // Update totals and final
  document.getElementById('grandTotal').innerText = grand.toFixed(2);
  document.getElementById('laborTotal').innerText = laborSum.toFixed(2);
  updateFinalTotal();

  // Persist and sync
  localStorage.setItem('cart', JSON.stringify(cart));
  syncCartData();
}


    function updateArea(i,v){ cart[i].area = parseFloat(v)||0; updateCartTable(); }
    function changeQty(i,v){ cart[i].quantity = parseInt(v)||1; updateCartTable(); }
    function updatePrice(i,v){ cart[i].price = parseFloat(v)||0; updateCartTable(); }
    function updateLabor(i,v){ cart[i].labor = parseFloat(v)||0; updateCartTable(); }
    function removeItem(i){ cart.splice(i,1); updateCartTable(); }

    // Discount & Final Total
    document.getElementById('discount')?.addEventListener('input', updateFinalTotal);
    function updateFinalTotal() {
      const g = parseFloat(document.getElementById('grandTotal').innerText)||0;
      const d = parseFloat(document.getElementById('discount').value)||0;
      document.getElementById('finalTotal').innerText = (g - g*d/100).toFixed(2);
    }

    updateCartTable();

    function syncCartData() {
  document.getElementById('cart_data').value = JSON.stringify(cart);
}
  
// at end of updateCartTable():
  syncCartData();

    // Auto‐scroll to the item card if one was loaded
    <?php if ($item): ?>
    window.addEventListener('DOMContentLoaded', () => {
      document.getElementById('itemCard').scrollIntoView({ behavior: 'smooth' });
    });
    <?php endif; ?>

    // --- Modal Logic ---
    const modal     = document.getElementById('modal');
    const openBtn   = document.getElementById('openModal');
    const closeBtn  = document.getElementById('closeModal');
    const body      = document.getElementById('modalBody');

    openBtn.addEventListener('click', () => {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      fetch('../items_insertion/show.php')
        .then(r => r.text())
        .then(html => {
          body.innerHTML = html;
          attachImagePreview();
          attachFormSubmission();
        });
    });
    closeBtn.addEventListener('click', () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    });
    window.addEventListener('click', e => {
      if (e.target === modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    });

    // --- Functions pulled in from show.php ---
    function attachImagePreview() {
      const img = body.querySelector('#imagePreview');
      const inp = body.querySelector('#fileInput');
      if (!img || !inp) return;
      inp.addEventListener('change', function(e){
        const f = this.files[0];
        if (!f) return;
        const r = new FileReader();
        r.onload = ev => img.src = ev.target.result;
        r.readAsDataURL(f);
      });
    }
    function attachFormSubmission() {
      const form = body.querySelector('form#addItemForm');
      const errP = body.querySelector('.form-error');
      if (!form) return;
      form.addEventListener('submit', e => {
        e.preventDefault();
        errP.classList.add('hidden');
        const fd = new FormData(form);
        fetch('../items_insertion/show.php', { method: 'POST', body: fd })
          .then(r => r.json())
          .then(json => {
            if (json.success) {
              alert(json.message);
              modal.classList.add('hidden');
              modal.classList.remove('flex');
            } else {
              errP.textContent = json.message;
              errP.classList.remove('hidden');
            }
          })
          .catch(() => {
            errP.textContent = 'Unexpected error.';
            errP.classList.remove('hidden');
          });
      });
    }
  </script>
</body>
</html>

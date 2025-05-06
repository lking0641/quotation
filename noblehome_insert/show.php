<?php
// ../noblehome_insert/show.php
include '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $code       = trim($_POST['product_code'] ?? '');
    $name       = trim($_POST['product_name'] ?? '');
    $unit       = trim($_POST['product_unit'] ?? '');
    $desc       = trim($_POST['product_description'] ?? '');
    $price      = floatval($_POST['product_price'] ?? 0);
    $category   = trim($_POST['product_category'] ?? '');
    $imageBlob  = null;

    if ($code === '' || $name === '' || $unit === '' || $price <= 0) {
        echo json_encode([
          'success'=>false,
          'message'=>'Please fill in code, name, unit, and price.'
        ]);
        exit;
    }

    if (!empty($_FILES['product_image']['tmp_name'])) {
        $imageBlob = file_get_contents($_FILES['product_image']['tmp_name']);
    }

    $stmt = $conn->prepare("
        INSERT INTO noble_products
          (code, product_name, unit, description, product_price, category, product_image)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $null = NULL;
    $stmt->bind_param(
      'ssssdsb',
      $code,
      $name,
      $unit,
      $desc,
      $price,
      $category,
      $null
    );
    if ($imageBlob) {
        $stmt->send_long_data(6, $imageBlob);
    }
    if ($stmt->execute()) {
        echo json_encode([
          'success'=>true,
          'message'=>'Product added successfully.'
        ]);
    } else {
        echo json_encode([
          'success'=>false,
          'message'=>$stmt->error
        ]);
    }
    exit;
}
?>

<form id="addProductForm"
      method="POST"
      enctype="multipart/form-data"
      class="space-y-4">
  <p class="form-error text-red-600 hidden"></p>

  <div id="imgUpload"
       class="w-full h-64 border-2 border-black border-dashed rounded-lg
              bg-gray-100 flex items-center justify-center cursor-pointer overflow-hidden">
    <img id="imagePreview"
         src="../img/image_alt.jpg"
         alt="Click to choose image"
         class="w-full h-full object-contain" />
  </div>
  <input type="file"
         id="productImageFile"
         name="product_image"
         accept="image/*"
         class="hidden" />

  <input type="text"
         name="product_code"
         placeholder="Product Code"
         required
         class="w-full px-4 py-2 border border-black rounded-lg focus:ring-2 focus:ring-orange-500" />

  <input type="text"
         name="product_name"
         placeholder="Product Name"
         required
         class="w-full px-4 py-2 border border-black rounded-lg focus:ring-2 focus:ring-orange-500" />

  <input type="text"
         name="product_unit"
         placeholder="Unit (e.g. pcs, kg, box)"
         required
         class="w-full px-4 py-2 border border-black rounded-lg focus:ring-2 focus:ring-orange-500" />

  <textarea name="product_description"
            placeholder="Product Description"
            required
            class="w-full px-4 py-2 border border-black rounded-lg focus:ring-2 focus:ring-orange-500 h-24 resize-none"></textarea>

  <input type="number"
         name="product_price"
         step="0.01"
         placeholder="Product Price"
         required
         class="w-full px-4 py-2 border border-black rounded-lg focus:ring-2 focus:ring-orange-500" />

  <input type="text"
         name="product_category"
         placeholder="Product Category"
         required
         class="w-full px-4 py-2 border border-black rounded-lg focus:ring-2 focus:ring-orange-500" />

  <button type="submit"
          class="w-full py-2 bg-orange-500 text-black font-semibold rounded-lg hover:opacity-90">
    Add Product
  </button>
</form>

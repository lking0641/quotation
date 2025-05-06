<?php
include '../connection/connection.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Real Living Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
            <!-- Logo -->
            <img src="../logo/white.png" alt="RealLiving Logo" class="h-[50px] w-50 rounded-full object-cover" />
            <img src="../logo/nble.png" alt="RealLiving Logo" class="h-[60px] w-50  object-cover" />
            <!-- Brand Name -->
            <!-- <h3 class="text-sm font-semibold text-left leading-tight">
                <span class="text-[#219ebc]">Rea</span><span class="bg-[linear-gradient(90deg,_hsla(192,70%,43%,1)_0%,_hsla(192,70%,43%,1)_35%,_hsla(43,100%,51%,1)_35%,_hsla(43,100%,51%,1)_100%)] bg-clip-text text-transparent">L</span><span class="text-[#ffb703]">iving</span>
                <span class="text-black"> & </span>
                <span class="text-[#ee9b00]">N</span><span>H</span>
            </h3> -->
        </div>

        <!-- Desktop Nav -->
        <ul class="hidden md:flex space-x-8 text-gray-600 font-medium">
            <li><a href="#" class="hover:text-blue-500">Dashboard</a></li>
            <li><a href="#" class="hover:text-blue-500">sample</a></li>
            <li><a href="#" class="hover:text-blue-500">sample</a></li>
            <li><a href="../installation_quotation/quotation.php" class="hover:text-blue-500">Quotation</a></li>
        </ul>

        <!-- Profile / User -->


        <!-- Mobile Hamburger -->
        <div class="md:hidden">
            <button id="menu-btn" class="text-gray-600 focus:outline-none">
                ☰
            </button>
        </div>
    </nav>


    <!-- Optional Mobile Menu (JS can toggle this) -->
    <div id="mobile-menu" class="md:hidden hidden bg-white shadow-md px-6 py-4 space-y-4">
        <a href="#" class="block text-gray-600 hover:text-blue-500">Dashboard</a>
        <a href="#" class="block text-gray-600 hover:text-blue-500">Properties</a>
        <a href="#" class="block text-gray-600 hover:text-blue-500">Appointments</a>
        <a href="#" class="block text-gray-600 hover:text-blue-500">Messages</a>
    </div>

    <main class="p-6 flex flex-col items-center justify-center">
        <!-- Branding Section -->

        <!-- Flex Container: ReaLiving and NobleHome side by side -->
        <div class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-8">

            <!-- ReaLiving Container -->
            <div class="text-center">
                <div class="inline-block">
                    <span class="text-[#219ebc] text-6xl font-extrabold">Rea</span><span class="text-6xl font-extrabold bg-[linear-gradient(90deg,_hsla(192,70%,43%,1)_0%,_hsla(192,70%,43%,1)_35%,_hsla(43,100%,51%,1)_35%,_hsla(43,100%,51%,1)_100%)] bg-clip-text text-transparent">L</span><span class="text-[#ffb703] text-6xl font-extrabold">iving</span>
                </div>
                <p class="text-sm text-gray-600 mt-1" style="font-family: 'Times New Roman', serif;">
                    Design Center Corporation
                </p>
            </div>
            <!-- NobleHome Container -->
            <div class="inline-block text-center">
                <div class="text-6xl font-extrabold">
                    <span style="font-family: 'Times New Roman', serif;" class="text-[#ee9b00]">N</span><span style="font-family: Arial, sans-serif;">oble</span><span class="text-gray-600" style="font-family: 'Times New Roman', serif;">H</span><span style="font-family: Arial, sans-serif;">ome</span>
                </div>
                <p class="text-sm text-gray-600 mt-1" style="font-family: 'Times New Roman', serif;">
                    Construction Corporation
                </p>
            </div>
        </div>

        <div class="mb-6 w-full max-w-md mt-4">
            <div class="flex items-center w-full bg-white rounded-lg shadow px-4 py-2">
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search here..."
                    class="w-full bg-transparent focus:outline-none text-gray-700" />
                <button id="searchBtn" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </div>



    <!-- Client Records Table -->
<div class="bg-white shadow-md rounded p-6 w-full overflow-x-auto">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Client Records</h2>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Picture</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Updated</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php
            $result = $conn->query("SELECT * FROM user_info ORDER BY created_at DESC");
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $clientLink = 'client_update.php?id=' . urlencode($row['id']);
            ?>
                  <tr class="hover:bg-gray-100 transition duration-200 client-row">
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
        <?php echo htmlspecialchars($row['clientname']); ?>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
        <?php echo htmlspecialchars($row['status']); ?>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <img src="../uploads/<?php echo htmlspecialchars($row['picture']); ?>" alt="Client Picture" class="w-16 h-16 rounded-full object-cover border border-gray-300" />
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        <?php echo $row['created_at']; ?>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
        <a href="<?php echo $clientLink; ?>" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs px-4 py-2 rounded-md transition duration-200">
            Update
        </a>
    </td>
</tr>

                <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

    </main>


    <!-- JavaScript for Search -->
    <script>
      // Search functionality
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');

function performSearch() {
    const filter = searchInput.value.trim().toLowerCase();
    const rows = document.querySelectorAll('.client-row'); // Use the class name for rows

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let rowContainsSearch = false; // Flag to check if row matches the search

        // Loop through all columns in the row
        cells.forEach(cell => {
            const cellText = cell.textContent.trim().toLowerCase();
            if (cellText.includes(filter)) {
                rowContainsSearch = true;
            }
        });

        // Show or hide the row based on search result
        if (filter === '' || rowContainsSearch) {
            row.style.display = ''; // Show row if it matches
        } else {
            row.style.display = 'none'; // Hide row if it doesn't match
        }
    });
}

// Trigger on search button click
searchBtn.addEventListener('click', performSearch);

// Trigger on Enter key
searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault(); // Prevent form submission if inside form
        performSearch();
    }
});

    </script>

    <script>
        const menuBtn = document.getElementById("menu-btn");
        const mobileMenu = document.getElementById("mobile-menu");
        menuBtn.addEventListener("click", () => {
            mobileMenu.classList.toggle("hidden");
        });
    </script>
</body>

</html>
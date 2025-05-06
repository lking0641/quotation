<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-white shadow-md p-4 hidden md:block h-full overflow-y-auto">
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="mb-6 flex flex-col items-center justify-center text-center">
        <img src="../logo/white.png" alt="Logo" class="h-12 w-full object-cover mb-2" />
        <h3 class="text-sm font-semibold leading-tight">
            <span class="text-[#219ebc]">Rea</span><span class="bg-[linear-gradient(90deg,_hsla(192,70%,43%,1)_0%,_hsla(192,70%,43%,1)_35%,_hsla(43,100%,51%,1)_35%,_hsla(43,100%,51%,1)_100%)] bg-clip-text text-transparent">L</span><span class="text-[#ffb703]">iving</span>
            <span class="text-black"> & </span>
            <span class="text-[#ee9b00]">N</span><span>H</span>
        </h3>
    </div>

    <ul class="space-y-4 text-gray-700 font-medium">
        <li class="mt-5 text-xs font-bold text-gray-500 uppercase">Dashboard</li>
        <li>
            <a href="dashboard.php"
                class="block text-sm rounded-md px-3 py-2 transition duration-150 
                      hover:bg-gray-100 hover:text-[#219ebc] 
                      <?= $current_page == 'dashboard.php' ? 'bg-gray-300 text-[#219ebc] font-bold' : '' ?>">
                Dashboard
            </a>
        </li>

        <li class="mt-5 text-xs font-bold text-gray-500 uppercase">Client Management</li>
        <li>
            <a href="client_management.php"
                class="block text-sm rounded-md px-3 py-2 transition duration-150 
                      hover:bg-gray-100 hover:text-[#219ebc] 
                      <?= $current_page == 'client_management.php' ? 'bg-gray-300 text-[#219ebc] font-bold' : '' ?>">
                Add New Client
            </a>
        </li>

        <li>
            <a href="mainpage.php"
                class="block text-sm rounded-md px-3 py-2 transition duration-150 
                      hover:bg-gray-100 hover:text-[#219ebc] 
                      <?= $current_page == 'mainpage.php' ? 'bg-gray-300 text-[#219ebc] font-bold' : '' ?>">
                Client Management
            </a>
        </li>
    </ul>
</aside>



<script>
    const menuBtn = document.getElementById("menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");

    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener("click", () => {
            mobileMenu.classList.toggle("hidden");
        });
    }
</script>

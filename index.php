<!DOCTYPE html>
<html lang="az" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>UstaMarket - Xırdavat & Təsərrüfat Malları</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            dark: '#022c22'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Xüsusi CSS Faylı -->
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen selection:bg-brand-500 selection:text-white">

    <!-- 1. YUXARI MOBİL VƏ MASAÜSTÜ REKLAM BANNERİ -->
    <div id="promoBanner" class="bg-gradient-to-r from-brand-900 via-brand-700 to-brand-900 text-white text-xs py-2 px-3 relative flex items-center justify-between shadow-md z-50">
        <div class="container mx-auto flex items-center justify-center gap-2 text-center overflow-hidden">
            <span class="bg-amber-400 text-brand-900 font-extrabold px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wider shrink-0">Xüsusi Təklif</span>
            <p class="font-medium truncate text-[11px] sm:text-xs">
                Bütün alətlərə <strong>25%-dək Endirim!</strong> İlk sifarişdə pulsuz çatdırılma.
            </p>
            <a href="#products" class="underline font-semibold hover:text-amber-300 transition text-xs shrink-0 hidden sm:inline">İndi Al &rarr;</a>
        </div>
        <button onclick="document.getElementById('promoBanner').remove()" class="text-white/80 hover:text-white p-1 rounded-lg ml-2" aria-label="Bağla">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>

    <!-- 2. STICKY HEADER & NAVİQASİYA -->
    <header class="sticky top-0 bg-white/95 backdrop-blur-md shadow-sm z-40 border-b border-gray-100">
        <div class="container mx-auto px-3 sm:px-4 py-2.5 sm:py-3 flex items-center justify-between gap-2 sm:gap-4">
            <button onclick="toggleMobileMenu()" class="lg:hidden p-2.5 text-gray-700 hover:text-brand-600 rounded-xl hover:bg-brand-50 focus:outline-none" aria-label="Menyu">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <a href="#" class="flex items-center gap-2 sm:gap-3 group shrink-0">
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-gradient-to-tr from-brand-700 to-brand-500 text-white rounded-xl flex items-center justify-center shadow-md shadow-brand-500/30 group-hover:scale-105 transition duration-300">
                    <i class="fa-solid fa-screwdriver-wrench text-lg sm:text-2xl"></i>
                </div>
                <div>
                    <span class="text-lg sm:text-2xl font-black text-brand-900 tracking-tight block leading-none">USTA<span class="text-brand-600">MARKET</span></span>
                    <span class="text-[9px] sm:text-[10px] uppercase font-bold text-gray-400 tracking-widest block mt-0.5">Xırdavat & Məişət</span>
                </div>
            </a>

            <div class="flex-1 max-w-xl mx-4 hidden lg:block relative">
                <div class="relative flex items-center">
                    <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Axtarış: Qazma maşını, boya, bolt, kabel..." class="w-full pl-10 pr-10 py-2.5 bg-gray-100 border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white text-sm transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 text-gray-400"></i>
                </div>
            </div>

            <div class="flex items-center gap-1 sm:gap-3">
                <button onclick="toggleMobileSearchBar()" class="lg:hidden p-2.5 text-gray-700 hover:text-brand-600 rounded-xl hover:bg-brand-50" aria-label="Axtarış">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </button>

                <button onclick="toggleCartDrawer()" class="relative p-2.5 rounded-xl hover:bg-brand-50 text-gray-700 hover:text-brand-700 transition" aria-label="Səbət">
                    <i class="fa-solid fa-cart-shopping text-xl"></i>
                    <span id="cartCount" class="absolute -top-1 -right-1 bg-amber-500 text-white font-bold text-[10px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-white shadow">0</span>
                </button>

                <!-- Qonaq olarkən görünən Giriş / Qeydiyyat düymələri -->
                <div id="authButtons" class="flex items-center gap-2">
                    <button onclick="openAuthModal('login')" class="px-3.5 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50 rounded-lg transition min-h-[44px]">Giriş et</button>
                    <button onclick="openAuthModal('register')" class="px-4 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-md shadow-brand-600/20 transition flex items-center gap-1.5 min-h-[44px]">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                        <span>Qeydiyyat</span>
                    </button>
                </div>

                <!-- İstifadəçi daxil olduqda görünən profil və çıxış paneli -->
                <div id="userProfileBadge" class="hidden items-center gap-2 pl-2">
                    <img id="headerAvatar" src="https://placehold.co/100x100/059669/ffffff?text=Usta" class="w-9 h-9 rounded-full border-2 border-brand-500 object-cover" alt="Profil">
                    <span id="headerUserName" class="text-sm font-semibold text-gray-800 hidden md:inline">İstifadəçi</span>
                    <button onclick="logout()" class="p-2 text-gray-400 hover:text-red-500 rounded-lg" title="Çıxış">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobileSearchContainer" class="hidden px-4 py-2 bg-gray-50 border-t border-gray-100 lg:hidden">
            <div class="relative flex items-center">
                <input type="text" id="mobileSearchInput" onkeyup="filterProductsMobile()" placeholder="Məhsul, kateqoriya axtar..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 text-gray-400"></i>
            </div>
        </div>

        <div class="hidden lg:block bg-brand-900 text-white shadow-inner">
            <div class="container mx-auto px-4 flex items-center justify-between">
                <nav class="flex items-center space-x-1 text-sm font-medium py-1">
                    <a href="#products" onclick="filterByCategory('Maşın Əşyaları')" class="px-3 py-2 hover:text-amber-300 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-car-side text-xs text-brand-500"></i> Maşın
                    </a>
                    <a href="#products" onclick="filterByCategory('Ev Təsərrüfatı')" class="px-3 py-2 hover:text-amber-300 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-faucet-drip text-xs text-brand-500"></i> Ev Təsərrüfatı
                    </a>
                    <a href="#products" onclick="filterByCategory('Elektronika Hissələri')" class="px-3 py-2 hover:text-amber-300 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-xs text-brand-500"></i> Elektronika Hissələri
                    </a>
                </nav>

                <button onclick="openContactModal()" class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-brand-900 font-bold rounded-lg text-sm transition flex items-center gap-2 my-1">
                    <i class="fa-solid fa-headset"></i>
                    <span>Əlaqə Saxla</span>
                </button>
            </div>
        </div>
    </header>

    <!-- 3. MOBİL NAVİQASİYA DRAWER-İ -->
    <div id="mobileDrawerBackdrop" onclick="toggleMobileMenu()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden transition-opacity"></div>
    <div id="mobileDrawer" class="fixed top-0 left-0 w-4/5 max-w-xs h-full bg-white z-50 transform -translate-x-full drawer-transition flex flex-col justify-between shadow-2xl">
        <div class="p-4 bg-brand-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-screwdriver-wrench text-amber-400 text-lg"></i>
                <span class="font-bold text-lg tracking-tight">USTAMARKET</span>
            </div>
            <button onclick="toggleMobileMenu()" class="p-2 text-white/80 hover:text-white">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
            <div id="mobileAuthBox" class="p-3 bg-brand-50 rounded-xl border border-brand-100 space-y-2">
                <p class="text-xs text-brand-800 font-semibold text-center">Sifariş üçün giriş edin</p>
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="toggleMobileMenu(); openAuthModal('login')" class="w-full py-2 bg-white border border-brand-600 text-brand-700 font-bold text-xs rounded-lg shadow-sm">Giriş et</button>
                    <button onclick="toggleMobileMenu(); openAuthModal('register')" class="w-full py-2 bg-brand-600 text-white font-bold text-xs rounded-lg shadow-sm">Qeydiyyat</button>
                </div>
            </div>
        </div>

        <div class="p-4 border-t bg-gray-50">
            <button onclick="toggleMobileMenu(); openContactModal()" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-brand-900 font-bold rounded-xl text-sm transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-headset"></i>
                <span>Əlaqə Saxla</span>
            </button>
        </div>
    </div>

    <!-- 4. ƏSAS MƏZMUN -->
    <main class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 flex-1">
        <div class="relative w-full h-40 sm:h-64 md:h-72 rounded-2xl overflow-hidden mb-6 sm:mb-8 shadow-xl bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 flex items-center">
            <div class="absolute inset-0 bg-black/25"></div>
            <div class="relative z-10 px-4 sm:px-8 md:px-12 max-w-xl text-white">
                <span class="bg-amber-400 text-brand-900 text-[10px] sm:text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">Müasir Xırdavat</span>
                <h1 class="text-xl sm:text-3xl md:text-4xl font-extrabold mt-1 sm:mt-2 leading-tight">Keyfiyyətli Tikinti & Təsərrüfat Malları</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-200 line-clamp-2">Peşəkar ustalar və ev üçün hər şey bir tıkla qapınızda!</p>
                <button onclick="document.getElementById('products').scrollIntoView()" class="mt-3 sm:mt-4 px-4 py-2 sm:px-5 sm:py-2.5 bg-white text-brand-900 font-bold rounded-xl shadow hover:bg-amber-400 transition text-xs sm:text-sm">
                    Məhsullara Bax
                </button>
            </div>
        </div>

        <div id="products" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h2 id="catalogTitle" class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">Bütün Məhsullar</h2>
                <p class="text-xs text-gray-500">Bazada olan canlı xırdavat məhsulları</p>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <span class="text-xs text-gray-500 hidden sm:inline">Çeşidləmə:</span>
                <select onchange="sortProducts(this.value)" class="bg-white border border-gray-200 text-xs sm:text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="default">Ən yenilər</option>
                    <option value="low">Qiymət: Ucuzdan bahaya</option>
                    <option value="high">Qiymət: Bahadan ucuza</option>
                </select>
            </div>
        </div>

        <div id="productsGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
            <!-- JS Injection -->
        </div>
    </main>

    <!-- 5. MODAL: GİRİŞ VƏ QEYDİYYAT -->
    <div id="authModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative my-4 sm:my-8 fade-in">
            <button onclick="closeAuthModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center z-10">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="flex border-b border-gray-100 bg-gray-50">
                <button id="loginTabBtn" onclick="switchAuthTab('login')" class="flex-1 py-3.5 font-bold text-center border-b-2 border-brand-600 text-brand-700 bg-white text-sm">Giriş et</button>
                <button id="registerTabBtn" onclick="switchAuthTab('register')" class="flex-1 py-3.5 font-bold text-center text-gray-500 hover:text-gray-700 text-sm">Qeydiyyat</button>
            </div>

            <!-- GİRİŞ FORMU -->
            <form id="loginForm" onsubmit="handleLogin(event)" class="p-4 sm:p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">E-poçt</label>
                    <input type="email" required placeholder="nümunə@mail.com" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Şifrə</label>
                    <input type="password" required placeholder="******" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">
                </div>
                <button type="submit" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg shadow-brand-600/30 transition text-sm min-h-[44px]">Giriş Et</button>
            </form>

           <!-- QEYDİYYAT FORMU -->
            <form id="registerForm" onsubmit="handleRegister(event)" class="p-4 sm:p-6 space-y-3.5 hidden max-h-[75vh] overflow-y-auto custom-scrollbar">
                <div class="text-center border-b border-gray-100 pb-3">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Profil Şəkli</label>
                    <div class="relative w-24 h-24 mx-auto mb-2">
                        <img id="avatarPreview" src="https://placehold.co/150x150/e2e8f0/64748b?text=Foto" class="w-full h-full rounded-full object-cover border-4 border-brand-100 shadow-inner" alt="Profil">
                        <label for="profileImageInput" class="absolute bottom-0 right-0 bg-brand-600 text-white p-2 rounded-full cursor-pointer hover:bg-brand-700 shadow-md">
                            <i class="fa-solid fa-camera text-xs"></i>
                        </label>
                        <input type="file" id="profileImageInput" accept="image/*" onchange="previewAndCropImage(event)" class="hidden">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">Ad</label>
                        <input type="text" id="regFirstName" required placeholder="Əli" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">Soyad</label>
                        <input type="text" id="regLastName" required placeholder="Məmmədov" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">Ata Adı</label>
                        <input type="text" id="regPatronymic" placeholder="Həsən" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">Ölkə Kodu</label>
                        <select id="countrySelect" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                            <option value="+994">🇦🇿 Azərbaycan (+994)</option>
                            <option value="+90">🇹🇷 Türkiyə (+90)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">Telefon</label>
                        <input type="tel" id="regPhone" required placeholder="501234567" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">E-Mail</label>
                        <input type="email" id="regEmail" required placeholder="ali@mail.com" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">Doğum Tarixi</label>
                        <input type="date" id="regBirthDate" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">Şifrə</label>
                        <input type="password" id="regPassword" required placeholder="******" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase mb-1">Təkrar Şifrə</label>
                        <input type="password" id="regConfirmPassword" required placeholder="******" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg shadow-brand-600/30 transition text-sm min-h-[44px] mt-2">Qeydiyyatı Tamamla</button>
            </form>
        </div>
    </div>

    <!-- 6. MODAL: MƏHSUL DETALLARI -->
    <div id="productDetailModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden relative my-4 sm:my-8 fade-in max-h-[90vh] flex flex-col">
            <button onclick="closeProductDetailModal()" class="absolute top-3 right-3 z-20 text-gray-400 hover:text-gray-600 bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="overflow-y-auto custom-scrollbar p-4 sm:p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <div class="relative h-52 sm:h-72 bg-gray-50 rounded-xl overflow-hidden mb-3 border border-gray-100 flex items-center justify-center">
                            <img id="detailMainImage" src="" class="max-h-full max-w-full object-contain p-2" alt="Məhsul">
                        </div>
                        <div id="detailThumbnails" class="flex gap-2 overflow-x-auto pb-1 custom-scrollbar"></div>
                    </div>
                    <div class="flex flex-col justify-between">
                        <div>
                            <span id="detailCategory" class="text-[10px] font-extrabold text-brand-700 uppercase tracking-wider bg-brand-50 px-2.5 py-1 rounded-md">Kateqoriya</span>
                            <h3 id="detailTitle" class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-2">Məhsul Adı</h3>
                            <p id="detailPrice" class="text-2xl sm:text-3xl font-black text-brand-700 mt-3">0.00 ₼</p>
                            <p id="detailDescription" class="text-gray-600 text-xs sm:text-sm mt-3 leading-relaxed">Məhsul haqqında ətraflı məlumat.</p>
                        </div>
                        <div class="mt-5 border-t border-gray-100 pt-4">
                            <button id="addToCartFromDetailBtn" onclick="addToCartFromDetail()" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cart-plus"></i>
                                <span>Səbətə Əlavə Et</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. SƏBƏT DRAWER-İ -->
    <div id="cartDrawer" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden justify-end">
        <div class="bg-white w-full max-w-sm h-full flex flex-col justify-between shadow-2xl animate-slide-left">
            <div class="p-4 border-b flex items-center justify-between bg-brand-900 text-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-cart-shopping text-amber-400"></i>
                    <h3 class="font-bold text-base">Səbətiniz</h3>
                </div>
                <button onclick="toggleCartDrawer()" class="text-white/80 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div id="cartItemsContainer" class="p-3 sm:p-4 flex-1 overflow-y-auto space-y-3 custom-scrollbar"></div>
            <div class="p-4 border-t bg-gray-50 space-y-3">
                <div class="flex justify-between text-base font-extrabold text-gray-900">
                    <span>Məbləğ:</span>
                    <span id="cartTotalPrice" class="text-brand-700">0.00 ₼</span>
                </div>
                <button onclick="checkout()" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 text-brand-900 font-extrabold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                    <span>Sifarişi Rəsmiləşdir</span>
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript Faylı -->
    <script src="script.js"></script>
</body>
</html>
let productsData = [];
let currentUser = null;
let cart = [];
let currentDetailProduct = null;
let selectedQuantity = 1;
let activeCategory = 'all';
let compressedImageBlob = null; // Sıxışdırılmış şəkil üçün dəyişən

window.onload = function () {
    fetchProducts();
};

function fetchProducts(search = '', categoryId = '') {
    let url = 'api.php?action=get_products';
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (categoryId) url += `&category_id=${categoryId}`;

    fetch(url)
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                productsData = response.data.map(p => ({
                    id: p.id,
                    title: p.title,
                    category: p.category_name || 'Xırdavat',
                    category_id: p.category_id,
                    price: parseFloat(p.price),
                    image: p.primary_image || 'https://placehold.co/400x300/059669/ffffff?text=UstaMarket',
                    images: [p.primary_image || 'https://placehold.co/400x300/059669/ffffff?text=UstaMarket'],
                    desc: p.description || 'Məhsul haqqında məlumat yoxdur.'
                }));
                renderProducts(productsData);
            }
        })
        .catch(err => console.log('Serverə qoşulma xətası:', err));
}

function renderProducts(items) {
    const grid = document.getElementById('productsGrid');
    grid.innerHTML = '';

    if (items.length === 0) {
        grid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-6">Heç bir məhsul tapılmadı.</p>';
        return;
    }

    items.forEach(product => {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition flex flex-col justify-between overflow-hidden group';
        card.innerHTML = `
            <div class="cursor-pointer overflow-hidden relative bg-gray-50 flex items-center justify-center p-2 h-36 sm:h-48" onclick="openProductDetail(${product.id})">
                <img src="${product.image}" alt="${product.title}" class="max-h-full max-w-full object-contain group-hover:scale-105 transition duration-300">
                <span class="absolute top-2 left-2 bg-brand-50 text-brand-700 text-[9px] sm:text-[10px] font-extrabold px-2 py-0.5 rounded-md truncate max-w-[80%]">${product.category}</span>
            </div>
            <div class="p-2.5 sm:p-4 flex-1 flex flex-col justify-between">
                <div>
                    <h3 onclick="openProductDetail(${product.id})" class="font-bold text-gray-800 hover:text-brand-600 transition text-xs sm:text-sm cursor-pointer line-clamp-2 leading-snug">${product.title}</h3>
                </div>
                <div class="mt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span class="text-base sm:text-lg font-black text-brand-800">${product.price.toFixed(2)} ₼</span>
                    <button onclick="addToCart(${product.id})" class="w-full sm:w-auto px-3 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 text-xs font-bold min-h-[38px]">
                        <i class="fa-solid fa-cart-plus"></i>
                        <span>Əlavə Et</span>
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });
}

function toggleMobileMenu() {
    const drawer = document.getElementById('mobileDrawer');
    const backdrop = document.getElementById('mobileDrawerBackdrop');
    if (drawer.classList.contains('-translate-x-full')) {
        drawer.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    } else {
        drawer.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }
}

function toggleMobileSearchBar() {
    const container = document.getElementById('mobileSearchContainer');
    container.classList.toggle('hidden');
}

function filterProducts() {
    const val = document.getElementById('searchInput').value;
    fetchProducts(val);
}

function filterProductsMobile() {
    const val = document.getElementById('mobileSearchInput').value;
    fetchProducts(val);
}

function filterByCategory(cat) {
    activeCategory = cat;
    document.getElementById('catalogTitle').innerText = cat;
    const filtered = productsData.filter(p => p.category.toLowerCase() === cat.toLowerCase());
    renderProducts(filtered);
}

function sortProducts(type) {
    let sorted = [...productsData];
    if (type === 'low') sorted.sort((a, b) => a.price - b.price);
    if (type === 'high') sorted.sort((a, b) => b.price - a.price);
    renderProducts(sorted);
}

function openAuthModal(tab = 'login') {
    document.getElementById('authModal').classList.remove('hidden');
    switchAuthTab(tab);
}

function closeAuthModal() {
    document.getElementById('authModal').classList.add('hidden');
}

function switchAuthTab(tab) {
    const loginForm = document.getElementById('loginForm');
    const regForm = document.getElementById('registerForm');
    const loginBtn = document.getElementById('loginTabBtn');
    const regBtn = document.getElementById('registerTabBtn');

    if (tab === 'login') {
        loginForm.classList.remove('hidden');
        regForm.classList.add('hidden');
        loginBtn.className = "flex-1 py-3.5 font-bold text-center border-b-2 border-brand-600 text-brand-700 bg-white text-sm";
        regBtn.className = "flex-1 py-3.5 font-bold text-center text-gray-500 hover:text-gray-700 text-sm";
    } else {
        loginForm.classList.add('hidden');
        regForm.classList.remove('hidden');
        regBtn.className = "flex-1 py-3.5 font-bold text-center border-b-2 border-brand-600 text-brand-700 bg-white text-sm";
        loginBtn.className = "flex-1 py-3.5 font-bold text-center text-gray-500 hover:text-gray-700 text-sm";
    }
}

// Şəkli seçib Canvas vasitəsilə avtomatik kiçildən və sıxışdıran funksiya
function previewAndCropImage(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (event) {
        const img = new Image();
        img.onload = function () {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            let width = img.width;
            let height = img.height;
            const maxSize = 300; // Profil şəkli üçün ideal ölçü

            if (width > height) {
                if (width > maxSize) {
                    height *= maxSize / width;
                    width = maxSize;
                }
            } else {
                if (height > maxSize) {
                    width *= maxSize / height;
                    height = maxSize;
                }
            }

            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);

            // Ekranda önizləmə üçün göstəririk
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            document.getElementById('avatarPreview').src = dataUrl;

            // Şəkli sıxışdırıb yadda saxlayırıq
            canvas.toBlob(function(blob) {
                compressedImageBlob = blob;
            }, 'image/jpeg', 0.85);
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

function handleLogin(e) {
    e.preventDefault();
    const email = e.target.elements[0].value;
    const password = e.target.elements[1].value;

    fetch('api.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, password: password })
    })
    .then(res => res.json())
    .then(response => {
        if (response.status === 'success') {
            currentUser = {
                name: response.user.first_name + ' ' + response.user.last_name,
                avatar: response.user.profile_image || 'https://placehold.co/100x100/059669/ffffff?text=Usta'
            };
            updateUserUI();
            closeAuthModal();
            alert(response.message);
        } else {
            alert('Xəta: ' + response.message);
        }
    })
    .catch(err => alert('Serverə qoşulma xətası.'));
}

// Qeydiyyat funksiyası (FormData ilə şəkilsiz və ya şəkillİ göndərmə)
function handleRegister(e) {
    e.preventDefault();
    
    const password = document.getElementById('regPassword').value;
    const confirmPassword = document.getElementById('regConfirmPassword').value;

    if (password !== confirmPassword) {
        alert('Şifrələr uyğun gəlmir!');
        return;
    }

    const formData = new FormData();
    formData.append('first_name', document.getElementById('regFirstName').value);
    formData.append('last_name', document.getElementById('regLastName').value);
    formData.append('patronymic', document.getElementById('regPatronymic').value);
    formData.append('country_code', document.getElementById('countrySelect').value);
    formData.append('phone', document.getElementById('regPhone').value);
    formData.append('email', document.getElementById('regEmail').value);
    formData.append('birth_date', document.getElementById('regBirthDate').value);
    formData.append('password', password);

    // Əgər şəkil seçilibsə əlavə edirik
    if (compressedImageBlob) {
        formData.append('profile_image', compressedImageBlob, 'avatar.jpg');
    }

    fetch('api.php?action=register', {
        method: 'POST',
        body: formData // FormData istifadə etdikdə Headers-ə Content-Type yazılmır
    })
    .then(res => res.json())
    .then(response => {
        if (response.status === 'success') {
            alert(response.message);
            switchAuthTab('login');
        } else {
            alert('Qeydiyyat xətası: ' + response.message);
        }
    })
    .catch(err => alert('Serverə qoşulmaq mümkün olmadı.'));
}

function updateUserUI() {
    if (currentUser) {
        document.getElementById('authButtons').classList.add('hidden');
        document.getElementById('mobileAuthBox').classList.add('hidden');
        const badge = document.getElementById('userProfileBadge');
        badge.classList.remove('hidden');
        badge.classList.add('flex');
        document.getElementById('headerAvatar').src = currentUser.avatar;
        document.getElementById('headerUserName').innerText = currentUser.name;
    }
}

function logout() {
    currentUser = null;
    document.getElementById('authButtons').classList.remove('hidden');
    document.getElementById('mobileAuthBox').classList.remove('hidden');
    document.getElementById('userProfileBadge').classList.add('hidden');
    alert('Sistemdən çıxış edildi.');
}

function openContactModal() { document.getElementById('contactModal').classList.remove('hidden'); }
function closeContactModal() { document.getElementById('contactModal').classList.add('hidden'); }

function openProductDetail(id) {
    const product = productsData.find(p => p.id === id);
    if (!product) return;
    currentDetailProduct = product;
    document.getElementById('detailTitle').innerText = product.title;
    document.getElementById('detailCategory').innerText = product.category;
    document.getElementById('detailPrice').innerText = product.price.toFixed(2) + " ₼";
    document.getElementById('detailDescription').innerText = product.desc;
    document.getElementById('detailMainImage').src = product.image;
    document.getElementById('productDetailModal').classList.remove('hidden');
}

function closeProductDetailModal() { document.getElementById('productDetailModal').classList.add('hidden'); }

function addToCart(id, qty = 1) {
    if (!currentUser) {
        alert('Səbətə əlavə etmək üçün giriş edin.');
        openAuthModal('login');
        return;
    }
    const product = productsData.find(p => p.id === id);
    const exist = cart.find(i => i.id === id);
    if (exist) exist.qty += qty;
    else cart.push({ ...product, qty: qty });
    updateCartUI();
}

function addToCartFromDetail() {
    if (currentDetailProduct) {
        addToCart(currentDetailProduct.id, selectedQuantity);
        closeProductDetailModal();
    }
}

function toggleCartDrawer() {
    const drawer = document.getElementById('cartDrawer');
    if (drawer.classList.contains('hidden')) {
        drawer.classList.remove('hidden');
        drawer.classList.add('flex');
    } else {
        drawer.classList.add('hidden');
        drawer.classList.remove('flex');
    }
}

function updateCartUI() {
    const container = document.getElementById('cartItemsContainer');
    const cartCountBadge = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotalPrice');

    container.innerHTML = '';
    let total = 0;
    let count = 0;

    cart.forEach(item => {
        total += item.price * item.qty;
        count += item.qty;
    });

    cart.forEach(item => {
        const el = document.createElement('div');
        el.className = 'flex items-center justify-between bg-gray-50 p-2.5 rounded-xl border border-gray-100';
        el.innerHTML = `
            <div class="flex items-center gap-2.5">
                <img src="${item.image}" class="w-10 h-10 object-contain bg-white rounded-lg p-1 border" alt="">
                <div>
                    <h4 class="font-bold text-xs text-gray-800 line-clamp-1">${item.title}</h4>
                    <p class="text-xs text-brand-700 font-black">${item.price.toFixed(2)} ₼</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold bg-white px-2 py-0.5 border rounded">x${item.qty}</span>
                <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-700 p-1 text-xs">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(el);
    });

    cartCountBadge.innerText = count;
    cartTotal.innerText = total.toFixed(2) + " ₼";
}

function removeFromCart(id) {
    cart = cart.filter(i => i.id !== id);
    updateCartUI();
}

function checkout() {
    if (cart.length === 0) { alert('Səbətiniz boşdur!'); return; }
    alert('Sifarişiniz uğurla rəsmiləşdirildi!');
    cart = [];
    updateCartUI();
    toggleCartDrawer();
}
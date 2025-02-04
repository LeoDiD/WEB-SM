const productForm = document.getElementById('productForm');
const productList = document.getElementById('productList');
const editModal = document.getElementById('editModal');
const closeModal = document.getElementById('closeModal');
const editForm = document.getElementById('editForm');
const editPriceInput = document.getElementById('editPrice');
const editStockInput = document.getElementById('editStock');
const editProductId = document.getElementById('editProductId');
const addProductModal = document.getElementById('addProductModal');
const closeAddProductModal = document.getElementById('closeAddProductModal');

// Fetch products from the database
async function fetchProducts() {
    const response = await fetch('api.php');
    const products = await response.json();
    productList.innerHTML = '';
    products.forEach(product => {
        const productContainer = document.createElement('div');
        productContainer.className = 'product-container';
        productContainer.innerHTML = `
            <h3>${product.name}</h3>
            <img src="${product.image}" alt="${product.name}">
            <p>Price: $${product.price}</p>
            <p>Stock: ${product.stock}</p>
            <button onclick="openEditModal(${product.id}, ${product.price}, ${product.stock})">Edit</button>
        `;
        productList.appendChild(productContainer);
    });
}

// Open the edit modal
function openEditModal(id, price, stock) {
    editProductId.value = id;
    editPriceInput.value = price; // Set the price in the modal
    editStockInput.value = stock; // Set the stock in the modal
    editModal.style.display = 'block'; // Show the modal
}

// Close the edit modal
closeModal.onclick = function() {
    editModal.style.display = 'none'; // Hide the modal
}

// Open the add product modal
document.getElementById('addProductBtn').onclick = function() {
    addProductModal.style.display = 'block'; // Show the add product modal
}

// Close the add product modal
closeAddProductModal.onclick = function() {
    addProductModal.style.display = 'none'; // Hide the add product modal
}

// Handle the edit form submission
editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const price = editPriceInput.value;
    const stock = editStockInput.value;
    const id = editProductId.value;

    const response = await fetch('api.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&price=${price}&stock=${stock}`
    });

    const result = await response.json();
    alert(result.message || result.error);
    fetchProducts(); // Refresh the product list
    editModal.style.display = 'none'; // Close the modal
});

// Handle the add product form submission
productForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('name').value;
    const price = document.getElementById('price').value;
    const image = document.getElementById('image').value;

    const response = await fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `name=${name}&price=${price}&image=${image}`
    });

    const result = await response.json();
    alert(result.message || result.error);
    fetchProducts(); // Refresh the product list
    addProductModal.style.display = 'none'; // Close the add product modal
});

// Close the modal when clicking outside of it
window.onclick = function(event) {
    if (event.target == addProductModal) {
        addProductModal.style.display = 'none'; // Hide the add product modal
    }
    if (event.target == editModal) {
        editModal.style.display = 'none'; // Hide the edit modal
    }
}

// Initial fetch of products
fetchProducts();
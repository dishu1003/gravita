document.addEventListener('DOMContentLoaded', () => {
    const cartContainer = document.getElementById('cart-container');
    if (!cartContainer) return;

    const csrfToken = document.querySelector('input[name="_csrf"]')?.value;

    const updateCart = async (productId, qty, action) => {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('qty', qty);
        formData.append('action', action);
        formData.append('_csrf', csrfToken);

        try {
            const response = await fetch('/cart.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            if (data.success) {
                updateDOM(data);
            } else {
                console.error('Cart update failed:', data.error);
            }
        } catch (error) {
            console.error('Network error:', error);
        }
    };

    const updateDOM = (data) => {
        // Update header cart count
        const badge = document.querySelector('.header .badge');
        if (badge) {
            badge.textContent = data.cartCount;
            if (data.cartCount > 0) {
                badge.classList.add('badge--pulse');
                setTimeout(() => badge.classList.remove('badge--pulse'), 600);
            }
        }

        // Update totals
        document.getElementById('summary-subtotal').textContent = `₹${data.totals.subtotal.toFixed(2)}`;
        document.getElementById('summary-tax').textContent = `₹${data.totals.tax.toFixed(2)}`;
        document.getElementById('summary-shipping').textContent = `₹${data.totals.shipping.toFixed(2)}`;
        document.getElementById('summary-total').textContent = `₹${data.totals.total.toFixed(2)}`;

        // Re-render cart items
        const itemsContainer = cartContainer.querySelector('.cart-items');
        if (itemsContainer) {
            if (data.items.length === 0) {
                cartContainer.innerHTML = `
                    <div class="cart-empty">
                        <div class="cart-empty__icon">🛒</div>
                        <h2>Your cart is empty</h2>
                        <p>Looks like you haven't added any fragrances yet.</p>
                        <a href="/shop.php" class="btn btn--primary">Continue Shopping</a>
                    </div>`;
            } else {
                itemsContainer.innerHTML = data.items.map(item => `
                    <div class="cart-item" data-product-id="${item.id}">
                        <a href="/product.php?slug=${item.slug}" class="cart-item__img">
                            <img src="/uploads/${item.image}" alt="${item.name}" loading="lazy" width="100" height="100">
                        </a>
                        <div class="cart-item__info">
                            <a href="/product.php?slug=${item.slug}">${item.name}</a>
                            <div class="cart-item__price">₹${parseFloat(item.price).toFixed(2)}</div>
                        </div>
                        <div class="cart-item__actions">
                            <div class="quantity-selector">
                                <button class="quantity-btn" data-action="decrease" aria-label="Decrease quantity">-</button>
                                <input type="number" class="quantity-input" value="${item.qty}" min="1" aria-label="Quantity">
                                <button class="quantity-btn" data-action="increase" aria-label="Increase quantity">+</button>
                            </div>
                            <button class="cart-item__remove" data-action="remove">Remove</button>
                        </div>
                        <div class="cart-item__subtotal" style="text-align:right;font-weight:bold;">
                            ₹${parseFloat(item.subtotal).toFixed(2)}
                        </div>
                    </div>
                `).join('');
            }
        }
    };

    cartContainer.addEventListener('click', (e) => {
        const target = e.target;
        const cartItem = target.closest('.cart-item');
        if (!cartItem) return;

        const productId = cartItem.dataset.productId;
        const quantityInput = cartItem.querySelector('.quantity-input');
        let currentQty = parseInt(quantityInput.value, 10);

        if (target.matches('[data-action="increase"]')) {
            currentQty++;
            quantityInput.value = currentQty;
            updateCart(productId, currentQty, 'update');
        } else if (target.matches('[data-action="decrease"]')) {
            currentQty--;
            if (currentQty > 0) {
                quantityInput.value = currentQty;
                updateCart(productId, currentQty, 'update');
            } else {
                // Quantity is 0 or less, so remove the item
                updateCart(productId, 0, 'remove');
            }
        } else if (target.matches('[data-action="remove"]')) {
            updateCart(productId, 0, 'remove');
        }
    });

    cartContainer.addEventListener('change', (e) => {
        const target = e.target;
        if (target.matches('.quantity-input')) {
            const cartItem = target.closest('.cart-item');
            const productId = cartItem.dataset.productId;
            let qty = parseInt(target.value, 10);

            if (isNaN(qty) || qty < 1) {
                qty = 1;
                target.value = qty;
            }

            updateCart(productId, qty, 'update');
        }
    });
});

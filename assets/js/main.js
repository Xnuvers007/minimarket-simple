/**
 * Main JavaScript File - Sistem Manajemen Toko
 */

// Document Ready
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Sidebar Toggle for Mobile
    $('.sidebar-toggle').click(function() {
        $('.sidebar').toggleClass('show');
    });

    // Close sidebar when clicking outside on mobile
    $(document).click(function(event) {
        if ($(window).width() <= 992) {
            if (!$(event.target).closest('.sidebar, .sidebar-toggle').length) {
                $('.sidebar').removeClass('show');
            }
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);

    // Confirm Delete
    $('.btn-delete').click(function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            e.preventDefault();
            return false;
        }
    });

    // Form Validation
    $('.needs-validation').submit(function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Image Preview
    $('.image-upload').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('.image-preview').html('<img src="' + e.target.result + '" class="img-fluid rounded">');
            }
            reader.readAsDataURL(file);
        }
    });

    // Number input format (thousand separator)
    $('.number-format').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatNumber(value));
    });

    // Price input format (Rupiah)
    $('.price-input').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatRupiah(value));
    });

    // DataTable initialization (if exists)
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            "pageLength": 10,
            "ordering": true,
            "searching": true
        });
    }

    // Search filter
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('.searchable-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Print functionality
    $('.btn-print').click(function() {
        window.print();
    });

    // Export to Excel (simple method)
    $('.btn-export-excel').click(function() {
        const table = $(this).data('table');
        const uri = 'data:application/vnd.ms-excel;base64,';
        const template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>';
        
        const context = {
            worksheet: 'Worksheet',
            table: $(table).html()
        };

        const downloadLink = document.createElement("a");
        downloadLink.href = uri + base64(format(template, context));
        downloadLink.download = 'export_' + new Date().getTime() + '.xls';
        downloadLink.click();
    });

    // Quantity increment/decrement
    $('.qty-increment').click(function() {
        const input = $(this).siblings('.qty-input');
        let value = parseInt(input.val()) || 0;
        input.val(value + 1).trigger('change');
    });

    $('.qty-decrement').click(function() {
        const input = $(this).siblings('.qty-input');
        let value = parseInt(input.val()) || 0;
        if (value > 1) {
            input.val(value - 1).trigger('change');
        }
    });

    // AJAX Add to Cart
    $('.btn-add-to-cart').click(function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const quantity = $(this).data('quantity') || 1;
        
        $.ajax({
            url: 'add_to_cart.php',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    showAlert('success', 'Produk berhasil ditambahkan ke keranjang!');
                    updateCartCount();
                } else {
                    showAlert('danger', data.message || 'Gagal menambahkan produk!');
                }
            },
            error: function() {
                showAlert('danger', 'Terjadi kesalahan sistem!');
            }
        });
    });

    // Update Cart Count
    function updateCartCount() {
        $.get('get_cart_count.php', function(data) {
            $('.cart-count').text(data.count);
        });
    }

    // Calculate Cart Total
    $('.cart-quantity-update').change(function() {
        calculateCartTotal();
    });

    function calculateCartTotal() {
        let total = 0;
        $('.cart-item').each(function() {
            const price = parseFloat($(this).data('price'));
            const quantity = parseInt($(this).find('.cart-quantity-update').val());
            const subtotal = price * quantity;
            $(this).find('.item-subtotal').text(formatRupiah(subtotal.toString()));
            total += subtotal;
        });
        $('.cart-total').text(formatRupiah(total.toString()));
    }

    // Stock Warning
    $('.stock-input').on('input', function() {
        const value = parseInt($(this).val()) || 0;
        const minStock = parseInt($(this).data('min-stock')) || 10;
        
        if (value < minStock) {
            $(this).addClass('border-danger');
            $(this).siblings('.stock-warning').removeClass('d-none');
        } else {
            $(this).removeClass('border-danger');
            $(this).siblings('.stock-warning').addClass('d-none');
        }
    });

    // POS: Add item to transaction
    $('.pos-add-item').click(function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productPrice = $(this).data('product-price');
        
        addPOSItem(productId, productName, productPrice);
    });

    // Auto-save draft
    let autoSaveTimer;
    $('.auto-save').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            saveDraft();
        }, 2000);
    });

    function saveDraft() {
        const formData = $('.auto-save-form').serialize();
        $.post('save_draft.php', formData, function(response) {
            console.log('Draft saved');
        });
    }

    // Real-time search
    $('.live-search').on('keyup', function() {
        const query = $(this).val();
        if (query.length >= 3) {
            $.get('search.php', { q: query }, function(data) {
                $('.search-results').html(data).show();
            });
        } else {
            $('.search-results').hide();
        }
    });

    // Dark mode toggle (optional)
    $('.theme-toggle').click(function() {
        $('body').toggleClass('dark-mode');
        const isDark = $('body').hasClass('dark-mode');
        localStorage.setItem('darkMode', isDark);
    });

    // Load dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
        $('body').addClass('dark-mode');
    }
});

// Helper Functions
function formatRupiah(angka, prefix = 'Rp ') {
    const numberString = angka.replace(/[^,\d]/g, '').toString();
    const split = numberString.split(',');
    const sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        const separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix + rupiah;
}

function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}

function base64(s) {
    return window.btoa(unescape(encodeURIComponent(s)));
}

function format(s, c) {
    return s.replace(/{(\w+)}/g, function(m, p) {
        return c[p];
    });
}

function addPOSItem(productId, productName, productPrice) {
    // Check if item already exists
    const existingItem = $(`.pos-item[data-product-id="${productId}"]`);
    
    if (existingItem.length > 0) {
        // Increment quantity
        const qtyInput = existingItem.find('.pos-qty');
        const currentQty = parseInt(qtyInput.val());
        qtyInput.val(currentQty + 1).trigger('change');
    } else {
        // Add new item
        const itemHtml = `
            <div class="pos-item border-bottom pb-2 mb-2" data-product-id="${productId}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${productName}</strong><br>
                        <small class="text-muted">${formatRupiah(productPrice.toString())}</small>
                    </div>
                    <div class="input-group" style="width: 120px;">
                        <button class="btn btn-sm btn-outline-secondary qty-decrement" type="button">-</button>
                        <input type="number" class="form-control form-control-sm text-center pos-qty" value="1" min="1">
                        <button class="btn btn-sm btn-outline-secondary qty-increment" type="button">+</button>
                    </div>
                    <button class="btn btn-sm btn-danger remove-pos-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('.pos-cart-items').append(itemHtml);
    }
    
    calculatePOSTotal();
}

function calculatePOSTotal() {
    let total = 0;
    $('.pos-item').each(function() {
        const price = parseFloat($(this).data('product-price'));
        const quantity = parseInt($(this).find('.pos-qty').val());
        total += price * quantity;
    });
    $('.pos-total').text(formatRupiah(total.toString()));
}

// Remove POS item
$(document).on('click', '.remove-pos-item', function() {
    $(this).closest('.pos-item').remove();
    calculatePOSTotal();
});

// POS quantity change
$(document).on('change', '.pos-qty', function() {
    calculatePOSTotal();
});
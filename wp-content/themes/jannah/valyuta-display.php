<?php
/**
 * Valyuta Rates Display Template
 * Exact design matching the screenshot
 */

// Get unique ID for this instance
$unique_id = uniqid('valyuta_');
$current_currency = !empty($atts['currency']) ? $atts['currency'] : 'USD';
$show_cash = $atts['show_cash'] === 'true';

// Debug output for currency
error_log("VALYUTA DEBUG - Current currency: " . $current_currency);
error_log("VALYUTA DEBUG - Show cash: " . ($show_cash ? 'true' : 'false'));

// Debug information
if (WP_DEBUG) {
    error_log('Valyuta Display Debug:');
    error_log('Current currency: ' . $current_currency);
    error_log('Show cash: ' . ($show_cash ? 'true' : 'false'));
    error_log('Banks with rates count: ' . count($banks_with_rates));
}
?>

<div class="valyuta-container" id="<?php echo $unique_id; ?>">
    <div class="valyuta-header">
        <h2 class="valyuta-title">Bank məzənnələri</h2>
        <p class="valyuta-subtitle">Məzənnələrin saytlardan ilk</p>
    </div>
    
    <table class="valyuta-table" id="rates-table-<?php echo $unique_id; ?>">
        <thead>
            <tr>
                <th rowspan="2" class="bank-column">
                    <select id="currency-select-<?php echo $unique_id; ?>" class="currency-dropdown">
                        <option value="USD" selected="selected">USD 🇺🇸</option>
                        <option value="EUR">EUR 🇪🇺</option>
                        <option value="RUB">RUB 🇷🇺</option>
                        <option value="GBP">GBP 🇬🇧</option>
                        <option value="TRY">TRY 🇹🇷</option>
                    </select>
                </th>
                <th colspan="2">Nağd</th>
                <th colspan="2">Nağdsız</th>
            </tr>
            <tr>
                <th>Alış</th>
                <th>Satış</th>
                <th>Alış</th>
                <th>Satış</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($banks_with_rates as $bank): ?>
            <tr>
                <td class="bank-name"><?php echo esc_html($bank->bank_name); ?></td>
                <td class="rate-cell"><?php echo $bank->cash_buy_rate ? number_format($bank->cash_buy_rate, 4) : '-'; ?></td>
                <td class="rate-cell"><?php echo $bank->cash_sell_rate ? number_format($bank->cash_sell_rate, 4) : '-'; ?></td>
                <td class="rate-cell"><?php echo $bank->buy_rate ? number_format($bank->buy_rate, 4) : '-'; ?></td>
                <td class="rate-cell"><?php echo $bank->sell_rate ? number_format($bank->sell_rate, 4) : '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Hidden button for functionality -->
    <button id="fetch-live-rates-<?php echo $unique_id; ?>" style="display: none;"></button>
</div>

<style>
#<?php echo $unique_id; ?> .valyuta-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0;
    background: transparent;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border-radius: 12px;
    overflow: hidden;
}

#<?php echo $unique_id; ?> .valyuta-header {
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    color: white;
    text-align: center;
    padding: 20px;
    margin: 0;
}

#<?php echo $unique_id; ?> .valyuta-title {
    color: white;
    font-size: 20px;
    font-weight: 600;
    margin: 0;
}

#<?php echo $unique_id; ?> .valyuta-subtitle {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
    margin: 5px 0 0 0;
    font-weight: 400;
}

#<?php echo $unique_id; ?> .valyuta-table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    margin: 0;
    font-size: 14px;
}

#<?php echo $unique_id; ?> .valyuta-table th {
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    color: white;
    padding: 16px 12px;
    text-align: center;
    font-weight: 600;
    font-size: 14px;
    border: none;
    border-right: 1px solid rgba(255,255,255,0.2);
}

#<?php echo $unique_id; ?> .valyuta-table th:last-child {
    border-right: none;
}

#<?php echo $unique_id; ?> .bank-column {
    text-align: center !important;
    min-width: 180px;
    vertical-align: middle;
}

#<?php echo $unique_id; ?> .currency-dropdown {
    background: rgba(255,255,255,1);
    border: 2px solid rgba(255,255,255,0.8);
    border-radius: 8px;
    padding: 8px 12px;
    color: #333;
    font-size: 14px;
    font-weight: 600;
    min-width: 140px;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,<svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L6 6L11 1" stroke="%23333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 40px;
}

#<?php echo $unique_id; ?> .currency-dropdown:focus {
    outline: none;
    background: white;
    box-shadow: 0 0 0 3px rgba(255,255,255,0.6);
    border-color: white;
}

#<?php echo $unique_id; ?> .valyuta-table td {
    padding: 14px 12px;
    text-align: center;
    border-bottom: 1px solid #e8ecf0;
    background: white;
    border-right: 1px solid #e8ecf0;
}

#<?php echo $unique_id; ?> .valyuta-table td:last-child {
    border-right: none;
}

#<?php echo $unique_id; ?> .bank-name {
    text-align: left !important;
    font-weight: 600;
    color: #2c3e50;
    padding-left: 16px;
    border-right: 1px solid #e8ecf0;
}

#<?php echo $unique_id; ?> .rate-cell {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

#<?php echo $unique_id; ?> .valyuta-table tbody tr:hover td {
    background: #f0f6ff;
}

#<?php echo $unique_id; ?> .valyuta-table tbody tr:nth-child(even) td {
    background: #fafbfc;
}

#<?php echo $unique_id; ?> .valyuta-table tbody tr:nth-child(even):hover td {
    background: #f0f6ff;
}

#<?php echo $unique_id; ?> .loading-text {
    color: #7f8c8d;
    font-style: italic;
    font-size: 13px;
}

#<?php echo $unique_id; ?> .no-data {
    color: #e74c3c;
    font-size: 13px;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    #<?php echo $unique_id; ?> .valyuta-container {
        margin: 10px;
        border-radius: 8px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-header {
        padding: 15px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-title {
        font-size: 18px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-subtitle {
        font-size: 12px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-table {
        font-size: 12px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-table th,
    #<?php echo $unique_id; ?> .valyuta-table td {
        padding: 10px 8px;
    }
    
    #<?php echo $unique_id; ?> .currency-dropdown {
        min-width: 120px;
        font-size: 12px;
        padding: 6px 10px;
        padding-right: 32px;
    }
    
    #<?php echo $unique_id; ?> .bank-name {
        padding-left: 12px;
    }
    
    #<?php echo $unique_id; ?> .bank-column {
        min-width: 140px;
    }
}

@media (max-width: 480px) {
    #<?php echo $unique_id; ?> .valyuta-table {
        font-size: 11px;
    }
    
    #<?php echo $unique_id; ?> .bank-column {
        min-width: 120px;
    }
    
    #<?php echo $unique_id; ?> .currency-dropdown {
        min-width: 100px;
        font-size: 11px;
    }
}
</style>

<script>
// Debug and localize ajaxurl for frontend (check if already exists)
if (typeof ajaxurl === 'undefined') {
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
}
console.log('AJAX URL:', ajaxurl);

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing valyuta system...');
    const uniqueId = '<?php echo $unique_id; ?>';
    console.log('Unique ID:', uniqueId);
    
    const currencySelect = document.getElementById('currency-select-' + uniqueId);
    const ratesTable = document.getElementById('rates-table-' + uniqueId);
    const fetchLiveBtn = document.getElementById('fetch-live-rates-' + uniqueId);
    const lastUpdatedEl = document.getElementById('last-updated-' + uniqueId);
    
    console.log('Currency select found:', currencySelect);
    console.log('Rates table found:', ratesTable);
    console.log('Fetch button found:', fetchLiveBtn);
    
    
    if (fetchLiveBtn) {
        fetchLiveBtn.addEventListener('click', function() {
            const selectedCurrency = currencySelect.value;
            fetchLiveRates(selectedCurrency);
        });
    }
    
    function updateRates(currency, fromLiveFetch = false) {
        // Show loading state
        ratesTable.style.opacity = '0.6';
        
        // Show loading message in table
        const tbody = ratesTable.querySelector('tbody');
        const originalContent = tbody.innerHTML;
        const colCount = 5; // Always 5 columns now (Banklar + 4 rate columns)
        tbody.innerHTML = '<tr><td colspan="' + colCount + '" style="text-align: center; padding: 40px;"><span style="color: #667eea;">📊 Məzənnələr yüklənir...</span></td></tr>';
        
        // Make AJAX request to get new rates
        const url = ajaxurl + '?action=get_rates&currency=' + currency;
        console.log('Making AJAX request to:', url);
        
        fetch(url)
            .then(response => {
                console.log('AJAX response status:', response.status);
                console.log('AJAX response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('AJAX response data:', data);
                if (data.success && data.data && data.data.length > 0) {
                    updateTableContent(data.data);
                    if (fromLiveFetch) {
                        updateLastUpdatedTime();
                        showSuccessMessage('Məzənnələr uğurla yeniləndi!');
                    }
                } else {
                    // If no data, try to fetch live rates automatically
                    console.log('No cached data found, fetching live rates...');
                    if (!fromLiveFetch) {
                        fetchLiveRates(currency);
                        return; // fetchLiveRates will handle the UI updates
                    } else {
                        tbody.innerHTML = '<tr><td colspan="' + colCount + '" style="text-align: center; padding: 40px;"><span style="color: #dc3545;">❌ Bu valyuta üçün məlumat tapılmadı</span></td></tr>';
                    }
                }
                ratesTable.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error fetching rates:', error);
                tbody.innerHTML = originalContent; // Restore original content on error
                ratesTable.style.opacity = '1';
                if (fromLiveFetch) {
                    showErrorMessage('Məzənnələr yenilənərkən xəta baş verdi.');
                }
            });
    }
    
    function fetchLiveRates(currency) {
        const btnText = fetchLiveBtn.querySelector('.btn-text');
        const btnLoading = fetchLiveBtn.querySelector('.btn-loading');
        
        // Show loading state
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
        fetchLiveBtn.disabled = true;
        ratesTable.style.opacity = '0.6';
        
        // Create FormData for POST request
        const formData = new FormData();
        formData.append('action', 'fetch_live_rates');
        formData.append('currency', currency);
        formData.append('force_refresh', 'true');
        formData.append('nonce', '<?php echo wp_create_nonce('valyuta_fetch_rates'); ?>');
        
        // Make AJAX request to fetch live rates
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update table with new rates
                    updateTableContent(data.data.rates);
                    updateLastUpdatedTime();
                    showSuccessMessage(data.data.message || 'Canlı məzənnələr uğurla yeniləndi!');
                } else {
                    showErrorMessage(data.data || 'Canlı məzənnələr yenilənərkən xəta baş verdi.');
                }
            })
            .catch(error => {
                console.error('Error fetching live rates:', error);
                showErrorMessage('API ilə əlaqə qurula bilmədi.');
            })
            .finally(() => {
                // Reset button state
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                fetchLiveBtn.disabled = false;
                ratesTable.style.opacity = '1';
            });
    }
    
    function updateLastUpdatedTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('az-AZ', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        });
        const updateTimeEl = lastUpdatedEl.querySelector('.update-time');
        if (updateTimeEl) {
            updateTimeEl.textContent = timeStr;
        }
    }
    
    function showSuccessMessage(message) {
        showNotification(message, 'success');
    }
    
    function showErrorMessage(message) {
        showNotification(message, 'error');
    }
    
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `valyuta-notification ${type}`;
        notification.textContent = message;
        
        // Style the notification
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            animation: slideInRight 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Remove notification after 4 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }
    
    // Add CSS animations
    if (!document.getElementById('valyuta-animations')) {
        const style = document.createElement('style');
        style.id = 'valyuta-animations';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Initialize with current time
    updateLastUpdatedTime();
    
    // Auto-load data on page load and when currency changes
    function initializeRates() {
        // Force set USD as default
        if (currencySelect) {
            currencySelect.value = 'USD';
            // Force refresh the dropdown display
            currencySelect.selectedIndex = 0;
            // Trigger change event to ensure styling updates
            currencySelect.dispatchEvent(new Event('change'));
            console.log('Set currency dropdown to USD');
            console.log('Current dropdown value:', currencySelect.value);
            console.log('Selected index:', currencySelect.selectedIndex);
        } else {
            console.error('Currency select element not found!');
        }
        const selectedCurrency = 'USD';
        console.log('Initializing with currency:', selectedCurrency);
        updateRates(selectedCurrency, false);
    }
    
    // Auto-load rates when currency dropdown changes
    if (currencySelect) {
        currencySelect.addEventListener('change', function() {
            const selectedCurrency = this.value;
            console.log('Currency changed to:', selectedCurrency);
            updateRates(selectedCurrency, false);
        });
        
        // Initialize rates on page load
        initializeRates();
        
        // Additional fix: Set focus and blur to force refresh
        setTimeout(function() {
            if (currencySelect) {
                currencySelect.focus();
                currencySelect.blur();
                console.log('Applied focus/blur fix for dropdown display');
            }
        }, 100);
    }
    
    function updateTableContent(banks) {
        const tbody = ratesTable.querySelector('tbody');
        tbody.innerHTML = '';
        
        banks.forEach(bank => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td class="bank-name">${bank.bank_name}</td>
                <td class="rate-cell">${bank.cash_buy_rate ? parseFloat(bank.cash_buy_rate).toFixed(4) : '-'}</td>
                <td class="rate-cell">${bank.cash_sell_rate ? parseFloat(bank.cash_sell_rate).toFixed(4) : '-'}</td>
                <td class="rate-cell">${bank.buy_rate ? parseFloat(bank.buy_rate).toFixed(4) : '-'}</td>
                <td class="rate-cell">${bank.sell_rate ? parseFloat(bank.sell_rate).toFixed(4) : '-'}</td>
            `;
            
            tbody.appendChild(row);
        });
    }
});

// Make ajaxurl available for frontend
<?php if (!is_admin()): ?>
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
<?php endif; ?>
</script>
<?php
/**
 * Valyuta Rates Display Template
 * Exact design matching the screenshot
 */

// Get unique ID for this instance
$unique_id = uniqid('valyuta_');
$current_currency = !empty($atts['currency']) ? $atts['currency'] : 'USD';
$show_cash = $atts['show_cash'] === 'true';

// Debug information
if (WP_DEBUG) {
    error_log('Valyuta Display Debug:');
    error_log('Current currency: ' . $current_currency);
    error_log('Show cash: ' . ($show_cash ? 'true' : 'false'));
    error_log('Banks with rates count: ' . count($banks_with_rates));
}
?>

<div class="valyuta-rates-container" id="<?php echo $unique_id; ?>">
    <div class="valyuta-header">
        <h2 class="valyuta-title">Bank məzənnələri</h2>
        <p class="valyuta-subtitle">Məzənnələri aşağılıqla izlə</p>
    </div>
    
    <div class="valyuta-controls">
        <div class="controls-left">
            <div class="currency-selector">
                <select id="currency-select-<?php echo $unique_id; ?>" class="currency-dropdown">
                    <option value="USD" <?php selected($current_currency, 'USD'); ?>>🇺🇸 USD</option>
                    <option value="EUR" <?php selected($current_currency, 'EUR'); ?>>🇪🇺 EUR</option>
                    <option value="RUB" <?php selected($current_currency, 'RUB'); ?>>🇷🇺 RUB</option>
                    <option value="GBP" <?php selected($current_currency, 'GBP'); ?>>🇬🇧 GBP</option>
                    <option value="TRY" <?php selected($current_currency, 'TRY'); ?>>🇹🇷 TRY</option>
                </select>
            </div>
        </div>
        <div class="controls-right">
            <button id="fetch-live-rates-<?php echo $unique_id; ?>" class="fetch-live-btn">
                <span class="btn-text">🔄 Canlı məzənnələr</span>
                <span class="btn-loading" style="display: none;">⏳ Yenilənir...</span>
            </button>
            <div class="last-updated" id="last-updated-<?php echo $unique_id; ?>">
                <small>Son yeniləmə: <span class="update-time">Bilinmir</span></small>
            </div>
        </div>
    </div>
    
    <div class="valyuta-table-container">
        <table class="valyuta-rates-table" id="rates-table-<?php echo $unique_id; ?>">
            <thead>
                <tr>
                    <th class="bank-column">Banklar</th>
                    <th class="rate-column">Alış</th>
                    <th class="rate-column">Satış</th>
                    <?php if ($show_cash): ?>
                    <th class="rate-column cash-column">Nağd</th>
                    <th class="rate-column cash-column">Nağdsız</th>
                    <?php endif; ?>
                </tr>
                <tr class="header-labels">
                    <th></th>
                    <th></th>
                    <th></th>
                    <?php if ($show_cash): ?>
                    <th class="sub-header">Alış</th>
                    <th class="sub-header">Satış</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banks_with_rates as $bank): ?>
                <tr class="bank-row">
                    <td class="bank-name">
                        <span class="bank-title"><?php echo esc_html($bank->bank_name); ?></span>
                    </td>
                    <td class="rate-value buy-rate">
                        <?php echo $bank->buy_rate ? number_format($bank->buy_rate, 4) : '-'; ?>
                    </td>
                    <td class="rate-value sell-rate">
                        <?php echo $bank->sell_rate ? number_format($bank->sell_rate, 4) : '-'; ?>
                    </td>
                    <?php if ($show_cash): ?>
                    <td class="rate-value cash-buy-rate">
                        <?php echo $bank->cash_buy_rate ? number_format($bank->cash_buy_rate, 4) : '-'; ?>
                    </td>
                    <td class="rate-value cash-sell-rate">
                        <?php echo $bank->cash_sell_rate ? number_format($bank->cash_sell_rate, 4) : '-'; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="valyuta-info">
        <div class="info-box">
            <p class="info-text">
                <i class="info-icon">ℹ️</i>
                Valyuta məzənnələrinin birbaşa bank saytlarından son 10 dəqiqədən bir yenilənir. Bunu baxmayaraq, düzgün alınmın məlumatlar da ola bilər. Məlumatların dəqiqliyindən əlaqəli normalarla ilə dəqiqləşdirin.
            </p>
            <a href="#" class="update-link">Bankları aldırım və məzənə fərqləri</a>
        </div>
    </div>
</div>

<style>
#<?php echo $unique_id; ?> .valyuta-rates-container {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 0;
    margin: 20px 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

#<?php echo $unique_id; ?> .valyuta-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 30px;
    text-align: left;
}

#<?php echo $unique_id; ?> .valyuta-title {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 8px 0;
    line-height: 1.2;
}

#<?php echo $unique_id; ?> .valyuta-subtitle {
    font-size: 16px;
    margin: 0;
    opacity: 0.9;
    font-weight: 400;
}

#<?php echo $unique_id; ?> .valyuta-controls {
    background: white;
    padding: 20px 30px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

#<?php echo $unique_id; ?> .controls-left {
    display: flex;
    align-items: center;
}

#<?php echo $unique_id; ?> .controls-right {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

#<?php echo $unique_id; ?> .currency-selector {
    display: flex;
    align-items: center;
}

#<?php echo $unique_id; ?> .fetch-live-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

#<?php echo $unique_id; ?> .fetch-live-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

#<?php echo $unique_id; ?> .fetch-live-btn:active {
    transform: translateY(0);
}

#<?php echo $unique_id; ?> .fetch-live-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

#<?php echo $unique_id; ?> .last-updated {
    color: #6c757d;
    font-size: 12px;
}

#<?php echo $unique_id; ?> .update-time {
    font-weight: 500;
    color: #495057;
}

#<?php echo $unique_id; ?> .currency-dropdown {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 16px;
    font-weight: 500;
    color: #495057;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,<svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L6 6L11 1" stroke="%23495057" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 50px;
    min-width: 150px;
}

#<?php echo $unique_id; ?> .currency-dropdown:hover {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

#<?php echo $unique_id; ?> .currency-dropdown:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

#<?php echo $unique_id; ?> .valyuta-table-container {
    background: white;
    overflow-x: auto;
}

#<?php echo $unique_id; ?> .valyuta-rates-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    font-size: 14px;
}

#<?php echo $unique_id; ?> .valyuta-rates-table thead th {
    background: #f8f9fa;
    color: #6c757d;
    font-weight: 600;
    padding: 16px 20px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#<?php echo $unique_id; ?> .valyuta-rates-table .header-labels th {
    background: #f8f9fa;
    padding: 8px 20px;
    font-size: 12px;
    font-weight: 500;
    color: #868e96;
}

#<?php echo $unique_id; ?> .valyuta-rates-table .bank-column {
    min-width: 200px;
}

#<?php echo $unique_id; ?> .valyuta-rates-table .rate-column {
    min-width: 80px;
    text-align: center;
}

#<?php echo $unique_id; ?> .valyuta-rates-table .cash-column {
    background: #f1f3f4;
}

#<?php echo $unique_id; ?> .valyuta-rates-table .sub-header {
    text-align: center;
    font-size: 11px;
}

#<?php echo $unique_id; ?> .valyuta-rates-table tbody tr {
    transition: background-color 0.2s ease;
}

#<?php echo $unique_id; ?> .valyuta-rates-table tbody tr:hover {
    background-color: #f8f9fa;
}

#<?php echo $unique_id; ?> .valyuta-rates-table tbody tr:nth-child(even) {
    background-color: #fdfdfd;
}

#<?php echo $unique_id; ?> .valyuta-rates-table tbody td {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
}

#<?php echo $unique_id; ?> .bank-name {
    font-weight: 500;
    color: #212529;
    font-size: 14px;
}

#<?php echo $unique_id; ?> .rate-value {
    text-align: center;
    font-weight: 500;
    color: #495057;
    font-size: 14px;
    font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
}

#<?php echo $unique_id; ?> .valyuta-info {
    background: #e3f2fd;
    padding: 20px 30px;
}

#<?php echo $unique_id; ?> .info-box {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

#<?php echo $unique_id; ?> .info-text {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 0;
    font-size: 13px;
    line-height: 1.5;
    color: #1565c0;
}

#<?php echo $unique_id; ?> .info-icon {
    font-size: 16px;
    margin-top: 1px;
}

#<?php echo $unique_id; ?> .update-link {
    color: #1976d2;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

#<?php echo $unique_id; ?> .update-link:hover {
    text-decoration: underline;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    #<?php echo $unique_id; ?> .valyuta-header {
        padding: 30px 20px;
        text-align: center;
    }
    
    #<?php echo $unique_id; ?> .valyuta-title {
        font-size: 24px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-controls {
        padding: 15px 20px;
    }
    
    #<?php echo $unique_id; ?> .currency-dropdown {
        width: 100%;
        max-width: 200px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-rates-table {
        font-size: 12px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-rates-table thead th,
    #<?php echo $unique_id; ?> .valyuta-rates-table tbody td {
        padding: 12px 10px;
    }
    
    #<?php echo $unique_id; ?> .bank-column {
        min-width: 150px;
    }
    
    #<?php echo $unique_id; ?> .rate-column {
        min-width: 60px;
    }
    
    #<?php echo $unique_id; ?> .valyuta-info {
        padding: 15px 20px;
    }
}

@media (max-width: 480px) {
    #<?php echo $unique_id; ?> .valyuta-rates-table {
        font-size: 11px;
    }
    
    #<?php echo $unique_id; ?> .bank-column {
        min-width: 120px;
    }
    
    #<?php echo $unique_id; ?> .rate-column {
        min-width: 50px;
    }
}
</style>

<script>
// Localize ajaxurl for frontend
const ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

document.addEventListener('DOMContentLoaded', function() {
    const uniqueId = '<?php echo $unique_id; ?>';
    const currencySelect = document.getElementById('currency-select-' + uniqueId);
    const ratesTable = document.getElementById('rates-table-' + uniqueId);
    const fetchLiveBtn = document.getElementById('fetch-live-rates-' + uniqueId);
    const lastUpdatedEl = document.getElementById('last-updated-' + uniqueId);
    
    
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
        const colCount = <?php echo $show_cash ? '5' : '3'; ?>;
        tbody.innerHTML = '<tr><td colspan="' + colCount + '" style="text-align: center; padding: 40px;"><span style="color: #667eea;">📊 Məzənnələr yüklənir...</span></td></tr>';
        
        // Make AJAX request to get new rates
        const url = ajaxurl + '?action=get_rates&currency=' + currency;
        console.log('Making AJAX request to:', url);
        
        fetch(url)
            .then(response => {
                console.log('AJAX response status:', response.status);
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
        
        console.log('Fetching live rates for currency:', currency);
        console.log('AJAX URL:', ajaxurl);
        
        // Make AJAX request to fetch live rates
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                console.log('Live rates response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Live rates response data:', data);
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
        const selectedCurrency = currencySelect.value || 'USD';
        // Set USD as default if no currency is selected
        if (!currencySelect.value) {
            currencySelect.value = 'USD';
        }
        console.log('Initializing with currency:', selectedCurrency);
        console.log('Currency select element:', currencySelect);
        console.log('Currency select value:', currencySelect.value);
        updateRates(selectedCurrency, false);
    }
    
    // Auto-load rates when currency dropdown changes
    if (currencySelect) {
        currencySelect.addEventListener('change', function() {
            const selectedCurrency = this.value;
            updateRates(selectedCurrency, false);
        });
        
        // Initialize rates on page load
        initializeRates();
    }
    
    function updateTableContent(banks) {
        const tbody = ratesTable.querySelector('tbody');
        tbody.innerHTML = '';
        
        banks.forEach(bank => {
            const row = document.createElement('tr');
            row.className = 'bank-row';
            
            const showCash = <?php echo $show_cash ? 'true' : 'false'; ?>;
            const cashColumns = showCash ? `
                <td class="rate-value cash-buy-rate">
                    ${bank.cash_buy_rate ? parseFloat(bank.cash_buy_rate).toFixed(4) : '-'}
                </td>
                <td class="rate-value cash-sell-rate">
                    ${bank.cash_sell_rate ? parseFloat(bank.cash_sell_rate).toFixed(4) : '-'}
                </td>
            ` : '';
            
            row.innerHTML = `
                <td class="bank-name">
                    <span class="bank-title">${bank.bank_name}</span>
                </td>
                <td class="rate-value buy-rate">
                    ${bank.buy_rate ? parseFloat(bank.buy_rate).toFixed(4) : '-'}
                </td>
                <td class="rate-value sell-rate">
                    ${bank.sell_rate ? parseFloat(bank.sell_rate).toFixed(4) : '-'}
                </td>
                ${cashColumns}
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
<?php
/**
 * Valyuta Rates Display Template
 * Exact design matching the screenshot
 */

// Get unique ID for this instance
$unique_id = uniqid('valyuta_');
$current_currency = $atts['currency'];
$show_cash = $atts['show_cash'] === 'true';
?>

<div class="valyuta-rates-container" id="<?php echo $unique_id; ?>">
    <div class="valyuta-header">
        <h2 class="valyuta-title">Bank məzənnələri</h2>
        <p class="valyuta-subtitle">Məzənnələri aşağılıqla izlə</p>
    </div>
    
    <div class="valyuta-controls">
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
}

#<?php echo $unique_id; ?> .currency-selector {
    display: flex;
    align-items: center;
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
document.addEventListener('DOMContentLoaded', function() {
    const uniqueId = '<?php echo $unique_id; ?>';
    const currencySelect = document.getElementById('currency-select-' + uniqueId);
    const ratesTable = document.getElementById('rates-table-' + uniqueId);
    
    if (currencySelect) {
        currencySelect.addEventListener('change', function() {
            const selectedCurrency = this.value;
            updateRates(selectedCurrency);
        });
    }
    
    function updateRates(currency) {
        // Show loading state
        ratesTable.style.opacity = '0.6';
        
        // Make AJAX request to get new rates
        fetch(ajaxurl + '?action=get_rates&currency=' + currency)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTableContent(data.data);
                }
                ratesTable.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error fetching rates:', error);
                ratesTable.style.opacity = '1';
            });
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
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
    <div class="overflow-x-auto">
        <div class="w-full min-w-[800px] grid grid-cols-3 gap-5">
            <div class="space-y-3 w-auto">
                <div class="currency-dropdown-wrapper">
                    <div class="currency-dropdown-button" id="currency-button-<?php echo $unique_id; ?>">
                        <div class="currency-display">
                            <span class="currency-text">USD 🇺🇸</span>
                        </div>
                        <svg class="dropdown-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                    <div class="currency-dropdown-menu" id="currency-menu-<?php echo $unique_id; ?>">
                        <div class="currency-option" data-value="USD">
                            <span class="currency-text">USD 🇺🇸</span>
                        </div>
                        <div class="currency-option" data-value="EUR">
                            <span class="currency-text">EUR 🇪🇺</span>
                        </div>
                        <div class="currency-option" data-value="RUB">
                            <span class="currency-text">RUB 🇷🇺</span>
                        </div>
                        <div class="currency-option" data-value="GBP">
                            <span class="currency-text">GBP 🇬🇧</span>
                        </div>
                        <div class="currency-option" data-value="TRY">
                            <span class="currency-text">TRY 🇹🇷</span>
                        </div>
                    </div>
                    <select id="currency-select-<?php echo $unique_id; ?>" style="display: none;">
                        <option value="USD" selected="selected">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="RUB">RUB</option>
                        <option value="GBP">GBP</option>
                        <option value="TRY">TRY</option>
                    </select>
                </div>
                <div class="bank-header">Banklar</div>
                <div class="bank-list">
                    <?php foreach ($banks_with_rates as $bank): ?>
                    <div class="bank-name"><?php echo esc_html($bank->bank_name); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="space-y-3">
                <h4 class="column-header">Nağd</h4>
                <div class="rate-subheader">
                    <div class="text-center">Alış</div>
                    <div class="text-center">Satış</div>
                </div>
                <div class="rate-list">
                    <?php foreach ($banks_with_rates as $bank): ?>
                    <div class="rate-row">
                        <div class="rate-cell"><?php echo $bank->cash_buy_rate ? number_format($bank->cash_buy_rate, 4) : '-'; ?></div>
                        <div class="rate-cell"><?php echo $bank->cash_sell_rate ? number_format($bank->cash_sell_rate, 4) : '-'; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="space-y-3">
                <h4 class="column-header">Nağdsız</h4>
                <div class="rate-subheader">
                    <div class="text-center">Alış</div>
                    <div class="text-center">Satış</div>
                </div>
                <div class="rate-list">
                    <?php foreach ($banks_with_rates as $bank): ?>
                    <div class="rate-row">
                        <div class="rate-cell"><?php echo $bank->buy_rate ? number_format($bank->buy_rate, 4) : '-'; ?></div>
                        <div class="rate-cell"><?php echo $bank->sell_rate ? number_format($bank->sell_rate, 4) : '-'; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <button id="fetch-live-rates-<?php echo $unique_id; ?>" style="display: none;"></button>
    
    <div class="info-notice">
        <div class="info-icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none">
                <path fill="#8199D3" d="M20 0a20 20 0 1 0 0 40 20 20 0 0 0 0-40Zm2 28a2 2 0 0 1-4 0V18a2 2 0 0 1 4 0v10Zm-2-14a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"></path>
            </svg>
        </div>
        <div class="info-content">
            <p class="info-text">Valyuta məzənnələrini birbaşa bank saytlarından hər 10 dəqiqədən bir yenilənir. Buna baxmayaraq, düzgün olmayan məlumatlar da ala bilərik. Məlumatları bankların əlaqə nömrələri ilə dəqiqləşdirin.</p>
            <a class="info-link" href="/contact">Bankda oldum və məzənə fərqlidir.</a>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div class="faq-section">
        <div class="faq-header">
            <h2 class="faq-title">Ən çox soruşulanlar</h2>
        </div>
        <div class="faq-accordion">
            <?php 
            $faqs = get_valyuta_faqs();
            foreach ($faqs as $index => $faq): 
                $faq_id = 'faq-' . $unique_id . '-' . $faq->id;
            ?>
            <div class="faq-item" data-faq-id="<?php echo $faq->id; ?>">
                <button type="button" class="faq-question" aria-expanded="false" data-target="#<?php echo $faq_id; ?>">
                    <span class="faq-question-text"><?php echo esc_html($faq->question); ?></span>
                    <svg class="faq-arrow" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none">
                        <path stroke="#8199D3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m4 9 8 8 8-8"/>
                    </svg>
                </button>
                <div class="faq-answer" id="<?php echo $faq_id; ?>">
                    <div class="faq-answer-content">
                        <?php echo wp_kses_post($faq->answer); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
#<?php echo $unique_id; ?> .valyuta-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

#<?php echo $unique_id; ?> .overflow-x-auto {
    overflow-x: auto;
}

#<?php echo $unique_id; ?> .grid {
    display: grid;
}

#<?php echo $unique_id; ?> .grid-cols-3 {
    grid-template-columns: repeat(3, 1fr);
}

#<?php echo $unique_id; ?> .grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
}

#<?php echo $unique_id; ?> .gap-5 {
    gap: 20px;
}

#<?php echo $unique_id; ?> .gap-10 {
    gap: 40px;
}

#<?php echo $unique_id; ?> .space-y-3 > * + * {
    margin-top: 12px;
}

#<?php echo $unique_id; ?> .space-y-5 > * + * {
    margin-top: 20px;
}

#<?php echo $unique_id; ?> .w-full {
    width: 100%;
}

#<?php echo $unique_id; ?> .min-w-[800px] {
    min-width: 800px;
}

#<?php echo $unique_id; ?> .currency-dropdown-wrapper {
    position: relative;
    width: 100%;
}

#<?php echo $unique_id; ?> .currency-dropdown-button {
    background: #F5F5F5;
    height: 56px;
    border: none;
    border-radius: 10px;
    padding: 0 24px;
    font-size: 18px;
    font-weight: 500;
    width: 100%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

#<?php echo $unique_id; ?> .currency-display {
    display: flex;
    align-items: center;
}

#<?php echo $unique_id; ?> .currency-text {
    font-size: 18px;
    font-weight: 500;
    color: #000;
}

#<?php echo $unique_id; ?> .dropdown-arrow {
    width: 24px;
    height: 24px;
    color: #666;
    transition: transform 0.2s ease;
}

#<?php echo $unique_id; ?> .currency-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #F5F5F5;
    border-radius: 10px;
    margin-top: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: none;
    overflow: hidden;
}

#<?php echo $unique_id; ?> .currency-dropdown-menu.open {
    display: block;
}

#<?php echo $unique_id; ?> .currency-option {
    padding: 16px 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

#<?php echo $unique_id; ?> .currency-option:last-child {
    border-bottom: none;
}

#<?php echo $unique_id; ?> .currency-option:hover {
    background: rgba(0, 0, 0, 0.05);
}

#<?php echo $unique_id; ?> .currency-option.selected {
    background: rgba(0, 123, 255, 0.1);
}

#<?php echo $unique_id; ?> .currency-dropdown-button.open .dropdown-arrow {
    transform: rotate(180deg);
}

#<?php echo $unique_id; ?> .bank-header,
#<?php echo $unique_id; ?> .column-header {
    background: #F5F5F5;
    height: 56px;
    border-radius: 10px;
    padding: 0 24px;
    text-align: center;
    font-size: 18px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
}

#<?php echo $unique_id; ?> .rate-subheader {
    background: #F5F5F5;
    height: 56px;
    border-radius: 10px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    align-items: center;
}

#<?php echo $unique_id; ?> .bank-list,
#<?php echo $unique_id; ?> .rate-list {
    background: #F5F5F5;
    padding: 24px;
    border-radius: 10px;
}

#<?php echo $unique_id; ?> .bank-list > * + *,
#<?php echo $unique_id; ?> .rate-list > * + * {
    margin-top: 20px;
}

#<?php echo $unique_id; ?> .bank-name {
    font-size: 18px;
    font-weight: normal;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#<?php echo $unique_id; ?> .rate-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 40px;
}

#<?php echo $unique_id; ?> .rate-cell {
    font-size: 18px;
    font-weight: normal;
    text-align: center;
}

#<?php echo $unique_id; ?> .text-center {
    text-align: center;
}

#<?php echo $unique_id; ?> .info-notice {
    display: flex;
    gap: 18px;
    padding: 24px;
    background: rgba(221, 230, 252, 0.5);
    border-radius: 12px;
    margin-top: 20px;
    margin-bottom: 80px;
}

#<?php echo $unique_id; ?> .info-icon-wrapper {
    flex-shrink: 0;
}

#<?php echo $unique_id; ?> .info-content {
    flex: 1;
}

#<?php echo $unique_id; ?> .info-text {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 20px;
    color: #333;
    line-height: 1.5;
    margin-top: 0;
}

#<?php echo $unique_id; ?> .info-link {
    font-size: 16px;
    font-weight: 500;
    color: #7384AC;
    text-decoration: underline;
}

#<?php echo $unique_id; ?> .faq-section {
    margin-top: 40px;
    margin-bottom: 80px;
}

#<?php echo $unique_id; ?> .faq-header {
    max-width: 46vw;
    margin-bottom: 32px;
}

#<?php echo $unique_id; ?> .faq-title {
    font-size: 30px;
    font-weight: 600;
    color: #333;
    margin: 0;
    line-height: 1.2;
}

#<?php echo $unique_id; ?> .faq-accordion {
    space-y: 16px;
}

#<?php echo $unique_id; ?> .faq-item {
    border-bottom: 1px solid #e5e7eb;
}

#<?php echo $unique_id; ?> .faq-question {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: all 0.3s ease;
}

#<?php echo $unique_id; ?> .faq-question:hover {
    text-decoration: underline;
}

#<?php echo $unique_id; ?> .faq-question-text {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    flex: 1;
    padding-right: 16px;
}

#<?php echo $unique_id; ?> .faq-arrow {
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

#<?php echo $unique_id; ?> .faq-question[aria-expanded="true"] .faq-arrow {
    transform: rotate(180deg);
}

#<?php echo $unique_id; ?> .faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
}

#<?php echo $unique_id; ?> .faq-answer.open {
    max-height: 500px;
    padding-bottom: 16px;
}

#<?php echo $unique_id; ?> .faq-answer-content {
    font-size: 14px;
    line-height: 1.6;
    color: #666;
}

#<?php echo $unique_id; ?> .faq-answer-content p {
    margin: 0;
}

#<?php echo $unique_id; ?> .faq-answer-content p + p {
    margin-top: 12px;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    #<?php echo $unique_id; ?> .valyuta-container {
        padding: 10px;
    }
    
    #<?php echo $unique_id; ?> .min-w-[800px] {
        min-width: 600px;
    }
    
    #<?php echo $unique_id; ?> .currency-dropdown-button,
    #<?php echo $unique_id; ?> .bank-header,
    #<?php echo $unique_id; ?> .column-header,
    #<?php echo $unique_id; ?> .rate-subheader {
        height: 48px;
        font-size: 16px;
    }
    
    #<?php echo $unique_id; ?> .currency-text {
        font-size: 16px;
    }
    
    #<?php echo $unique_id; ?> .dropdown-arrow {
        width: 20px;
        height: 20px;
    }
    
    #<?php echo $unique_id; ?> .bank-name,
    #<?php echo $unique_id; ?> .rate-cell {
        font-size: 16px;
    }
    
    #<?php echo $unique_id; ?> .bank-list,
    #<?php echo $unique_id; ?> .rate-list {
        padding: 20px;
    }
    
    #<?php echo $unique_id; ?> .info-notice {
        gap: 16px;
        padding: 20px;
        margin-bottom: 60px;
    }
    
    #<?php echo $unique_id; ?> .info-text,
    #<?php echo $unique_id; ?> .info-link {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    #<?php echo $unique_id; ?> .min-w-[800px] {
        min-width: 500px;
    }
    
    #<?php echo $unique_id; ?> .currency-dropdown-button,
    #<?php echo $unique_id; ?> .bank-header,
    #<?php echo $unique_id; ?> .column-header,
    #<?php echo $unique_id; ?> .rate-subheader {
        height: 44px;
        font-size: 14px;
        padding: 0 16px;
    }
    
    #<?php echo $unique_id; ?> .currency-text {
        font-size: 14px;
    }
    
    #<?php echo $unique_id; ?> .dropdown-arrow {
        width: 18px;
        height: 18px;
    }
    
    #<?php echo $unique_id; ?> .bank-name,
    #<?php echo $unique_id; ?> .rate-cell {
        font-size: 14px;
    }
    
    #<?php echo $unique_id; ?> .bank-list,
    #<?php echo $unique_id; ?> .rate-list {
        padding: 16px;
    }
    
    #<?php echo $unique_id; ?> .gap-10 {
        gap: 20px;
    }
    
    #<?php echo $unique_id; ?> .info-notice {
        gap: 12px;
        padding: 16px;
        margin-bottom: 40px;
    }
    
    #<?php echo $unique_id; ?> .info-text,
    #<?php echo $unique_id; ?> .info-link {
        font-size: 13px;
    }
    
    #<?php echo $unique_id; ?> .info-icon-wrapper svg {
        width: 32px;
        height: 32px;
    }
    
    #<?php echo $unique_id; ?> .faq-header {
        max-width: 100%;
        margin-bottom: 24px;
    }
    
    #<?php echo $unique_id; ?> .faq-title {
        font-size: 24px;
    }
    
    #<?php echo $unique_id; ?> .faq-question-text {
        font-size: 16px;
    }
    
    #<?php echo $unique_id; ?> .faq-section {
        margin-bottom: 40px;
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
    const currencyButton = document.getElementById('currency-button-' + uniqueId);
    const currencyMenu = document.getElementById('currency-menu-' + uniqueId);
    const ratesContainer = document.getElementById(uniqueId);
    const fetchLiveBtn = document.getElementById('fetch-live-rates-' + uniqueId);
    
    console.log('Currency select found:', currencySelect);
    console.log('Currency button found:', currencyButton);
    console.log('Currency menu found:', currencyMenu);
    console.log('Rates container found:', ratesContainer);
    console.log('Fetch button found:', fetchLiveBtn);
    
    
    // Custom dropdown functionality
    if (currencyButton && currencyMenu) {
        // Toggle dropdown on button click
        currencyButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown();
        });
        
        // Handle option selection
        const currencyOptions = currencyMenu.querySelectorAll('.currency-option');
        currencyOptions.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.dataset.value;
                const text = this.querySelector('.currency-text').textContent;
                
                // Update button display
                currencyButton.querySelector('.currency-text').textContent = text;
                
                // Update hidden select
                currencySelect.value = value;
                
                // Remove selected class from all options
                currencyOptions.forEach(opt => opt.classList.remove('selected'));
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Close dropdown
                closeDropdown();
                
                // Trigger rates update
                console.log('Currency changed to:', value);
                updateRates(value, false);
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!currencyButton.contains(e.target) && !currencyMenu.contains(e.target)) {
                closeDropdown();
            }
        });
    }
    
    function toggleDropdown() {
        const isOpen = currencyMenu.classList.contains('open');
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }
    
    function openDropdown() {
        currencyMenu.classList.add('open');
        currencyButton.classList.add('open');
    }
    
    function closeDropdown() {
        currencyMenu.classList.remove('open');
        currencyButton.classList.remove('open');
    }
    
    if (fetchLiveBtn) {
        fetchLiveBtn.addEventListener('click', function() {
            const selectedCurrency = currencySelect.value;
            fetchLiveRates(selectedCurrency);
        });
    }
    
    function updateRates(currency, fromLiveFetch = false) {
        // Show loading state
        ratesContainer.style.opacity = '0.6';
        
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
                    updateGridContent(data.data);
                    if (fromLiveFetch) {
                        showSuccessMessage('Məzənnələr uğurla yeniləndi!');
                    }
                } else {
                    // If no data, try to fetch live rates automatically
                    console.log('No cached data found, fetching live rates...');
                    if (!fromLiveFetch) {
                        fetchLiveRates(currency);
                        return; // fetchLiveRates will handle the UI updates
                    }
                }
                ratesContainer.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error fetching rates:', error);
                ratesContainer.style.opacity = '1';
                if (fromLiveFetch) {
                    showErrorMessage('Məzənnələr yenilənərkən xəta baş verdi.');
                }
            });
    }
    
    function fetchLiveRates(currency) {
        // Show loading state
        ratesContainer.style.opacity = '0.6';
        
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
                    // Update grid with new rates
                    updateGridContent(data.data.rates);
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
                ratesContainer.style.opacity = '1';
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
    
    // FAQ Accordion functionality
    const faqQuestions = ratesContainer.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const targetId = this.getAttribute('data-target');
            const targetAnswer = document.querySelector(targetId);
            
            if (isExpanded) {
                // Close this FAQ
                this.setAttribute('aria-expanded', 'false');
                targetAnswer.classList.remove('open');
            } else {
                // Close all other FAQs first
                faqQuestions.forEach(otherQuestion => {
                    if (otherQuestion !== this) {
                        otherQuestion.setAttribute('aria-expanded', 'false');
                        const otherTargetId = otherQuestion.getAttribute('data-target');
                        const otherTargetAnswer = document.querySelector(otherTargetId);
                        if (otherTargetAnswer) {
                            otherTargetAnswer.classList.remove('open');
                        }
                    }
                });
                
                // Open this FAQ
                this.setAttribute('aria-expanded', 'true');
                targetAnswer.classList.add('open');
            }
        });
    });
    
    // Initialize with current time
    updateLastUpdatedTime();
    
    // Auto-load data on page load and when currency changes
    function initializeRates() {
        // Force set USD as default
        if (currencySelect) {
            currencySelect.value = 'USD';
            console.log('Set currency dropdown to USD');
            console.log('Current dropdown value:', currencySelect.value);
        }
        
        // Set initial selected option in custom dropdown
        if (currencyMenu) {
            const usdOption = currencyMenu.querySelector('[data-value="USD"]');
            if (usdOption) {
                usdOption.classList.add('selected');
            }
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
    
    function updateGridContent(banks) {
        // Update bank names
        const bankList = ratesContainer.querySelector('.bank-list');
        if (bankList) {
            bankList.innerHTML = '';
            banks.forEach(bank => {
                const bankDiv = document.createElement('div');
                bankDiv.className = 'bank-name';
                bankDiv.textContent = bank.bank_name;
                bankList.appendChild(bankDiv);
            });
        }
        
        // Update cash rates (Nağd)
        const cashRatesList = ratesContainer.querySelectorAll('.rate-list')[0];
        if (cashRatesList) {
            cashRatesList.innerHTML = '';
            banks.forEach(bank => {
                const rateRow = document.createElement('div');
                rateRow.className = 'rate-row';
                rateRow.innerHTML = `
                    <div class="rate-cell">${bank.cash_buy_rate ? parseFloat(bank.cash_buy_rate).toFixed(4) : '-'}</div>
                    <div class="rate-cell">${bank.cash_sell_rate ? parseFloat(bank.cash_sell_rate).toFixed(4) : '-'}</div>
                `;
                cashRatesList.appendChild(rateRow);
            });
        }
        
        // Update cashless rates (Nağdsız)
        const cashlessRatesList = ratesContainer.querySelectorAll('.rate-list')[1];
        if (cashlessRatesList) {
            cashlessRatesList.innerHTML = '';
            banks.forEach(bank => {
                const rateRow = document.createElement('div');
                rateRow.className = 'rate-row';
                rateRow.innerHTML = `
                    <div class="rate-cell">${bank.buy_rate ? parseFloat(bank.buy_rate).toFixed(4) : '-'}</div>
                    <div class="rate-cell">${bank.sell_rate ? parseFloat(bank.sell_rate).toFixed(4) : '-'}</div>
                `;
                cashlessRatesList.appendChild(rateRow);
            });
        }
    }
});

// Make ajaxurl available for frontend
<?php if (!is_admin()): ?>
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
<?php endif; ?>
</script>
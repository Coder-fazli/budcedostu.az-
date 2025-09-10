<?php
/**
 * Financial Calculator Shortcode
 * Usage: [financial_calculator]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function financial_calculator_shortcode($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'type' => 'all', // all, credit, mortgage, deposit
        'default' => 'credit' // default calculator to show
    ), $atts);
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="financial-calculator-widget">
        <!-- Calculator Type Selector -->
        <div class="calc-type-buttons">
            <button class="type-btn active" data-calc="credit">Kredit kalkulyatoru</button>
            <button class="type-btn" data-calc="mortgage">İpoteka kalkulyatoru</button>
            <button class="type-btn" data-calc="deposit">Əmanət kalkulyatoru</button>
        </div>

        <!-- Credit Calculator -->
        <div class="calculator credit-calculator active">
            <div class="value-box">
                <div class="value-label">Kredit məbləği</div>
                <div class="value-display">
                    <span id="credit-amount-display">25,000</span>₼
                </div>
                <input type="range" id="credit-amount-slider" min="1000" max="1000000" value="25000" class="slider">
                <input type="hidden" id="credit-amount" value="25000">
            </div>

            <div class="input-row">
                <div class="value-box half">
                    <div class="value-label">İllik faiz dərəcəsi</div>
                    <div class="value-display">
                        <span id="credit-rate-display">10</span>%
                    </div>
                    <input type="range" id="credit-rate-slider" min="1" max="30" value="10" step="0.1" class="slider">
                    <input type="hidden" id="credit-rate" value="10">
                </div>

                <div class="value-box half">
                    <div class="value-label">Kredit müddəti</div>
                    <div class="value-display">
                        <span id="credit-term-display">18</span> ay
                    </div>
                    <input type="range" id="credit-term-slider" min="1" max="360" value="18" class="slider">
                    <input type="hidden" id="credit-term" value="18">
                </div>
            </div>

            <div class="result-box">
                <div class="result-label">Aylıq ödəniş</div>
                <div class="result-value">
                    <span id="credit-payment">1,501.43</span> ₼
                </div>
            </div>
        </div>

        <!-- Mortgage Calculator -->
        <div class="calculator mortgage-calculator">
            <div class="value-box">
                <div class="value-label">İlkin ödəniş faizi</div>
                <div class="value-display">
                    <span id="down-payment-percent-display">15</span>%
                </div>
                <input type="range" id="down-payment-percent-slider" min="0" max="50" value="15" step="0.1" class="slider">
                <input type="hidden" id="down-payment-percent" value="15">
            </div>

            <div class="input-row">
                <div class="value-box half">
                    <div class="value-label">Mənzilin dəyəri</div>
                    <div class="value-display">
                        <span id="home-value-display">210,000</span>₼
                    </div>
                    <input type="range" id="home-value-slider" min="10000" max="5000000" value="210000" class="slider">
                    <input type="hidden" id="home-value" value="210000">
                </div>

                <div class="value-box half">
                    <div class="value-label">Kredit müddəti</div>
                    <div class="value-display">
                        <span id="mortgage-term-years-display">20</span> il
                    </div>
                    <input type="range" id="mortgage-term-years-slider" min="1" max="40" value="20" class="slider">
                    <input type="hidden" id="mortgage-term-years" value="20">
                </div>
            </div>

            <div class="results-grid">
                <div class="result-item">
                    <div class="result-label">Minimal İlkin ödəniş</div>
                    <div class="result-value">
                        <span id="min-down-payment">31,500.00</span> ₼
                    </div>
                </div>
                <div class="result-item">
                    <div class="result-label">Aylıq ödəniş</div>
                    <div class="result-value">
                        <span id="mortgage-payment">3,745.32</span> ₼
                    </div>
                </div>
                <div class="result-item">
                    <div class="result-label">Kredit məbləği</div>
                    <div class="result-value">
                        <span id="loan-amount">178,500.00</span> ₼
                    </div>
                </div>
                <div class="result-item">
                    <div class="result-label">İllik % dərəcəsi</div>
                    <div class="result-value">
                        <span id="annual-rate">25.00</span> %
                    </div>
                </div>
            </div>
        </div>

        <!-- Deposit Calculator -->
        <div class="calculator deposit-calculator">
            <div class="value-box">
                <div class="value-label">İlkin əmanət məbləği</div>
                <div class="value-display">
                    <span id="deposit-amount-display">80,000</span>₼
                </div>
                <input type="range" id="deposit-amount-slider" min="100" max="1000000" value="80000" class="slider">
                <input type="hidden" id="deposit-amount" value="80000">
            </div>

            <div class="input-row">
                <div class="value-box half">
                    <div class="value-label">İllik faiz dərəcəsi</div>
                    <div class="value-display">
                        <span id="deposit-rate-display">12</span>%
                    </div>
                    <input type="range" id="deposit-rate-slider" min="0.1" max="30" value="12" step="0.1" class="slider">
                    <input type="hidden" id="deposit-rate" value="12">
                </div>

                <div class="value-box half">
                    <div class="value-label">Müddət</div>
                    <div class="value-display">
                        <span id="deposit-term-display">18</span> ay
                    </div>
                    <input type="range" id="deposit-term-slider" min="1" max="120" value="18" class="slider">
                    <input type="hidden" id="deposit-term" value="18">
                </div>
            </div>

            <div class="results-grid">
                <div class="result-item">
                    <div class="result-label">Aylıq faiz gəliri</div>
                    <div class="result-value">
                        <span id="monthly-interest">800.00</span> ₼
                    </div>
                </div>
                <div class="result-item">
                    <div class="result-label">Müddət sonunda faiz gəliri</div>
                    <div class="result-value">
                        <span id="total-interest">14,400.00</span> ₼
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Include CSS and JavaScript
    financial_calculator_enqueue_assets();
    
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('financial_calculator', 'financial_calculator_shortcode');

// Enqueue CSS and JavaScript for the shortcode
function financial_calculator_enqueue_assets() {
    static $assets_loaded = false;
    
    if ($assets_loaded) {
        return;
    }
    
    $assets_loaded = true;
    ?>
    
    <style>
    .financial-calculator-widget {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #f1f5f9;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 1200px;
        margin: 20px auto;
    }

    .financial-calculator-widget .calculator {
        display: none;
    }

    .financial-calculator-widget .calculator.active {
        display: block;
    }

    .financial-calculator-widget .value-box {
        --tw-bg-opacity: 1;
        background-color: rgb(245 245 245 / var(--tw-bg-opacity, 1));
        border-radius: 12px;
        padding: 20px 24px 8px 24px;
        margin-bottom: 24px;
        border: none;
        box-shadow: none;
        position: relative;
    }

    .financial-calculator-widget .value-box.half {
        flex: 1;
        margin-bottom: 0;
    }

    .financial-calculator-widget .value-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: none;
        letter-spacing: 0.25px;
    }

    .financial-calculator-widget .value-display {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
        line-height: 1.2;
        letter-spacing: -0.5px;
    }

    .financial-calculator-widget .input-row {
        display: flex;
        gap: 20px;
        margin-bottom: 24px;
    }

    .financial-calculator-widget .result-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 24px 32px;
        text-align: center;
        margin-top: 32px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .financial-calculator-widget .result-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: none;
        letter-spacing: 0.25px;
    }

    .financial-calculator-widget .slider {
        width: 100%;
        height: 2px;
        --tw-bg-opacity: 1;
        background: linear-gradient(to right, rgb(161 185 244 / var(--tw-bg-opacity, 1)) 0%, rgb(161 185 244 / var(--tw-bg-opacity, 1)) var(--fill-percent, 0%), #E5E7EB var(--fill-percent, 0%), #E5E7EB 100%);
        outline: none;
        -webkit-appearance: none;
        appearance: none;
        cursor: pointer;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        transition: all 0.2s ease;
        padding: 0;
        margin: 0;
        border: none;
    }

    .financial-calculator-widget .slider::-webkit-slider-runnable-track {
        width: 100%;
        height: 2px;
        border-radius: 0 0 12px 12px;
        background: transparent;
    }

    .financial-calculator-widget .slider::-webkit-slider-thumb {
        appearance: none;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        --tw-bg-opacity: 1;
        background: rgb(161 185 244 / var(--tw-bg-opacity, 1));
        cursor: grab;
        border: none;
        box-shadow: 0 1px 3px rgba(161, 185, 244, 0.3);
        transition: all 0.2s ease;
        position: relative;
        z-index: 2;
        margin-top: -7px;
    }

    .financial-calculator-widget .slider::-webkit-slider-thumb:hover {
        transform: scale(1.1);
        cursor: grab;
        box-shadow: 0 2px 6px rgba(161, 185, 244, 0.4);
    }

    .financial-calculator-widget .slider::-webkit-slider-thumb:active {
        transform: scale(1.05);
        cursor: grabbing;
        box-shadow: 0 1px 3px rgba(161, 185, 244, 0.5);
    }

    .financial-calculator-widget .slider::-moz-range-thumb {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        --tw-bg-opacity: 1;
        background: rgb(161 185 244 / var(--tw-bg-opacity, 1));
        cursor: grab;
        border: none;
        box-shadow: 0 1px 3px rgba(161, 185, 244, 0.3);
        transition: all 0.2s ease;
        margin-top: -7px;
    }

    .financial-calculator-widget .slider::-moz-range-thumb:hover {
        transform: scale(1.1);
        cursor: grab;
        box-shadow: 0 2px 6px rgba(161, 185, 244, 0.4);
    }

    .financial-calculator-widget .slider::-moz-range-thumb:active {
        transform: scale(1.05);
        cursor: grabbing;
    }

    .financial-calculator-widget .slider::-moz-range-track {
        height: 2px;
        border-radius: 0 0 12px 12px;
        --tw-bg-opacity: 1;
        background: linear-gradient(to right, rgb(161 185 244 / var(--tw-bg-opacity, 1)) 0%, rgb(161 185 244 / var(--tw-bg-opacity, 1)) var(--fill-percent, 0%), #E5E7EB var(--fill-percent, 0%), #E5E7EB 100%);
        border: none;
    }

    .financial-calculator-widget .calc-type-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }

    .financial-calculator-widget .type-btn {
        background: white;
        border: 1px solid #e8ecf4;
        padding: 12px 24px;
        border-radius: 12px;
        color: #64748b;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .financial-calculator-widget .type-btn.active {
        background: #6B7FF7;
        color: white;
        border-color: #6B7FF7;
        box-shadow: 0 2px 8px rgba(107, 127, 247, 0.25);
    }

    .financial-calculator-widget .type-btn:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .financial-calculator-widget .type-btn.active:hover {
        background: #5a6fd8;
        border-color: #5a6fd8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(107, 127, 247, 0.35);
    }

    .financial-calculator-widget .results-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 40px;
    }

    .financial-calculator-widget .result-item {
        --tw-bg-opacity: 1;
        background-color: rgb(245 245 245 / var(--tw-bg-opacity, 1));
        border-radius: 12px;
        padding: 20px 24px;
        text-align: left;
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
    }

    .financial-calculator-widget .result-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .financial-calculator-widget .result-item label {
        display: block;
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: none;
        letter-spacing: 0.25px;
        line-height: 1.3;
    }

    .financial-calculator-widget .result-item .result-value {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
        letter-spacing: -0.5px;
    }

    .financial-calculator-widget .result-value {
        font-size: 32px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.1;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .financial-calculator-widget {
            padding: 25px;
            margin: 10px;
        }
        
        .financial-calculator-widget .input-row {
            flex-direction: column;
            gap: 20px;
        }
        
        .financial-calculator-widget .calc-type-buttons {
            flex-direction: column;
        }
        
        .financial-calculator-widget .type-btn {
            width: 100%;
        }
        
        .financial-calculator-widget .results-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .financial-calculator-widget .result-item {
            text-align: center;
        }
        
        .financial-calculator-widget .result-item .result-value {
            font-size: 24px;
        }
        
        .financial-calculator-widget .result-value {
            font-size: 28px;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calculator switching functionality
        const calcButtons = document.querySelectorAll('.financial-calculator-widget .type-btn');
        const calculators = document.querySelectorAll('.financial-calculator-widget .calculator');

        calcButtons.forEach(button => {
            button.addEventListener('click', function() {
                const calcType = this.dataset.calc;
                const widget = this.closest('.financial-calculator-widget');
                
                // Update button states within this widget
                widget.querySelectorAll('.type-btn').forEach(btn => btn.classList.remove('active'));
                widget.querySelectorAll(`[data-calc="${calcType}"]`).forEach(btn => btn.classList.add('active'));
                
                // Show/hide calculators within this widget
                widget.querySelectorAll('.calculator').forEach(calc => calc.classList.remove('active'));
                widget.querySelector(`.${calcType}-calculator`).classList.add('active');
                
                // Update slider fills for the active calculator
                setTimeout(() => {
                    widget.querySelectorAll(`.${calcType}-calculator .slider`).forEach(slider => {
                        updateSliderFill(slider);
                    });
                    
                    // Run the appropriate calculation
                    if (calcType === 'credit') updateCreditCalculator();
                    else if (calcType === 'mortgage') updateMortgageCalculator(); 
                    else if (calcType === 'deposit') updateDepositCalculator();
                }, 50);
            });
        });

        // Credit Calculator
        function updateCreditCalculator() {
            const amount = parseFloat(document.getElementById('credit-amount').value);
            const rate = parseFloat(document.getElementById('credit-rate').value) / 100 / 12;
            const term = parseInt(document.getElementById('credit-term').value);
            
            const payment = (amount * rate * Math.pow(1 + rate, term)) / (Math.pow(1 + rate, term) - 1);
            document.getElementById('credit-payment').textContent = formatNumber(payment.toFixed(2));
        }

        // Mortgage Calculator
        function updateMortgageCalculator() {
            const homeValue = parseFloat(document.getElementById('home-value').value);
            const downPaymentPercent = parseFloat(document.getElementById('down-payment-percent').value);
            const termYears = parseInt(document.getElementById('mortgage-term-years').value);
            const annualRate = 25.0; // Fixed rate from design
            
            const minDownPayment = homeValue * (downPaymentPercent / 100);
            const loanAmount = homeValue - minDownPayment;
            const monthlyRate = annualRate / 100 / 12;
            const termMonths = termYears * 12;
            
            const monthlyPayment = (loanAmount * monthlyRate * Math.pow(1 + monthlyRate, termMonths)) / (Math.pow(1 + monthlyRate, termMonths) - 1);
            
            document.getElementById('min-down-payment').textContent = formatNumber(minDownPayment.toFixed(2));
            document.getElementById('mortgage-payment').textContent = formatNumber(monthlyPayment.toFixed(2));
            document.getElementById('loan-amount').textContent = formatNumber(loanAmount.toFixed(2));
            document.getElementById('annual-rate').textContent = annualRate.toFixed(2);
        }

        // Deposit Calculator
        function updateDepositCalculator() {
            const amount = parseFloat(document.getElementById('deposit-amount').value);
            const annualRate = parseFloat(document.getElementById('deposit-rate').value);
            const termMonths = parseInt(document.getElementById('deposit-term').value);
            
            const monthlyInterest = (amount * annualRate / 100) / 12;
            const totalInterest = monthlyInterest * termMonths;
            
            document.getElementById('monthly-interest').textContent = formatNumber(monthlyInterest.toFixed(2));
            document.getElementById('total-interest').textContent = formatNumber(totalInterest.toFixed(2));
        }

        // Update slider fill based on value
        function updateSliderFill(slider) {
            const value = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
            slider.style.setProperty('--fill-percent', value + '%');
        }

        // Format number with commas
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Sync inputs with sliders and displays
        function syncInputs(inputId, sliderId, displayId, updateFunction) {
            const input = document.getElementById(inputId);
            const slider = document.getElementById(sliderId);
            const display = document.getElementById(displayId);
            
            // Initialize fill
            updateSliderFill(slider);
            
            slider.addEventListener('input', function() {
                input.value = this.value;
                display.textContent = formatNumber(this.value);
                updateSliderFill(this);
                updateFunction();
            });
        }

        // Initialize all calculators
        syncInputs('credit-amount', 'credit-amount-slider', 'credit-amount-display', updateCreditCalculator);
        syncInputs('credit-rate', 'credit-rate-slider', 'credit-rate-display', updateCreditCalculator);
        syncInputs('credit-term', 'credit-term-slider', 'credit-term-display', updateCreditCalculator);

        syncInputs('home-value', 'home-value-slider', 'home-value-display', updateMortgageCalculator);
        syncInputs('down-payment-percent', 'down-payment-percent-slider', 'down-payment-percent-display', updateMortgageCalculator);
        syncInputs('mortgage-term-years', 'mortgage-term-years-slider', 'mortgage-term-years-display', updateMortgageCalculator);

        syncInputs('deposit-amount', 'deposit-amount-slider', 'deposit-amount-display', updateDepositCalculator);
        syncInputs('deposit-rate', 'deposit-rate-slider', 'deposit-rate-display', updateDepositCalculator);
        syncInputs('deposit-term', 'deposit-term-slider', 'deposit-term-display', updateDepositCalculator);

        // Initialize all slider fills on page load
        document.querySelectorAll('.financial-calculator-widget .slider').forEach(slider => {
            updateSliderFill(slider);
        });

        // Initial calculations
        updateCreditCalculator();
        updateMortgageCalculator();
        updateDepositCalculator();
    });
    </script>
    
    <?php
}
?>
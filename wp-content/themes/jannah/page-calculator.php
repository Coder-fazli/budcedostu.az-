<?php
/*
Template Name: Financial Calculators
*/

get_header(); ?>

<div class="financial-calculators-page">
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="main-title">Hesab kitabın düz olsun ən</h1>
            <div class="calculator-buttons">
                <button class="calc-btn active" data-calc="credit">Kredit kalkulyatoru</button>
                <button class="calc-btn" data-calc="mortgage">İpoteka kalkulyatoru</button>
                <button class="calc-btn" data-calc="deposit">Əmanət kalkulyatoru</button>
            </div>
        </div>

        <!-- Calculator Section -->
        <div class="calculator-section">
            <h2 class="section-title">Hesabla</h2>
            
            <!-- Calculator Type Selector -->
            <div class="calc-type-buttons">
                <button class="type-btn" data-calc="credit">Kredit kalkulyatoru</button>
                <button class="type-btn active" data-calc="mortgage">İpoteka kalkulyatoru</button>
                <button class="type-btn" data-calc="deposit">Əmanət kalkulyatoru</button>
            </div>

            <!-- Credit Calculator -->
            <div class="calculator credit-calculator active">
                <div class="input-group">
                    <label>Kredit məbləği</label>
                    <div class="input-wrapper">
                        <input type="number" id="credit-amount" value="25000" min="1000" max="1000000">
                        <span class="currency">₼</span>
                    </div>
                    <input type="range" id="credit-amount-slider" min="1000" max="1000000" value="25000" class="slider">
                </div>

                <div class="input-row">
                    <div class="input-group half">
                        <label>İllik faiz dərəcəsi</label>
                        <div class="input-wrapper">
                            <input type="number" id="credit-rate" value="10" min="1" max="30" step="0.1">
                            <span class="unit">%</span>
                        </div>
                        <input type="range" id="credit-rate-slider" min="1" max="30" value="10" step="0.1" class="slider">
                    </div>

                    <div class="input-group half">
                        <label>Kredit müddəti</label>
                        <div class="input-wrapper">
                            <input type="number" id="credit-term" value="18" min="1" max="360">
                            <span class="unit">ay</span>
                        </div>
                        <input type="range" id="credit-term-slider" min="1" max="360" value="18" class="slider">
                    </div>
                </div>

                <div class="result">
                    <label>Aylıq ödəniş</label>
                    <div class="result-value">
                        <span id="credit-payment">1501.43</span> ₼
                    </div>
                </div>
            </div>

            <!-- Mortgage Calculator -->
            <div class="calculator mortgage-calculator">
                <div class="input-group">
                    <label>İlkin ödəniş faizi</label>
                    <div class="input-wrapper">
                        <input type="number" id="down-payment-percent" value="15" min="0" max="50" step="0.1">
                        <span class="unit">%</span>
                    </div>
                    <input type="range" id="down-payment-percent-slider" min="0" max="50" value="15" step="0.1" class="slider">
                </div>

                <div class="input-row">
                    <div class="input-group half">
                        <label>Mənzilin dəyəri</label>
                        <div class="input-wrapper">
                            <input type="number" id="home-value" value="210000" min="10000" max="5000000">
                            <span class="currency">₼</span>
                        </div>
                        <input type="range" id="home-value-slider" min="10000" max="5000000" value="210000" class="slider">
                    </div>

                    <div class="input-group half">
                        <label>Kredit müddəti</label>
                        <div class="input-wrapper">
                            <input type="number" id="mortgage-term-years" value="20" min="1" max="40">
                            <span class="unit">il</span>
                        </div>
                        <input type="range" id="mortgage-term-years-slider" min="1" max="40" value="20" class="slider">
                    </div>
                </div>

                <div class="results-grid">
                    <div class="result-item">
                        <label>Minimal İlkin ödəniş</label>
                        <div class="result-value">
                            <span id="min-down-payment">31500.00</span> ₼
                        </div>
                    </div>
                    <div class="result-item">
                        <label>Aylıq ödəniş</label>
                        <div class="result-value">
                            <span id="mortgage-payment">3745.32</span> ₼
                        </div>
                    </div>
                    <div class="result-item">
                        <label>Kredit məbləği</label>
                        <div class="result-value">
                            <span id="loan-amount">178500.00</span> ₼
                        </div>
                    </div>
                    <div class="result-item">
                        <label>İllik % dərəcəsi</label>
                        <div class="result-value">
                            <span id="annual-rate">25.00</span> %
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deposit Calculator -->
            <div class="calculator deposit-calculator">
                <div class="input-group">
                    <label>İlkin əmanət məbləği</label>
                    <div class="input-wrapper">
                        <input type="number" id="deposit-amount" value="80000" min="100" max="1000000">
                        <span class="currency">₼</span>
                    </div>
                    <input type="range" id="deposit-amount-slider" min="100" max="1000000" value="80000" class="slider">
                </div>

                <div class="input-row">
                    <div class="input-group half">
                        <label>İllik faiz dərəcəsi</label>
                        <div class="input-wrapper">
                            <input type="number" id="deposit-rate" value="12" min="0.1" max="30" step="0.1">
                            <span class="unit">%</span>
                        </div>
                        <input type="range" id="deposit-rate-slider" min="0.1" max="30" value="12" step="0.1" class="slider">
                    </div>

                    <div class="input-group half">
                        <label>Müddət</label>
                        <div class="input-wrapper">
                            <input type="number" id="deposit-term" value="18" min="1" max="120">
                            <span class="unit">ay</span>
                        </div>
                        <input type="range" id="deposit-term-slider" min="1" max="120" value="18" class="slider">
                    </div>
                </div>

                <div class="results-grid">
                    <div class="result-item">
                        <label>Aylıq faiz gəliri</label>
                        <div class="result-value">
                            <span id="monthly-interest">800.00</span> ₼
                        </div>
                    </div>
                    <div class="result-item">
                        <label>Müddət sonunda faiz gəliri</label>
                        <div class="result-value">
                            <span id="total-interest">14400.00</span> ₼
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.financial-calculators-page {
    background: #F7F8FE;
    min-height: 100vh;
    padding: 60px 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.hero-section {
    text-align: center;
    margin-bottom: 60px;
}

.main-title {
    font-size: 42px;
    font-weight: 600;
    color: #1F2937;
    margin-bottom: 40px;
    line-height: 1.2;
}

.calculator-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.calc-btn, .type-btn {
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

.calc-btn.active, .type-btn.active {
    background: #667FFF;
    color: white;
    border-color: #667FFF;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
}

.calc-btn:hover, .type-btn:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.calc-btn.active:hover, .type-btn.active:hover {
    background: #5a6fd8;
    border-color: #5a6fd8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(107, 127, 247, 0.35);
}

.calculator-section {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #f1f5f9;
}

.section-title {
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 30px;
}

.calc-type-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.calculator {
    display: none;
}

.calculator.active {
    display: block;
}

.input-group {
    margin-bottom: 30px;
}

.input-row {
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
}

.input-group.half {
    flex: 1;
    margin-bottom: 0;
}

.input-group label {
    display: block;
    font-weight: 500;
    color: #6B7280;
    margin-bottom: 8px;
    font-size: 14px;
}

.input-wrapper {
    position: relative;
    margin-bottom: 10px;
}

.input-wrapper input {
    width: 100%;
    padding: 14px 50px 14px 16px;
    border: 1px solid rgba(31,41,55,0.12);
    border-radius: 14px;
    font-size: 18px;
    font-weight: 600;
    color: #1F2937;
    background: #FFFFFF;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    transition: all .2s ease;
}

.input-wrapper input:focus {
    outline: none;
    border-color: #667FFF;
    box-shadow: 0 0 0 3px rgba(102,127,255,.25);
}

.currency, .unit {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #6B7280;
    font-weight: 500;
    font-size: 14px;
}

.slider {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: #E5E7EB;
    outline: none;
    -webkit-appearance: none;
    appearance: none;
    cursor: pointer;
    position: relative;
}

/* Webkit browsers (Chrome, Safari) */
.slider::-webkit-slider-thumb {
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #667FFF;
    cursor: grab;
    border: 3px solid white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transition: all .2s ease;
    position: relative;
    z-index: 2;
}

.slider::-webkit-slider-thumb:hover {
    transform: scale(1.06);
    cursor: grab;
}

.slider::-webkit-slider-thumb:active {
    transform: scale(0.98);
    cursor: grabbing;
}

.slider:focus-visible::-webkit-slider-thumb {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15), 0 0 0 3px rgba(102,127,255,.25);
}

/* Firefox */
.slider::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #667FFF;
    cursor: grab;
    border: 3px solid white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transition: all .2s ease;
}

.slider::-moz-range-thumb:hover {
    transform: scale(1.06);
}

.slider::-moz-range-thumb:active {
    transform: scale(0.98);
    cursor: grabbing;
}

/* Track styling */
.slider::-webkit-slider-runnable-track {
    height: 6px;
    border-radius: 3px;
    background: #E5E7EB;
}

.slider::-moz-range-track {
    height: 6px;
    border-radius: 3px;
    background: #E5E7EB;
    border: none;
}

.result {
    background: #f8fafc;
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    margin-top: 30px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.result label {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.result-value {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
}

.results-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 30px;
}

.result-item {
    background: #f8fafc;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.result-item label {
    display: block;
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.result-item .result-value {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
}

@media (max-width: 768px) {
    .main-title {
        font-size: 28px;
    }
    
    .calculator-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .calc-btn, .type-btn {
        width: 100%;
        max-width: 300px;
    }
    
    .calculator-section {
        padding: 25px;
    }
    
    .input-row {
        flex-direction: column;
        gap: 20px;
    }
    
    .calc-type-buttons {
        flex-direction: column;
    }
    
    .results-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculator switching functionality
    const calcButtons = document.querySelectorAll('.calc-btn, .type-btn');
    const calculators = document.querySelectorAll('.calculator');

    calcButtons.forEach(button => {
        button.addEventListener('click', function() {
            const calcType = this.dataset.calc;
            
            // Update button states
            document.querySelectorAll('.calc-btn, .type-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll(`[data-calc="${calcType}"]`).forEach(btn => btn.classList.add('active'));
            
            // Show/hide calculators
            calculators.forEach(calc => calc.classList.remove('active'));
            document.querySelector(`.${calcType}-calculator`).classList.add('active');
        });
    });

    // Number formatting with commas
    function formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(Math.round(number * 100) / 100);
    }

    // Credit Calculator
    function updateCreditCalculator() {
        const amount = parseFloat(document.getElementById('credit-amount').value);
        const rate = parseFloat(document.getElementById('credit-rate').value) / 100 / 12;
        const term = parseInt(document.getElementById('credit-term').value);
        
        const payment = (amount * rate * Math.pow(1 + rate, term)) / (Math.pow(1 + rate, term) - 1);
        document.getElementById('credit-payment').textContent = formatNumber(payment);
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
        
        document.getElementById('min-down-payment').textContent = formatNumber(minDownPayment);
        document.getElementById('mortgage-payment').textContent = formatNumber(monthlyPayment);
        document.getElementById('loan-amount').textContent = formatNumber(loanAmount);
        document.getElementById('annual-rate').textContent = annualRate.toFixed(1);
    }

    // Deposit Calculator
    function updateDepositCalculator() {
        const amount = parseFloat(document.getElementById('deposit-amount').value);
        const annualRate = parseFloat(document.getElementById('deposit-rate').value);
        const termMonths = parseInt(document.getElementById('deposit-term').value);
        
        const monthlyInterest = (amount * annualRate / 100) / 12;
        const totalInterest = monthlyInterest * termMonths;
        
        document.getElementById('monthly-interest').textContent = formatNumber(monthlyInterest);
        document.getElementById('total-interest').textContent = formatNumber(totalInterest);
    }

    // Sync inputs with sliders
    function syncInputs(inputId, sliderId, updateFunction) {
        const input = document.getElementById(inputId);
        const slider = document.getElementById(sliderId);
        
        input.addEventListener('input', function() {
            // Remove commas for calculation
            const numericValue = this.value.replace(/,/g, '');
            slider.value = numericValue;
            updateFunction();
            
            // Format with commas for display
            if (numericValue && !isNaN(numericValue)) {
                this.value = formatNumber(parseFloat(numericValue));
            }
        });
        
        slider.addEventListener('input', function() {
            const numericValue = this.value;
            input.value = formatNumber(parseFloat(numericValue));
            updateFunction();
        });
        
        // Format initial value
        if (input.value && !isNaN(input.value)) {
            input.value = formatNumber(parseFloat(input.value));
        }
    }

    // Initialize all calculators
    syncInputs('credit-amount', 'credit-amount-slider', updateCreditCalculator);
    syncInputs('credit-rate', 'credit-rate-slider', updateCreditCalculator);
    syncInputs('credit-term', 'credit-term-slider', updateCreditCalculator);

    syncInputs('home-value', 'home-value-slider', updateMortgageCalculator);
    syncInputs('down-payment-percent', 'down-payment-percent-slider', updateMortgageCalculator);
    syncInputs('mortgage-term-years', 'mortgage-term-years-slider', updateMortgageCalculator);

    syncInputs('deposit-amount', 'deposit-amount-slider', updateDepositCalculator);
    syncInputs('deposit-rate', 'deposit-rate-slider', updateDepositCalculator);
    syncInputs('deposit-term', 'deposit-term-slider', updateDepositCalculator);

    // Initial calculations
    updateCreditCalculator();
    updateMortgageCalculator();
    updateDepositCalculator();
});
</script>

<?php get_footer(); ?>
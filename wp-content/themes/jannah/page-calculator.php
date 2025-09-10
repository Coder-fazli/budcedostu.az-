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
                <button class="type-btn active" data-calc="credit">Kredit kalkulyatoru</button>
                <button class="type-btn" data-calc="mortgage">İpoteka kalkulyatoru</button>
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
    background: #f8fafc;
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
    color: #1e293b;
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
    background: #6B7FF7;
    color: white;
    border-color: #6B7FF7;
    box-shadow: 0 2px 8px rgba(107, 127, 247, 0.25);
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
    color: #64748b;
    margin-bottom: 8px;
    font-size: 14px;
}

.input-wrapper {
    position: relative;
    margin-bottom: 10px;
}

.input-wrapper input {
    width: 100%;
    padding: 16px 50px 16px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}

.input-wrapper input:focus {
    outline: none;
    border-color: #94a3b8;
    box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.1);
    transform: translateY(-1px);
}

.currency, .unit {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-weight: 500;
    font-size: 14px;
}

.slider {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: linear-gradient(to right, #6B7FF7 0%, #6B7FF7 var(--fill-percent, 0%), #E5E7EB var(--fill-percent, 0%), #E5E7EB 100%);
    outline: none;
    -webkit-appearance: none;
    appearance: none;
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
}

/* Webkit Track */
.slider::-webkit-slider-runnable-track {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: transparent;
}

/* Enhanced Glass Effect Thumb */
.slider::-webkit-slider-thumb {
    appearance: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6B7FF7 0%, #5a6fd8 100%);
    cursor: grab;
    border: 3px solid rgba(255, 255, 255, 0.9);
    box-shadow: 
        0 2px 8px rgba(107, 127, 247, 0.3),
        0 4px 16px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
    margin-top: -9px;
}

.slider::-webkit-slider-thumb:hover {
    transform: scale(1.15);
    cursor: grab;
    box-shadow: 
        0 4px 16px rgba(107, 127, 247, 0.4),
        0 8px 24px rgba(0, 0, 0, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.6),
        0 0 0 4px rgba(107, 127, 247, 0.1);
}

.slider::-webkit-slider-thumb:active {
    transform: scale(1.05);
    cursor: grabbing;
    box-shadow: 
        0 2px 8px rgba(107, 127, 247, 0.4),
        0 4px 16px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.4);
}

/* Firefox */
.slider::-moz-range-thumb {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6B7FF7 0%, #5a6fd8 100%);
    cursor: grab;
    border: 3px solid rgba(255, 255, 255, 0.9);
    box-shadow: 
        0 2px 8px rgba(107, 127, 247, 0.3),
        0 4px 16px rgba(0, 0, 0, 0.1);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    margin-top: -9px;
}

.slider::-moz-range-thumb:hover {
    transform: scale(1.15);
    cursor: grab;
    box-shadow: 
        0 4px 16px rgba(107, 127, 247, 0.4),
        0 8px 24px rgba(0, 0, 0, 0.15);
}

.slider::-moz-range-thumb:active {
    transform: scale(1.05);
    cursor: grabbing;
}

.slider::-moz-range-track {
    height: 6px;
    border-radius: 3px;
    background: linear-gradient(to right, #6B7FF7 0%, #6B7FF7 var(--fill-percent, 0%), #E5E7EB var(--fill-percent, 0%), #E5E7EB 100%);
    border: none;
}

.result {
    background: white;
    border-radius: 20px;
    padding: 32px;
    text-align: center;
    margin-top: 40px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.02);
}

.result label {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 12px;
    text-transform: none;
    letter-spacing: 0.2px;
}

.result-value {
    font-size: 32px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.1;
}

.results-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-top: 40px;
}

.result-item {
    background: white;
    border-radius: 20px;
    padding: 28px 24px;
    text-align: left;
    border: 1px solid #f1f5f9;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.3s ease;
}

.result-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), 0 2px 6px rgba(0, 0, 0, 0.04);
}

.result-item label {
    display: block;
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 12px;
    text-transform: none;
    letter-spacing: 0.2px;
    line-height: 1.3;
}

.result-item .result-value {
    font-size: 26px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.1;
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
        gap: 16px;
    }
    
    .result {
        padding: 24px;
        margin-top: 30px;
    }
    
    .result-item {
        padding: 24px 20px;
        text-align: center;
    }
    
    .result-item .result-value {
        font-size: 24px;
    }
    
    .result-value {
        font-size: 28px;
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
            
            // Update slider fills for the active calculator
            setTimeout(() => {
                document.querySelectorAll(`.${calcType}-calculator .slider`).forEach(slider => {
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
        document.getElementById('credit-payment').textContent = payment.toFixed(2);
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
        
        document.getElementById('min-down-payment').textContent = minDownPayment.toFixed(2);
        document.getElementById('mortgage-payment').textContent = monthlyPayment.toFixed(2);
        document.getElementById('loan-amount').textContent = loanAmount.toFixed(2);
        document.getElementById('annual-rate').textContent = annualRate.toFixed(2);
    }

    // Deposit Calculator
    function updateDepositCalculator() {
        const amount = parseFloat(document.getElementById('deposit-amount').value);
        const annualRate = parseFloat(document.getElementById('deposit-rate').value);
        const termMonths = parseInt(document.getElementById('deposit-term').value);
        
        const monthlyInterest = (amount * annualRate / 100) / 12;
        const totalInterest = monthlyInterest * termMonths;
        
        document.getElementById('monthly-interest').textContent = monthlyInterest.toFixed(2);
        document.getElementById('total-interest').textContent = totalInterest.toFixed(2);
    }

    // Update slider fill based on value
    function updateSliderFill(slider) {
        const value = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
        slider.style.setProperty('--fill-percent', value + '%');
    }

    // Sync inputs with sliders
    function syncInputs(inputId, sliderId, updateFunction) {
        const input = document.getElementById(inputId);
        const slider = document.getElementById(sliderId);
        
        // Initialize fill
        updateSliderFill(slider);
        
        input.addEventListener('input', function() {
            slider.value = this.value;
            updateSliderFill(slider);
            updateFunction();
        });
        
        slider.addEventListener('input', function() {
            input.value = this.value;
            updateSliderFill(this);
            updateFunction();
        });
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

    // Initialize all slider fills on page load
    document.querySelectorAll('.slider').forEach(slider => {
        updateSliderFill(slider);
    });

    // Run initial calculations
    updateCreditCalculator();
    updateMortgageCalculator();
    updateDepositCalculator();

    // Initialize all slider fills on page load
    document.querySelectorAll('.slider').forEach(slider => {
        updateSliderFill(slider);
    });

    // Initial calculations
    updateCreditCalculator();
    updateMortgageCalculator();
    updateDepositCalculator();
});
</script>

<?php get_footer(); ?>
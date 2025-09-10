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
                    <label>Evin dəyəri</label>
                    <div class="input-wrapper">
                        <input type="number" id="home-value" value="100000" min="10000" max="5000000">
                        <span class="currency">₼</span>
                    </div>
                    <input type="range" id="home-value-slider" min="10000" max="5000000" value="100000" class="slider">
                </div>

                <div class="input-row">
                    <div class="input-group half">
                        <label>İlk ödəniş</label>
                        <div class="input-wrapper">
                            <input type="number" id="down-payment" value="20000" min="0" max="1000000">
                            <span class="currency">₼</span>
                        </div>
                        <input type="range" id="down-payment-slider" min="0" max="1000000" value="20000" class="slider">
                    </div>

                    <div class="input-group half">
                        <label>İllik faiz dərəcəsi</label>
                        <div class="input-wrapper">
                            <input type="number" id="mortgage-rate" value="8" min="1" max="25" step="0.1">
                            <span class="unit">%</span>
                        </div>
                        <input type="range" id="mortgage-rate-slider" min="1" max="25" value="8" step="0.1" class="slider">
                    </div>
                </div>

                <div class="input-group">
                    <label>İpoteka müddəti</label>
                    <div class="input-wrapper">
                        <input type="number" id="mortgage-term" value="240" min="12" max="480">
                        <span class="unit">ay</span>
                    </div>
                    <input type="range" id="mortgage-term-slider" min="12" max="480" value="240" class="slider">
                </div>

                <div class="result">
                    <label>Aylıq ödəniş</label>
                    <div class="result-value">
                        <span id="mortgage-payment">0</span> ₼
                    </div>
                </div>
            </div>

            <!-- Deposit Calculator -->
            <div class="calculator deposit-calculator">
                <div class="input-group">
                    <label>Əmanət məbləği</label>
                    <div class="input-wrapper">
                        <input type="number" id="deposit-amount" value="10000" min="100" max="1000000">
                        <span class="currency">₼</span>
                    </div>
                    <input type="range" id="deposit-amount-slider" min="100" max="1000000" value="10000" class="slider">
                </div>

                <div class="input-row">
                    <div class="input-group half">
                        <label>İllik faiz dərəcəsi</label>
                        <div class="input-wrapper">
                            <input type="number" id="deposit-rate" value="5" min="0.1" max="20" step="0.1">
                            <span class="unit">%</span>
                        </div>
                        <input type="range" id="deposit-rate-slider" min="0.1" max="20" value="5" step="0.1" class="slider">
                    </div>

                    <div class="input-group half">
                        <label>Əmanət müddəti</label>
                        <div class="input-wrapper">
                            <input type="number" id="deposit-term" value="12" min="1" max="120">
                            <span class="unit">ay</span>
                        </div>
                        <input type="range" id="deposit-term-slider" min="1" max="120" value="12" class="slider">
                    </div>
                </div>

                <div class="result">
                    <label>Yekun məbləğ</label>
                    <div class="result-value">
                        <span id="deposit-total">0</span> ₼
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.financial-calculators-page {
    background: #f8f9ff;
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
    color: #6B7FF7;
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
    border: 2px solid #e0e6ff;
    padding: 12px 24px;
    border-radius: 25px;
    color: #666;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.calc-btn.active, .type-btn.active {
    background: #6B7FF7;
    color: white;
    border-color: #6B7FF7;
}

.calc-btn:hover, .type-btn:hover {
    border-color: #6B7FF7;
    color: #6B7FF7;
}

.calculator-section {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.section-title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
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
    color: #666;
    margin-bottom: 8px;
    font-size: 14px;
}

.input-wrapper {
    position: relative;
    margin-bottom: 10px;
}

.input-wrapper input {
    width: 100%;
    padding: 15px 50px 15px 15px;
    border: 2px solid #e0e6ff;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    background: #f8f9ff;
}

.input-wrapper input:focus {
    outline: none;
    border-color: #6B7FF7;
}

.currency, .unit {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-weight: 500;
}

.slider {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: #e0e6ff;
    outline: none;
    -webkit-appearance: none;
    appearance: none;
}

.slider::-webkit-slider-thumb {
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #6B7FF7;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(107, 127, 247, 0.3);
}

.slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #6B7FF7;
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 8px rgba(107, 127, 247, 0.3);
}

.result {
    background: #f0f3ff;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    margin-top: 30px;
}

.result label {
    font-size: 16px;
    color: #666;
    font-weight: 500;
    margin-bottom: 10px;
}

.result-value {
    font-size: 32px;
    font-weight: 700;
    color: #333;
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
        const downPayment = parseFloat(document.getElementById('down-payment').value);
        const loanAmount = homeValue - downPayment;
        const rate = parseFloat(document.getElementById('mortgage-rate').value) / 100 / 12;
        const term = parseInt(document.getElementById('mortgage-term').value);
        
        const payment = (loanAmount * rate * Math.pow(1 + rate, term)) / (Math.pow(1 + rate, term) - 1);
        document.getElementById('mortgage-payment').textContent = payment.toFixed(2);
    }

    // Deposit Calculator
    function updateDepositCalculator() {
        const amount = parseFloat(document.getElementById('deposit-amount').value);
        const rate = parseFloat(document.getElementById('deposit-rate').value) / 100;
        const term = parseInt(document.getElementById('deposit-term').value) / 12;
        
        const total = amount * Math.pow(1 + rate, term);
        document.getElementById('deposit-total').textContent = total.toFixed(2);
    }

    // Sync inputs with sliders
    function syncInputs(inputId, sliderId, updateFunction) {
        const input = document.getElementById(inputId);
        const slider = document.getElementById(sliderId);
        
        input.addEventListener('input', function() {
            slider.value = this.value;
            updateFunction();
        });
        
        slider.addEventListener('input', function() {
            input.value = this.value;
            updateFunction();
        });
    }

    // Initialize all calculators
    syncInputs('credit-amount', 'credit-amount-slider', updateCreditCalculator);
    syncInputs('credit-rate', 'credit-rate-slider', updateCreditCalculator);
    syncInputs('credit-term', 'credit-term-slider', updateCreditCalculator);

    syncInputs('home-value', 'home-value-slider', updateMortgageCalculator);
    syncInputs('down-payment', 'down-payment-slider', updateMortgageCalculator);
    syncInputs('mortgage-rate', 'mortgage-rate-slider', updateMortgageCalculator);
    syncInputs('mortgage-term', 'mortgage-term-slider', updateMortgageCalculator);

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
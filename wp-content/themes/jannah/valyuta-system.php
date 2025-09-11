<?php
/**
 * Currency Exchange Rates Management System
 * Bank Məzənnələri
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ValyutaSystem {
    
    private $table_banks;
    private $table_rates;
    
    public function __construct() {
        global $wpdb;
        $this->table_banks = $wpdb->prefix . 'valyuta_banks';
        $this->table_rates = $wpdb->prefix . 'valyuta_rates';
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_update_rates', array($this, 'ajax_update_rates'));
        add_action('wp_ajax_nopriv_get_rates', array($this, 'ajax_get_rates'));
        add_action('wp_ajax_get_rates', array($this, 'ajax_get_rates'));
    }
    
    public function init() {
        $this->create_tables();
        $this->populate_default_data();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Banks table
        $sql_banks = "CREATE TABLE $this->table_banks (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            logo_url varchar(255) DEFAULT NULL,
            website_url varchar(255) DEFAULT NULL,
            display_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        
        // Exchange rates table
        $sql_rates = "CREATE TABLE $this->table_rates (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            bank_id mediumint(9) NOT NULL,
            currency_code varchar(3) NOT NULL,
            buy_rate decimal(8,4) DEFAULT NULL,
            sell_rate decimal(8,4) DEFAULT NULL,
            cash_buy_rate decimal(8,4) DEFAULT NULL,
            cash_sell_rate decimal(8,4) DEFAULT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY bank_currency (bank_id, currency_code),
            FOREIGN KEY (bank_id) REFERENCES $this->table_banks(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_banks);
        dbDelta($sql_rates);
        
        // Update database version
        update_option('valyuta_db_version', '1.0');
    }
    
    /**
     * Populate default banks and sample data
     */
    private function populate_default_data() {
        global $wpdb;
        
        // Check if data already exists
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_banks");
        if ($count > 0) {
            return; // Data already exists
        }
        
        // Default banks from the screenshot
        $default_banks = array(
            array('name' => 'ABB', 'slug' => 'abb', 'display_order' => 1),
            array('name' => 'Unibank', 'slug' => 'unibank', 'display_order' => 2),
            array('name' => 'Yelo Bank', 'slug' => 'yelo-bank', 'display_order' => 3),
            array('name' => 'Kapital Bank', 'slug' => 'kapital-bank', 'display_order' => 4),
            array('name' => 'Turanbank', 'slug' => 'turanbank', 'display_order' => 5),
            array('name' => 'VTB', 'slug' => 'vtb', 'display_order' => 6),
            array('name' => 'Rabitabank', 'slug' => 'rabitabank', 'display_order' => 7),
            array('name' => 'Paşa bank', 'slug' => 'pasha-bank', 'display_order' => 8),
            array('name' => 'AF Bank', 'slug' => 'af-bank', 'display_order' => 9),
            array('name' => 'BTB Bank', 'slug' => 'btb-bank', 'display_order' => 10),
            array('name' => 'Expressbank', 'slug' => 'expressbank', 'display_order' => 11),
            array('name' => 'Access Bank', 'slug' => 'access-bank', 'display_order' => 12),
            array('name' => 'ASB Azərbaycan Sənaye Bankı', 'slug' => 'asb-bank', 'display_order' => 13),
            array('name' => 'Azərbaycan Beynəlxalq Bankı', 'slug' => 'abb-intl', 'display_order' => 14),
            array('name' => 'Günay Bank', 'slug' => 'gunay-bank', 'display_order' => 15),
            array('name' => 'Yapı Kredi Bank Azərbaycan', 'slug' => 'yapi-kredi', 'display_order' => 16),
            array('name' => 'Fatima MMC', 'slug' => 'fatima-mmc', 'display_order' => 17),
            array('name' => 'Bank Respublika', 'slug' => 'bank-respublika', 'display_order' => 18),
        );
        
        // Insert banks
        foreach ($default_banks as $bank) {
            $wpdb->insert($this->table_banks, $bank);
        }
        
        // Insert sample rates for all currencies (USD, EUR, RUB, GBP, TRY)
        $currencies_data = array(
            'USD' => array(
                'abb' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'unibank' => array('buy' => 1.6929, 'sell' => 1.6929, 'cash_buy' => 1.6929, 'cash_sell' => 1.6929),
                'yelo-bank' => array('buy' => 1.7000, 'sell' => 1.7000, 'cash_buy' => 1.7000, 'cash_sell' => 1.7000),
                'kapital-bank' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'turanbank' => array('buy' => 1.6900, 'sell' => null, 'cash_buy' => 1.6900, 'cash_sell' => null),
                'vtb' => array('buy' => 1.6929, 'sell' => 1.6929, 'cash_buy' => 1.6929, 'cash_sell' => 1.6929),
                'rabitabank' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'pasha-bank' => array('buy' => 1.7000, 'sell' => 1.7000, 'cash_buy' => 1.7000, 'cash_sell' => 1.7000),
                'af-bank' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'btb-bank' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'expressbank' => array('buy' => 1.7000, 'sell' => 1.7000, 'cash_buy' => 1.7000, 'cash_sell' => 1.7000),
                'access-bank' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'asb-bank' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'abb-intl' => array('buy' => 1.7000, 'sell' => 1.7000, 'cash_buy' => 1.7000, 'cash_sell' => 1.7000),
                'gunay-bank' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'yapi-kredi' => array('buy' => 1.6900, 'sell' => 1.6900, 'cash_buy' => 1.6900, 'cash_sell' => 1.6900),
                'fatima-mmc' => array('buy' => null, 'sell' => null, 'cash_buy' => null, 'cash_sell' => null),
                'bank-respublika' => array('buy' => 1.7000, 'sell' => 1.7000, 'cash_buy' => 1.7000, 'cash_sell' => 1.7000),
            ),
            'EUR' => array(
                'abb' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'unibank' => array('buy' => 1.8220, 'sell' => 1.8270, 'cash_buy' => 1.8170, 'cash_sell' => 1.8320),
                'yelo-bank' => array('buy' => 1.8300, 'sell' => 1.8350, 'cash_buy' => 1.8250, 'cash_sell' => 1.8400),
                'kapital-bank' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'turanbank' => array('buy' => 1.8200, 'sell' => null, 'cash_buy' => 1.8150, 'cash_sell' => null),
                'vtb' => array('buy' => 1.8220, 'sell' => 1.8270, 'cash_buy' => 1.8170, 'cash_sell' => 1.8320),
                'rabitabank' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'pasha-bank' => array('buy' => 1.8300, 'sell' => 1.8350, 'cash_buy' => 1.8250, 'cash_sell' => 1.8400),
                'af-bank' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'btb-bank' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'expressbank' => array('buy' => 1.8300, 'sell' => 1.8350, 'cash_buy' => 1.8250, 'cash_sell' => 1.8400),
                'access-bank' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'asb-bank' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'abb-intl' => array('buy' => 1.8300, 'sell' => 1.8350, 'cash_buy' => 1.8250, 'cash_sell' => 1.8400),
                'gunay-bank' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'yapi-kredi' => array('buy' => 1.8200, 'sell' => 1.8250, 'cash_buy' => 1.8150, 'cash_sell' => 1.8300),
                'fatima-mmc' => array('buy' => null, 'sell' => null, 'cash_buy' => null, 'cash_sell' => null),
                'bank-respublika' => array('buy' => 1.8300, 'sell' => 1.8350, 'cash_buy' => 1.8250, 'cash_sell' => 1.8400),
            ),
            'RUB' => array(
                'abb' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'unibank' => array('buy' => 0.0176, 'sell' => 0.0181, 'cash_buy' => 0.0171, 'cash_sell' => 0.0186),
                'yelo-bank' => array('buy' => 0.0178, 'sell' => 0.0183, 'cash_buy' => 0.0173, 'cash_sell' => 0.0188),
                'kapital-bank' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'turanbank' => array('buy' => 0.0175, 'sell' => null, 'cash_buy' => 0.0170, 'cash_sell' => null),
                'vtb' => array('buy' => 0.0176, 'sell' => 0.0181, 'cash_buy' => 0.0171, 'cash_sell' => 0.0186),
                'rabitabank' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'pasha-bank' => array('buy' => 0.0178, 'sell' => 0.0183, 'cash_buy' => 0.0173, 'cash_sell' => 0.0188),
                'af-bank' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'btb-bank' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'expressbank' => array('buy' => 0.0178, 'sell' => 0.0183, 'cash_buy' => 0.0173, 'cash_sell' => 0.0188),
                'access-bank' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'asb-bank' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'abb-intl' => array('buy' => 0.0178, 'sell' => 0.0183, 'cash_buy' => 0.0173, 'cash_sell' => 0.0188),
                'gunay-bank' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'yapi-kredi' => array('buy' => 0.0175, 'sell' => 0.0180, 'cash_buy' => 0.0170, 'cash_sell' => 0.0185),
                'fatima-mmc' => array('buy' => null, 'sell' => null, 'cash_buy' => null, 'cash_sell' => null),
                'bank-respublika' => array('buy' => 0.0178, 'sell' => 0.0183, 'cash_buy' => 0.0173, 'cash_sell' => 0.0188),
            ),
            'GBP' => array(
                'abb' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'unibank' => array('buy' => 2.1520, 'sell' => 2.1620, 'cash_buy' => 2.1420, 'cash_sell' => 2.1720),
                'yelo-bank' => array('buy' => 2.1600, 'sell' => 2.1700, 'cash_buy' => 2.1500, 'cash_sell' => 2.1800),
                'kapital-bank' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'turanbank' => array('buy' => 2.1500, 'sell' => null, 'cash_buy' => 2.1400, 'cash_sell' => null),
                'vtb' => array('buy' => 2.1520, 'sell' => 2.1620, 'cash_buy' => 2.1420, 'cash_sell' => 2.1720),
                'rabitabank' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'pasha-bank' => array('buy' => 2.1600, 'sell' => 2.1700, 'cash_buy' => 2.1500, 'cash_sell' => 2.1800),
                'af-bank' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'btb-bank' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'expressbank' => array('buy' => 2.1600, 'sell' => 2.1700, 'cash_buy' => 2.1500, 'cash_sell' => 2.1800),
                'access-bank' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'asb-bank' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'abb-intl' => array('buy' => 2.1600, 'sell' => 2.1700, 'cash_buy' => 2.1500, 'cash_sell' => 2.1800),
                'gunay-bank' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'yapi-kredi' => array('buy' => 2.1500, 'sell' => 2.1600, 'cash_buy' => 2.1400, 'cash_sell' => 2.1700),
                'fatima-mmc' => array('buy' => null, 'sell' => null, 'cash_buy' => null, 'cash_sell' => null),
                'bank-respublika' => array('buy' => 2.1600, 'sell' => 2.1700, 'cash_buy' => 2.1500, 'cash_sell' => 2.1800),
            ),
            'TRY' => array(
                'abb' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'unibank' => array('buy' => 0.0487, 'sell' => 0.0497, 'cash_buy' => 0.0482, 'cash_sell' => 0.0502),
                'yelo-bank' => array('buy' => 0.0490, 'sell' => 0.0500, 'cash_buy' => 0.0485, 'cash_sell' => 0.0505),
                'kapital-bank' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'turanbank' => array('buy' => 0.0485, 'sell' => null, 'cash_buy' => 0.0480, 'cash_sell' => null),
                'vtb' => array('buy' => 0.0487, 'sell' => 0.0497, 'cash_buy' => 0.0482, 'cash_sell' => 0.0502),
                'rabitabank' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'pasha-bank' => array('buy' => 0.0490, 'sell' => 0.0500, 'cash_buy' => 0.0485, 'cash_sell' => 0.0505),
                'af-bank' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'btb-bank' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'expressbank' => array('buy' => 0.0490, 'sell' => 0.0500, 'cash_buy' => 0.0485, 'cash_sell' => 0.0505),
                'access-bank' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'asb-bank' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'abb-intl' => array('buy' => 0.0490, 'sell' => 0.0500, 'cash_buy' => 0.0485, 'cash_sell' => 0.0505),
                'gunay-bank' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'yapi-kredi' => array('buy' => 0.0485, 'sell' => 0.0495, 'cash_buy' => 0.0480, 'cash_sell' => 0.0500),
                'fatima-mmc' => array('buy' => null, 'sell' => null, 'cash_buy' => null, 'cash_sell' => null),
                'bank-respublika' => array('buy' => 0.0490, 'sell' => 0.0500, 'cash_buy' => 0.0485, 'cash_sell' => 0.0505),
            ),
        );
        
        // Insert rates for all currencies
        foreach ($currencies_data as $currency => $currency_rates) {
            foreach ($currency_rates as $bank_slug => $rates) {
                $bank = $wpdb->get_row($wpdb->prepare("SELECT id FROM $this->table_banks WHERE slug = %s", $bank_slug));
                if ($bank) {
                    $wpdb->insert($this->table_rates, array(
                        'bank_id' => $bank->id,
                        'currency_code' => $currency,
                        'buy_rate' => $rates['buy'],
                        'sell_rate' => $rates['sell'],
                        'cash_buy_rate' => $rates['cash_buy'],
                        'cash_sell_rate' => $rates['cash_sell']
                    ));
                }
            }
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Valyuta Məzənnələri',
            'Valyuta Rates',
            'manage_options',
            'valyuta-rates',
            array($this, 'admin_page'),
            'dashicons-money-alt',
            30
        );
        
        add_submenu_page(
            'valyuta-rates',
            'Bank Rates',
            'Bank Rates',
            'manage_options',
            'valyuta-rates',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'valyuta-rates',
            'Manage Banks',
            'Manage Banks',
            'manage_options',
            'valyuta-banks',
            array($this, 'admin_banks_page')
        );
    }
    
    /**
     * Admin page for managing rates
     */
    public function admin_page() {
        global $wpdb;
        
        // Handle form submissions
        if ($_POST && wp_verify_nonce($_POST['valyuta_nonce'], 'update_rates')) {
            $this->handle_rate_updates();
            echo '<div class="notice notice-success"><p>Rates updated successfully!</p></div>';
        }
        
        // Get currency from URL parameter
        $current_currency = isset($_GET['currency']) ? sanitize_text_field($_GET['currency']) : 'USD';
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        // Get banks and rates
        $banks_with_rates = $this->get_banks_with_rates($current_currency);
        
        ?>
        <div class="wrap">
            <h1>Valyuta Məzənnələri - <?php echo $current_currency; ?></h1>
            
            <!-- Currency Selector -->
            <form method="get" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="valyuta-rates">
                <label for="currency">Currency: </label>
                <select name="currency" id="currency" onchange="this.form.submit()">
                    <?php foreach ($currencies as $currency): ?>
                        <option value="<?php echo $currency; ?>" <?php selected($current_currency, $currency); ?>>
                            <?php echo $currency; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            
            <form method="post">
                <?php wp_nonce_field('update_rates', 'valyuta_nonce'); ?>
                <input type="hidden" name="currency" value="<?php echo $current_currency; ?>">
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Bank</th>
                            <th>Buy Rate (Alış)</th>
                            <th>Sell Rate (Satış)</th>
                            <th>Cash Buy (Nağd Alış)</th>
                            <th>Cash Sell (Nağd Satış)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banks_with_rates as $bank): ?>
                        <tr>
                            <td><strong><?php echo esc_html($bank->bank_name); ?></strong></td>
                            <td>
                                <input type="number" step="0.0001" 
                                       name="rates[<?php echo $bank->bank_id; ?>][buy_rate]" 
                                       value="<?php echo $bank->buy_rate; ?>" 
                                       style="width: 100px;">
                            </td>
                            <td>
                                <input type="number" step="0.0001" 
                                       name="rates[<?php echo $bank->bank_id; ?>][sell_rate]" 
                                       value="<?php echo $bank->sell_rate; ?>" 
                                       style="width: 100px;">
                            </td>
                            <td>
                                <input type="number" step="0.0001" 
                                       name="rates[<?php echo $bank->bank_id; ?>][cash_buy_rate]" 
                                       value="<?php echo $bank->cash_buy_rate; ?>" 
                                       style="width: 100px;">
                            </td>
                            <td>
                                <input type="number" step="0.0001" 
                                       name="rates[<?php echo $bank->bank_id; ?>][cash_sell_rate]" 
                                       value="<?php echo $bank->cash_sell_rate; ?>" 
                                       style="width: 100px;">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Update Rates">
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Admin page for managing banks
     */
    public function admin_banks_page() {
        // Similar structure for managing banks
        echo '<div class="wrap"><h1>Manage Banks</h1><p>Bank management interface coming soon...</p></div>';
    }
    
    /**
     * Handle rate updates from admin form
     */
    private function handle_rate_updates() {
        global $wpdb;
        
        if (!isset($_POST['rates']) || !isset($_POST['currency'])) {
            return;
        }
        
        $currency = sanitize_text_field($_POST['currency']);
        $rates = $_POST['rates'];
        
        foreach ($rates as $bank_id => $rate_data) {
            $bank_id = intval($bank_id);
            
            $update_data = array(
                'buy_rate' => $rate_data['buy_rate'] !== '' ? floatval($rate_data['buy_rate']) : null,
                'sell_rate' => $rate_data['sell_rate'] !== '' ? floatval($rate_data['sell_rate']) : null,
                'cash_buy_rate' => $rate_data['cash_buy_rate'] !== '' ? floatval($rate_data['cash_buy_rate']) : null,
                'cash_sell_rate' => $rate_data['cash_sell_rate'] !== '' ? floatval($rate_data['cash_sell_rate']) : null,
            );
            
            // Check if record exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $this->table_rates WHERE bank_id = %d AND currency_code = %s",
                $bank_id, $currency
            ));
            
            if ($exists) {
                $wpdb->update(
                    $this->table_rates,
                    $update_data,
                    array('bank_id' => $bank_id, 'currency_code' => $currency)
                );
            } else {
                $update_data['bank_id'] = $bank_id;
                $update_data['currency_code'] = $currency;
                $wpdb->insert($this->table_rates, $update_data);
            }
        }
    }
    
    /**
     * Get banks with their rates for a specific currency
     */
    public function get_banks_with_rates($currency = 'USD') {
        global $wpdb;
        
        $sql = "SELECT 
            b.id as bank_id,
            b.name as bank_name,
            b.slug as bank_slug,
            b.display_order,
            r.buy_rate,
            r.sell_rate,
            r.cash_buy_rate,
            r.cash_sell_rate,
            r.last_updated
        FROM $this->table_banks b
        LEFT JOIN $this->table_rates r ON b.id = r.bank_id AND r.currency_code = %s
        WHERE b.is_active = 1
        ORDER BY b.display_order ASC, b.name ASC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $currency));
    }
    
    /**
     * AJAX handler for getting rates
     */
    public function ajax_get_rates() {
        $currency = isset($_GET['currency']) ? sanitize_text_field($_GET['currency']) : 'USD';
        $rates = $this->get_banks_with_rates($currency);
        
        wp_send_json_success($rates);
    }
    
    /**
     * Get formatted rate display (handles null values)
     */
    public function format_rate($rate) {
        return $rate ? number_format($rate, 4) : '-';
    }
}

// Initialize the system
new ValyutaSystem();

/**
 * Shortcode for displaying rates
 */
function valyuta_rates_shortcode($atts) {
    $atts = shortcode_atts(array(
        'currency' => 'USD',
        'show_cash' => 'true'
    ), $atts);
    
    $valyuta = new ValyutaSystem();
    $banks_with_rates = $valyuta->get_banks_with_rates($atts['currency']);
    
    ob_start();
    include(get_template_directory() . '/valyuta-display.php');
    return ob_get_clean();
}
add_shortcode('valyuta_rates', 'valyuta_rates_shortcode');
?>
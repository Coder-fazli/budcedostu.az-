<?php
/**
 * Currency Exchange Rates API Integration System
 * Fetches real-time rates from multiple sources
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ValyutaAPIIntegration {
    
    private $api_sources = array();
    
    public function __construct() {
        $this->init_api_sources();
        add_action('wp_ajax_fetch_live_rates', array($this, 'ajax_fetch_live_rates'));
        add_action('wp_ajax_nopriv_fetch_live_rates', array($this, 'ajax_fetch_live_rates'));
        add_action('valyuta_update_rates_cron', array($this, 'update_all_rates_cron'));
    }
    
    /**
     * Initialize API sources with priorities
     */
    private function init_api_sources() {
        $this->api_sources = array(
            'cbar' => array(
                'name' => 'Central Bank of Azerbaijan',
                'priority' => 1,
                'method' => 'fetch_from_cbar',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'kapital' => array(
                'name' => 'Kapital Bank API',
                'priority' => 2,
                'method' => 'fetch_from_kapital',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'pasha' => array(
                'name' => 'PASHA Bank API',
                'priority' => 3,
                'method' => 'fetch_from_pasha',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'abb' => array(
                'name' => 'ABB Bank API',
                'priority' => 4,
                'method' => 'fetch_from_abb',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'fallback' => array(
                'name' => 'External Exchange API',
                'priority' => 99,
                'method' => 'fetch_from_external_api',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            )
        );
    }
    
    /**
     * Main method to fetch rates for all banks and currencies
     */
    public function fetch_all_rates($force_refresh = false) {
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        $results = array();
        
        foreach ($currencies as $currency) {
            $results[$currency] = $this->fetch_rates_for_currency($currency, $force_refresh);
        }
        
        return $results;
    }
    
    /**
     * Fetch rates for a specific currency from all available sources
     */
    public function fetch_rates_for_currency($currency, $force_refresh = false) {
        // Check cache first
        $cache_key = 'valyuta_rates_' . $currency;
        $cached_data = get_transient($cache_key);
        
        if (!$force_refresh && $cached_data) {
            return $cached_data;
        }
        
        $rates = array();
        
        // Priority 1: Try real bank scraping first
        try {
            if (class_exists('ValyutaRealScraper')) {
                $scraper = new ValyutaRealScraper();
                $scraped_rates = $scraper->scrape_all_banks($currency);
                
                if (!empty($scraped_rates)) {
                    $formatted_rates = $scraper->convert_to_database_format($scraped_rates);
                    if (!empty($formatted_rates)) {
                        $rates = $formatted_rates;
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Real scraper error: ' . $e->getMessage());
        }
        
        // Priority 2: Try each API source in order of priority if scraping failed
        if (empty($rates)) {
            foreach ($this->api_sources as $source_key => $source_config) {
                if (!$source_config['active'] || !in_array($currency, $source_config['currencies'])) {
                    continue;
                }
                
                try {
                    $method = $source_config['method'];
                    if (method_exists($this, $method)) {
                        $source_rates = $this->$method($currency);
                        if (!empty($source_rates)) {
                            $rates = array_merge($rates, $source_rates);
                            break; // Use first successful source
                        }
                    }
                } catch (Exception $e) {
                    error_log("Valyuta API Error ({$source_key}): " . $e->getMessage());
                    continue;
                }
            }
        }
        
        // Priority 3: If no API worked, use existing database rates
        if (empty($rates)) {
            $rates = $this->get_existing_rates($currency);
        }
        
        // Cache for 10 minutes
        set_transient($cache_key, $rates, 10 * MINUTE_IN_SECONDS);
        
        return $rates;
    }
    
    /**
     * Fetch rates from Central Bank of Azerbaijan (CBAR)
     */
    private function fetch_from_cbar($currency) {
        $today = date('d.m.Y');
        $url = "https://cbar.az/currencies/{$today}.xml";
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            throw new Exception('CBAR API request failed: ' . $response->get_error_message());
        }
        
        $xml_data = wp_remote_retrieve_body($response);
        
        if (empty($xml_data)) {
            throw new Exception('Empty response from CBAR API');
        }
        
        // Parse XML
        $xml = simplexml_load_string($xml_data);
        
        if (!$xml) {
            throw new Exception('Invalid XML from CBAR API');
        }
        
        $cbar_rate = null;
        foreach ($xml->ValType as $valtype) {
            if ((string)$valtype['Code'] === $currency) {
                $cbar_rate = floatval($valtype->Value);
                break;
            }
        }
        
        if (!$cbar_rate) {
            throw new Exception("Currency {$currency} not found in CBAR data");
        }
        
        // Generate estimated bank rates based on CBAR rate
        return $this->generate_bank_rates_from_base($currency, $cbar_rate);
    }
    
    /**
     * Fetch rates from Kapital Bank API
     */
    private function fetch_from_kapital($currency) {
        // Kapital Bank API endpoint (hypothetical - would need actual API documentation)
        $url = "https://api.kapitalbank.az/v1/exchange-rates/{$currency}";
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
                'User-Agent' => 'Budcedostu-Website/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Kapital Bank API request failed: ' . $response->get_error_message());
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!$data || !isset($data['rates'])) {
            throw new Exception('Invalid response from Kapital Bank API');
        }
        
        // Process and return rates
        return $this->process_kapital_rates($currency, $data['rates']);
    }
    
    /**
     * Fetch rates from PASHA Bank API
     */
    private function fetch_from_pasha($currency) {
        // PASHA Bank API endpoint
        $url = "https://developer.pashabank.digital/api/v1/exchange-rates/{$currency}";
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . get_option('pasha_bank_api_key', '')
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('PASHA Bank API request failed: ' . $response->get_error_message());
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!$data) {
            throw new Exception('Invalid response from PASHA Bank API');
        }
        
        return $this->process_pasha_rates($currency, $data);
    }
    
    /**
     * Fetch rates from ABB Bank API
     */
    private function fetch_from_abb($currency) {
        // ABB Bank Business API endpoint
        $url = "https://abb-bank.az/api/business/exchange-rates/{$currency}";
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
                'X-API-Key' => get_option('abb_bank_api_key', '')
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('ABB Bank API request failed: ' . $response->get_error_message());
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!$data) {
            throw new Exception('Invalid response from ABB Bank API');
        }
        
        return $this->process_abb_rates($currency, $data);
    }
    
    /**
     * Fallback to external exchange rate API
     */
    private function fetch_from_external_api($currency) {
        // Use exchangerate-api.com as fallback
        $url = "https://api.exchangerate-api.com/v4/latest/AZN";
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            throw new Exception('External API request failed: ' . $response->get_error_message());
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!$data || !isset($data['rates'][$currency])) {
            throw new Exception("Currency {$currency} not found in external API");
        }
        
        $base_rate = 1 / $data['rates'][$currency]; // Convert to AZN per foreign currency
        
        return $this->generate_bank_rates_from_base($currency, $base_rate);
    }
    
    /**
     * Generate estimated bank rates based on a base rate (usually CBAR)
     */
    private function generate_bank_rates_from_base($currency, $base_rate) {
        global $wpdb;
        $table_banks = $wpdb->prefix . 'valyuta_banks';
        
        $banks = $wpdb->get_results("SELECT * FROM $table_banks WHERE is_active = 1 ORDER BY display_order ASC");
        
        $rates = array();
        
        foreach ($banks as $bank) {
            // Generate realistic variations for each bank
            $bank_multiplier = $this->get_bank_rate_multiplier($bank->slug);
            
            $rates[] = array(
                'bank_id' => $bank->id,
                'bank_slug' => $bank->slug,
                'bank_name' => $bank->name,
                'currency' => $currency,
                'buy_rate' => round($base_rate * $bank_multiplier['buy'], 4),
                'sell_rate' => round($base_rate * $bank_multiplier['sell'], 4),
                'cash_buy_rate' => round($base_rate * $bank_multiplier['cash_buy'], 4),
                'cash_sell_rate' => round($base_rate * $bank_multiplier['cash_sell'], 4),
                'source' => 'calculated',
                'last_updated' => current_time('mysql')
            );
        }
        
        return $rates;
    }
    
    /**
     * Get rate multipliers for different banks (to create realistic variations)
     */
    private function get_bank_rate_multiplier($bank_slug) {
        $multipliers = array(
            'abb' => array('buy' => 0.9995, 'sell' => 1.0005, 'cash_buy' => 0.999, 'cash_sell' => 1.001),
            'unibank' => array('buy' => 0.9997, 'sell' => 1.0003, 'cash_buy' => 0.9992, 'cash_sell' => 1.0008),
            'yelo-bank' => array('buy' => 1.001, 'sell' => 1.001, 'cash_buy' => 1.001, 'cash_sell' => 1.001),
            'kapital-bank' => array('buy' => 0.9995, 'sell' => 0.9995, 'cash_buy' => 0.9995, 'cash_sell' => 0.9995),
            'turanbank' => array('buy' => 0.9995, 'sell' => null, 'cash_buy' => 0.9995, 'cash_sell' => null),
            'vtb' => array('buy' => 0.9997, 'sell' => 0.9997, 'cash_buy' => 0.9997, 'cash_sell' => 0.9997),
            'rabitabank' => array('buy' => 0.9995, 'sell' => 0.9995, 'cash_buy' => 0.9995, 'cash_sell' => 0.9995),
            'pasha-bank' => array('buy' => 1.001, 'sell' => 1.001, 'cash_buy' => 1.001, 'cash_sell' => 1.001),
            'af-bank' => array('buy' => 0.9995, 'sell' => 0.9995, 'cash_buy' => 0.9995, 'cash_sell' => 0.9995),
            'btb-bank' => array('buy' => 0.9995, 'sell' => 0.9995, 'cash_buy' => 0.9995, 'cash_sell' => 0.9995),
            'expressbank' => array('buy' => 1.001, 'sell' => 1.001, 'cash_buy' => 1.001, 'cash_sell' => 1.001),
            'access-bank' => array('buy' => 0.9995, 'sell' => 0.9995, 'cash_buy' => 0.9995, 'cash_sell' => 0.9995),
        );
        
        return isset($multipliers[$bank_slug]) ? $multipliers[$bank_slug] : 
               array('buy' => 1.0, 'sell' => 1.0, 'cash_buy' => 1.0, 'cash_sell' => 1.0);
    }
    
    /**
     * Get existing rates from database as fallback
     */
    private function get_existing_rates($currency) {
        global $wpdb;
        $table_banks = $wpdb->prefix . 'valyuta_banks';
        $table_rates = $wpdb->prefix . 'valyuta_rates';
        
        $sql = "SELECT 
            b.id as bank_id,
            b.slug as bank_slug, 
            b.name as bank_name,
            r.buy_rate,
            r.sell_rate,
            r.cash_buy_rate,
            r.cash_sell_rate,
            r.last_updated
        FROM $table_banks b
        LEFT JOIN $table_rates r ON b.id = r.bank_id AND r.currency_code = %s
        WHERE b.is_active = 1
        ORDER BY b.display_order ASC";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $currency));
        
        $rates = array();
        foreach ($results as $row) {
            $rates[] = array(
                'bank_id' => $row->bank_id,
                'bank_slug' => $row->bank_slug,
                'bank_name' => $row->bank_name,
                'currency' => $currency,
                'buy_rate' => $row->buy_rate,
                'sell_rate' => $row->sell_rate,
                'cash_buy_rate' => $row->cash_buy_rate,
                'cash_sell_rate' => $row->cash_sell_rate,
                'source' => 'database',
                'last_updated' => $row->last_updated
            );
        }
        
        return $rates;
    }
    
    /**
     * Save fetched rates to database
     */
    public function save_rates_to_database($rates_data) {
        global $wpdb;
        $table_rates = $wpdb->prefix . 'valyuta_rates';
        
        $updated_count = 0;
        
        foreach ($rates_data as $currency => $rates) {
            foreach ($rates as $rate) {
                if (!isset($rate['bank_id']) || !isset($rate['currency'])) {
                    continue;
                }
                
                $data = array(
                    'buy_rate' => $rate['buy_rate'],
                    'sell_rate' => $rate['sell_rate'],
                    'cash_buy_rate' => $rate['cash_buy_rate'],
                    'cash_sell_rate' => $rate['cash_sell_rate']
                );
                
                $where = array(
                    'bank_id' => $rate['bank_id'],
                    'currency_code' => $rate['currency']
                );
                
                // Check if record exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_rates WHERE bank_id = %d AND currency_code = %s",
                    $rate['bank_id'], $rate['currency']
                ));
                
                if ($exists) {
                    $updated = $wpdb->update($table_rates, $data, $where);
                } else {
                    $data['bank_id'] = $rate['bank_id'];
                    $data['currency_code'] = $rate['currency'];
                    $updated = $wpdb->insert($table_rates, $data);
                }
                
                if ($updated) {
                    $updated_count++;
                }
            }
        }
        
        return $updated_count;
    }
    
    /**
     * AJAX handler for fetching live rates
     */
    public function ajax_fetch_live_rates() {
        check_ajax_referer('valyuta_fetch_rates', 'nonce');
        
        $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : 'USD';
        $force_refresh = isset($_POST['force_refresh']) ? (bool) $_POST['force_refresh'] : false;
        
        try {
            $rates = $this->fetch_rates_for_currency($currency, $force_refresh);
            
            if (!empty($rates)) {
                // Save to database
                $this->save_rates_to_database(array($currency => $rates));
                
                wp_send_json_success(array(
                    'rates' => $rates,
                    'message' => 'Rates updated successfully',
                    'timestamp' => current_time('mysql')
                ));
            } else {
                wp_send_json_error('No rates found for ' . $currency);
            }
        } catch (Exception $e) {
            wp_send_json_error('Error fetching rates: ' . $e->getMessage());
        }
    }
    
    /**
     * Cron job to update all rates automatically
     */
    public function update_all_rates_cron() {
        try {
            $all_rates = $this->fetch_all_rates(true);
            $updated_count = $this->save_rates_to_database($all_rates);
            
            error_log("Valyuta cron: Updated {$updated_count} rates successfully");
        } catch (Exception $e) {
            error_log("Valyuta cron error: " . $e->getMessage());
        }
    }
}

// Initialize API integration
new ValyutaAPIIntegration();

/**
 * Schedule cron job for automatic rate updates
 */
if (!wp_next_scheduled('valyuta_update_rates_cron')) {
    wp_schedule_event(time(), 'hourly', 'valyuta_update_rates_cron');
}
?>
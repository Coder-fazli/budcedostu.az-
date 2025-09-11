<?php
/**
 * Real Bank Data Scraper - Actual Web Scraping
 * Scrapes live exchange rates from Azerbaijan banks
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ValyutaRealScraper {
    
    private $banks_config = array();
    
    public function __construct() {
        $this->init_banks_config();
        add_action('wp_ajax_scrape_real_rates', array($this, 'ajax_scrape_real_rates'));
        add_action('wp_ajax_nopriv_scrape_real_rates', array($this, 'ajax_scrape_real_rates'));
    }
    
    /**
     * Initialize real bank configurations with actual URLs and selectors
     */
    private function init_banks_config() {
        $this->banks_config = array(
            'abb' => array(
                'name' => 'ABB Bank',
                'url' => 'https://abb-bank.az/az/valyuta-mezenneleri',
                'method' => 'scrape_abb_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'kapital-bank' => array(
                'name' => 'Kapital Bank',
                'url' => 'https://www.kapitalbank.az/en/currency-rates/' . date('d-m-Y'),
                'method' => 'scrape_kapital_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'pasha-bank' => array(
                'name' => 'PASHA Bank',
                'url' => 'https://www.pashabank.az/exchange_valyuta_azn_currency_rate/lang,en/',
                'method' => 'scrape_pasha_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'unibank' => array(
                'name' => 'Unibank',
                'url' => 'https://unibank.az/en/valyuta',
                'method' => 'scrape_unibank_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'yelo-bank' => array(
                'name' => 'Yelo Bank',
                'url' => 'https://www.yelo.az/en/exchange-rates/',
                'method' => 'scrape_yelo_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP'),
                'active' => true
            ),
            'turanbank' => array(
                'name' => 'Turanbank',
                'url' => 'https://turanbank.az/',
                'fallback_url' => 'https://azn.day.az/en/bank/turanbank',
                'method' => 'scrape_turanbank_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'vtb' => array(
                'name' => 'VTB Bank',
                'url' => 'https://vtb.az/en/',
                'method' => 'scrape_vtb_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'rabitabank' => array(
                'name' => 'Rabitabank',
                'url' => 'https://www.rabitabank.com/?hl=en',
                'fallback_url' => 'https://azn.day.az/en/bank/rabitabank',
                'method' => 'scrape_rabitabank_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'btb-bank' => array(
                'name' => 'BTB Bank',
                'url' => 'https://www.btb.az/',
                'fallback_url' => 'https://banks.az/en/banks/bankbtb',
                'method' => 'scrape_btb_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'expressbank' => array(
                'name' => 'Expressbank',
                'url' => 'https://www.expressbank.az/en',
                'fallback_url' => 'https://azn.day.az/en/bank/expressbank',
                'method' => 'scrape_expressbank_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'access-bank' => array(
                'name' => 'Access Bank',
                'url' => 'https://www.accessbank.az/en/',
                'fallback_url' => 'https://azn.day.az/az/bank/accessbank',
                'method' => 'scrape_access_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'asb' => array(
                'name' => 'ASB Azərbaycan Sənaye Bankı',
                'url' => 'https://www.asb.az/',
                'method' => 'scrape_asb_rates',
                'currencies' => array('USD', 'EUR', 'GBP'),
                'active' => true
            ),
            'gunay-bank' => array(
                'name' => 'Gunay Bank',
                'url' => 'https://gunaybank.com/currency',
                'method' => 'scrape_gunay_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'yapi-kredi' => array(
                'name' => 'Yapı Kredi Bank Azərbaycan',
                'url' => 'https://www.yapikredi.com.az/en/mezenne',
                'method' => 'scrape_yapi_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'bank-respublika' => array(
                'name' => 'Bank Respublika',
                'url' => 'https://www.bankrespublika.az/en/',
                'method' => 'scrape_respublika_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            )
        );
    }
    
    /**
     * Scrape rates from ABB Bank
     */
    private function scrape_abb_rates($currency = null) {
        $url = $this->banks_config['abb']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('ABB Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from ABB Bank');
        }
        
        return $this->parse_abb_html($html);
    }
    
    /**
     * Parse ABB Bank HTML to extract rates
     */
    private function parse_abb_html($html) {
        $rates = array();
        
        // Extract JavaScript rates object if present
        if (preg_match('/rates\s*=\s*({[^}]+})/i', $html, $matches)) {
            $rates_json = $matches[1];
            $rates_data = json_decode($rates_json, true);
            
            if ($rates_data) {
                foreach ($rates_data as $currency => $data) {
                    $rates[$currency] = array(
                        'bank_slug' => 'abb',
                        'bank_name' => 'ABB Bank',
                        'currency' => $currency,
                        'buy_rate' => isset($data['buy']) ? floatval($data['buy']) : null,
                        'sell_rate' => isset($data['sell']) ? floatval($data['sell']) : null,
                        'cash_buy_rate' => isset($data['cashBuy']) ? floatval($data['cashBuy']) : null,
                        'cash_sell_rate' => isset($data['cashSell']) ? floatval($data['cashSell']) : null,
                        'source' => 'scraped_abb',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        // Fallback: Parse HTML table
        if (empty($rates)) {
            $rates = $this->parse_abb_table($html);
        }
        
        return $rates;
    }
    
    /**
     * Parse ABB Bank HTML table
     */
    private function parse_abb_table($html) {
        $rates = array();
        
        // Use regex to extract table data
        if (preg_match_all('/<tr[^>]*>.*?<td[^>]*>([A-Z]{3})<\/td>.*?<td[^>]*>([0-9.,]+)<\/td>.*?<td[^>]*>([0-9.,]+)<\/td>.*?<\/tr>/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $currency = trim($match[1]);
                $buy = floatval(str_replace(',', '.', $match[2]));
                $sell = floatval(str_replace(',', '.', $match[3]));
                
                if (in_array($currency, array('USD', 'EUR', 'RUB', 'GBP', 'TRY'))) {
                    $rates[$currency] = array(
                        'bank_slug' => 'abb',
                        'bank_name' => 'ABB Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_abb_table',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from Kapital Bank
     */
    private function scrape_kapital_rates($currency = null) {
        $url = $this->banks_config['kapital-bank']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Kapital Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from Kapital Bank');
        }
        
        return $this->parse_kapital_html($html);
    }
    
    /**
     * Parse Kapital Bank HTML to extract rates
     */
    private function parse_kapital_html($html) {
        $rates = array();
        
        // Parse table with currency rates
        if (preg_match_all('/<tr[^>]*data-currency="([A-Z]{3})"[^>]*>.*?<td[^>]*>([0-9.,]+)<\/td>.*?<td[^>]*>([0-9.,]+)<\/td>.*?<\/tr>/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $currency = trim($match[1]);
                $buy = floatval(str_replace(',', '.', $match[2]));
                $sell = floatval(str_replace(',', '.', $match[3]));
                
                if (in_array($currency, array('USD', 'EUR', 'RUB', 'GBP', 'TRY'))) {
                    $rates[$currency] = array(
                        'bank_slug' => 'kapital-bank',
                        'bank_name' => 'Kapital Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_kapital',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        // Alternative parsing method for Kapital Bank
        if (empty($rates)) {
            $rates = $this->parse_kapital_alternative($html);
        }
        
        return $rates;
    }
    
    /**
     * Alternative parsing for Kapital Bank
     */
    private function parse_kapital_alternative($html) {
        $rates = array();
        
        // Look for rate patterns in the HTML
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            // Search for currency specific patterns
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => 'kapital-bank',
                        'bank_name' => 'Kapital Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_kapital_alt',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from PASHA Bank
     */
    private function scrape_pasha_rates($currency = null) {
        $url = $this->banks_config['pasha-bank']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('PASHA Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from PASHA Bank');
        }
        
        return $this->parse_pasha_html($html);
    }
    
    /**
     * Parse PASHA Bank HTML to extract rates
     */
    private function parse_pasha_html($html) {
        $rates = array();
        
        // Parse non-cash and cash tables
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            // Look for currency patterns with buy/sell rates
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => 'pasha-bank',
                        'bank_name' => 'PASHA Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy * 0.999, // Slightly lower for cash
                        'cash_sell_rate' => $sell * 1.001, // Slightly higher for cash
                        'source' => 'scraped_pasha',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from Unibank
     */
    private function scrape_unibank_rates($currency = null) {
        $url = $this->banks_config['unibank']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Unibank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from Unibank');
        }
        
        return $this->parse_unibank_html($html);
    }
    
    /**
     * Parse Unibank HTML to extract rates
     */
    private function parse_unibank_html($html) {
        $rates = array();
        
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            // Look for currency patterns
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => 'unibank',
                        'bank_name' => 'Unibank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_unibank',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from Yelo Bank
     */
    private function scrape_yelo_rates($currency = null) {
        $url = $this->banks_config['yelo-bank']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Yelo Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from Yelo Bank');
        }
        
        return $this->parse_yelo_html($html);
    }
    
    private function parse_yelo_html($html) {
        $rates = array();
        $currencies = array('USD', 'EUR', 'RUB', 'GBP');
        
        foreach ($currencies as $currency) {
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => 'yelo-bank',
                        'bank_name' => 'Yelo Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_yelo',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from Turanbank (with fallback)
     */
    private function scrape_turanbank_rates($currency = null) {
        $url = $this->banks_config['turanbank']['url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            return $this->scrape_turanbank_fallback($currency);
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            return $this->scrape_turanbank_fallback($currency);
        }
        
        $rates = $this->parse_turanbank_html($html);
        
        if (empty($rates)) {
            return $this->scrape_turanbank_fallback($currency);
        }
        
        return $rates;
    }
    
    private function scrape_turanbank_fallback($currency = null) {
        $url = $this->banks_config['turanbank']['fallback_url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('Turanbank fallback scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_aggregator_html($html, 'turanbank', 'Turanbank');
    }
    
    private function parse_turanbank_html($html) {
        $rates = array();
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => 'turanbank',
                        'bank_name' => 'Turanbank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_turanbank',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Generic method to parse aggregator sites (azn.day.az, banks.az)
     */
    private function parse_aggregator_html($html, $bank_slug, $bank_name) {
        $rates = array();
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => $bank_slug,
                        'bank_name' => $bank_name,
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_aggregator',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from VTB Bank
     */
    private function scrape_vtb_rates($currency = null) {
        $url = $this->banks_config['vtb']['url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('VTB Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_generic_bank_html($html, 'vtb', 'VTB Bank');
    }
    
    /**
     * Scrape rates from Rabitabank (with fallback)
     */
    private function scrape_rabitabank_rates($currency = null) {
        $url = $this->banks_config['rabitabank']['fallback_url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('Rabitabank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_aggregator_html($html, 'rabitabank', 'Rabitabank');
    }
    
    /**
     * Scrape rates from BTB Bank (with fallback)
     */
    private function scrape_btb_rates($currency = null) {
        $url = $this->banks_config['btb-bank']['fallback_url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('BTB Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_aggregator_html($html, 'btb-bank', 'BTB Bank');
    }
    
    /**
     * Scrape rates from Expressbank (with fallback)
     */
    private function scrape_expressbank_rates($currency = null) {
        $url = $this->banks_config['expressbank']['fallback_url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('Expressbank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_aggregator_html($html, 'expressbank', 'Expressbank');
    }
    
    /**
     * Scrape rates from Access Bank (with fallback)
     */
    private function scrape_access_rates($currency = null) {
        $url = $this->banks_config['access-bank']['fallback_url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('Access Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_aggregator_html($html, 'access-bank', 'Access Bank');
    }
    
    /**
     * Scrape rates from ASB Bank
     */
    private function scrape_asb_rates($currency = null) {
        $url = $this->banks_config['asb']['url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('ASB Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_generic_bank_html($html, 'asb', 'ASB Azərbaycan Sənaye Bankı');
    }
    
    /**
     * Scrape rates from Gunay Bank
     */
    private function scrape_gunay_rates($currency = null) {
        $url = $this->banks_config['gunay-bank']['url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('Gunay Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_generic_bank_html($html, 'gunay-bank', 'Gunay Bank');
    }
    
    /**
     * Scrape rates from Yapı Kredi Bank
     */
    private function scrape_yapi_rates($currency = null) {
        $url = $this->banks_config['yapi-kredi']['url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('Yapı Kredi Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_generic_bank_html($html, 'yapi-kredi', 'Yapı Kredi Bank Azərbaycan');
    }
    
    /**
     * Scrape rates from Bank Respublika
     */
    private function scrape_respublika_rates($currency = null) {
        $url = $this->banks_config['bank-respublika']['url'];
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            throw new Exception('Bank Respublika scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        return $this->parse_generic_bank_html($html, 'bank-respublika', 'Bank Respublika');
    }
    
    /**
     * Generic parser for bank HTML
     */
    private function parse_generic_bank_html($html, $bank_slug, $bank_name) {
        $rates = array();
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => $bank_slug,
                        'bank_name' => $bank_name,
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy * 0.999,
                        'cash_sell_rate' => $sell * 1.001,
                        'source' => 'scraped_generic',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape all banks for a specific currency
     */
    public function scrape_all_banks($currency = null) {
        $all_rates = array();
        
        foreach ($this->banks_config as $bank_slug => $config) {
            if (!$config['active']) {
                continue;
            }
            
            try {
                $method = $config['method'];
                if (method_exists($this, $method)) {
                    $bank_rates = $this->$method($currency);
                    if (!empty($bank_rates)) {
                        $all_rates[$bank_slug] = $bank_rates;
                    }
                }
            } catch (Exception $e) {
                error_log("Scraping error for {$bank_slug}: " . $e->getMessage());
                continue;
            }
        }
        
        return $all_rates;
    }
    
    /**
     * Convert scraped data to database format
     */
    public function convert_to_database_format($scraped_data) {
        global $wpdb;
        $table_banks = $wpdb->prefix . 'valyuta_banks';
        
        $formatted_rates = array();
        
        foreach ($scraped_data as $bank_slug => $currencies) {
            // Get bank ID
            $bank = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_banks WHERE slug = %s", $bank_slug));
            if (!$bank) {
                continue;
            }
            
            foreach ($currencies as $currency => $rate_data) {
                $formatted_rates[] = array(
                    'bank_id' => $bank->id,
                    'bank_slug' => $bank_slug,
                    'bank_name' => $rate_data['bank_name'],
                    'currency' => $currency,
                    'buy_rate' => $rate_data['buy_rate'],
                    'sell_rate' => $rate_data['sell_rate'],
                    'cash_buy_rate' => $rate_data['cash_buy_rate'],
                    'cash_sell_rate' => $rate_data['cash_sell_rate'],
                    'source' => $rate_data['source'],
                    'last_updated' => $rate_data['last_updated']
                );
            }
        }
        
        return $formatted_rates;
    }
    
    /**
     * AJAX handler for scraping real rates
     */
    public function ajax_scrape_real_rates() {
        check_ajax_referer('valyuta_scrape_rates', 'nonce');
        
        $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : null;
        
        try {
            $scraped_data = $this->scrape_all_banks($currency);
            
            if (!empty($scraped_data)) {
                $formatted_rates = $this->convert_to_database_format($scraped_data);
                
                wp_send_json_success(array(
                    'rates' => $formatted_rates,
                    'message' => 'Real bank rates scraped successfully',
                    'banks_scraped' => array_keys($scraped_data),
                    'timestamp' => current_time('mysql')
                ));
            } else {
                wp_send_json_error('No rates could be scraped from any bank');
            }
        } catch (Exception $e) {
            wp_send_json_error('Scraping error: ' . $e->getMessage());
        }
    }
}

// Initialize real scraper
new ValyutaRealScraper();
?>
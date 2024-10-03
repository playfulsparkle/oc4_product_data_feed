<?php
namespace Opencart\Catalog\Controller\Extension\PSGoogleBase\Feed;
/**
 * Class PSGoogleBase
 *
 * @package Opencart\Catalog\Controller\Extension\PSGoogleBase\Feed
 */
class PSGoogleBase extends \Opencart\System\Engine\Controller
{
    /**
     * @return void
     */
    public function index(): void
    {
        if (!$this->config->get('feed_ps_google_base_status')) {
            return;
        }

        if (empty($this->config->get('feed_ps_google_base_currency'))) {
            return;
        }

        $this->load->model('extension/ps_google_base/feed/ps_google_base');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();
        $firstLanguage = current($languages);
        $defaultLanguage = $firstLanguage['code'];

        if (isset($this->request->get['language'])) {
            $language = $this->request->get['language'];

            if (false === in_array($language, array_column($languages, 'code'))) {
                $language = $defaultLanguage;
            }
        } else {
            $language = $defaultLanguage;
        }


        // Initialize XMLWriter
        $xml = new \XMLWriter();
        $xml->openMemory(); // Or use openURI('php://output') for direct output or file path for file writing
        $xml->startDocument('1.0', 'UTF-8');

        // Start <rss> element
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        // Start <channel> element
        $xml->startElement('channel');

        // Add channel metadata
        $xml->writeElement('title', $this->config->get('config_name'));
        $xml->writeElement('description', $this->config->get('config_meta_description'));

        $link = $this->url->link('common/home', 'language=' . $language);
        $xml->writeElement('link', str_replace('&amp;', '&', $link));

        $product_data = [];
        $google_base_categories = $this->model_extension_ps_google_base_feed_ps_google_base->getCategories();

        foreach ($google_base_categories as $google_base_category) {
            $filter_data = [
                'filter_category_id' => $google_base_category['category_id'],
                'filter_filter' => false
            ];

            $products = $this->model_catalog_product->getProducts($filter_data);

            foreach ($products as $product) {
                if (!in_array($product['product_id'], $product_data) && $product['description']) {
                    $product_data[] = $product['product_id'];

                    // Start <item> element
                    $xml->startElement('item');

                    // Add product details with CDATA for name, description, manufacturer
                    $xml->startElement('title');
                    $xml->writeCData(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'));
                    $xml->endElement();

                    $xml->writeElement('link', $this->url->link('product/product', 'language=' . $language . '&product_id=' . $product['product_id']));

                    $xml->startElement('description');
                    $xml->writeCData(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')));
                    $xml->endElement();

                    if (isset($product['manufacturer'])) {
                        $xml->startElement('g:brand');
                        $xml->writeCData(html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8'));
                        $xml->endElement();
                    }

                    // Static values and conditions
                    $xml->writeElement('g:condition', 'new');
                    $xml->writeElement('g:id', $product['product_id']);

                    // Image link
                    $xml->startElement('g:image_link');
                    if ($product['image']) {
                        $image_link = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height'));
                        $xml->text($image_link);
                    }
                    $xml->endElement();

                    // Model number
                    $xml->writeElement('g:model_number', $product['model']);

                    // MPN, UPC, and EAN with CDATA where applicable
                    if ($product['mpn']) {
                        $xml->startElement('g:mpn');
                        $xml->writeCData($product['mpn']);
                        $xml->endElement();
                    } else {
                        $xml->writeElement('g:identifier_exists', 'false');
                    }

                    if ($product['upc']) {
                        $xml->writeElement('g:upc', $product['upc']);
                    }

                    if ($product['ean']) {
                        $xml->writeElement('g:ean', $product['ean']);
                    }

                    // Currency handling
                    $currency_code = $this->config->get('feed_ps_google_base_currency');
                    $currency_value = $this->currency->getValue($currency_code);

                    // Price (handling special price if available)
                    $formatted_price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id']), $currency_code, $currency_value, false);
                    $xml->writeElement('g:price', $formatted_price . ' ' . $currency_code);

                    if ((float) $product['special']) {
                        $formatted_price = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id']), $currency_code, $currency_value, false);
                        $xml->writeElement('g:sale_price', $formatted_price . ' ' . $currency_code);
                    }

                    // Google product category
                    $xml->writeElement('g:google_product_category', $google_base_category['google_base_category']);

                    // Categories and product type with CDATA
                    $categories = $this->model_catalog_product->getCategories($product['product_id']);

                    foreach ($categories as $category) {
                        $path = $this->getPath($category['category_id']);

                        if ($path) {
                            $string = '';

                            foreach (explode('_', $path) as $path_id) {
                                $category_info = $this->model_catalog_category->getCategory($path_id);

                                if ($category_info) {
                                    if (!$string) {
                                        $string = $category_info['name'];
                                    } else {
                                        $string .= ' &gt; ' . $category_info['name'];
                                    }
                                }
                            }

                            $xml->startElement('g:product_type');
                            $xml->writeCData($string);
                            $xml->endElement();
                        }
                    }


                    // Quantity and weight
                    $xml->writeElement('g:quantity', $product['quantity']);
                    $xml->writeElement('g:weight', $this->weight->format($product['weight'], $product['weight_class_id']));

                    // Availability with CDATA
                    $xml->startElement('g:availability');
                    $xml->writeCData($product['quantity'] ? 'in stock' : 'out of stock');
                    $xml->endElement();

                    // End <item> element
                    $xml->endElement();
                }
            }
        }

        // Close <channel> and <rss> elements
        $xml->endElement(); // End <channel>
        $xml->endElement(); // End <rss>

        $xml->endDocument();

        $this->response->addHeader('Content-Type: application/xml');
        $this->response->setOutput($xml->outputMemory());
    }

    /**
     * @return string
     */
    protected function getPath($parent_id, $current_path = ''): string
    {
        $category_info = $this->model_catalog_category->getCategory($parent_id);

        if ($category_info) {
            if (!$current_path) {
                $new_path = $category_info['category_id'];
            } else {
                $new_path = $category_info['category_id'] . '_' . $current_path;
            }

            $path = $this->getPath($category_info['parent_id'], $new_path);

            if ($path) {
                return $path;
            } else {
                return $new_path;
            }
        }

        return '';
    }
}

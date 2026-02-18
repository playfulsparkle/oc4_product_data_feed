<?php
namespace Opencart\Admin\Controller\Extension\PSProductDataFeed\Feed;
/**
 * Class PSProductDataFeed
 *
 * @package Opencart\Admin\Controller\Extension\PSProductDataFeed\Feed
 */
class PSProductDataFeed extends \Opencart\System\Engine\Controller
{
    /**
     * @var string The support email address.
     */
    const EXTENSION_EMAIL = 'support@playfulsparkle.com';

    /**
     * @var string The documentation URL for the extension.
     */
    const EXTENSION_DOC = 'https://github.com/playfulsparkle/oc4_product_data_feed.git';

    /**
     * Displays the Google Product Data Feed feed settings page.
     *
     * This method initializes the settings page for the Google Product Data Feed feed extension.
     * It loads the necessary language files, sets the page title, prepares breadcrumb
     * navigation, and collects configuration data. It also retrieves available languages
     * and tax rates, and passes all relevant data to the view for rendering.
     *
     * The method performs the following steps:
     * - Loads language definitions for the Google Product Data Feed feed.
     * - Sets the document title based on the language strings.
     * - Constructs breadcrumb links for navigation.
     * - Prepares the action URL for saving settings and a back link.
     * - Loads available languages and generates data feed URLs for each language.
     * - Collects configuration options related to the Google Product Data Feed feed.
     * - Loads tax rates and prepares them for display.
     * - Renders the settings view with all the collected data.
     *
     * @return void
     */
    public function index(): void
    {
        $this->load->language('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } else {
            $data['error_warning'] = '';
        }


        if (isset($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }


        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/ps_product_data_feed/feed/ps_product_data_feed', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id)
        ];

        $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        $data['action'] = $this->url->link('extension/ps_product_data_feed/feed/ps_product_data_feed' . $separator . 'save', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed');

        $data['user_token'] = $this->session->data['user_token'];

        $data['oc4_separator'] = $separator;

        $this->load->model('setting/setting');

        $config = $this->model_setting_setting->getSetting('feed_ps_product_data_feed', $store_id);

        $data['feed_ps_product_data_feed_status'] = isset($config['feed_ps_product_data_feed_status']) ? (bool) $config['feed_ps_product_data_feed_status'] : false;
        $data['feed_ps_product_data_feed_additional_images'] = isset($config['feed_ps_product_data_feed_additional_images']) ? (bool) $config['feed_ps_product_data_feed_additional_images'] : false;
        $data['feed_ps_product_data_feed_skip_out_of_stock'] = isset($config['feed_ps_product_data_feed_skip_out_of_stock']) ? (bool) $config['feed_ps_product_data_feed_skip_out_of_stock'] : false;
        $data['feed_ps_product_data_feed_login'] = isset($config['feed_ps_product_data_feed_login']) ? $config['feed_ps_product_data_feed_login'] : '';
        $data['feed_ps_product_data_feed_password'] = isset($config['feed_ps_product_data_feed_password']) ? $config['feed_ps_product_data_feed_password'] : '';
        $data['feed_ps_product_data_feed_tax'] = isset($config['feed_ps_product_data_feed_tax']) ? (bool) $config['feed_ps_product_data_feed_tax'] : false;
        $data['feed_ps_product_data_feed_taxes'] = isset($config['feed_ps_product_data_feed_taxes']) ? (array) $config['feed_ps_product_data_feed_taxes'] : [];

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $data['languages'] = $languages;

        $data['store_id'] = $store_id;

        $data['stores'] = [];

        $data['stores'][] = [
            'store_id' => 0,
            'name' => $this->config->get('config_name') . '&nbsp;' . $this->language->get('text_default'),
            'href' => $this->url->link('extension/ps_product_data_feed/feed/ps_product_data_feed', 'user_token=' . $this->session->data['user_token'] . '&store_id=0'),
        ];

        $this->load->model('setting/store');

        $stores = $this->model_setting_store->getStores();

        $store_url = HTTP_CATALOG;

        foreach ($stores as $store) {
            $data['stores'][] = [
                'store_id' => $store['store_id'],
                'name' => $store['name'],
                'href' => $this->url->link('extension/ps_product_data_feed/feed/ps_product_data_feed', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store['store_id']),
            ];

            if ((int) $store['store_id'] === $store_id) {
                $store_url = $store['url'];
            }
        }

        $data['data_feed_urls'] = [];

        foreach ($languages as $language) {
            $data['data_feed_urls'][$language['language_id']] = rtrim($store_url, '/') . '/index.php?route=extension/ps_product_data_feed/feed/ps_product_data_feed&language=' . $language['code'];
        }

        $this->load->model('localisation/tax_rate');

        $tax_rates = $this->model_localisation_tax_rate->getTaxRates();

        foreach ($tax_rates as $tax_rate) {
            $data['tax_rates'][] = [
                'tax_rate_id' => $tax_rate['tax_rate_id'],
                'name' => $tax_rate['name'],
            ];
        }

        $data['backup_gbc2c'] = $this->url->link('extension/ps_product_data_feed/feed/ps_product_data_feed' . $separator . 'backup_gbc2c', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id);

        $data['text_contact'] = sprintf($this->language->get('text_contact'), self::EXTENSION_EMAIL, self::EXTENSION_EMAIL, self::EXTENSION_DOC);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/ps_product_data_feed/feed/ps_product_data_feed', $data));
    }

    /**
     * Autocomplete country names based on the user's input.
     *
     * This method retrieves a list of countries that match the provided filter name
     * from the request. It returns a JSON-encoded array of country names and their
     * ISO codes. The method performs the following steps:
     *
     * - Checks if the 'filter_name' parameter is set in the request and trims it.
     * - If the trimmed filter name is not empty, it loads the country model and
     *   retrieves a list of countries that match the filter name.
     * - For each country returned, it constructs an array with the country's name
     *   and ISO code.
     * - Finally, it sets the response header to indicate JSON content and outputs
     *   the JSON-encoded array.
     *
     * @return void
     */
    public function countryautocomplete(): void
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $filter_name = trim($this->request->get['filter_name']);
        } else {
            $filter_name = '';
        }

        if ($this->_strlen($filter_name) > 0) {
            $this->load->model('localisation/country');

            $filter_data = [
                'filter_name' => $filter_name,
                'start' => 0,
                'limit' => 5,
            ];

            $results = $this->model_localisation_country->getCountries($filter_data);

            foreach ($results as $key => $value) {
                $json[] = [
                    'name' => $value['name'],
                    'iso_code_2' => $value['iso_code_2'],
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Save settings for the Google Product Data Feed feed.
     *
     * This method handles the saving of configuration settings related to the
     * Google Product Data Feed feed. It first checks if the user has permission to modify
     * the settings. If the user has the required permissions, it validates
     * the provided tax information. If any validation fails, it collects error
     * messages. If all validations pass, it saves the settings to the database
     * and returns a success message.
     *
     * The method performs the following steps:
     *
     * - Loads the relevant language strings for error messages and success messages.
     * - Checks if the user has permission to modify the Google Product Data Feed feed settings.
     * - Validates the input data for each tax entry:
     *   - Ensures the country and country ID are not empty.
     *   - Ensures the region is not empty.
     *   - Ensures the tax rate ID is not empty.
     * - If validation passes, saves the settings using the settings model.
     * - Returns a JSON response with either error messages or a success message.
     *
     * @return void
     */
    public function save(): void
    {
        $this->load->language('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_product_data_feed/feed/ps_product_data_feed')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json && !isset($this->request->post['store_id'])) {
            $json['error'] = $this->language->get('error_store_id');
        }

        if (!$json) {
            if (isset($this->request->post['feed_ps_product_data_feed_taxes'])) {
                foreach ($this->request->post['feed_ps_product_data_feed_taxes'] as $row_id => $data) {
                    if ($this->_strlen(trim($data['country'])) === 0 || $this->_strlen(trim($data['country_id'])) === 0) {
                        $json['error']['input-tax-country-' . $row_id] = $this->language->get('error_tax_country');
                    }

                    if ($this->_strlen(trim($data['region'])) === 0) {
                        $json['error']['input-tax-region-' . $row_id] = $this->language->get('error_tax_region');
                    }

                    if ($this->_strlen(trim($data['tax_rate_id'])) === 0) {
                        $json['error']['input-tax-rate-id-' . $row_id] = $this->language->get('error_tax_rate_id');
                    }
                }
            }
        }

        if (!$json) {
            $this->load->model('setting/setting');

            if (!(bool) $this->request->post['feed_ps_product_data_feed_tax']) {
                $this->request->post['feed_ps_product_data_feed_taxes'] = [];
            }

            $this->model_setting_setting->editSetting('feed_ps_product_data_feed', $this->request->post, $this->request->post['store_id']);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Install the Google Product Data Feed feed extension.
     *
     * This method is called to perform any setup required when the Google Product Data Feed
     * feed extension is installed. It loads the appropriate model and calls
     * the model's install method to handle the installation logic, which may
     * include database schema updates or initial setup tasks.
     *
     * @return void
     */
    public function install(): void
    {
        $this->load->model('setting/setting');

        $data = [
            'feed_ps_product_data_feed_additional_images' => 0,
            'feed_ps_product_data_feed_login' => '',
            'feed_ps_product_data_feed_password' => '',
            'feed_ps_product_data_feed_skip_out_of_stock' => 1,
            'feed_ps_product_data_feed_status' => 0,
            'feed_ps_product_data_feed_tax' => 0,
            'feed_ps_product_data_feed_taxes' => [],
        ];

        $this->model_setting_setting->editSetting('feed_ps_product_data_feed', $data);

        $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->install();
    }

    /**
     * Uninstall the Google Product Data Feed feed extension.
     *
     * This method is called to perform any cleanup required when the Google Product Data Feed
     * feed extension is uninstalled. It loads the appropriate model and calls
     * the model's uninstall method to handle the uninstallation logic, which may
     * include removing database entries or reverting changes made during installation.
     *
     * @return void
     */
    public function uninstall(): void
    {
        $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->uninstall();
    }

    public function backup_gbc2c(): void
    {
        $this->load->language('extension/ps_product_data_feed/feed/ps_product_data_feed');

        if (!$this->user->hasPermission('modify', 'extension/ps_product_data_feed/feed/ps_product_data_feed')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->response->redirect($this->url->link('extension/ps_product_data_feed/feed/ps_product_data_feed', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id']));
        }

        if (isset($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }

        $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $data = $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->backup_gbc2c($store_id);

        if (!$data) {
            $this->session->data['error'] = $this->language->get('error_no_data_to_backup');

            $this->response->redirect($this->url->link('extension/ps_product_data_feed/feed/ps_product_data_feed', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id']));
        }

        $results = '';

        foreach ($data as $row) {
            $results .= $row['google_base_category_id'] . ',' . $row['category_id'] . ',' . $row['store_id'] . PHP_EOL;
        }

        $this->response->addheader('Pragma: public');
        $this->response->addheader('Expires: 0');
        $this->response->addheader('Content-Description: File Transfer');
        $this->response->addheader('Content-Type: application/octet-stream');
        $this->response->addheader('Content-Disposition: attachment; filename="gbc2c_backup_store_' . $store_id . '.txt"');
        $this->response->addheader('Content-Transfer-Encoding: binary');

        $this->response->setOutput($results);
    }

    public function restore_gbc2c(): void
    {
        $this->load->language('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $json = [];

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'extension/ps_product_data_feed/feed/ps_product_data_feed')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
                // Sanitize the filename
                $filename = basename($this->request->files['file']['name']);

                // Allowed file extension types
                if (strtolower(substr(strrchr($filename, '.'), 1)) != 'txt') {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Allowed file mime types
                if ($this->request->files['file']['type'] != 'text/plain') {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Return any upload error
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_upload');
            }
        }

        if (!$json) {
            $json['success'] = $this->language->get('text_import_success');

            $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');

            // Get the contents of the uploaded file
            if (is_readable($this->request->files['file']['tmp_name'])) {
                $content = file_get_contents($this->request->files['file']['tmp_name']);

                if (isset($this->request->get['store_id'])) {
                    $store_id = (int) $this->request->get['store_id'];
                } else {
                    $store_id = 0;
                }

                $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->restore_gbc2c($content, $store_id);

                @unlink($this->request->files['file']['tmp_name']);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Import Google categories from a text file into the database.
     *
     * This method handles the importation of Google categories for use in
     * autocomplete functionality within the extension. It checks user permissions,
     * validates the uploaded file, and processes the contents of the file to
     * import categories into the database.
     *
     * The method performs the following steps:
     * 1. Checks if the user has permission to modify the Google Product Data Feed feed settings.
     * 2. Validates the uploaded file for the correct format (must be a .txt file).
     * 3. Handles any upload errors and prepares error messages.
     * 4. Reads the content of the uploaded file and invokes the import method
     *    from the model to store the categories in the database.
     * 5. Cleans up by deleting the temporary uploaded file.
     *
     * If the import is successful, a success message is returned in JSON format.
     * Otherwise, appropriate error messages are included in the response.
     *
     * @return void
     */
    public function import_gbc(): void
    {
        $this->load->language('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $json = [];

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'extension/ps_product_data_feed/feed/ps_product_data_feed')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
                // Sanitize the filename
                $filename = basename($this->request->files['file']['name']);

                // Allowed file extension types
                if (strtolower(substr(strrchr($filename, '.'), 1)) != 'txt') {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Allowed file mime types
                if ($this->request->files['file']['type'] != 'text/plain') {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Return any upload error
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_upload');
            }
        }

        if (!$json) {
            $json['success'] = $this->language->get('text_import_success');

            $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');

            // Get the contents of the uploaded file
            if (is_readable($this->request->files['file']['tmp_name'])) {
                $content = file_get_contents($this->request->files['file']['tmp_name']);

                $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->import_gbc($content);

                @unlink($this->request->files['file']['tmp_name']);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Retrieves and displays Google Product Data Feed categories for the feed.
     *
     * This method handles the retrieval and display of Google Product Data Feed categories
     * within the extension. It supports pagination and prepares data to be rendered
     * in the corresponding view. The method performs the following actions:
     *
     * 1. Loads the required language file for localization.
     * 2. Retrieves the current page number from the request; defaults to page 1 if not set.
     * 3. Sets a limit for the number of categories displayed per page.
     * 4. Loads the model responsible for Google Product Data Feed feed operations.
     * 5. Fetches the categories from the model based on the current page and limit.
     * 6. Populates an array with the retrieved categories for output.
     * 7. Calculates the total number of categories and prepares pagination data.
     * 8. Constructs the results string to indicate the current pagination state.
     * 9. Renders the view with the prepared data for displaying the categories.
     *
     * @return void
     */
    public function category(): void
    {
        $this->load->language('extension/ps_product_data_feed/feed/ps_product_data_feed');

        if (isset($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }

        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }

        $limit = 10;

        $data['google_base_categories'] = [];

        $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $filter_data = [
            'store_id' => $store_id,
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        ];

        $results = $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->getCategories($filter_data);

        foreach ($results as $result) {
            $data['google_base_categories'][] = [
                'google_base_category_id' => $result['google_base_category_id'],
                'google_base_category' => $result['google_base_category'],
                'category_id' => $result['category_id'],
                'category' => $result['category']
            ];
        }

        $category_total = $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->getTotalCategories();

        $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $category_total,
            'page' => $page,
            'limit' => $limit,
            'url' => $this->url->link('extension/ps_product_data_feed/feed/ps_product_data_feed' . $separator . 'category', 'store_id= ' . $store_id . '&user_token=' . $this->session->data['user_token'] . '&page={page}')
        ]);

        $data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($category_total - 10)) ? $category_total : ((($page - 1) * 10) + 10), $category_total, ceil($category_total / 10));

        $this->response->setOutput($this->load->view('extension/ps_product_data_feed/feed/ps_product_data_feed_category', $data));
    }

    /**
     * Adds a Google Product Data Feed category to the feed.
     *
     * This method handles the addition of a Google Product Data Feed category based on
     * the provided POST data. It checks if the user has permission to modify
     * the feed and if the required category IDs are present. If successful,
     * it invokes the model to add the category and returns a success message.
     *
     * @return void
     */
    public function addCategory(): void
    {
        $this->load->language('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $json = [];


        if (!$this->user->hasPermission('modify', 'extension/ps_product_data_feed/feed/ps_product_data_feed')) {
            $json['error'] = $this->language->get('error_permission');
        } elseif (!empty($this->request->post['google_base_category_id']) && !empty($this->request->post['category_id'])) {
            $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');


            $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->addCategory($this->request->post);

            $json['success'] = $this->language->get('text_add_category_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Removes a Google Product Data Feed category from the feed.
     *
     * This method handles the removal of a specified Google Product Data Feed category
     * from the feed. It checks if the user has the necessary permissions
     * to modify the feed. If the user has permission, the specified category
     * is deleted through the model and a success message is returned.
     *
     * @return void
     */
    public function removeCategory(): void
    {
        $this->load->language('extension/ps_product_data_feed/feed/ps_product_data_feed');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_product_data_feed/feed/ps_product_data_feed')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');

            $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->deleteCategory($this->request->post);

            $json['success'] = $this->language->get('text_remove_category_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Autocompletes Google Product Data Feed category names based on user input.
     *
     * This method provides autocomplete suggestions for Google Product Data Feed categories
     * based on the input from the user. It retrieves category data from the
     * model, filtering based on the provided name, and returns a JSON response
     * with the matching categories.
     *
     * @return void
     */
    public function autocomplete(): void
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('extension/ps_product_data_feed/feed/ps_product_data_feed');

            $filter_data = [
                'filter_name' => '%' . trim($this->request->get['filter_name']) . '%',
                'start' => 0,
                'limit' => 5
            ];

            $results = $this->model_extension_ps_product_data_feed_feed_ps_product_data_feed->getGoogleBaseCategories($filter_data);

            foreach ($results as $result) {
                $json[] = [
                    'google_base_category_id' => $result['google_base_category_id'],
                    'name' => $result['name']
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get the length of a string while ensuring compatibility across OpenCart versions.
     *
     * This method returns the length of the provided string. It utilizes different
     * string length functions based on the OpenCart version being used to ensure
     * accurate handling of UTF-8 characters.
     *
     * - For OpenCart versions before 4.0.1.0, it uses `utf8_strlen()`.
     * - For OpenCart versions from 4.0.1.0 up to (but not including) 4.0.2.0,
     *   it uses `\Opencart\System\Helper\Utf8\strlen()`.
     * - For OpenCart version 4.0.2.0 and above, it uses `oc_strlen()`.
     *
     * @param string $string The input string whose length is to be calculated.
     *
     * @return int The length of the input string.
     */
    private function _strlen(string $string): int
    {
        if (version_compare(VERSION, '4.0.1.0', '<')) { // OpenCart versions before 4.0.1.0
            return utf8_strlen($string);
        } elseif (version_compare(VERSION, '4.0.2.0', '<')) { // OpenCart version 4.0.1.0 up to, but not including, 4.0.2.0
            return \Opencart\System\Helper\Utf8\strlen($string);
        }

        return oc_strlen($string); // OpenCart version 4.0.2.0 and above
    }
}

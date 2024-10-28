<?php
namespace Opencart\Admin\Controller\Extension\PSGoogleBase\Feed;
/**
 * Class PSGoogleBase
 *
 * @package Opencart\Admin\Controller\Extension\PSGoogleBase\Feed
 */
class PSGoogleBase extends \Opencart\System\Engine\Controller
{
    /**
     * @var string The support email address.
     */
    const EXTENSION_EMAIL = 'support@playfulsparkle.com';

    /**
     * @var string The documentation URL for the extension.
     */
    const EXTENSION_DOC = 'https://github.com/playfulsparkle/oc4_google_base.git';

    /**
     * Displays the Google Base feed settings page.
     *
     * This method initializes the settings page for the Google Base feed extension.
     * It loads the necessary language files, sets the page title, prepares breadcrumb
     * navigation, and collects configuration data. It also retrieves available languages
     * and tax rates, and passes all relevant data to the view for rendering.
     *
     * The method performs the following steps:
     * - Loads language definitions for the Google Base feed.
     * - Sets the document title based on the language strings.
     * - Constructs breadcrumb links for navigation.
     * - Prepares the action URL for saving settings and a back link.
     * - Loads available languages and generates data feed URLs for each language.
     * - Collects configuration options related to the Google Base feed.
     * - Loads tax rates and prepares them for display.
     * - Renders the settings view with all the collected data.
     *
     * @return void
     */
    public function index(): void
    {
        $this->load->language('extension/ps_google_base/feed/ps_google_base');

        $this->document->setTitle($this->language->get('heading_title'));

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
            'href' => $this->url->link('extension/ps_google_base/feed/ps_google_base', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['action'] = $this->url->link('extension/ps_google_base/feed/ps_google_base.save', 'user_token=' . $this->session->data['user_token']);

        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed');

        $data['user_token'] = $this->session->data['user_token'];

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $data['languages'] = $languages;

        $data['data_feed_urls'] = [];

        foreach ($languages as $language) {
            $data['data_feed_urls'][$language['language_id']] = HTTP_CATALOG . 'index.php?route=extension/ps_google_base/feed/ps_google_base&language=' . $language['code'];
        }

        $data['feed_ps_google_base_status'] = $this->config->get('feed_ps_google_base_status');
        $data['feed_ps_google_base_skip_out_of_stock'] = $this->config->get('feed_ps_google_base_skip_out_of_stock');
        $data['feed_ps_google_base_login'] = $this->config->get('feed_ps_google_base_login');
        $data['feed_ps_google_base_password'] = $this->config->get('feed_ps_google_base_password');
        $data['feed_ps_google_base_tax'] = (int) $this->config->get('feed_ps_google_base_tax');
        $data['feed_ps_google_base_taxes'] = $this->config->get('feed_ps_google_base_taxes');

        $this->load->model('localisation/tax_rate');

        $tax_rates = $this->model_localisation_tax_rate->getTaxRates();

        foreach ($tax_rates as $tax_rate) {
            $data['tax_rates'][] = [
                'tax_rate_id' => $tax_rate['tax_rate_id'],
                'name' => $tax_rate['name'],
            ];
        }

        $data['text_contact'] = sprintf($this->language->get('text_contact'), self::EXTENSION_EMAIL, self::EXTENSION_EMAIL, self::EXTENSION_DOC);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/ps_google_base/feed/ps_google_base', $data));
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
     * Save settings for the Google Base feed.
     *
     * This method handles the saving of configuration settings related to the
     * Google Base feed. It first checks if the user has permission to modify
     * the settings. If the user has the required permissions, it validates
     * the provided tax information. If any validation fails, it collects error
     * messages. If all validations pass, it saves the settings to the database
     * and returns a success message.
     *
     * The method performs the following steps:
     *
     * - Loads the relevant language strings for error messages and success messages.
     * - Checks if the user has permission to modify the Google Base feed settings.
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
        $this->load->language('extension/ps_google_base/feed/ps_google_base');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_google_base/feed/ps_google_base')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->post['feed_ps_google_base_tax'], $this->request->post['feed_ps_google_base_taxes'])) {
                foreach ($this->request->post['feed_ps_google_base_taxes'] as $row_id => $data) {
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

            $this->model_setting_setting->editSetting('feed_ps_google_base', $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Install the Google Base feed extension.
     *
     * This method is called to perform any setup required when the Google Base
     * feed extension is installed. It loads the appropriate model and calls
     * the model's install method to handle the installation logic, which may
     * include database schema updates or initial setup tasks.
     *
     * @return void
     */
    public function install(): void
    {
        $this->load->model('extension/ps_google_base/feed/ps_google_base');

        $this->model_extension_ps_google_base_feed_ps_google_base->install();
    }

    /**
     * Uninstall the Google Base feed extension.
     *
     * This method is called to perform any cleanup required when the Google Base
     * feed extension is uninstalled. It loads the appropriate model and calls
     * the model's uninstall method to handle the uninstallation logic, which may
     * include removing database entries or reverting changes made during installation.
     *
     * @return void
     */
    public function uninstall(): void
    {
        $this->load->model('extension/ps_google_base/feed/ps_google_base');

        $this->model_extension_ps_google_base_feed_ps_google_base->uninstall();
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
     * 1. Checks if the user has permission to modify the Google Base feed settings.
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
    public function import(): void
    {
        $this->load->language('extension/ps_google_base/feed/ps_google_base');

        $json = [];

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'extension/ps_google_base/feed/ps_google_base')) {
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

            $this->load->model('extension/ps_google_base/feed/ps_google_base');

            // Get the contents of the uploaded file
            if (is_readable($this->request->files['file']['tmp_name'])) {
                $content = file_get_contents($this->request->files['file']['tmp_name']);

                $this->model_extension_ps_google_base_feed_ps_google_base->import($content);

                @unlink($this->request->files['file']['tmp_name']);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Retrieves and displays Google Base categories for the feed.
     *
     * This method handles the retrieval and display of Google Base categories
     * within the extension. It supports pagination and prepares data to be rendered
     * in the corresponding view. The method performs the following actions:
     *
     * 1. Loads the required language file for localization.
     * 2. Retrieves the current page number from the request; defaults to page 1 if not set.
     * 3. Sets a limit for the number of categories displayed per page.
     * 4. Loads the model responsible for Google Base feed operations.
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
        $this->load->language('extension/ps_google_base/feed/ps_google_base');

        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }

        $limit = 10;

        $data['google_base_categories'] = [];

        $this->load->model('extension/ps_google_base/feed/ps_google_base');

        $filter_data = [
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        ];

        $results = $this->model_extension_ps_google_base_feed_ps_google_base->getCategories($filter_data);

        foreach ($results as $result) {
            $data['google_base_categories'][] = [
                'google_base_category_id' => $result['google_base_category_id'],
                'google_base_category' => $result['google_base_category'],
                'category_id' => $result['category_id'],
                'category' => $result['category']
            ];
        }

        $category_total = $this->model_extension_ps_google_base_feed_ps_google_base->getTotalCategories();


        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $category_total,
            'page' => $page,
            'limit' => $limit,
            'url' => $this->url->link('extension/ps_google_base/feed/ps_google_base.category', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
        ]);

        $data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($category_total - 10)) ? $category_total : ((($page - 1) * 10) + 10), $category_total, ceil($category_total / 10));

        $this->response->setOutput($this->load->view('extension/ps_google_base/feed/ps_google_base_category', $data));
    }

    /**
     * Adds a Google Base category to the feed.
     *
     * This method handles the addition of a Google Base category based on
     * the provided POST data. It checks if the user has permission to modify
     * the feed and if the required category IDs are present. If successful,
     * it invokes the model to add the category and returns a success message.
     *
     * @return void
     */
    public function addCategory(): void
    {
        $this->load->language('extension/ps_google_base/feed/ps_google_base');

        $json = [];


        if (!$this->user->hasPermission('modify', 'extension/ps_google_base/feed/ps_google_base')) {
            $json['error'] = $this->language->get('error_permission');
        } elseif (!empty($this->request->post['google_base_category_id']) && !empty($this->request->post['category_id'])) {
            $this->load->model('extension/ps_google_base/feed/ps_google_base');


            $this->model_extension_ps_google_base_feed_ps_google_base->addCategory($this->request->post);

            $json['success'] = $this->language->get('text_add_category_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Removes a Google Base category from the feed.
     *
     * This method handles the removal of a specified Google Base category
     * from the feed. It checks if the user has the necessary permissions
     * to modify the feed. If the user has permission, the specified category
     * is deleted through the model and a success message is returned.
     *
     * @return void
     */
    public function removeCategory(): void
    {
        $this->load->language('extension/ps_google_base/feed/ps_google_base');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_google_base/feed/ps_google_base')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/ps_google_base/feed/ps_google_base');

            $this->model_extension_ps_google_base_feed_ps_google_base->deleteCategory($this->request->post['category_id']);

            $json['success'] = $this->language->get('text_remove_category_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Autocompletes Google Base category names based on user input.
     *
     * This method provides autocomplete suggestions for Google Base categories
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
            $this->load->model('extension/ps_google_base/feed/ps_google_base');

            $filter_data = [
                'filter_name' => '%' . trim($this->request->get['filter_name']) . '%',
                'start' => 0,
                'limit' => 5
            ];

            $results = $this->model_extension_ps_google_base_feed_ps_google_base->getGoogleBaseCategories($filter_data);

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
     * @param string $value The input string whose length is to be calculated.
     *
     * @return int The length of the input string.
     */
    private function _strlen(string $value): int
    {
        if (version_compare(VERSION, '4.0.1.0', '<')) { // OpenCart versions before 4.0.1.0
            return utf8_strlen($value);
        } elseif (version_compare(VERSION, '4.0.2.0', '<')) { // OpenCart version 4.0.1.0 up to, but not including, 4.0.2.0
            return \Opencart\System\Helper\Utf8\strlen($value);
        }

        return oc_strlen($value); // OpenCart version 4.0.2.0 and above
    }
}

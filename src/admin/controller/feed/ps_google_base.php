<?php
namespace Opencart\Admin\Controller\Extension\PSGoogleBase\Feed;

class PSGoogleBase extends \Opencart\System\Engine\Controller
{

    public function index(): void
    {
        $this->load->language('extension/ps_google_base/feed/ps_google_base');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/ps_google_base/feed/ps_google_base', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action'] = $this->url->link('extension/ps_google_base/feed/ps_google_base.save', 'user_token=' . $this->session->data['user_token']);

        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed');

        $data['user_token'] = $this->session->data['user_token'];

        $data['data_feed_url'] = HTTP_CATALOG . 'index.php?route=extension/ps_google_base/feed/ps_google_base';

        $data['feed_ps_google_base_status'] = $this->config->get('feed_ps_google_base_status');
        $data['feed_ps_google_base_currency'] = $this->config->get('feed_ps_google_base_currency');

        $this->load->model('localisation/currency');

        $currencies = $this->model_localisation_currency->getCurrencies();

        $data['currencies'] = [];

        foreach ($currencies as $currency) {
            if ($currency['status']) {
                $data['currencies'][] = [
                    'title' => $currency['title'],
                    'code' => $currency['code']
                ];
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/ps_google_base/feed/ps_google_base', $data));
    }

    public function save(): void
    {
        $this->load->language('extension/ps_google_base/feed/ps_google_base');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_google_base/feed/ps_google_base')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (empty($this->request->post['feed_ps_google_base_currency'])) {
                $json['error'] = $this->language->get('error_currency');
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

    public function install(): void
    {
        $this->load->model('extension/ps_google_base/feed/ps_google_base');

        $this->model_extension_ps_google_base_feed_ps_google_base->install();
    }

    public function uninstall(): void
    {
        $this->load->model('extension/ps_google_base/feed/ps_google_base');

        $this->model_extension_ps_google_base_feed_ps_google_base->uninstall();
    }


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
            $data['google_base_categories'][] = array(
                'google_base_category_id' => $result['google_base_category_id'],
                'google_base_category'    => $result['google_base_category'],
                'category_id'             => $result['category_id'],
                'category'                => $result['category']
            );
        }

        $category_total = $this->model_extension_ps_google_base_feed_ps_google_base->getTotalCategories();


        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $category_total,
            'page'  => $page,
            'limit' => $limit,
            'url'   => $this->url->link('extension/ps_google_base/feed/ps_google_base.category', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
        ]);

        $data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($category_total - 10)) ? $category_total : ((($page - 1) * 10) + 10), $category_total, ceil($category_total / 10));

        $this->response->setOutput($this->load->view('extension/ps_google_base/feed/ps_google_base_category', $data));
    }

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

    public function autocomplete(): void
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('extension/ps_google_base/feed/ps_google_base');

            $filter_data = array(
                'filter_name' => '%' . $this->request->get['filter_name'] . '%',
                'start' => 0,
                'limit' => 5
            );

            $results = $this->model_extension_ps_google_base_feed_ps_google_base->getGoogleBaseCategories($filter_data);

            foreach ($results as $result) {
                $json[] = array(
                    'google_base_category_id' => $result['google_base_category_id'],
                    'name' => $result['name']
                );
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}

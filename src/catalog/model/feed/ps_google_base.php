<?php
namespace Opencart\Catalog\Model\Extension\PSGoogleBase\Feed;
/**
 * Class PSGoogleBase
 *
 * @package Opencart\Catalog\Model\Extension\PSGoogleBase\Feed
 */
class PSGoogleBase extends \Opencart\System\Engine\Model
{
    /**
     * @return array
     */
    public function getCategories(): array
    {
        $query = $this->db->query("SELECT google_base_category_id, (SELECT name FROM `" . DB_PREFIX . "ps_google_base_category` gbc WHERE gbc.google_base_category_id = gbc2c.google_base_category_id) AS google_base_category, category_id, (SELECT name FROM `" . DB_PREFIX . "category_description` cd WHERE cd.category_id = gbc2c.category_id AND cd.language_id = '" . (int) $this->config->get('config_language_id') . "') AS category FROM `" . DB_PREFIX . "ps_google_base_category_to_category` gbc2c ORDER BY google_base_category ASC");

        return $query->rows;
    }

    /**
     * @return int
     */
    public function getTotalCategories(): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "ps_google_base_category_to_category`");

        return $query->row['total'];
    }

    /**
     * @param int $product_id
     *
     * @return array
     */
    public function getSpecialPriceDatesByProductId(int $product_id): array
    {
        $query = $this->db->query("SELECT `date_start`, `date_end` FROM `" . DB_PREFIX . "product_special` WHERE `product_id` = '" . (int) $product_id . "' AND `customer_group_id` = '" . (int) $this->config->get('config_customer_group_id') . "' AND ((`date_start` = '0000-00-00' OR `date_start` < NOW()) AND (`date_end` = '0000-00-00' OR `date_end` > NOW()))");

        if ($query->num_rows) {
            return [
                'date_start' => $query->row['date_start'],
                'date_end' => $query->row['date_end']
            ];
        }

        return [];
    }

}

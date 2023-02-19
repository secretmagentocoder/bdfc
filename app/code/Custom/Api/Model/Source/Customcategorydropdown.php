<?php

namespace Custom\Api\Model\Source;

class Customcategorydropdown extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource 
{
    public function getAllOptions() {
        if ($this->_options === null) {
            /*$this->_options = [
                ['label' => __('--Select--'), 'value' => ''],
                ['label' => __('Option 1'), 'value' => 1],
                ['label' => __('Option 2'), 'value' => 2]
            ];*/
            $this->_options = $this->custom_category_options();
        }
        return $this->_options;
    }

    // custom_category_options
    public function custom_category_options()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        $query = $connection->select()->from('web_custom_allowence_category', ['*']);
        $results = $connection->fetchAll($query);

        $custom_category_options = [];
        $custom_category_option = [];
        $custom_category_option ['label']= '--Select--';
        $custom_category_option ['value']= '';
        $custom_category_options []= $custom_category_option;

        if (!empty($results)) {
            foreach ($results as $value) {
                $option_id = $value['id'];
                $option_name = $value['category_code'];

                $custom_category_option = [];
                $custom_category_option ['label']= $option_name;
                $custom_category_option ['value']= $option_id;
                $custom_category_options []= $custom_category_option;
            }
        }

        return $custom_category_options;
    }
}
<?php
/**
 * Mytunes Resource Setup
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Mysql4_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{

    /**
     * setup the entities to install
     *
     * @return array
     */
    public function getDefaultEntities() {
        $defaults = $this->_getDefaultProductAttributeFields();

        return array(
            // entities for products
            'catalog_product' => array(
                'entity_model'      => 'catalog/product',
                'attribute_model'   => 'catalog/resource_eav_attribute',
                'table'             => 'catalog/product',
                'additional_attribute_table'
                                    => 'catalog/eav_attribute',
                'entity_attribute_collection'
                                    => 'catalog/product_attribute_collection',
                'attributes'        => array(
                    // toggle for mytunes player on a product
                    'mytunes_enable_player' => array_merge($defaults, array(
                        'type'     => 'int',
                        'label'    => 'Enable Mytunes Player',
                        'input'    => 'select',
                        'source'   => 'eav/entity_attribute_source_boolean',
                        'default'  => '1',
                    )),
                    // toggle for download option on a product
                    'mytunes_enable_downloads' => array_merge($defaults, array(
                        'type'     => 'int',
                        'label'    => 'Enable Downloads',
                        'input'    => 'select',
                        'source'   => 'eav/entity_attribute_source_boolean',
                        'default'  => '1',
                    )),
                    // toggle complete download of whole album
                    'mytunes_enable_albumdownload' => array_merge($defaults, array(
                        'type'     => 'int',
                        'label'    => 'Enable Download of complete Album',
                        'input'    => 'select',
                        'source'   => 'eav/entity_attribute_source_boolean',
                        'default'  => '1',
                    )),
                    // artist of an album
                    'mytunes_artist' => array_merge($defaults, array(
                        'group'    => 'General',
                        'label'    => 'Artist',
                        'type'     => 'varchar',
                        'input'    => 'text',
                        'default'  => '',
                        'visible'  => 1,
                        'sort_order' => 1,   // show it right on top
                        'user_defined' => 1  // so it can be remove from attribute sets in backend
                    )),
                )
            )
        );
    }

    /**
     * get default attribute settings for a catalog/product entitiy
     *
     * @return array
     */
    private function _getDefaultProductAttributeFields() {
        return array(
            'backend'           => '',
            'frontend'          => '',
            'class'             => '',
            'source'            => '',
            'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'visible'           => 0, // not visible in backend by default (only used in mytunes templates explicitly)
            'required'          => 1,
            'user_defined'      => 0,
            'searchable'        => 0,
            'filterable'        => 0,
            'comparable'        => 0,
            'visible_on_front'  => 0,
            'visible_in_advanced_search' => 0,
            'unique'            => 0,
            'apply_to'          => Que_Mytunes_Model_Product_Type::TYPE_MYTUNES,
            'is_configurable'      => 1
        );
    }

    /**
     * Add attribute to an entity type
     *
     * If attribute is system will add to all existing attribute sets
     *
     * @Override of Mage_Eav_Model_Entity_Setup @v1.4.1.1
     * to be able to add attributes only to specified attribute sets.
     * This is only needed, when Mytunes shall have an own attribute set ...
     *
     * @param string|integer $entityTypeId
     * @param string $code
     * @param array $attr
     * @return Mage_Eav_Model_Entity_Setup
     */
    /*
    public function addAttribute($entityTypeId, $code, array $attr)
    {
        $entityTypeId = $this->getEntityTypeId($entityTypeId);
        $data = array_merge(
            array(
                'entity_type_id' => $entityTypeId,
                'attribute_code' => $code
            ),
            $this->_prepareValues($attr)
         );

        $sortOrder = isset($attr['sort_order']) ? $attr['sort_order'] : null;
        if ($id = $this->getAttribute($entityTypeId, $code, 'attribute_id')) {
            $this->updateAttribute($entityTypeId, $id, $data, null, $sortOrder);
        } else {
            $this->_insertAttribute($data);
        }

        if (!empty($attr['group'])) {
            $sets = $this->_conn->fetchAll('select * from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);
            foreach ($sets as $set) {
                if (!empty($attr['attribute_set'])) {
                    if ($attr['attribute_set'] == $set['attribute_set_name']) {
                        $this->addAttributeGroup($entityTypeId, $set['attribute_set_id'], $attr['group']);
                        $this->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $attr['group'], $code, $sortOrder);
                    }
                } else {
                    $this->addAttributeGroup($entityTypeId, $set['attribute_set_id'], $attr['group']);
                    $this->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $attr['group'], $code, $sortOrder);
                }
            }
        }
        if (empty($attr['user_defined'])) {
            $sets = $this->_conn->fetchAll('select * from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);
            foreach ($sets as $set) {
                if (!empty($attr['attribute_set'])) {
                    if ($attr['attribute_set'] == $set['attribute_set_name']) {
                        $this->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $attr['group'], $code, $sortOrder);
                    }
                } else {
                    $this->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $this->_generalGroupName, $code, $sortOrder);
                }
            }
        }

        if (isset($attr['option']) && is_array($attr['option'])) {
            $option = $attr['option'];
            $option['attribute_id'] = $this->getAttributeId($entityTypeId, $code);
            $this->addAttributeOption($option);
        }

        return $this;
    }
    */

    /**
     * Prepare catalog attribute values to save
     *
     * @Copied from Mage_Catalog_Model_Resource_Eav_Mysql4_Setup @v1.4.1.1
     *
     * @param array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'frontend_input_renderer'   => $this->_getValue($attr, 'input_renderer', ''),
            'source_model'              => $this->_getValue($attr, 'source', ''),
            'is_global'                 => $this->_getValue($attr, 'global', 1),
            'is_visible'                => $this->_getValue($attr, 'visible', 1),
            'is_searchable'             => $this->_getValue($attr, 'searchable', 0),
            'is_filterable'             => $this->_getValue($attr, 'filterable', 0),
            'is_comparable'             => $this->_getValue($attr, 'comparable', 0),
            'is_visible_on_front'       => $this->_getValue($attr, 'visible_on_front', 0),
            'is_wysiwyg_enabled'        => $this->_getValue($attr, 'wysiwyg_enabled', 0),
            'is_html_allowed_on_front'  => $this->_getValue($attr, 'is_html_allowed_on_front', 0),
            'is_visible_in_advanced_search'
                                        => $this->_getValue($attr, 'visible_in_advanced_search', 0),
            'is_used_for_price_rules'   => $this->_getValue($attr, 'used_for_price_rules', 1),
            'is_filterable_in_search'   => $this->_getValue($attr, 'filterable_in_search', 0),
            'used_in_product_listing'   => $this->_getValue($attr, 'used_in_product_listing', 0),
            'used_for_sort_by'          => $this->_getValue($attr, 'used_for_sort_by', 0),
            'apply_to'                  => $this->_getValue($attr, 'apply_to', ''),
            'position'                  => $this->_getValue($attr, 'position', 0),
            'is_configurable'           => $this->_getValue($attr, 'is_configurable', 1)
        ));
        return $data;
    }
}
<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_ShoppingFeeds_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{

    public function upgradeGoogleShoppingFeed()
    {
        $gsfDbVer = $this->_getResource()->getDbVersion('googlebasefeedgenerator_setup');
        $gsfConfigVer = '0.5.7';
        $coreResourceTableName = $this->getTable('core/resource');

        // Module is installed
        if ($gsfDbVer !== false) {
            $status = version_compare($gsfConfigVer, $gsfDbVer);
            if (Mage_Core_Model_Resource_Setup::VERSION_COMPARE_GREATER == $status) {
                // We run the googlebasefeedgenerator_setup files
                $sqlUpgrades = $this->getGsfFiles('sql');
                $dataUpgrades = $this->getGsfFiles('data');

                for ($i = 1; $i <= 5; $i++) {
                    for ($j = 0; $j <= 9; $j++) {
                        $version = sprintf('0.%s.%s', $i, $j);
                        if (version_compare($version, $gsfDbVer, '>')) {
                            $ran = false;
                            if (isset($sqlUpgrades[$version])) {
                                // Run SQL file
                                $file = $sqlUpgrades[$version];
                                include $file;
                                $ran = true;
                            }
                            if (isset($dataUpgrades[$version])) {
                                // Run DATA file
                                $file = $dataUpgrades[$version];
                                include $file;
                                $ran = true;
                            }
                            if ($ran === true) {
                                $this->getConnection()->update(
                                    $coreResourceTableName,
                                    array('version' => $version, 'data_version' => $version),
                                    'code = "googlebasefeedgenerator_setup"'
                                );
                            }
                        }
                    }
                }
            }
            $moduleXml = rtrim(Mage::getBaseDir('etc'), DS) . DS . 'modules' . DS . 'RocketWeb_GoogleBaseFeedGenerator.xml';
            if (file_exists($moduleXml)) {
                // Lets disable the old module
                $content = file_get_contents($moduleXml);
                $content = str_replace('<active>true</active>', '<active>false</active>', $content);
                file_put_contents($moduleXml, $content);
            }

            return true;
        }
        return false;
    }

    protected function getGsfFiles($type = 'sql')
    {
        $path = rtrim(Mage::getModuleDir($type,'RocketWeb_ShoppingFeeds'), DS) . DS . 'googlebasefeedgenerator_setup';
        $directory = new RecursiveDirectoryIterator($path,RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory,RecursiveIteratorIterator::LEAVES_ONLY);

        $paths = array();
        foreach ($iterator as $file) {
            if (strpos($file, 'upgrade') !== false) {
                $parts = explode('-', $file);
                foreach ($parts as $part) {
                    if (isset($part) && strpos($part, '.php')) {
                        $version = str_replace('.php', '', $part);
                        $paths[$version] = $file;
                    }
                }
            }
        }
        return $paths;
    }
    /**
     * @param $map
     * @param $row
     * @param $keys
     * @param $to_replace
     */
    private function updateMapParams(&$map, $row, $keys, $to_replace)
    {
        // load global data
        $global_data = $this->getConnection()
            ->fetchAll("SELECT * from `{$this->getTable('core_config_data')}` WHERE scope = 'default' AND scope_id = '0' AND path IN ('". implode("','", $keys). "')");

        // load store data
        $store_data = $this->getConnection()
            ->fetchAll("SELECT * from `{$this->getTable('core_config_data')}`
                WHERE scope = '{$row['scope']}' AND scope_id = '{$row['scope_id']}' AND path IN ('". implode("','", $keys). "')");

        // replace the map values with store data
        foreach ($store_data as $value) {
            if (count($to_replace) && in_array($value['path'], array_keys($to_replace)) && !is_null($value['value'])) {
                $p = $to_replace[$value['path']];
                $map[$p]['param'] = $value['value'];
                unset($to_replace[$value['path']]);
            }
        }

        // replace the map values not found in store data with global data
        foreach ($global_data as $value) {
            if (count($to_replace) && in_array($value['path'], array_keys($to_replace)) && !is_null($value['value'])) {
                $p = $to_replace[$value['path']];
                $map[$p]['param'] = $value['value'];
                unset($to_replace[$value['path']]);
            }
        }
    }

    /**
     * Updates the serialized map config into DB
     * @param $map_rows
     * @param $keys
     */
    function updateColumnMap($map_rows, $keys)
    {
        // Migrate old config paths to directive parameters, loop through store maps
        foreach ($map_rows as $r => $row) {

            // map of this store
            $map = unserialize($row['value']);
            $to_replace = array();

            foreach ($map as $k => $column) {

                // find the map rows to replace based on path information
                $path = array_key_exists($column['attribute'], $keys) ? $keys[$column['attribute']] : null;
                if (!$path) {
                    $path = array_key_exists($column['column'], $keys) ? $keys[$column['column']] : null;
                }
                if ($path) {
                    $to_replace[$path] = $k;
                }

                // set static_value directive instead of default_values
                if (array_key_exists('default_value', $column) && !empty($column['default_value'])) {
                    $map[$k]['attribute'] = 'rw_gbase_directive_static_value';
                    $map[$k]['param'] = $column['default_value'];
                }
                unset($map[$k]['default_value']);

                // update apparel directive to new unique one
                if (in_array($column['column'], array_keys($keys)) && strpos($column['attribute'], 'apparel') !== false) {
                    $map[$k]['attribute'] = 'rw_gbase_directive_variant_attributes';
                }
            }

            $this->updateMapParams($map, $row, $keys, $to_replace);

            // save the new $map for this store
            $sql = "UPDATE `{$this->getTable('core_config_data')}` SET value = '". serialize($map). "' WHERE config_id = '{$row['config_id']}'";
            $this->run($sql);

            $map_rows[$r]['value'] = serialize($map);
        }

        return $map_rows;
    }

    /**
     * Updates the serialized replace empty config into DB
     * @param $map_rows
     * @param $keys
     */
    function updateReplaceEmptyMap($map_rows, $keys, $add_rules = array())
    {
        foreach ($map_rows as $r => $row) {

            $rules = unserialize($row['value']);
            $to_replace = array();
            $order = 1;
            $k = 0;

            foreach ($rules as $k => $rule) {

                // set rule order values
                if (array_key_exists('rule_order', $rule)) {
                    $order = ($order < (int)$rule['rule_order']) ? (int)$rule['rule_order'] : $order;

                    if (empty($rule['rule_order'])) {
                        $rules[$k]['order'] = ++$order;
                    } else {
                        $rules[$k]['order'] = $rule['rule_order'];
                    }
                }

                // find the map rows to replace based on path information
                $path = array_key_exists($rule['attribute'], $keys) ? $keys[$rule['attribute']] : null;
                if (!$path) {
                    $path = array_key_exists($rule['column'], $keys) ? $keys[$rule['column']] : null;
                }
                if ($path) {
                    $to_replace[$path] = $k;
                }

                // set static_value directive instead of default_values
                if (array_key_exists('static', $rule) && !empty($rule['static'])) {
                    $rules[$k]['attribute'] = 'rw_gbase_directive_static_value';
                    $rules[$k]['param'] = $rule['static'];
                }

                // update apparel directive to new unique one
                if (in_array($rule['column'], array_keys($keys)) && strpos($rule['attribute'], 'apparel') !== false) {
                    $rules[$k]['attribute'] = 'rw_gbase_directive_variant_attributes';
                }

            }

            $this->updateMapParams($rules, $row, $keys, $to_replace);

            foreach ($add_rules as $entry) {
                $rules[$k.++$order] = array_merge(array('order' => $order), $entry);
            }

            // save the new $empty_map for this store
            $sql = "UPDATE `{$this->getTable('core_config_data')}` SET value = '". serialize($rules). "' WHERE config_id = '{$row['config_id']}'";
            $this->run($sql);

            $map_rows[$r]['value'] = serialize($rules);
        }

        return $map_rows;
    }

    /**
     * @param $map_rows
     * @param $replace_data
     */
    public function replaceDirectives($map_rows, $replace_data)
    {
        foreach ($map_rows as $row) {
            // map of this store
            $map = unserialize($row['value']);
            foreach ($map as $k => $column) {
                foreach ($replace_data as $search => $replace) {
                    if ($map[$k]['attribute'] == $search && !$replace) {
                        unset($map[$k]);
                    } elseif ($map[$k]['attribute'] == $search) {
                        $map[$k]['attribute'] = str_replace($search, $replace, $map[$k]['attribute']);
                    }
                }
            }

            // save the new $map for this store
            $sql = "UPDATE `{$this->getTable('core_config_data')}` SET value = '". serialize($map). "' WHERE config_id = '{$row['config_id']}'";
            $this->run($sql);
        }
    }

    /**
     * Updates map rules by rule attribute / directive.
     * EX: $values = array('rw_gbase_directive_product_type_magento_category' => array('param' => 3))
     * would update param = 3 for all maps having specified directive
     *
     * @param $values
     */
    public function updateMapByAttribute($values)
    {
        $map_rows = $this->getConnection()->fetchAll("SELECT * from `{$this->getTable('core_config_data')}` WHERE path IN ('rocketweb_shoppingfeeds/columns/map_product_columns', 'rocketweb_shoppingfeeds/filters/map_replace_empty_columns')");
        foreach ($map_rows as $k => $row) {
            $map = unserialize($row['value']);

            foreach ($map as $k => $rule) {
                foreach ($values as $attribute => $data) {
                    if ($rule['attribute'] == $attribute) {
                        foreach ($data as $key => $value) {
                            $map[$k][$key] = $value;
                        }
                    }
                }
            }

            // save the new $map for this store
            $sql = "UPDATE `{$this->getTable('core_config_data')}` SET value = '". serialize($map). "' WHERE config_id = '{$row['config_id']}'";
            $this->run($sql);
        }
    }
}
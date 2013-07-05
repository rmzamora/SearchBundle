<?php

/*
 * This file is part of the RzSearchBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\SearchBundle\Model;

use Rz\CkeditorBundle\Exception\ConfigManagerException;
use Doctrine\ORM\PersistentCollection;

class ConfigManager implements ConfigManagerInterface
{
    /** @var array */
    protected $configs;
    protected $options;

    /**
     * Creates a CKEditor config manager.
     *
     * @param array $configs The CKEditor configs.
     */
    public function __construct(array $configs = array())
    {
        $this->setConfigs($configs);
    }

    /**
     * {@inheritdoc}
     */
    public function hasConfigs()
    {
        return !empty($this->configs);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigs(array $configs)
    {
        foreach ($configs as $name => $config) {
            $this->setConfig($name, $config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasConfig($name)
    {
        return isset($this->configs[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($name)
    {
        if (!$this->hasConfig($name)) {
            throw ConfigManagerException::configDoesNotExist($name);
        }

        return $this->configs[$name];
    }

    /**
 * {@inheritdoc}
 */
    public function setConfig($name, array $config)
    {
        $this->configs[$name] = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldMap($model_id, $field)
    {
      return isset($this->configs[$model_id]['field_mapping'][$field]) ? $this->configs[$model_id]['field_mapping'][$field] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldValue($model_id, $entity, $field)
    {
        $getter = 'get'.ucfirst($this->getFieldMap($model_id, $field));

        if (!method_exists($entity, $getter)) {
            throw new \RuntimeException(sprintf("Class '%s' should have a method '%s'.", get_class($entity), $getter));
        }

        $value = $entity->$getter();

        if ($value instanceof PersistentCollection) {
            $temp = null;
            foreach ($value as $child) {
                $temp[] =  $child->__toString();
            }
            return $temp;
        } elseif(is_object($value) && !($value instanceof \DateTime)) {
            return $value->__toString();
        } else {
            return $value;
        }
    }

    protected function getter($field)
    {
        return 'get'.ucfirst($field);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexFields($model_id)
    {
        $indexFields =  null;
        foreach ($this->getConfig($model_id)['field_mapping'] as $index => $fields) {
            $indexFields[] = $index;
        }

        return $indexFields;
    }
}
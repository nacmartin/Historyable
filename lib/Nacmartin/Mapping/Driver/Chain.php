<?php

namespace Nacmartin\Mapping\Driver;

use Nacmartin\Mapping\Driver,
    Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * The chain mapping driver enables chained
 * extension mapping driver support
 * 
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Nacho Martin <nitram.ohcan@gmail.com>
 * @package Nacmartin.Mapping.Driver
 * @subpackage Chain
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Chain implements Driver
{
    /**
     * List of drivers nested
     * @var array
     */
    private $_drivers = array();
    
    /**
     * Add a nested driver.
     *
     * @param Driver $nestedDriver
     * @param string $namespace
     */
    public function addDriver(Driver $nestedDriver, $namespace)
    {
        $this->_drivers[$namespace] = $nestedDriver;
    }
    
    /**
     * Get the array of nested drivers.
     *
     * @return array $drivers
     */
    public function getDrivers()
    {
        return $this->_drivers;
    }
    
    /**
     * {@inheritDoc}
     */
    public function validateFullMetadata(ClassMetadata $meta, array $config) {}
    
    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata(ClassMetadata $meta, array &$config)
    {
        foreach ($this->_drivers as $namespace => $driver) {
            if (strpos($meta->name, $namespace) === 0) {
                $driver->readExtendedMetadata($meta, $config);
                return;
            }
        }
        throw new \Nacmartin\Exception\UnexpectedValueException('Class ' . $meta->name . ' is not a valid entity or mapped super class.');
    }

    /**
     * Passes in the mapping read by original driver
     *
     * @param $driver
     * @return void
     */
    public function setOriginalDriver($driver)
    {
        //not needed here
    }
}

<?php

namespace Nacmartin\Historyable;

/**
 * This interface is not necessary but can be implemented for
 * Domain Objects which in some cases needs to be identified as
 * Historyable
 * 
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Nacho Martin <nitram.ohcan@gmail.com>
 * @package Nacmartin.Historyable
 * @subpackage Historyable
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface Historyable
{
    // this interface is not necessary to implement
    /**
     * @Nacmartin\Historyable
     * to mark the class as historyable use class annotation @Nacmartin\Historyable
     * this object will contain now a history
     * example:
     * 
     * @Nacmartin\Historyable
     * class MyEntity
     */
}


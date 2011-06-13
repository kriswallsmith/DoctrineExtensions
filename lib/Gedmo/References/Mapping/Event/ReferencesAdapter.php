<?php

namespace Gedmo\References\Mapping\Event;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Mapping\Event\AdapterInterface;

/**
 * Doctrine event adapter interface
 * for References behavior
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 * @package Gedmo\References\Mapping\Event
 * @subpackage ReferencesAdapter
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface ReferencesAdapter extends AdapterInterface
{
    function getIdentifier($om, $object, $single = true);
    function getSingleReference($om, $class, $identifier);
}
<?php

namespace Gedmo\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Loggable annotation for Loggable behavioral extension
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Mapping.Annotation
 * @subpackage Loggable
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @Annotation
 */
final class Loggable extends Annotation
{
    public $logEntryClass;
}


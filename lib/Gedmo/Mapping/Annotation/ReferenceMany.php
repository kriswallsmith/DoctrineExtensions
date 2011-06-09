<?php

namespace Gedmo\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Reference annotation for ORM -> ODM references extension
 * to be user like @ReferenceOne(type="entity", class="MyEntity", identifier="entity_id")
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 * @package Gedmo.Mapping.Annotation
 * @subpackage Language
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class ReferenceMany extends Reference
{
}


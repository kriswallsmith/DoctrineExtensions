<?php

namespace Gedmo\References\Mapping\Driver;

use Gedmo\Mapping\Driver\AnnotationDriverInterface,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Exception\InvalidMappingException;

/**
 * This is an annotation mapping driver for References
 * behavioral extension.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 * @package Gedmo.References.Mapping.Driver
 * @subpackage Annotation
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Annotation implements AnnotationDriverInterface
{
    /**
     * Annotation to mark field as reference to one
     */
    const REFERENCE_ONE = 'Gedmo\\Mapping\\Annotation\\ReferenceOne';

    /**
     * Annotation to mark field as reference to many
     */
    const REFERENCE_MANY = 'Gedmo\\Mapping\\Annotation\\ReferenceMany';

    private $annotations = array(
        'referenceOne'  => self::REFERENCE_ONE,
        'referenceMany' => self::REFERENCE_MANY,
    );

    /**
     * Annotation reader instance
     *
     * @var object
     */
    private $reader;

    /**
     * original driver if it is available
     */
    protected $_originalDriver = null;

    /**
     * {@inheritDoc}
     */
    public function setAnnotationReader($reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritDoc}
     */
    public function validateFullMetadata(ClassMetadata $meta, array $config)
    {
//        if ($config && !isset($config['fields'])) {
//            throw new InvalidMappingException("Unable to find any sluggable fields specified for Sluggable entity - {$meta->name}");
//        }
    }

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata(ClassMetadata $meta, array &$config) {
        $class = $meta->getReflectionClass();
        // property annotations
        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                $meta->isInheritedField($property->name) ||
                isset($meta->associationMappings[$property->name]['inherited'])
            ) {
                continue;
            }
            foreach($this->annotations as $key => $annotation) {
                if ($reference = $this->reader->getPropertyAnnotation($property, $annotation)) {
                    $config[$key][] = array(
                        'field'      => $property->getName(),
                        'type'       => $reference->type,
                        'class'      => $reference->class,
                        'identifier' => $reference->identifier,
                        'mappedBy'   => $reference->mappedBy,
                        'indersedBy' => $reference->inversedBy,
                    );
                }
            }
        }
    }

    /**
     * Checks if $field type is valid as Sluggable field
     *
     * @param ClassMetadata $meta
     * @param string $field
     * @return boolean
     */
    protected function isValidField(ClassMetadata $meta, $field)
    {
        $mapping = $meta->getFieldMapping($field);
        return $mapping && in_array($mapping['type'], $this->validTypes);
    }

    /**
     * Passes in the mapping read by original driver
     *
     * @param $driver
     * @return void
     */
    public function setOriginalDriver($driver)
    {
        $this->_originalDriver = $driver;
    }
}
<?php

namespace SmartHome\Common\Abstracts;

use ReflectionClass;
use ReflectionProperty;
use Exception;
use Doctrine\Common\Collections\Selectable;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class Entity {

    abstract public function getCollection (string $entityName): Selectable;

    abstract public function setRelation(string $entityName, ?Entity $value);

    protected function checkEntityName ($entityName): self {
        if (!is_subclass_of($entityName, Entity::class)) {
            throw new Exception('Given entity '.$entityName.' is not a subclass of '.Entity::class);
        }

        return $this;
    }

    /**
     * It finds relation defined by "mappedBy" in doc.
     *
     * @param string $target Target entity class name
     * @param string $match Match string 'mappedBy', 'inversedBy'
     *
     * @return string|null
     */
    public static function findMappedBy (string $target, string $match): ?string {
        if (!is_subclass_of($target, Entity::class)) {
            throw new Exception('Given entity '.$target.' is not a subclass of '.Entity::class);
        }

        $refl = new ReflectionClass(static::class);
        $properties = $refl->getProperties();

        $result = null;
        foreach ($properties as $property) { /* @var $property ReflectionProperty */
            $doc = $property->getDocComment();

            if (strpos($doc, substr(strrchr($target, '\\'), 1)) && strpos($doc, $match)) {
                $matches = [];
                preg_match('/'.$match.'="(.*)"/', $doc, $matches);

                if (!empty($matches)) {
                    $result = $matches[1];
                    break;
                }
            }
        }

        return $result;
    }

}

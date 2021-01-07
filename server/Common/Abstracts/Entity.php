<?php

namespace SmartHome\Common\Abstracts;

use ReflectionClass;
use ReflectionProperty;
use Exception;
use Doctrine\Common\Collections\Selectable;

/**
 * This file defines abstract class for Entity.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class Entity {

    /**
     * Gets collection by entity name
     *
     * @param string $entityName Entity name
     *
     * @return Selectable
     */
    abstract public function getCollection(string $entityName): Selectable;

    /**
     * Sets relation entity
     *
     * @param string      $entityName Entity name
     * @param Entity|null $value      Entity
     *
     * @return void
     */
    abstract public function setRelation(string $entityName, ?Entity $value);

    /**
     * It finds relation defined by "$match" in doc.
     *
     * @param string $target Target entity class name
     * @param string $match  Match string 'mappedBy', 'inversedBy'
     *
     * @return string|null
     *
     * @throws Exception
     */
    public static function findMappedBy(string $target, string $match): ?string {
        if (!is_subclass_of($target, self::class)) {
            throw new Exception('Given entity '.$target.' is not a subclass of '.self::class);
        }

        $refl       = new ReflectionClass(static::class);
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

    /**
     * It checks that given entityName is subclass of basic Entity
     *
     * @param string $entityName Entity name
     *
     * @return self
     *
     * @throws Exception
     */
    protected function checkEntityName($entityName): self {
        if (!is_subclass_of($entityName, self::class)) {
            throw new Exception('Given entity '.$entityName.' is not a subclass of '.self::class);
        }

        return $this;
    }

}

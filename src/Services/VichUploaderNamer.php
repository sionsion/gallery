<?php
namespace App\Services;

use Behat\Transliterator\Transliterator;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class VichUploaderNamer implements NamerInterface
{

    /**
     * {@inheritdoc}
     */
    public function name($object, PropertyMapping $mapping): string
    {
        switch (true) {
            case $object instanceof MyCustomEntity:
                // Based on a specific class
                $name = Transliterator::transliterate($object->getFirstname().'-'.$object->getLastname().'-'.$object->getEmail());
                break;

            case method_exists($object, 'getSlug'):
                // Based on slug
                $name = $object->getSlug();
                break;

            case method_exists($object, '__toString'):
                // Based on stringification of the entity
                $name = (string) $object;
                break;

            default:
                // Or based on an id
                $name = $object->getId();
        }
        $filepath = implode("/", [
            $mapping->getUriPrefix(),
            strtotime("now").'-'.uniqid().'.'.$mapping->getFile($object)->getClientOriginalExtension()
        ]);
        return $filepath;
    }
}
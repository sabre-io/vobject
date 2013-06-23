<?php

namespace Sabre\VObject;

class VCardConverter {

    /**
     * Converts a vCard object to a new version.
     *
     * targetVersion must be one of:
     *   Document::VCARD21
     *   Document::VCARD30
     *   Document::VCARD40
     *
     * Currently only 3.0 and 4.0 as an input version, and 4.0 as an
     * outputversion are supported.
     */
    public function convert(Component\VCard $input, $targetVersion) {

        if ($targetVersion !== Document::VCARD40) {
            throw new \InvalidArgumentException('Currently you can convert only to vCard 4');
        }
        switch($input->getDocumentType()) {
            case Document::VCARD30 :
                // supported
                break;
            case Document::VCARD40 :
                // Nothing to do here
                return clone $input;
            default :
                throw new \InvalidArgumentException('Currently you can only convert from vCard 3');
                break;
        }

        $output = new Component\VCard(array(
            'VERSION' => '4.0',
        ));

        foreach($input->children as $property) {

            $this->convertProperty($input, $output, $property);

        }

        return $output;

    }

    /**
     * Handles conversion of a single property.
     *
     * @param Component\VCard $input
     * @param Component\VCard $output
     * @param Property $property
     * @return void
     */
    protected function convertProperty(Component\VCard $input, Component\VCard $output, Property $property) {

        // Skipping these, those are automatically added.
        if (in_array($property->name, array('VERSION', 'PRODID'))) {
            return;
        }

        $parameters = $property->parameters();

        $valueType = null;
        if (isset($parameters['VALUE'])) {
            $valueType = $parameters['VALUE']->getValue();
            unset($parameters['VALUE']);
        }

        // Binary does not exist anymore in vCard 4. We need to convert it to a
        // uri.
        if ($property instanceof Property\Binary) {

            $newProperty = $output->createProperty(
                $property->name,
                null, // no value
                array(), // no parameters yet
                'URI' // Forcing the URI type
            );

            $mimeType = 'application/octet-stream';

            // See if we can find a better mimetype.
            if (isset($parameters['TYPE'])) {

                $newTypes = array();
                foreach($parameters['TYPE']->getParts() as $typePart) {
                    if (in_array(
                        strtoupper($typePart),
                        array('JPEG','PNG','GIF')
                    )) {
                        $mimeType = 'image/' . strtolower($typePart);
                    } else {
                        $newTypes[] = $typePart;
                    }
                }

                // If there were any parameters we're not converting to a
                // mime-type, we need to keep them.
                if ($newTypes) {
                    $parameters['TYPE']->setParts($newTypes);
                } else {
                    unset($parameters['TYPE']);
                }

            }

            $newProperty->setValue('data:' . $mimeType . ';base64,' . base64_encode($property->getValue()));

        } else {

            $newProperty = $output->createProperty(
                $property->name,
                $property->getParts(),
                array(), // no parameters yet
                $valueType
            );

        }

        // Adding all parameters.
        foreach($parameters as $param) {

            switch($param->name) {

                // We need to see if there's any TYPE=PREF, because in vCard 4
                // that's now PREF=1.
                case 'TYPE' :
                    foreach($param->getParts() as $paramPart) {

                        if (strtoupper($paramPart)==='PREF') {
                            $newProperty->add('PREF','1');
                        } else {
                            $newProperty->add($param->name, $paramPart);
                        }

                    }
                    break;
                // These no longer exist in vCard 4
                case 'ENCODING' :
                case 'CHARSET' :
                    break;
                default :
                    $newProperty->add($param->name, $param->getParts());
                    break;

            }


        }

        // Lastly, we need to see if there's a need for a VALUE parameter.
        if (get_class($newProperty)!==$output->getClassNameForPropertyName($newProperty->name)) {

            $newProperty->add('VALUE', $newProperty->getValueType());

        }

        $output->add($newProperty);


    }

}

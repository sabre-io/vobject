<?php

namespace Sabre\VObject\Component;

use Sabre\VObject;

/**
 * The VCard component
 *
 * This component represents the BEGIN:VCARD and END:VCARD found in every
 * vcard.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class VCard extends VObject\Document {

    /**
     * The default name for this component.
     *
     * This should be 'VCALENDAR' or 'VCARD'.
     *
     * @var string
     */
    static $defaultName = 'VCARD';

    /**
     * Caching the version number
     *
     * @var int
     */
    private $version = null;

    /**
     * Returns the current document type.
     *
     * @return void
     */
    public function getDocumentType() {

        if (!$this->version) {
            $version = $this->select('VERSION');
            switch($version->getValue()) {
                case '2.1' :
                    $this->version = self::VCARD21;
                    break;
                case '3.0' :
                    $this->version = self::VCARD30;
                    break;
                case '4.0' :
                    $this->version = self::VCARD40;
                    break;
                default :
                    $this->version = self::UNKNOWN;
                    break;

            }
        }

        return $this->version;

    }

    /**
     * VCards with version 2.1, 3.0 and 4.0 are found.
     *
     * If the VCARD doesn't know its version, 4.0 is assumed.
     */
    const DEFAULT_VERSION = self::VCARD40;

    /**
     * Validates the node for correctness.
     *
     * The following options are supported:
     *   - Node::REPAIR - If something is broken, and automatic repair may
     *                    be attempted.
     *
     * An array is returned with warnings.
     *
     * Every item in the array has the following properties:
     *    * level - (number between 1 and 3 with severity information)
     *    * message - (human readable message)
     *    * node - (reference to the offending node)
     *
     * @param int $options
     * @return array
     */
    public function validate($options = 0) {

        $warnings = array();

        $version = $this->select('VERSION');
        if (count($version)!==1) {
            $warnings[] = array(
                'level' => 1,
                'message' => 'The VERSION property must appear in the VCARD component exactly 1 time',
                'node' => $this,
            );
            if ($options & self::REPAIR) {
                $this->VERSION = self::DEFAULT_VERSION;
            }
        } else {
            $version = (string)$this->VERSION;
            if ($version!=='2.1' && $version!=='3.0' && $version!=='4.0') {
                $warnings[] = array(
                    'level' => 1,
                    'message' => 'Only vcard version 4.0 (RFC6350), version 3.0 (RFC2426) or version 2.1 (icm-vcard-2.1) are supported.',
                    'node' => $this,
                );
                if ($options & self::REPAIR) {
                    $this->VERSION = '4.0';
                }
            }

        }
        $fn = $this->select('FN');
        if (count($fn)!==1) {
            $warnings[] = array(
                'level' => 1,
                'message' => 'The FN property must appear in the VCARD component exactly 1 time',
                'node' => $this,
            );
            if (($options & self::REPAIR) && count($fn) === 0) {
                // We're going to try to see if we can use the contents of the
                // N property.
                if (isset($this->N)) {
                    $value = explode(';', (string)$this->N);
                    if (isset($value[1]) && $value[1]) {
                        $this->FN = $value[1] . ' ' . $value[0];
                    } else {
                        $this->FN = $value[0];
                    }

                // Otherwise, the ORG property may work
                } elseif (isset($this->ORG)) {
                    $this->FN = (string)$this->ORG;
                }

            }
        }

        return array_merge(
            parent::validate($options),
            $warnings
        );

    }

}


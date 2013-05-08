<?php

namespace Sabre\VObject;

/**
 * Document
 *
 * A document is just like a component, except that it's also the top level
 * element.
 *
 * Both a VCALENDAR and a VCARD are considered documents.
 *
 * This class also provides a registry for document types.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
abstract class Document extends Component {

    const UNKNOWN = 0;
    const VCALENDAR10 = 1;
    const ICALENDAR20 = 2;
    const VCARD21 = 3;
    const VCARD30 = 4;
    const VCARD40 = 5;

    /**
     * Returns the current document type.
     *
     * @return void
     */
    public function getDocumentType() {

        return self::UNKNOWN;

    }

}

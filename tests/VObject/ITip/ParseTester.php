<?php

namespace Sabre\VObject\ITip;

/**
 * Tests 'parse' related functionality.
 *
 * This class provides some convenience functions to make this easier.
 * 
 * @copyright Copyright (C) 2007-2014 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/) 
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class ParseTester extends \PHPUnit_Framework_TestCase {

    function parse($oldMessage, $newMessage, $expected = array(), $currentUser = 'mailto:one@example.org') {

        $broker = new Broker();
        $result = $broker->parseEvent($newMessage, $currentUser, $oldMessage);

        $this->assertEquals(count($expected), count($result));

        foreach($expected as $index=>$ex) {

            $message = $result[$index];

            foreach($ex as $key=>$val) {

                if ($key==='message') {
                    $this->assertEquals(
                        str_replace("\n", "\r\n", $val),
                        rtrim($message->message->serialize(), "\r\n")
                    );
                } else {
                    $this->assertEquals($val, $message->$key);
                }

            }

        }

    }
}

# SabreTooth VObject library

[![Build Status](https://secure.travis-ci.org/evert/sabre-vobject.png?branch=master)](http://travis-ci.org/evert/sabre-vobject)

The VObject library allows you to easily parse and manipulate iCalendar and vCard objects using PHP.
The goal of the VObject library is to create a very complete library, with an easy to use API.

# Installation

VObject requires PHP 5.3, and should be installed using composer.
The general composer instructions can be found on the [[http://getcomposer.org/doc/00-intro.md composer website]].

After that, just declare the vobject dependency as follows:

```
"require" : {
    "sabre/vobject" : "2.0.*"
}
```

Then, run `composer.phar update` and you should be good.

# Usage

## Parsing

For our example, we will be using the following vcard:

```
BEGIN:VCARD
VERSION:3.0
PRODID:-//Sabre//Sabre VObject 2.0//EN
N:Planck;Max;;;
FN:Max Planck
EMAIL;TYPE=WORK:mplanck@example.org
item1.TEL;TYPE=CELL:(+49)3144435678
item1.X-ABLabel:Private cell
item2.TEL;TYPE=WORK:(+49)5554564744
item2.X-ABLabel:Work
END:VCARD
```


If we want to just print out Max' full name, you can just use property access:


```php

use Sabre\VObject;

$card = VObject\Reader::read($data);
echo $card->FN;

```

## Changing properties

Creating properties is pretty similar. If we like to add his birthday, we just
set the property:

```php

$card->BDAY = '1858-04-23';

```

Note that in the previous example, we're actually updating any existing BDAY that
may already exist. If we want to add a new property, without overwriting the previous
we can do this with the `add` method. 

```php

$card->add('EMAIL','max@example.org');

```

## Parameters

If we want to also specify that this is max' home email addresses, we can do this with
a third parameter:

```

$card->add('EMAIL', 'max@example'org', array('type' => 'HOME'));

```

If we want to read out all the email addresses and their type, this would look something
like this:

```
foreach($card->EMAIL as $email) {

    echo $email['TYPE'], ' - ', $email;

}
```



Design for VObject 3.0
======================

There are some fundamental issues with the current VObject library. The
purpose of this document is to describe them, and gather all the data
needed to make sure that this will not be an issue in VObject 3.0.

Most of the issues revolve around escaping, and mapping of properties to
classes.

Relevant documents
------------------

* http://tools.ietf.org/html/rfc5545 - iCalendar 2.0
* http://tools.ietf.org/html/rfc2445 - The old iCalendar 2.0 spec.
* http://tools.ietf.org/html/rfc6350 - vCard 4.0
* http://tools.ietf.org/html/rfc2425 - Mime-dir (syntax for vCard 3.0)
* http://tools.ietf.org/html/rfc2426 - vCard 3.0
* http://www.imc.org/pdi/pdiproddev.html - Old school vCalendar 1.0 and vCard 2.1

We care about pretty much all of them, with the exception of vCalendar 1.0.
This particular format is at this point so outdated, it's pretty much irrelevant.

We may add support for that one later though.

We also don't really care about the old iCalendar 2.0 spec. There are some
differences, but they are not relevant to us at the moment.

### Some specs we care about later down the road

* http://tools.ietf.org/html/rfc6321 - xCal
* http://tools.ietf.org/html/rfc6351 - xCard
* http://tools.ietf.org/html/draft-kewisch-et-al-icalendar-in-json-00 - jCal
* And.. jCard.. it will happen, no RFC drafts yet though :)

Escaping
--------

In a vCard and iCalendar object, escaping of values must be done on the
following characters:

* semicolon - `\;`
* newline - `\n` or `\N`
* comma - `\,`
* slash - `\\`

The only spec that's totally unclear about this is vCard 2.1. It only really
mentions escaping of property parameters. And for those, it only mentions
escaping of the semi-colon.

So even for 2.1 vcards we're going to assume that list. In practice it seems
that vCard 2.1 producers follow those rules for the most part.

Bonus
-----

The brand new (February 2013) RFC6868 also uses `^` as an escape character.
This is a BC break for both vCard and iCalendar, but we will implement it too
in VObject 3.0.

http://tools.ietf.org/html/rfc6868

Delimiters
----------

The reason `;` and `,` are escaped, is because properties may have multiple values,
and when they do.. they are separated by either `;` or `,`.

Un-escaping in VObject 2.1
--------------------------

Unescaping in vobject 2.1 is done rather naively. It really only escapes
newlines and slashes when reading property values, and it does so in the
Reader class.

The original assumption was that values with multiple values (N, EXDATE,
etc) would do so internally, but this is highly problematic.

We don't know anymore in those property classes if something was meant as a
real delimiter, or as an escaped delimiter, if `\;` was encoded, because it
could have meant `\\;` before, or it could have meant `\;`.

Furthermore, when serializing again, we only serialize newlines and slashes
again. The problem there, is if a genuine `\;` showed up in the property
value, we will all of a sudden encode it as `\\;`.

**In conclusion**: there is absolutely no way right now to decode a
multi-valued property, and encode it again.. if one of the values contained
a literal `;`.

Property constructors
---------------------

The other issue in this whole ordeal, is that property constructors receive
a half-assed, partially unescaped string. This may in practice work OK for the
vCard and iCalendar specs, but will become problematic when we're implementing
xCal, xCard, jCal and jCard.

### VObject 3.0 approach

Every property-class will get a static deserialize() method, responsible for
doing all deserializing.

The constructor for every property-class will receive a value that's logical
for PHP, and allow for total serialization and deserialization.

This means that for single-value properties, it will be a string, and for
multiple-value properties it will be an array.

Since every property class knows to fully serialize and deserialize itself,
they should have full flexibility and no data-loss.

Then, when we want to add (x|j card|calendar) support, each format will get
its own serializer and static deserializer method.

The `VALUE=` argument
---------------------

Another flaw in the system, is that currently specific property classes are
mapped based on their property name. For instance, a `DTSTART` property is
mapped to a `DateTime` class, and the `N` is mapped to a `Compound` class.

The way this was actually designed, is that every property has a value-type.

The value-type is specified by a `VALUE` parameter. For instance, if a
`DTSTART` property has `VALUE=DATE`, it means that the value-type is a DATE.

Many properties can specify how they are encoded with `VALUE`. An `ATTACH`
property may have `VALUE=BINARY`m or `VALUE=URI`.

Every property class should really be mapped based on this value-type.
Also, every property has a default value-type. That means that if no `VALUE`
parameter is supplied, it defaults to some value.

For example, the default value-type for `DTSTART` is not `DATE`, but it's
`DATE-TIME`.

### In VObject 3.0

Every property is mapped based on the content of the `VALUE` parameter.
If no `VALUE` parameter is supplied, there will be a default list of
parameters that are used instead.

### Multiple values

The problem with that approach, is that many properties have a single value,
but some have multiple values.

`DTSTART`, `DUE`, `BDAY` and many others contain a single `DATE-TIME` or
`DATE`, but `EXDATE` can contain multiple `DATE-TIME` or `DATE` values.

Some of these are split based on `;`, others with `,`. I keep getting stuck
with this issue. At the moment there's a `MultipleDateTime` class, and a `Date`
class. For standard text there's `Property` and `Compound` for multiple
properties.

I don't want to have to duplicate every property class for the ones that may
support multiple. So instead, I made the decision that the best course of
action is to allow every property to have 1 or more values.

### Two Issues

This should cover almost any situation, but then there's the issue of vCard
and iCalendar producers that don't escape `;` and `,` in single-value
properties.

So we need to create exceptions for that.

One other exception is `DTEND`. By default `DTEND` is encoded a `DATE-TIME`
value, but if `DTSTART` has `VALUE=DATE`, `DTEND` must also be a date, and it's
not required to actually specify this.

So even with this basic model, that's in itself a robust way to parse this
format, we will need a whole bunch of exceptions to the rules.

### iCalendar and vCard differences

In iCalendar multiple values are always separated with `;`, but in vCards they
are sometimes `;` and sometimes `,`. So we must also somehow special-case
and deal with these differences.

### Conclusion

When these modifications are implemented, I believe we should be able to handle
any future oddity in the realm of vCard and iCalendar.

I really want to push this library to the point where it's the one and only
choice for PHP to deal with these formats, and really these two major issues
(value-based encoding and escaping) are the things that stand it's way.

After those have been tackled, I believe there will be no other option for
PHP developers to parse and generate these formats.

Generally I believe that these formats could have been great and re-usable for
other applications. The concepts of grouping, the way components are nested
are actually not bad.

But a lack of properly defining how things should be done, the fact that not
every single value can be encoded (see the `bonus` chapter), and the fact that
almost every single design concept (`VALUE=`) has it's exceptions, makes me
believe that this format is a dead end, and there should never be another
`vAnything` or `iAnything`.

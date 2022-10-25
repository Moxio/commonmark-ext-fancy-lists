![CI](https://github.com/Moxio/commonmark-ext-fancy-lists/workflows/CI/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/moxio/commonmark-ext-fancy-lists/v/stable)](https://packagist.org/packages/moxio/commonmark-ext-fancy-lists)
[![Buy us a tree](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen)](https://plant.treeware.earth/Moxio/commonmark-ext-fancy-lists)

moxio/commonmark-ext-fancy-lists
================================
Extension for the [`league/commonmark`](https://github.com/thephpleague/commonmark)
Markdown parser to support additional numbering types for ordered lists.

Uses unofficial markdown syntax based on the syntax supported by
[Pandoc](https://pandoc.org/MANUAL.html#extension-fancy_lists). See the
section [Syntax](#syntax) below for details.

The parser is a modified version of the original [`ListParser`](https://github.com/thephpleague/commonmark/blob/1.5/src/Block/Parser/ListParser.php)
from [`league/commonmark`](https://github.com/thephpleague/commonmark)
by [Colin O'Dell](https://github.com/colinodell), which is licensed
under the BSD-3-Clause License. It is in turn based on the
[CommonMark JS reference implementation](https://github.com/jgm/commonmark.js)
by [John MacFarlane](https://github.com/jgm).


Requirements
------------
This library requires PHP version 7.4 or higher and a `1.x` release of
`league/commonmark`.

Installation
------------
Install as a dependency using composer:
```
$ composer require --dev moxio/commonmark-ext-fancy-lists
```

Usage
-----
Add `FancyListsExtension` as an extension to your CommonMark environment
instance and you're good to go:
```php
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Moxio\CommonMark\Extension\FancyLists\FancyListsExtension;

$environment = Environment::createCommonMarkEnvironment();
$environment->addExtension(new FancyListsExtension());

// Use $environment when building your CommonMarkConverter
$converter = new CommonMarkConverter([], $environment);
echo $converter->convertToHtml('
a) foo
b) bar
c) baz
');
```
See the [CommonMark documentation](https://commonmark.thephpleague.com/1.5/extensions/overview/#usage)
for more information about using extensions.

Syntax
------
The supported markdown syntax is based on the one used by
[Pandoc](https://pandoc.org/MANUAL.html#extension-fancy_lists).

A simple example:
```markdown
i. foo
ii. bar
iii. baz
```
The will yield HTML output like:
```html
<ol type="i">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
```

A more complex example:
```markdown
c. charlie
#. delta
   iv) subfour
   #) subfive
   #) subsix
#. echo
```

A short description of the syntactical rules:

* Apart from numbers, also letters (uppercase or lowercase) and
  Roman numerals (uppercase or lowercase) can be used to number
  ordered list items. Like lists marked with numbers, they need to
  be followed by a single right-parenthesis or period.
* Changing list marker types (also between uppercase and lowercase,
  or the symbol after the 'number') starts a new list.
* The numeral of the first item determines the numbering of the list.
  If the first item is numbered "b", the next item will be numbered
  "c", even if it is marked "z" in the source. This corresponds to
  the normal `league/commonmark` behavior for numeric lists, and
  essentially also implements [Pandoc's `startnum` extension](https://pandoc.org/MANUAL.html#extension-fancy_lists).
* If the first list item is numbered "I" or "i", the list is considered
  to be numbered using Roman numerals, starting at 1. If the list
  starts with another single letter that could be interpreted as a
  Roman numeral, the list is numbered using letters: a first item
  marked with "C." uses uppercase letters starting at 3, not Roman
  numerals starting a 100.
* In subsequent list items, such symbols can be used without any
  ambiguity: in "B.", "C.", "D." the "C" is the letter "C"; in
  "IC.", "C.", "CI." the "C" is a Roman 100.
* A "#" may be used in place of any numeral to continue a list. If
  the first item in a list is marked with "#", that list is numbered
  "1", "2", "3", etc.
* A list marker consisting of a single uppercase letter followed by
  a period (including Roman numerals like "I." or "V.") needs to be
  followed by at least two spaces ([rationale](https://pandoc.org/MANUAL.html#fn1)).

All of the above are entirely compatible with how Pandoc works. There
are two small differences with Pandoc's syntax:

* This plugin does not support list numbers enclosed in parentheses,
  as the Commonmark spec does not support these either for lists
  numbered with Arabic numerals.
* Pandoc does not allow any list to interrupt a paragraph. In the
  spirit of the Commonmark spec (which allows only lists starting
  with 1 to interrupt a paragraph), this plugins allows lists that
  start with "A", "a", "I" or "i" (i.e. all 'first numerals') to
  interrupt a paragraph. The same holds for the "#" generic numbered
  list item marker.

Configuration
-------------
Supported configuration options:

* `allow_ordinal` - Whether to allow an [ordinal indicator](https://en.wikipedia.org/wiki/Ordinal_indicator)
  (`º`) after the numeral, as occurs in e.g. legal documents (default: `false`). If this option is enabled,
  input like
  ```markdown
  1º. foo
  2º. bar
  3º. baz
  ```
  will be converted to
  ```html
  <ol class="ordinal">
    <li>foo</li>
    <li>bar</li>
    <li>baz</li>
  </ol>
  ```
  You will need [custom CSS](https://codepen.io/MoxioHD/pen/GRrjpRb) to re-insert the ordinal indicator
  into the displayed output based on the `ordinal` class.

  Because the ordinal indicator is commonly confused with other characters like the degree symbol, these
  characters are tolerated and considered equivalent to the ordinal indicator.
* `allow_multi_letter` - Whether to allow multi-letter alphabetic numerals, to number lists beyond 26
  (default: `false`). If this option is enabled,
  input like
  ```markdown
  AA. foo
  AB. bar
  AC. baz
  ```
  will be converted to
  ```html
  <ol type="A" start="27">
    <li>foo</li>
    <li>bar</li>
    <li>baz</li>
  </ol>
  ```
  Multi-letter alphabetic numerals can consist of at most 3 characters, which should be enough for a
  typical list. When a list starts with a numeral that can be both Roman or multi-letter alphabetic,
  like "II", it is considered to be Roman.

Versioning
----------
This project adheres to [Semantic Versioning](http://semver.org/).

Contributing
------------
Contributions to this project are more than welcome. When reporting an issue,
please include the input to reproduce the issue, along with the expected
output. When submitting a PR, please include tests with your changes.

License
-------
This project is released under the MIT license.

Treeware
--------
This package is [Treeware](https://treeware.earth/). If you use it in production,
then we'd appreciate it if you [**buy the world a tree**](https://plant.treeware.earth/Moxio/commonmark-ext-fancy-lists)
to thank us for our work. By contributing to the Treeware forest you'll be creating
employment for local families and restoring wildlife habitats.

---
Made with love, coffee and fun by the [Moxio](https://www.moxio.com) team from
Delft, The Netherlands. Interested in joining our awesome team? Check out our
[vacancies](https://werkenbij.moxio.com/) (in Dutch).

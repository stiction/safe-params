# safe-params
sanitize params

A useful package for retrieving type-safe params

valid types:
```
int
float
bool
string
string.trim trimmed string
array
uint64 '0' ~ '18446744073709551615'
array.int
array.float
array.bool
array.string
array.string.trim
array.uint64
```

## Installation

```bash
composer require stiction/safe-params
```

## Examples

```php
use Stiction\SafeParams\SafeParamsParser;

$parser = new SafeParamsParser;
$age = $parser->parse(' 29  ', 'int');

/*
int(29)
*/
var_dump($age);
```

```php
use Stiction\SafeParams\SafeParamsParser;

$data = [
    'name' => '   jack   ',
    'id' => 29,
    'hobbies' => [21, 'baseball', 3.14],
    'address' => 'a beautiful village',
];
$spec = ['name' => 'string.trim', 'id' => 'uint64', 'hobbies' => 'array.int'];
$parser = new SafeParamsParser;
$safeData = $parser->parse($data, $spec);

/*
array(3) {
  ["name"]=>
  string(4) "jack"
  ["id"]=>
  string(2) "29"
  ["hobbies"]=>
  array(3) {
    [0]=>
    int(21)
    [1]=>
    int(0)
    [2]=>
    int(3)
  }
}
*/
var_dump($safeData);
```

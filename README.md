# ArinDB-Client

A PHP library to communicate with the ARIN database (Reg-RWS).

## Setting up data objects

There is a multitude of possibilities to set up the data object.

- The XML leaf nodes (e.g. `<city>`) use `setValue()`
- collection elements (such as `<comment>` or `<originASes>`) also support `addValue()`
- all payloads use `set()` and `add()` together with the element name
- payloads and collection elements implement the `ArrayAccess` interface. I.e. you can traverse the XML structure 
as if it were a multidimensional array (where collections represent numerically indexed and payloads associative 
arrays). Note that you can only set named leaf nodes’ values via array access (cf. `$net['ASN']` vs. `$net['net']` 
in the example below).

```php
use Dormilich\WebService\ARIN\Payloads\Customer;
use Dormilich\WebService\ARIN\Payloads\Country;
use Dormilich\WebService\ARIN\Payloads\Net;
use Dormilich\WebService\ARIN\Payloads\NetBlock;

$customer = new Customer;

// adding simple values
$customer
  ->set('city', 'MyTown')
  ->set('postalCode', 12345)
;
// set values array style
$customer['city'] = 'AnyTown';

// delete values
unset($customer['city']);

// some elements know what to save
$customer['private'] = 'on';
var_dump($customer['private']->getValue()); // bool(true)
// …even if you use their alias
echo $customer['private']->getName(); // customerPrivate

// set up sub-payloads…
// …partially…
$customer['country']['code2'] = 'US';
// …or at once
$country = new Country;
$country['code3'] = 'USA';
$country['e164'] = 1;       # that’s the country calling code, btw.
$customer['country'] = $country;

// set up multi-line elements’ values…
$customer
  ->add('comment', 'line 1')
  ->add('comment', 'line 2')
;
$customer['comment'][] = 'line 3';
$customer['comment']->addValue('line 4');

// …edit them…
$customer['comment'][3] = 'LINE 4';

// …or delete selected ones
unset($customer['comment'][2]);

// element groups work similar (but you have to know what to put in!)
$net = new Net;
$net['ASN'][0] = new Element('originAS'); # make sure that name is correct
$net['ASN'][0]->setValue('AS-007');

// and of course they are editable
$net['net'][0] = new NetBlock;
$net['net'][0]['start'] = '192.168.10.32';
$net['net'][0]['end']   = '192.168.10.63';
```

These data objects perform some basic validation (you can’t put an object into a field that expects a string and vice 
versa, some type-related fields allow only predefined values) but generally you have to know what belongs where. 

Exceptions are fired for

- invalid value types
- value constraint violations
- accessing non-existent fields

## Setting up the web service

I’ve not created that part yet.

## Working with the web service

See above.

## Error handling

See above.

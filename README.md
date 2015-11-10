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
use Dormilich\WebService\ARIN\Elements\Element;
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
$net['ASN'][0] = Element::createWith('originAS', 'AS-007');

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

For the web service to work, you need a connection object that implements the `ClientAdapter` interface. This can be 
an existing library or you can write the connectivity functionality yourself (although I recommend the first option). 

To configure the web service itself there are four options to set:
- _environment_ : either "live" for the prduction database or "test" for the OT&E database.
- _password_ : your respective API key for accessing either database.
- _encoding_ : the encoding charset for the XML you will send. Defaults to UTF-8.
- _strict_ : set this option to FALSE if you want to bypass the pre-serialisation validity check. Defaults to TRUE.

## Working with the web service

There are two web service objects available: _TicketRWS_ for anything that is processed through tickets (tickets, 
reports, and ROA) and _CommonRWS_ that relates to CRUD operations (such as assigning a network to a customer).

```php
use Dormilich\WebService\ARIN\WebService\CommonRWS;
use Dormilich\WebService\ARIN\Payloads\Customer;
use Dormilich\WebService\ARIN\Payloads\Net;

$client = new MyClient(…);
$arin = new CommonRWS($client, [
    'environment' => 'live',
    'password'    => 'my-arin-password',
]);

/* set up customer */

$customer = new Customer;

# set up customer object…

// don’t ask me why customers have to be newly created for every net
$customer = $arin->create($customer, 'PARENT-NET-HANDLE');

/* set up net with that customer */

$net = new Net;

# assign network properties, among that…
$net['customer']  = $customer->getHandle();
$net['parentNet'] = 'PARENT-NET-HANDLE';

// don’t ask me why there is a need for a wrapper
$response = $arin->create($net)['net'];

// mind that a network assignment will result 
// in a ticket if the automated process failed.
try {
    $net = $response['net'];
}
catch (Exception $e) {
    $ticket = $response['ticket'];
}

// alternately fetch the first element
$net = $response[0];

if ($net instanceof Net) {
    # net successfully assigned
}
``` 

## Error handling

The error handling depends on how your connection object handles HTTP errors. If the Reg-RWS returns an error 
payload, you can convert that to an object via `Payload::loadXML()` or you use the object you received from 
the web service call if your connection didn’t throw an exception.

## Note

All payloads and group elements are iterable and can therefore be used directly in a `foreach()` loop. 
Additionally, payloads can be serialised into JSON.

All elements can be converted into a string. If there is an XML attribute associated with that element, 
you can access it as the object’s property.

# php-ping
PPPing, the Purplepixie PHP Ping Library is a low-level socket based ICMP ping library written by [David Cutting](https://davecutting.uk) for use within the [FreeNATS Network Monitor](https://purplepixie.org/freenats/) but now spun out as a standalone library.

## Obtaining PPPing

The easiest option is to use [composer](https://getcomposer.org) to be able to obtain the latest package via [Packagist](https://packagist.org/packages/purplepixie/php-ping) with the command:

```
composer require purplepixie/php-ping
```

Using composer you can then autoload the classes as needed.

You can also download the source directly (from a tagged release or clone the repo) and include the file ```src/PurplePixie/PhpPing/PPPing.php```.

Note the ```PPPing``` class is in the namespace ```\PurplePixie\PhpPing\PPPing```.

## Using PPPing

Once included it's very simple to use PPPing.

```php
use \PurplePixie\PhpPing\PPPing;
$ping = new PPPing(); // instantiate a PPPing object
$ping->setHostname("www.google.com"); // host to ping (hostname or IP)
$result=$ping->Ping(); // perform a single ICMP ping
// Result is either a negative number (error) or 0 up which is return in ms
if ($result<0) // error
    echo "Error: ".$ping->strError($result)."\n";
else
    echo "Return: ".$result." ms\n";
```

### Additional Information Available

Following a ping information about the last result is available via ```PPPing::getLast()``` which is an associative array with keys as follows:

- set - boolean indicating if data was returned or not
- result - the result value of the ping (negative number means error, 0 or positive is return time in ms)
- ttl - the TTL of the returned packet
- hops - an estimation of the hops based on socket TTL and returned TTL (not always accurate)
- source - IP address of the return packet source (the remote system)
- destination - IP address of the destination of the return packet (local system) 

### Other Options and Variables

Various options can be read and set as needed within the ```PPPing``` object

- ```PPPing::getHostname()``` gets the remote hostname or IP
- ```PPPing::setHostname($host)``` sets the remote hostname or IP
- ```PPPing::getTTL()``` gets the TTL
- ```PPPing::setTTL($ttl)``` sets the TTL
- ```PPPing::getTimeout()``` gets the Timeout (seconds)
- ```PPPing::setTimeout($timeout)``` sets the Timeout (seconds)
- ```PPPing::getPackage()``` gets the packet package (defaults to "PPPing")
- ```PPPing::setPackage($package)``` sets the packet package
- ```PPPing::getDebug()``` gets the debug status flag (boolean)
- ```PPPing::setDebug($d)``` sets the debug status flag (boolean)
- ```PPPing::getSequence()``` gets the sequence
- ```PPPing::setSequence($s)``` sets the sequence
- ```PPPing::getIdentity()``` gets the identity used
- ```PPPing::getLast()``` gets the data about the last ping

## Example CLI Implementation

In the ```bin``` folder is a ping.php which can be run on the command-line to demonstrate the functionality of PPPing. Options to the ```ping.php``` script are:

```
Usage: php ping.php [options] hostname

Options available are as follows:

 --ttl x | -t x    Set TTL (not working with ppping yet)
 --debug | -d      Turn on debug mode and output
 --delay x         Minimum delay between pings in seconds (or decimal)
 --help | -h       Display this help and quit
 --count x | -c x  Number of times to ping (default infinity)

hostname must be a resolvable host or an IP address to ping (required)
```
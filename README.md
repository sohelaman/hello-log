# HelloLog
### Hello World kind of logger for PHP

## Features
* Logs data in file or returns data for printing/storing.
* Additional information like timestamp, data type, serial number is added to log.
* Simple browser friendly formatting.
* Supports major data types, arrays, objects.
* Supports output in JSON format.
* Built in counter.
* Support for changing file name any time while logging.
* Time based file name suffix can be used to segregate log files.

## Basic Usage
Grab the package using Composer
```bash
$ composer require sohelaman/hello-log
```

Then use it in your PHP script
```php
// Composer autoload
require_once 'vendor/autoload.php';

use HelloLog\HelloLog;

// Create a HelloLog instance
$hellolog = new HelloLog('/home/sohel/Documents/Logfile.log');

// Simply print a message, this will not put message in log file
$hellolog->msg('Arthur Curry, I hear you can talk to fish.');

// Put some info in log file
$data = "Luke, I am your father!";
$hellolog->info($data);

// AND YESS!! There are shortcuts too!
$data = array("Diana" => "He said he'll fight with us?", "Bruce" => "More or less.");
$hellolog->i($data); // Same as info()

// Similarly put error or warning into the log
$hellolog->warn("So, you're fast?");
$hellolog->error("That feels like an over simplification.");

// Pass array or object
$hellolog->i($_SERVER);

// Return output instead of putting into log. Pass true as second parameter.
echo $hellolog->i("I'm real when it's useful.", true); // Prints instead of putting into log
```

## Documentation
### Constructor
* Default constructor will use **/tmp** directory and a file called **hellolog.log**.
* *Note that in many systems, apache users like http or www-data create subdirectories under /tmp directory and then create files.*
```php
$hellolog = new HelloLog();
```
* Instantiate with path and file segmentation configurations
```php
$hellolog = new HelloLog(<file path>, <file segmentation>);
$hellolog = new HelloLog('/home/sohel/Documents/Logfile.log', 'DAY');
```
* Third constructor parameter is the overwrite flag. If it's true, then the file will be overwritten on each log.
```php
$hellolog = new HelloLog(<file path>, <file segmentation>, <overwrite flag>);
$hellolog = new HelloLog('/home/sohel/Documents/Logfile.log', 'DAY', true);
```
* NOTE that, once overwrite flag is turned on, file will be overwritten EACH time you put anyting into log file. So, previous content will be gone from that file. Overwrite flag can be turned on or off using overwrite() method. If not explicitly specified, overwrite flag is truned off by default.
```php
$hellolog->overwrite(false); // Turns off overwrite flag. So, logs will be appended after this call.
$hellolog->overwrite(true); // Turns on overwrite flag.
```

### File Segmentation
* Log file segmentation puts a suffix in log file name based on time.
* For example, 'DAY' will add a suffix for a day, such as 2016-08-30, and make the file name like **Logfile_2016-08-30.log**
* Suffix is added with the given file name, so if there's a file named **Logfle.log**, then a new file will be created with a name **Logfile_2016-08-30.log** if not already exists,
* Other segmentations are 'NONE', 'YEAR', 'MONTH', 'DAY', 'HOUR', 'MINUTE', 'SECOND'
* Default segmentation is 'NONE', which won't create any segments and put all logs in one file.

### Getters and Setters
* You can set file path and file segmentation using setter methods, and these will impact instantly.
```php
$hellolog->setPath('/var/www/logs/New.log');
$hellolog->setSeg('MINUTE'); // 'NONE', 'YEAR', 'MONTH', 'DAY', 'HOUR', 'MINUTE', 'SECOND'
```
* Get current path and segmentation configuration using getter methods.
```php
$path = $hellolog->getPath();
$seg = $hellolog->getSeg();
```

### Logging Type
* There are five similar yet different methods
```php
$data = 'I can do this all day!';
$hellolog->info($data); // General info, uses print_r() output
$hellolog->error($data);
$hellolog->warn($data);
$hellolog->debug($data); // Extensive debug, uses var_export() output
$hellolog->json($_SERVER); // Uses JSON string
```
* All of these methods have short names.

```php
$hellolog->i($data);
$hellolog->d($data);
$hellolog->j($data);
$hellolog->e($data);
$hellolog->w($data);
```

* Output can be returned instead of putting in log file. Passing **true** as second parameter will return the data.

```php
$output = $hellolog->d($data, true);
$output = $hellolog->j($data, true);
```

### Output Format
* Output data is a string containing a preamble followed by formatted data.
* Preamble consists of log serial number, timestamp, log type and data type. Preamble looks like following,
```
[2] [2016-09-03 09:35:10] [DEBUG] [DATATYPE:object]
```
* When data is returned, by default it's wrapped inside ```<pre>``` tag, just to increase readability on browser.
* Pretty formatting can be disabled and enabled by calling **ugly()** and **pretty()** methods respectively.

```php
$hellolog->ugly(); // Disables <pre> wrapper
$hellolog->pretty();
```
* json() method has a third parameter, allowing raw output. Raw output does not have any preamble or pretty formatting.
```php
$serverJSON = $hellolog->j($_SERVER, true, true); // Returns JSON string of $_SERVER variable.
```

### Counter
* HelloLog has a built in counter. Counting starts from zero. Each time calling count() method will increment counter by one.
```php
echo $hellolog->count(); // Increments the counter by one
```
* Counter has other actions, like decrement. reset and get current value. Action strings are passed as function parameter. For all actions, counter performs the action first and then returns the counter value.
```php
echo $hellolog->count('DEC'); // Decrements the counter by one
echo $hellolog->count('RESET'); // Resets the counter to zero
echo $hellolog->count('GET'); // Get the current counter value
```
### Others
* Print timestamp. Timestamp can be returned by passing **true** as parameter.
```php
$hellolog->time();
echo $hellolog->time(true);
```
* Print a simple message
```php
$hellolog->msg('Winter is coming!');
$hellolog->msg(); // This will simply print 'Hello, World!'
```

**That'd be all. Thanks!**

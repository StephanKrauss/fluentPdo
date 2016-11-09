# FluentPdo
Minimalistisches ORM. **Achtung** , momentan noch im Beta Stadium.  
Nicht für den produktiven Einsatz verwenden.


## Credits
Thanks to
+ elzekool/FluentDbal , elzekool , http://www.kooldevelopment.nl
+ Funktion zur Generierung der realen Query

## Key features
+ fluentes schreiben der PDO 
+ Query wird als **reale** Query zurückgegeben
+ Cachen der Query
+ Rudimentäre Methoden zur Generiwerung der Resultates

## Example
```php
  
include_once('fluentPdo.php');
	include_once( 'fluentException.php' );
  
	$server   = 'mysql:dbname=test;host=localhost; port=3306';
	$user     = 'test';
	$password = 'test';
  
	$options  = array
	(
	    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
	);
  
	$pdo = new PDO($server, $user, $password, $options);
	$fluentPdo = new fluentPdo($pdo);
  
	$cols = array('id','name');
  
	$where = array(
		"name = :name"
	);
  
	// Variablen aus einem Formular
	$formVars = array(
	    	'name' => 'mustermann'
	);
  
/** @var $stmtPdo fluentPdo  */
$stmtPdo = $fluentPdo->select($cols)->from('users')->where($where)->execute($formVars);
  
$rawQuery = $fluentPdo->getRawQuery();
  
$cleanQuery = $fluentPdo->getRealSql();
    
$timeQuery = $fluentPdo->getTime();

```

## License

(MIT License)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

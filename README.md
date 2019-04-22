# Burning PHP  

**Burning PHP** is a runtime code analyzer that will help you to find bottlenecks in your script by modifying all source codes with a special structure before it run in fact, then you could use some external tools to read this output file to identify a lot of kind of issues.

## Configuration

You could customize how *Burning PHP* will run by creating a file called `.burning.json`. It is a JSON5 compatible file that contains all available configurations of this package.

See the file [.burning.json](.burning.json) comments to understand how each properties works.

## Output Structure  

The output file is named by default `.sess{%requestMs}.burning-session` and stored at `.burning` folder relative to the current working dir. It is a JSON file with a bunch of objects. Each object will have an `type` key that identifies what it is and will help to know what is the anothers properties of this object.

### Common Properties

All types are fulfilled with some default properties:

* **type** (*string*): the type name;
* **timestamp** (*float*): the timestamp with precision of microseconds when the type was instantiated;

### Types

#### autoload

The `autoload` type is generated when a class is autoloaded by the **Burning** autoloader. Naturally, autoloaded files must be considered too as required.

Only autoloaded classes from target package are written.

##### Properties

* **classname** (*string*): the namespaced name of class;
* **file** (*string*): the absolute path of the autoloaded class;

#### initialize

The `initialize` type is generated when a new session is started by the **Burning**. It counterpart is the `shutdown` type.

##### Properties

* **version** (*int*): the version of the **Burning** in integer notation (*eg. 1.0.0 = 10000*);
* **requestTimestamp** (*float*): the timestamp of the start of the request, with precision of microseconds;

#### shutdown

The `shutdown` type is generated when the script shutdowns. It counterpart is the `initialize` type.

If the process is killed before terminating properly (eg. via `SIGKILL`), this type will not be written by default. If you really need of this object, so you need enable the `forceWriteShutdownObject` option. The `clean` property will give you a clue if the process was finished without be killed when it is `true`.

##### Properties

* **clean** (*boolean*): will be `true` if it is a clean shutdown (*eg. the process was not killed*);

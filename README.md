
# Burning PHP    
  
**Burning PHP** is a runtime code analyzer that will help you to find bottlenecks in your script by modifying all source codes with a special structure before it run in fact, then you could use some external tools to read this output file to identify a lot of kind of issues.  
  
## Configuration  
  
You could customize how *Burning PHP* will run by creating a file called `.burning.json`. It is a JSON5 compatible file that contains all available configurations of this package.  
  
See the file [.burning.json](.burning.json) comments to understand how each properties works.  
  
## Output Structure    
  
The output file is named by default `.sess{%requestMs}.burning-session` and stored at `.burning/sessions` folder relative to the current working dir. It is a JSON file with a bunch of objects. Each object will have an `type` key that identifies what it is and will help to know what is the anothers properties of this object.  
  
### Common Properties  
  
All types are fulfilled with some default properties:  
  
* **type** (*string*): the type name;  
* **timestamp** (*float*): the timestamp with precision of microseconds when the type was instantiated;  
  
### Types (Engine)  
  
#### initialize  
  
The `initialize` type is generated when a new session is started by the **Burning**. It counterpart is the `shutdown` type.  
  
##### Properties  
  
* **version** (*int*): the version of the **Burning** in integer notation (*eg. 1.0.0 = 10000*);  
* **requestTimestamp** (*float*): the timestamp of the start of the request, with precision of microseconds;  
* **workingDirectory** (*string*): the working directory of the process;  
  
#### path  
  
The `path` type is generated before a file be loaded, generally a class by the autoloader, but it could be a included or required file.  
  
If the file is too a class, but is loaded before the autoloader, so the `autoloading` property will not be filled immediately.  
  
##### Properties  
  
* **file** (*string*): the file path relative to working directory;  
* **autoloading** (*?string*): if the path was called first by the autoloader, so it will declare this property with the autoloaded class;  
  
#### shutdown  
  
The `shutdown` type is generated when the script shutdowns. It counterpart is the `initialize` type.  
  
If the process is killed before terminating properly (eg. via `SIGKILL`), this type will not be written by default. If you really need of this object, so you need enable the `forceWriteShutdownObject` option. The `clean` property will give you a clue if the process was finished without be killed when it is `true`.  
  
##### Properties  
  
* **clean** (*boolean*): `true` if it was a clean shutdown (*aka. the process was not killed*);  
  
### Types (Expression)  
  
Script expressions will generate some additional properties:  
  
* **file** (*int*): the file index written by `file` type;  
* **offset** (*int*): the offset based on start of file that indicates where the type structure began to be defined (*eg. `<offset>return true;`*);  
* **length** (*int*): the length of the type structure based of began of the structure definition (*eg. `return true` length is 11*);  
  
#### call  
  
The `call` type is generated when some class method is called.  
  
When the method is called by the first time, it will fill the property `name` with the fullname of the method, including the class and it absolute namespace, and the property `as` with an unique index for each full name called. Then for next calls it will have only the `name` property with a *int* instead of *string*. This *int* will indicate the method `name` indirectly that was registered to `as` property on first call.  

##### Properties  
  
* **name** (*string, int*): the absolute name of the method, including the class with full namespace;  
* **as** (*int*): the index to save or get the name;

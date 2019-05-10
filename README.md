# Burning PHP

**Burning PHP** is a runtime code analyzer that will help you to find bottlenecks in your script by modifying all source codes with a special structure before it run in fact, then you could use some external tools to read this output file to identify a lot of kind of issues.

## Configuration

You could customize how *Burning PHP* will run by creating a file called `.burning.json`. It is a JSON5 compatible file that contains all available configurations of this package.

See the file [.burning.json](.burning.json) comments to understand how each properties works.

## Output Structure

The output file is a JSON named by default `.sess{%requestMs}.burning-session` and stored at `.burning/sessions` folder relative to the current working dir. Each key of the root object is a type of data structure that helps you to know how to read this object value.

### Types 

#### InitializeType

The `InitializeType` is generated when a new session is started by the **Burning**. It counterpart is the `ShutdownType`.

##### Properties

* **version** (*int*): the version of the **Burning** in integer notation (*eg. 1.0.0 = 10000*);
* **requestTimestamp** (*float*): the timestamp of the start of the request, with precision of microseconds;
* **timestamp** (*float*): the timestamp of the start of the **Burning**, with precision of microseconds;
* **workingDirectory** (*string*): the working directory of the process;

#### CallType

The `CallType` is generated when a class method is called.

##### Properties

* **functions** (*CallReference[]*): contains all functions or methods called. The key represents the function absolute name;
    * **callFlows** (*CallFlow[]*): contains all flows of the function called;
        * **starts** (*float*): the exact moment that the function was called;

#### PathType

The `PathType` is generated for each file loaded, generally a class by the autoloader, but it could be a included or required file.

If the file is too a class, but is loaded before the autoloader, so the `class` property will not be filled immediately. 

##### Properties

* **files** (*PathReference[]*): contains all files loaded. The key represents the file path relative to working directory;
    * **class** (*?string*): the class name, when the file is loaded by the autoloader;
    * **timestamp** (*float*): the exact moment that this file was loaded, with precison of microseconds;

#### ShutdownType

The `ShutdownType` is generated when the script shutdowns. It counterpart is the `InitializeType`.

##### Properties

* **timestamp** (*float*): the exact moment that the shutdown occurs, with precison of microseconds;

# Burning PHP  

**Burning PHP** is a runtime code analyzer that will help you to find bottlenecks in your script by modifying all source codes with a special structure before it run in fact, then you could use some external tools to read this output file to identify a lot of kind of issues.  

## Output Structure  

The output file is named by default `.sess{%requestMs}.burning-session` and stored at `.burning` folder relative to the current working dir. It is a JSON file with a bunch of objects. Each object will have an `type` key that identifies what it is and will help to know what is the anothers properties of this object.  

### Types  

#### initialize

The `initialize` type is generated when a new session is started by the **Burning**. It counterpart is the `shutdown` type.

##### Properties

* **version** (*int*): the version of the **Burning** in integer notation (*eg. 1.0.0 = 10000*);
* **timestamp** (*float*): the timestamp of the start of the session process, with milliseconds precision;
* **requestTimestamp** (*float*): the timestamp of the start of the request, with milliseconds precision;

#### shutdown

The `shutdown` type is generated when the script shutdowns. It counterpart is the `initialize` type.

If the process is killed before terminating properly (eg. via `SIGKILL`), this type will not be written.

##### Properties

* **timestamp** (*float*): the timestamp at the shutdown moment, with milliseconds precision;

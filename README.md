# database-helper

Database Helper Functions

Mostely extracted aus n98-magerun(2) to be used in other projects.
Independent of symfony packages.

## Features

* Strip database (export only the structure, not the data), originally baseds on [SelfScripts] (https://github.com/amenk/SelfScripts/blob/master/mysql-stripped-dump)
* Human Readable exports and optimize on import (originally by @amenk)
* Compressing / uncompressing dumps (by the n98 team)
* Pipe through pipe viewer for nice progress bars
* Remove definer statements from dumps


## Testing

Before running tests, set the following environment variables:

* `PHPUNIT_DB_HOSTNAME` - defaults to localhost
* `PHPUNIT_DB_USERNAME`
* `PHPUNIT_DB_PASSWORD`
* `PHPUNIT_DB_NAME` - optional - will be auto generated im omitted

## Similar Packages

https://github.com/spatie/db-dumper

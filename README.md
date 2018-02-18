# database-helper

Database Helper Functions

Mostely extracted aus n98-magerun(2) to be used in other projects.
Independent of symfony packages.

## Features

* Strip database (export only the structure, not the data), originally baseds on [SelfScripts] (https://github.com/amenk/SelfScripts/blob/master/mysql-stripped-dump)
* Compressing / uncompressing dumps
* Pipe through pipe viewer for nice progress bars
* Remove definer statements from dumps

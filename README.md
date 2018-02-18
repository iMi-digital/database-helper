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

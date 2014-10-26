norm-fdb
========

Norm File Database Driver

## norm-fdb is for you if you:

- Need application but dont have any database server;
- Dont have accessed to root user to install something;
- Lazy enough to use common database on development;
- Anti mainstream.

## Install

Append to composer.json

```
"reekoheek/norm-fdb": "dev-master"
```

Append to config/config.php

```php
return array(
    ...
    'Norm\\Provider\\NormProvider' => array(
        'datasources' => array(
            'filedb' => array(
                'driver' => 'ROH\\FDB\\Connection',
                'dataDir' => '../data',
            ),
        ),
        ...
    ),
);
```

That's all
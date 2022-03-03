# how to use
```json
    "require": {
        "fy403/aurora-client-php": "dev-master"
    }
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/fy403/aurora-client-php.git"
        }
    ],
```

# use fy403/auroraclientphp/Client;
# $client = new Client(...);
# #Client->Init(...);
# $centerResponse = $client->SendSync($centerRequest);
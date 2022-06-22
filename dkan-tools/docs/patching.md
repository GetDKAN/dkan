# Patching

If it is necessary or expedient to overwrite files in DKAN or Drupal core, it is recommended that you create a _/src/patches_ directory where you can store local [patch](https://ariejan.net/2009/10/26/how-to-create-and-apply-a-patch-with-git/)
files with the changes. A patch will make it possible to re-apply these changes once a newer version of DKAN or Drupal is applied to your project.

Update your project composer.json
```
"require": {
    "cweagans/composer-patches": "~1.0"
},
"extra": {
        "enable-patching": true,
        "patches": {
            "drupal/core": {
                "1234": "https://www.drupal.org/files/issues/1234-thing-to-fix.patch"
            },
            "getdkan/dkan": {
                "000": "https://patch-diff.githubusercontent.com/raw/GetDKAN/dkan/pull/000.patch",
                "bugfix info": "src/patches/localfile.patch"
            }
        }
    }
```

then run:
```
dktl composer update
dktl composer update --lock
```

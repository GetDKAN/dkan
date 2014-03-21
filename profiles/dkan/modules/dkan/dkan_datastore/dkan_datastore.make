core = 7.x
api = 2

projects[feeds][subdir] = "contrib"
projects[feeds][version] = "2.x"
projects[feeds][download][type] = "git"
projects[feeds][download][url] = "http://git.drupal.org/project/feeds.git"
projects[feeds][download][revision] = 1383713
projects[feeds][download][branch] = 7.x-2.x
projects[feeds][type] = "module"
projects[feeds][patch][1428272] = http://drupal.org/files/feeds-encoding_support_CSV-1428272-52.patch
projects[feeds][patch][1127696] = http://drupal.org/files/feeds-1127696-multiple-importers-per-content-type-59.patch

projects[feeds_field_fetcher][subdir] = contrib
projects[feeds_field_fetcher][download][type] = git
projects[feeds_field_fetcher][download][url] = "http://git.drupal.org/project/feeds_field_fetcher.git"
projects[feeds_field_fetcher][download][branch] = master
projects[feeds_field_fetcher][type] = "module"

projects[feeds_flatstore_processor][subdir] = contrib
projects[feeds_flatstore_processor][download][type] = git
projects[feeds_flatstore_processor][download][url] = "http://git.drupal.org/sandbox/acouch/1952754.git"
projects[feeds_flatstore_processor][download][branch] = master
projects[feeds_flatstore_processor][type] = "module"

projects[schema][subdir] = contrib
projects[schema][patch][1237974] = http://drupal.org/files/schema-support-custom-types-1237974-48.patch
projects[services][subdir] = contrib
projects[data][subdir] = contrib
projects[data][version] = 1.x
projects[job_scheduler][subdir] = contrib
projects[job_scheduler][version] = 2.x

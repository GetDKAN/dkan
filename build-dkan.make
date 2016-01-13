api = 2
core = 7.x

includes[core] = drupal-org-core.make

; Profile

projects[dkan][type] = profile
projects[dkan][download][type] = git
projects[dkan][download][url] = https://github.com/NuCivic/dkan.git
projects[dkan][download][branch] = fix_problem_with_long_text_on_field_additional_info_civic_159

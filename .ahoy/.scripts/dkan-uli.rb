require "./dkan/.ahoy/.scripts/config.rb"

if CONFIG["default"]["https_everywhere"]
  url="surl"
else
  url="url"
end

url=`ahoy drush --uri="$(ahoy docker #{url})" uli`
os =`uname`

if os =~ /Darwin/
  `open #{url}`
else
  puts url
end

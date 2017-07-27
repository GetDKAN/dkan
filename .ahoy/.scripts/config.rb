require 'yaml'

# Deep merge only available in ruby >= 3.0
# TODO: replace defaults with custom deep_merge

begin
  CONFIG = YAML.load_file("config/config.yml")
rescue Exception => msg
  puts "Loading of Configuration errored out with: #{msg}."
  puts "Using default CONFIG instead."
  CONFIG = {}
end

if not CONFIG.has_key? "circle"
  CONFIG["circle"] = {}
end

if not CONFIG["circle"].has_key? "skip_features"
  CONFIG["circle"]["skip_features"] = []
end

if not CONFIG.has_key? "default"
  CONFIG["default"] = {}
end

if not CONFIG["default"].has_key? "https_everywhere"
  CONFIG["default"]["https_everywhere"] = false
end

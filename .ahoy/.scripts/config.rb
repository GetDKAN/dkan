require 'yaml'

begin
  CONFIG = YAML.load_file("config/config.yml")
rescue Exception => msg
  puts "Loading of Configuration errored out with: #{msg}."
  puts "Using default CONFIG instead."
  CONFIG = {"circle" => {"skip_features" => []}}
end



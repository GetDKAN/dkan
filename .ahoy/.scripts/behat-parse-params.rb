#
# Defines helper function for processing behat parameters.
#
require "base64"
require "./dkan/.ahoy/.scripts/config.rb"
require "pp"

def behat_join_params args
  args.join(" ").split("--").map do |arg|
    key_value = arg.split("=")

    if key_value[0].nil?
      next
    end

    if key_value.size == 2
      "--" + key_value[0].strip + "=" + "'" + "#{key_value[1]}".strip + "'"
    else
      if [
        "colors", "no-colors", "end", "suite", "format",
        "out", "format-settings", "init",  "lang", "name",
        "tags", "role", "story-syntax","definitions", "append-snippets",
        "no-snippets", "strict", "order", "rerun", "stop-on-failure",
        "dry-run", "profile", "config", "verbose", "help",
        "config-reference", "version", "no-interaction", "skip-composer"
      ].include? key_value[0].strip
        "--" + key_value[0].strip
      else
        puts key_value
        key_value[0].strip
      end
    end
  end.join(" ")
end

def behat_parse_params args
  files = []
  params = []

  args.each do |param|

    puts "Seaching #{param} for feature files..."
    # Fetch all of the feature files for each parameter (directories)
    if Dir.exist? param
      Dir.glob("#{param}/*.feature") {
        |f|
        filename = File.basename(f)
        if CONFIG["circle"]["skip_features"].include? filename
          next
        end
        files.push f
      }

      # Add loose features passed in as direct paths
    elsif File.exist? param
      files.push param

      # Track non feature parameters
    else 
      params.push param
    end
  end

  unless params.include? '--no-colors'
    params.unshift '--colors'
  end

  {:files => files, :params => params}
end

def behat_parse_suite file
  default = "dkan_starter"

  return default if  file.nil? or file.empty?

  key = file.split('/').reverse[3]

  return default if key.nil?

  types = {
    :dkan => "dkan",
    :config => "custom",
  }

  if types[key.to_sym]
    types[key.to_sym]
  else
    default
  end
end

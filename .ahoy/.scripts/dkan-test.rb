# This script initiates a behat test session for any of the dkan development
# environements; including CircleCI and local. This script parses the arguments
# to fix the inherent limitation of envoking commands with quotes via ahoy.
require "pp"
require "base64"
require "./dkan/.ahoy/.scripts/behat-parse-params"

BEHAT_FOLDER = ENV.has_key?("BEHAT_FOLDER") ? ENV["BEHAT_FOLDER"] : "docroot/profiles/dkan/test"
ALT_CONFIG_FILE = ENV.has_key?("ALT_CONFIG_FILE") ? ENV["ALT_CONFIG_FILE"] : "behat.local.yml"
BEHAT_ENV = ENV['HOSTNAME']
SKIP_COMPOSER_FLAG="--skip-composer"

if File.exists? "#{BEHAT_FOLDER}/#{ALT_CONFIG_FILE}"
  puts "Using #{BEHAT_FOLDER}/#{ALT_CONFIG_FILE} .."
  CONFIG="--config=#{ALT_CONFIG_FILE}"
elsif ENV['CI'] == "true"
  puts "Using behat.circleci.yml config .."
  CONFIG="--config=behat.circleci.yml"
elsif BEHAT_ENV == "cli"
  puts "Using behat.docker.yml config .."
  CONFIG="--config=behat.docker.yml"
else
  puts "Using behat.yml"
  CONFIG="--config=behat.yml"
end

def args_payload
  payload = Base64.decode64(ARGV[0]).split(" ")
  if payload[0] == 'payload:'
    return payload.drop 1
  end
  ARGV
end

# Parse given arguments in a format that behat understands.
def main
  payload = args_payload

  if payload.include? SKIP_COMPOSER_FLAG
    puts  "Skipping composer install.."
    payload.delete(SKIP_COMPOSER_FLAG)
  else
    puts "Installing behat dependencies.."
    `bash dkan/.ahoy/.scripts/composer-install.sh #{BEHAT_FOLDER}`
  end

  Dir.chdir(BEHAT_FOLDER) do
    parsed = behat_parse_params(payload)
    files = parsed[:files].join(" ")
    params = behat_join_params(parsed[:params])

    puts "RUNNING: bin/behat #{files} #{params} #{CONFIG}"

    IO.popen("bin/behat #{files} #{params} #{CONFIG}") do |io|
      while line = io.gets
        print line
      end
      io.close
      exit(1) unless $?.success?
    end
  end
end

if ARGV.size > 0
  main
end

# This script initiates a behat test session for any of the dkan development
# environements; including CircleCI and local. This script parses the arguments
# to fix the inherent limitation of envoking commands with quotes via ahoy.

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

# Parse given arguments in a format that behat understands.
def behat_param_parse args
  args.split("--").map do |arg|
    key_value = arg.split("=")

    if key_value[0].nil?
      next
    end

    if key_value.size == 2
      "--" + key_value[0].strip + "=" + "'" + "#{key_value[1]}".strip + "'"
    else
      if [
        "colors", "no-color", "end", "suite", "format",
        "out", "format-settings", "init",  "lang", "name",
        "tags", "role", "story-syntax","definitions", "append-snippets",
        "no-snippets", "strict", "order", "rerun", "stop-on-failure",
        "dry-run", "profile", "config", "verbose", "help",
        "config-reference", "version", "no-interaction"
      ].include? key_value[0].strip
        "--" + key_value[0].strip
      else
        puts key_value
        key_value[0].strip
      end
    end
  end.join(" ")
end

def main
  if ARGV.include? SKIP_COMPOSER_FLAG
    puts  "Skipping composer install.."
    ARGV.delete(SKIP_COMPOSER_FLAG)
  else
    puts "Installing behat dependencies.."
    `bash dkan/.ahoy/.scripts/composer-install.sh #{BEHAT_FOLDER}`
  end

  Dir.chdir(BEHAT_FOLDER) do
    # print command output as it comes
    puts "RUNNING: bin/behat #{behat_param_parse(ARGV[0])} #{CONFIG}"

    IO.popen("bin/behat #{behat_param_parse(ARGV[0])} #{CONFIG}").each do |line|
      puts line
    end
  end
end

if ARGV.size > 0
  main
end

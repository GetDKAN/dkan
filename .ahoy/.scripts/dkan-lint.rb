require 'json'
require 'uri'
require 'net/http'
require 'fileutils'
require 'git'

include FileUtils
include Git

# Get the list of files from a PR
def get_pr_files(user, repo, pr)
  uri = URI.parse("https://api.github.com/repos/#{user}/#{repo}/pulls/#{pr}/files")

  http = Net::HTTP.new(uri.host, uri.port)
  request = Net::HTTP::Get.new(uri.request_uri)
  http.use_ssl = true
  response = http.request(request)
  result = JSON.parse(response.body)
  files = Array.new

  result.each do |i|
    files.push(i['filename'])
  end

  return files
end

if ENV.key?("CI_PULL_REQUEST")
  user = ENV['CIRCLE_PROJECT_USERNAME']
  repo = ENV['CIRCLE_PROJECT_REPONAME']
  pr = ENV['CIRCLE_PR_NUMBER']
  files = get_pr_files(user, repo, pr)
elsif ARGV.any?
  files = ARGV
else
  FileUtils.cd 'dkan'
  g = Git.open('.')
  files = g.diff.name_status.select{|k,v| v != "D"}.keys
  FileUtils.cd '..'
end

if files.any?
  # Filter file list for approved file types
  files.select!{ |i| i[/\.*(\.php|\.inc|\.module|\.install|\.profile|\.info)$/] }
  files.map! {|item| 'dkan/' + item}
  puts "Linting files:\n" + files.join("\n")
  puts `dkan/test/bin/phpcs --standard=Drupal,DrupalPractice -n --ignore=test/dkanextension/*,patches/* #{files.join(" ")}`
end

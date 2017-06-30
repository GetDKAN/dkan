# This file is to help with running behat tests on circleCI. It's needed to:
#  - Handle circleCI's parallelization.
#  - Handle searching in multiple directories for feature files.
# Ex. There are 3 VMs setup: (3)
require "pathname"
require "./dkan/.ahoy/.scripts/behat-parse-params.rb"
require "./dkan/.ahoy/.scripts/circle-behat-balancer.rb"
require "pp"

CIRCLE_ARTIFACTS =  ENV.has_key?("CIRCLE_ARTIFACTS") ? ENV['CIRCLE_ARTIFACTS'] : "assets"
CIRCLE_NODE_TOTAL = node_total()
CIRCLE_NODE_INDEX = node_index()
TOKENS = {
  "s" => (1),
  "m" => (60),
  "h" => (60 * 60),
  "d" => (60 * 60 * 24)
}

puts "CIRCLE_NODE_TOTAL = #{CIRCLE_NODE_TOTAL}"
puts "CIRCLE_NODE_INDEX = #{CIRCLE_NODE_INDEX}"


error = 0
parsed = behat_parse_params(ARGV)
params = behat_join_params parsed[:params]
files = balance(parsed[:files])

files[CIRCLE_NODE_INDEX].each_index do |i|
  file = Pathname(files[CIRCLE_NODE_INDEX][i]).realpath.to_s
  suite = behat_parse_suite(file)
  puts "RUNNING: ahoy dkan test #{file} --suite=#{suite} --format=pretty --out=std --format=junit --out='#{CIRCLE_ARTIFACTS}/junit' #{params} --colors"
  IO.popen(
  "ahoy dkan test #{file} --suite=#{suite} --format=pretty --out=std --format=junit --out='#{CIRCLE_ARTIFACTS}/junit' #{params} --colors"
  ) do |io|
    while line = io.gets
      print line
    end
    io.close
    error = 1 unless $?.success?
  end
end

Kernel.exit(error) unless error == 0

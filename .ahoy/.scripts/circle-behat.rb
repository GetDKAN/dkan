# This file is to help with running behat tests on circleCI. It's needed to:
#  - Handle circleCI's parallelization.
#  - Handle searching in multiple directories for feature files.
# Ex. There are 3 VMs setup: (3)
require "pathname"
require "./dkan/.ahoy/.scripts/behat-parse-params.rb"
require "pp"

unless ENV.has_key?("CIRCLE_NODE_INDEX")
  puts "No parrallelism found, setting defaults to run all tests."
  CIRCLE_NODE_TOTAL=1
  CIRCLE_NODE_INDEX=0
else 
  CIRCLE_NODE_TOTAL = ENV['CIRCLE_NODE_TOTAL'].to_i
  CIRCLE_NODE_INDEX = ENV['CIRCLE_NODE_INDEX'].to_i
end

CIRCLE_ARTIFACTS =  ENV.has_key?("CIRCLE_ARTIFACTS") ? ENV['CIRCLE_ARTIFACTS'] : "assets"

puts "CIRCLE_NODE_TOTAL = #{CIRCLE_NODE_TOTAL}"
puts "CIRCLE_NODE_INDEX = #{CIRCLE_NODE_INDEX}"


error = 0
parsed = behat_parse_params(ARGV)
files = parsed[:files]
params = behat_join_params parsed[:params]

files.each_index do |i|
  if (i % CIRCLE_NODE_TOTAL)  == CIRCLE_NODE_INDEX
    file = Pathname(files[i]).realpath.to_s
    puts "RUNNING: ahoy dkan test #{file} --format=pretty --out=std --format=junit --out='#{CIRCLE_ARTIFACTS}/junit' #{params} --colors"
    IO.popen(
    "ahoy dkan test #{file} --format=pretty --out=std --format=junit --out='#{CIRCLE_ARTIFACTS}/junit' #{params} --colors"
    ) do |io|
      while line = io.gets
        print line
      end
      io.close
      error = 1 unless $?.success?
    end
  end
end

Kernel.exit(error) unless error == 0

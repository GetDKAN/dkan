require "pathname"

TOKENS = {
  "s" => (1),
  "m" => (60),
  "h" => (60 * 60),
  "d" => (60 * 60 * 24)
}


def parse_time(time_string, measure = "m")
  time = 0 
  time_string.scan(/([-+]?[0-9]*\.?[0-9]*)(\w)/).each do |amount, measure|
    begin
      time += amount.to_i * TOKENS[measure]
    rescue TypeError => te
      puts "Missing time header"
    rescue StandardError => se
      puts "An error happen trying to parse the time header"
    end
  end
  return time
end

def parse_header(file) 
  file = Pathname(file).realpath.to_s
  first_line = File.open(file, &:gets)
  first_line.sub! "#", ""
  if first_line.include? "time"
    time = first_line.split(":")[1].strip
    time = parse_time(time)
  else 
    time = 60 # For missing headers default to 60s
  end 
  return time
end 

def sort_files(files)
  keyed_files = {}
  files.each_index do |i|
    keyed_files[files[i]] = parse_header(files[i])
  end
  sorted_files = keyed_files.sort_by {|k, v| k}.sort_by {|k, v| v}.reverse
  puts "Feature Files (sorted by weight desc)"
  pp sorted_files
  return sorted_files
end

def node_total()
  unless ENV.has_key?("CIRCLE_NODE_TOTAL")
    return 1
  else 
    return ENV["CIRCLE_NODE_TOTAL"].to_i
  end
end

def node_index()
  unless ENV.has_key?("CIRCLE_NODE_INDEX")
    return 0
  else 
    return ENV["CIRCLE_NODE_INDEX"].to_i
  end
end

def get_containers()
  return Array.new(node_total(), 0)
end

def get_distribution()
  distribution = {}
  to = node_total() - 1;
  for i in 0..to
    distribution[i] = []
  end
  return distribution
end 

def balance(files)
  containers = get_containers()
  distribution = get_distribution()
  files = sort_files(files)
  files.each_index do |i|
    emptier = containers.each_with_index.min[1]
    file, weight = files[i]
    containers[emptier] += weight
    distribution[emptier].push(file)
  end
  return distribution
end


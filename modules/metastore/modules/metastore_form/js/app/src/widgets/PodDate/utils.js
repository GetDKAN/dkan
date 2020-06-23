export function range(start, end) {
  var arr = [];
  for (var i = start; i <= end; i += 1) {
    arr.push(i);
  }
  return arr;
}

export function Pad(value) {
  return String(value).padStart(2, "0");
}

export function dateToValues(value) {
  value = value ? value.split("-") : [];
  return value;
}

import React from "react";

export default ({
  items,
  value,
  label,
  name,
  onChange,
  renderOption,
  generateValue
}) => {
  //const v = value ? value : '';
  return (
    <select value={value} onChange={onChange} name={name} className="form-control">
      <option value="">
        {label}
      </option>
      {items.map((item, index) => (
        <option
          key={item}
          value={generateValue ? generateValue(item, index) : item}
        >
          {renderOption ? renderOption(item, index) : item}
        </option>
      ))}
    </select>
  );
};

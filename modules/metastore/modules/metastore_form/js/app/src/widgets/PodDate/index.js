import React, { useMemo, useState, useCallback, useEffect } from "react";
import Select from "./Select";
import { range, dateToValues, Pad } from "./utils";

export default ({
  onChange,
  schema,
  name,
  uiSchema,
  idSchema,
  formData,
  required,
  minDate,
  maxDate,
  yearLabel = "Year",
  monthLabel = "Month",
  dayLabel = "Day",
  className = "dc-pod-date"
}) => {
  const date = formData ? dateToValues(formData) : ['', '', ''];
  const { title, description } = schema;
  const { $id } = idSchema;
  const startDate = useMemo(() => (minDate || new Date(1900, 0, 1)), [minDate])
  const endDate = useMemo(() => (maxDate || new Date()), [maxDate]);
  const firstYear = useMemo(() => startDate.getFullYear(), [startDate]);
  const lastYear = useMemo(() => endDate.getFullYear(), [endDate]);
  const yearRange = useMemo(() => range(firstYear, lastYear), [firstYear, lastYear]);
  const monthRange = useMemo(() => range(1, 12), []);
  const dayRange = useMemo(() => range(1, 31), []);

  const [year, setYear] = useState('');
  const [month, setMonth] = useState('');
  const [day, setDay] = useState('');
  const [value, setValue] = useState(date.join('-'));
  console.log('value: ', value);

  // useEffect(() => {
  //   if (date) {
  //     if (date[0]) {
  //       setYear(date[0])
  //     }
  //     if (date[1]) {
  //       setMonth(Pad(date[1]))
  //     }
  //     if (date[2]) {
  //       setDay(Pad(date[2]))
  //     }
  //   }
  // }, [date]);

  const handleChange = useCallback(
    e => {
      const inpValue = e.target.value;
      const field  = e.target.name;
      console.log(inpValue);
      if (field === 'year') {
        setYear(inpValue)
        date[0] = inpValue
        date[1] = date[1]
        date[2] = date[2]
      }
      if (field === 'month') {
        setMonth(inpValue)
        date[0] = date[0]
        date[2] = date[2]
        date[1] = inpValue
      }
      if (field === 'day') {
        setDay(inpValue)
        date[0] = date[0]
        date[1] = date[1]
        date[2] = inpValue
      }
      setValue(date.join('-'))
      onChange(value);
      console.log(date);
      console.log(date[0]);
    }
  );

  // useEffect(() => {
  //   if (year) {
  //     date[0] = year;
  //   }
  //   if (month) {
  //     date[1] = month;
  //   }
  //   if (day) {
  //     date[2] = day;
  //   }

  // }, [year, month, day]);

  return (
    <div className={`${className} form-group field`}>
      <label className="control-label" htmlFor={$id}>{title}</label>
      <div className="dc-field-label" id={`${$id}__description`}>{description}</div>
      <div className="dc-select-group form-inline">
        <Select
          key='year'
          label={yearLabel}
          items={yearRange}
          value={date[0]}
          onChange={handleChange}
          name='year'
          renderOption={null}
          generateValue={null}
        />
        <Select
          key='month'
          label={monthLabel}
          items={monthRange}
          value={date[1]}
          onChange={handleChange}
          name='month'
          renderOption={monthRange ? v => Pad(v) : null}
          generateValue={monthRange ? v => Pad(v) : null}
        />
        <Select
          key='day'
          label={dayLabel}
          items={dayRange}
          value={date[2]}
          onChange={handleChange}
          name='day'
          renderOption={dayRange ? v => Pad(v) : null}
          generateValue={dayRange ? v => Pad(v) : null}
        />
        <input value={value} id={$id} required={required}/>
      </div>
    </div>
  );
};

import React, { useMemo, useState, useCallback, useEffect } from "react";
import Select from "./Select";
import { range, dateToValues, Pad } from "./utils";

export default ({
  formContext,
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
  const date = formData ? dateToValues(formData) : [];
  let value = '';
  const { title, description } = schema;
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

  useEffect(() => {
    if (date) {
      if (date[0]) {
        setYear(date[0])
      }
      if (date[1]) {
        setMonth(Pad(date[1]))
      }
      if (date[3]) {
        setDay(Pad(date[3]))
      }
    } else if (date === '') {
      setYear('')
      setMonth('')
      setDay('')
    }
  }, [date]);

  const handleChange = useCallback(
    e => {
      const inpValue = e.target.value;
      const field  = e.target.name;

      if (field === 'year') {
        setYear(inpValue)
      }
      if (field === 'month') {
        setMonth(inpValue)
      }
      if (field === 'day') {
        setDay(inpValue)
      }
      value = 'test';
    }
  );

  useEffect(() => {
    if (year) {
      date[0] = year;
    }
    if (month) {
      date[1] = month;
    }
    if (day) {
      date[2] = day;
    }
  }, [year, month, day]);

  useEffect(() => {
    value = date.join('-');
  }, [date]);

  console.log('d ', date);
  console.log('v ', value);

  return (
    <div className={`${className} form-group field`}>
      <label className="control-label" htmlFor={idSchema}>{title}</label>
      <div className="dc-field-label" id={`${idSchema}__description`}>{description}</div>
      <div className="dc-select-group form-inline">

          <Select
            key='year'
            label={yearLabel}
            items={yearRange}
            value={year}
            onChange={handleChange}
            name='year'
            renderOption={null}
            generateValue={null}
            required={required}
          />
          <Select
            key='month'
            label={monthLabel}
            items={monthRange}
            value={month}
            onChange={handleChange}
            name='month'
            renderOption={monthRange ? v => Pad(v) : null}
            generateValue={monthRange ? v => Pad(v) : null}
          />
          <Select
            key='day'
            label={dayLabel}
            items={dayRange}
            value={day}
            onChange={handleChange}
            name='day'
            renderOption={dayRange ? v => Pad(v) : null}
            generateValue={dayRange ? v => Pad(v) : null}
          />

      </div>
    </div>
  );
};

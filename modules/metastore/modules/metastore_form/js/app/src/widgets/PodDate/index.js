import React, { useMemo, useEffect } from "react";
import Select from "./Select";
import { range, Pad } from "./utils";

export default ({
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
  const { title, description } = schema;
  const startDate = useMemo(() => (minDate || new Date(1900, 0, 1)), [minDate])
  const endDate = useMemo(() => (maxDate || new Date()), [maxDate]);
  const firstYear = useMemo(() => startDate.getFullYear(), [startDate]);
  const lastYear = useMemo(() => endDate.getFullYear(), [endDate]);
  const yearRange = useMemo(() => range(firstYear, lastYear), [firstYear, lastYear]);
  const monthRange = useMemo(() => range(1, 12), []);
  const dayRange = useMemo(() => range(1, 31), []);
  const fields = ['year','month','day'];
  let value = formData;
  console.log('formData: ', formData);
  console.log('value: ', value);
  let v = value ? formData.split("-") : '';


  const labels = useMemo(
    () => ({ day: dayLabel, month: monthLabel, year: yearLabel }),
    [dayLabel, monthLabel, yearLabel]
  );

  const options = useMemo(
    () => ({ day: dayRange, month: monthRange, year: yearRange }),
    [dayRange, monthRange, yearRange]
  );

  const [state, setState] = React.useState({
    year: v ? v[1] : "",
    month: v ? v[2] : "",
    day: v ? v[3] : "",
    date: value
  });

  function handleChange(evt) {
    const value = evt.target.value;
    setState({
      ...state,
      [evt.target.name]: value
    });
  }

  useEffect(() => {
    setState({
      ...state,
      date: state.year + "-" + state.month + "-" + state.day
    });
  }, [state.year, state.month, state.day]);

  return (
    <div className={`${className} form-group field`}>
      <label className="control-label" for={idSchema}>{title}</label>
      <div className="dc-field-label" id={`${idSchema}__description`}>{description}</div>
      <div className="dc-select-group form-inline">
        {fields.map(field => (
          <Select
            key={field}
            label={labels[field]}
            items={options[field]}
            value={value}
            onChange={handleChange}
            name={field}
            renderOption={field === "day" || field === "month" ? v => Pad(v) : null}
            generateValue={field === "day" || field === "month" ? v => Pad(v) : null}
            required={required && field === "year" ? required : null}
          />
        ))}
      </div>
      <pre>{JSON.stringify(state, null, 2)}</pre>
    </div>
  );
};

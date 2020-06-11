import React, { useEffect } from "react";

export default () => {
  const [state, setState] = React.useState({
    value: "",
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

    });
  }, []);

  return (
    <div className="test">
      <select>
        <option value="1">test</option>
      </select>
    </div>
  );
};

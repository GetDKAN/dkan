import React, { useState, useEffect } from 'react';
import { useHistory } from "react-router-dom";
import Form from "@rjsf/core";
import 'bootstrap-lite/lib.bootstrap.css';
import ToastBox, { toast } from "react-toastbox";
import './App.scss';
import axios from "axios";
import podDate from "./widgets/PodDate";

function App({ tempUUID, isNew }) {
  const baseUrl = "";
  //const baseUrl = "http://localtest.me:32772/";
  let history = useHistory();

  const [identifier, setIdentifier] = useState(null);
  const [hasBeenUpdated, setHasBeenUpdated] = useState(false);
  const [message, setMessage] = useState("");
  const [schema, setSchema] = useState({});
  const [uiSchema, setUiSchema] = useState({});
  const [formData, setFormData] = useState({});


  useEffect(() => {
    async function fetchSchema() {
      const response = await axios.get(baseUrl + '/api/1/metastore/schemas/dataset').then(
        (response) => {
          let data = response.data;
          // Alter the schema to override the 'required' status on identifier.
          data.required = data.required.filter(item => item !== "identifier" );
          delete data.properties.identifier.minLength;
          setSchema(data);
        }
      );

      const response2 = await axios.get(baseUrl + '/api/1/metastore/schemas/dataset.ui');
      setUiSchema(response2.data);

      setIdentifier(tempUUID.toString());
    }

    fetchSchema();
  }, []);

  useEffect(() => {
    async function fetch() {
      setHasBeenUpdated(false);
      if (identifier && !isNew) {
        const response = await axios.get(baseUrl + '/api/1/metastore/schemas/dataset/items/' + identifier);
        setFormData(response.data);
      }
    }

    fetch();
  }, [identifier, hasBeenUpdated]);

  useEffect(() => {
    if (message.length > 0) {
      toast.success(message);
    }
  }, [message]);

  function cleanTheData(data) {
    let cleanData = {};
    Object.keys(data).forEach((key) => {
        if (isNaN(key)) {
          cleanData[key] = data[key];
        }
      }
    );
    // Assign the uuid from drupalSettings to the identifier field.
    if (!data.identifier) {
      cleanData.identifier = tempUUID.toString();
    }
    return cleanData;
  }

  function submitDataset(event) {
    const data = event.formData;
    const cleanData = cleanTheData(data);

    if (identifier.length > 0 && !isNew) {
      axios.put(baseUrl + '/api/1/metastore/schemas/dataset/items/' + identifier, cleanData).then(
        () => {
          setHasBeenUpdated(true);
          setMessage("The dataset with identifier " + identifier + " has been updated.");
        }
      ).catch((error) => {
        if (error.response) {
          setMessage(error.response.data.message);
        }
      });
    }
    else {
      axios.post(baseUrl + '/api/1/metastore/schemas/dataset/items', cleanData).then(
        (response) => {
          const id = response.data.identifier;
          setIdentifier(id);
          setMessage("A dataset with the identifier " + id + " has been created.");
        }
      ).catch((error) => {
        if (error.response) {
          setMessage(error.response.data.message);
        }
      });
    }
    window.scrollTo(0,0);
  }

  const CustomDescriptionField = ({id, description}) => {
    return <div className="dc-field-label"  id={id} dangerouslySetInnerHTML={{__html: description}} />
  };

  const fields = {
    DescriptionField: CustomDescriptionField,
    podDate: podDate
  };

  function transformErrors(errors) {
    return errors.map(error => {
      if (error.name === "pattern" && error.property === ".contactPoint.hasEmail") {
        error.message = "Enter a valid email address.";
      }
      if (error.name === "pattern" && error.property.includes(".distribution") && error.property.includes(".isssued")) {
        error.message = "Dates should be ISO 8601 of least resolution. In other words, as much of YYYY-MM-DDThh:mm:ss.sTZD as is relevant to this dataset.";
      }
      return error;
    });
  }

  return (
    <>
      <ToastBox
        timerExpires={10000}
        position="top-left"
        pauseOnHover={true}
        intent="success"
      />
      <button className="btn btn-default" type="button" onClick={event =>  window.location.href='/admin/content/datasets'}>Back to Datasets</button>
      <Form
        id="dc-json-editor"
        schema={schema}
        fields={fields}
        formData={formData}
        uiSchema={uiSchema}
        autoComplete="on"
        transformErrors={transformErrors}
        onSubmit={ (e) => {
          setMessage("");
          submitDataset(e);
        } }
        onError={(e) => { window.scrollTo(0,0); console.error(e);}}>
        <div className="dc-form-actions">
          <button className="btn btn-success" type="submit">Submit</button>
          <button className="btn btn-default" type="button" onClick={event =>  window.location.href='/admin/content/datasets'}>Cancel</button>
        </div>
      </Form>
    </>
  );
}

export default App;

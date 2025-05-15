# JSON Form Widget

This module provides a versatile way to create Drupal form elements from a JSON schema. Why would you need to create a form from JSON? This allows for a wide range of flexibility when customizing a content type without hard coding every field. Data publishers can use DKAN to create and edit custom dataset properties without the need to create patches, or hard-to-maintain overrides any time new schema is introduced. By saving field properties in JSON format, the input and output of the metadata structure remains the same. This also speeds up the performance when creating or updating hundreds of datasets at a time, this can happen in seconds rather than hours.

Using a combination of "router", "helper", and "handler" classes, as well as some extensions on Drupal core elements, it first determines the schema to build the form from the URL paramater in the route based on the data type (EX: ?schema=dataset or ?schema=data-dictionary) and then builds the form according to the retrieved schema and any schema user interface options if supplied (see SchemaUiHandler.php and its contained methods for more information about UI options).

The inspiration for this module and the syntax for the UI Schema come from [react-jsonschema-form](https://rjsf-team.github.io/react-jsonschema-form/docs/). While the UI schemas are not actually interoperable at this time, and RJSF supports more features of JSON-Schema than this module is currently able to, we hope to close that gap over time.

> **_TIP:_**
> A good way to visualize what this module is doing is to use [RJSF](https://github.com/rjsf-team) team's [react-jsonschema-form playground](https://rjsf-team.github.io/react-jsonschema-form/) as this modules functionality is largely similar in regards to how the schema translates to different form fields/structure.

The examples section of this readme can be used to understand how different field types translate directly to Drupal form elements and subsequently, how they would look within the Drupal user interface. They are provided under the presumption that the form is for creating a new data node (in this case a dataset).


## Table of contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Field type UI examples](#field-type-ui-examples)

## Features

- Create Drupal forms from JSON schema.
- Modify form element options created from JSON schema with specialized schema.ui JSON.
- Support for sorting form fields by weight: You can define the field order using a weight property in the schema.

## Requirements

This module is currently required and supplied by DKAN.

It requires:
DKAN:
- DKAN:Metastore (metastore)
- DKAN:Common (common)

Contrib:
- Select (or other) (select_or_other)
- Select2 (select2)

Drupal Core:
- User (user)
- HTTP Basic Authentication (basic_auth)
- System (system)
- Content Moderation (content_moderation)
- Workflows (workflows)

## Installation

The forms that the JSON Form Widget creates utilize the DKAN metastore for schema discovery and subsequently create nodes as the "data" content type. This module therefore is currently packaged with DKAN and as of now cannot be installed independantly. CivicActions plans to develop this module as a standalone Drupal module in the future.

## Configuration

DKAN includes schema files that follow the [Project Open Data DCAT-US schema standards](https://resources.data.gov/resources/dcat-us/). If you need to add additional metadata fields, or wish to remove fields, simply create a new schema/collection/ directory in the root of your site (usually docroot), and add your new schema files to it.

> **_NOTE:_**
> Read the documentation on [Changing your dataset schema](https://dkan.readthedocs.io/en/latest/user-guide/guide_custom_schemas.html) to learn how to add custom fields.

## Field Type UI Examples

If we look closely at the provided DKAN [dataset schema](https://github.com/GetDKAN/dkan/blob/2.x/schema/collections/dataset.json), and [dataset UI schema](https://github.com/GetDKAN/dkan/blob/2.x/schema/collections/dataset.ui.json), we can see some examples of how different field types are converted into form elements utilizing both the dataset.json (schema file) as well as the dataset.ui.json (schema UI file) which in turn create the form @ /node/add/data?schema=dataset.

> **_TIP:_**
> See the Schema UI Handler class as well as the Element folder and it's included class files for how different UI options are managed in code.

The following are some examples of Field types and associated options and how they appear as a form element respectively. Newly introduced schema UI options will be described only the first time they appear. The length of some JSON objects may be trimmed as compared to the provided dataset.json/ui.json files in order to keep this page's length to a minimum, but the functionality they are showcasing will remain unchanged.

> **_TIP:_**
> You can make fields required on the form using a JSON property array in your schema file similarly to how it is done in the example from the dataset.json file below:


    "required": [
      "title",
      "description",
      "identifier",
      "accessLevel",
      "modified",
      "keyword"
    ],

In the above example the listed fields (which would appear later as objects in the JSON) would be required fields in the Drupal form that is created.

### Text Field

**Schema File Example:**


    "title": {
      "title": "Title",
      "description": "Human-readable name of the asset. Should be in plain English and include sufficient detail to facilitate search and discovery.",
      "type": "string",
      "minLength": 1
    },

**UI Schema File Example:**

    "title": {
      "ui:options": {
        "description": "Name of the asset, in plain language. Include sufficient detail to facilitate search and discovery."
      }
    },

UI Options:
- description
  - Overrides the description for the field in the schema file and displays the value of the JSON property in the schema ui file instead.

**Form Element:**

![Screenshot of a "Title" Drupal form field with a description of "Name of the asset, in plain language. Include sufficient detail to facilitate search and discovery." used to show how a "textbox" field can be created using the JSON Form Widget module.](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/json_form_widget/string-textbox.png)

### Text Area

**Schema File Example:**

    "description": {
      "title": "Description",
      "description": "Human-readable description (e.g., an abstract) with sufficient detail to enable a user to quickly understand whether the asset is of interest.",
      "type": "string",
      "minLength": 1
    },

**UI Schema File Example:**

    "description": {
      "ui:options": {
        "widget": "textarea",
        "rows": 5,
        "description": "Description (e.g., an abstract) with sufficient detail to enable a user to quickly understand whether the asset is of interest.",
        "weight": 1
      }
    },

UI options:
- widget: textarea
  - Creates a larger text area box
- rows: 5
  - The text area has a height of 5 rows
- description
- weight: 1
  - Fields with a lower weight value will appear earlier in the form layout

### Formatted Text Area

**Schema File Example:**

    "description": {
      "title": "Description",
      "description": "Human-readable description (e.g., an abstract) with sufficient detail to enable a user to quickly understand whether the asset is of interest.",
      "type": "string",
      "minLength": 1
    },

**UI Schema File Example:**

    "description": {
      "ui:options": {
        "widget": "textarea",
        "rows": 5,
        "description": "Description (e.g., an abstract) with sufficient detail to enable a user to quickly understand whether the asset is of interest.",
        "textFormat": "html",
        "weight": 1
      }
    },

UI options:
- textFormat: html
  - This can be the machine name of any Drupal text format you have configured in your system
- weight: 1
  - Fields with a lower weight value will appear earlier in the form layout

You can use `textFormat` to add a WYSIWYG or other Drupal text editor plugin to your dataset form. Just enable the modules you need and assign the editor to your text format in Drupal at /admin/config/content/formats.

 Note that using the `textFormat` property will not affect how the field is rendered; you must handle this in your decoupled frontend or by overriding the node--data twig template or `metastore_preprocess_node__data()` method in metastore.theme.inc. See the [Theming Drupal](https://www.drupal.org/docs/develop/theming-drupal) guide for more information.

**Form Element:**

![Screenshot of a "Description" Drupal form field with a description of "Description (e.g., an abstract) with sufficient detail to enable a user to quickly understand whether the asset is of interest." used to show how a "textarea" field can be created using the JSON Form Widget module.](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/json_form_widget/string-textarea.png)

### URI

**Schema File Example:**

    "references": {
      "title": "Related Documents",
      "description": "Related documents such as technical information about a dataset, developer documentation, etc.",
      "type": "array",
      "items": {
        "type": "string",
        "format": "uri"
      }
    }

**UI Schema File Example:**

    "references": {
    "items": {
      "ui:options": {
        "placeholder": "http://"
      }
    }
  },

UI options:
- placeholder: http://
  - Text that will show slightly grayed out inside of the input box to serve as an example of or to relay information on what could be typed in.

 > **_NOTE:_**
 > The "items" property of this JSON object and how it translates to allowing for more than one of it's included objects to be created on the form.

**Form Element:**
![Screenshot of a "Related Documents" Drupal form field with a description of "Related documents such as technical information about a dataset, developer documentation, etc." used to show how a "URI" field can be created using the JSON Form Widget module.](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/json_form_widget/string-uri.png)

### Select Field

**Schema File Example:**

    "accrualPeriodicity": {
      "title": "Frequency",
      "description": "Frequency with which dataset is published.",
      "type": "string",
      "enum": [
        "R/P10Y",
        "R/P4Y",
        "R/P1Y",
        "R/P2M",
        "R/P3.5D",
        "R/P1D"
      ],
      "enumNames": [
        "Decennial",
        "Quadrennial",
        "Annual",
        "Bimonthly",
        "Semiweekly",
        "Daily"
      ]
    },

> **_NOTE:_**
> The enum (values) and enumNames (labels) property arrays.

**Schema File Example:**

> **_TIP:_**
> A corresponding object within a schema UI file is not required for each object within the schema file if UI options for that object are not needed/wanted.

**Form Element:**

![Screenshot of a "Frequency" Drupal form field with a description of "Frequency with which dataset is published." used to show how a "select" field can be created using the JSON Form Widget module.](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/json_form_widget/string-select.png)


### Date and Time

**Schema File Example:**

    "issued": {
      "title": "Release Date",
      "description": "Date of formal issuance.",
      "type": "string"
    },

**UI Schema File Example:**

    "issued": {
      "ui:options": {
        "widget": "flexible_datetime"
      }
    },

UI options:
- widget: flexible_datetime
  - Creates a date and time field (see FlexibleDateTime.php)

**Form Element:**

![Screenshot of a "Release Date" Drupal form field with a description of "Date of formal issuance." used to show how a "date and time" field can be created using the JSON Form Widget module.](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/json_form_widget/string-datetime.png)

### Date Range

**Schema File Example:**

    "temporal": {
      "title": "Temporal",
      "description": "The <a href=\"https://project-open-data.cio.gov/v1.1/schema/#temporal\">start and end dates</a> for which the dataset is applicable, separated by a \"/\" (i.e., 2000-01-15T00:45:00Z/2010-01-15T00:06:00Z).",
      "type": "string"
    },

**UI Schema File Example:**

    "temporal": {
      "ui:options": {
        "description": "The <a href=\"https://project-open-data.cio.gov/v1.1/schema/#temporal\">start and end dates</a> for which the dataset is applicable.",
        "widget": "date_range"
      }
    },

UI options:
- widget: date_range
  - Creates a date and time range field with a Start Date and End Date input (see DateRange.php)

**Form Element:**

![Screenshot of a "Temporal" Drupal form field with a description of "The start and end dates for which the dataset is applicable." used to show how a "date range" field can be created using the JSON Form Widget module.](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/json_form_widget/string-daterange.png)

### Expandable dropdown "details" box with autocomplete select list

**Schema File Example:**

    "publisher": {
      "$schema": "http://json-schema.org/draft-04/schema#",
      "id": "https://project-open-data.cio.gov/v1.1/schema/organization.json#",
      "title": "Organization",
      "description": "A Dataset Publisher Organization.",
      "type": "object",
      "required": [
        "name"
      ],
      "properties": {
        "@type": {
          "title": "Metadata Context",
          "description": "IRI for the JSON-LD data type. This should be org:Organization for each publisher",
          "type": "string",
          "default": "org:Organization"
        },
        "name": {
          "title": "Publisher Name",
          "description": "",
          "type": "string",
          "minLength": 1
        },
        "subOrganizationOf": {
          "title": "Parent Organization",
          "type": "string"
        }
      }
    },

> **_NOTE:_**
> The nested nature of this schema object "Publisher" and how it translates to a Drupal "details" form element box with fields within it.

**UI Schema File Example:**

    "publisher": {
      "ui:options": {
        "widget": "list",
        "type": "autocomplete",
        "allowCreate": "true",
        "titleProperty": "name",
        "source": {
          "metastoreSchema": "publisher"
        }
      },
      "properties": {
        "@type": {
          "ui:options": {
            "widget": "hidden"
          }
        },
        "subOrganizationOf": {
          "ui:options": {
            "widget": "hidden"
          }
        }
      }
    },

> **_NOTE:_**
> The 'widget: hidden' properties in this schema UI object and how they hide their respective fields from appearing on the final form. It's worth noting that in this example, out of the entire "Publisher" schema object, only the "Name" field is directly shown on the form due to the schema UI options.

UI options:
- widget: list
  - Signifies that this will be a list of items
- type: autocomplete
  - the list will show with an autocomplete function
    - The person filling out the form will type selections and enter them with previous options possibly showing below.
- allowCreate: true
  - The user can create list items by typing them, rather than only being able to select predetermined options.
- source
  - metestoreSchema: publisher
    - Where the predetermined options for the select list come from
      - In this instance they would be sourced from the "publisher" metastore. Note if you do not have any datasets saved, no suggestions will show here as it would not have any information to derive the suggestions from.

**Form Element:**

![Screenshot of an "Organization" Drupal form dropdown box with a description of "A Dataset Publisher Organization." used to show how a "Dropdown" box with an autocomplete field can be created using the JSON Form Widget module.](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/json_form_widget/object-dropdown-autocomplete.png)

### Fieldset with select other and upload or link fields

Includes (of note):
- Expandable details box
- Upload or Link
- Add one (Create more than one) functionality

**Schema File Example:**

    "distribution": {
      "title": "Distribution",
      "description": "A distribution is a container for the metadata specific to the data resource being shared. Each distribution should contain one <strong>Access URL</strong> or <strong>Download URL</strong>. When providing a Download URL, also include the format of the file. A distribution containing a Download URL to a csv or tsv file will generate queues that will import the data into a database table, this is referred to as a datastore. The datastore provides an API endpoint for users to run queries against the data.",
      "type": "array",
      "items": {
        "title": "Data File",
        "type": "object",
        "properties": {
          "@type": {
            "title": "Metadata Context",
            "description": "IRI for the JSON-LD data type. This should be dcat:Distribution for each Distribution.",
            "default": "dcat:Distribution",
            "type": "string",
            "readOnly": true
          },
          "title": {
            "title": "Title",
            "description": "Human-readable name of the file.",
            "type": "string",
            "minLength": 1
          },
          "description": {
            "title": "Description",
            "description": "Human-readable description of the file.",
            "type": "string",
            "minLength": 1
          },
          "format": {
            "title": "Format",
            "description": "A human-readable description of the file format of a distribution (i.e. csv, pdf, xml, kml, etc.).",
            "type": "string",
            "examples": [
              "arcgis",
              "csv",
              "esri rest",
              "geojson",
              "json",
              "kml",
              "pdf",
              "tsv",
              "xls",
              "xlsx",
              "xml",
              "zip"
            ]
          },
          "mediaType": {
            "title": "Media Type",
            "description": "The machine-readable file format (<a href=\"https://www.iana.org/assignments/media-types/media-types.xhtml\">IANA Media Type or MIME Type</a>) of the distribution’s downloadURL.",
            "type": "string"
          },
          "downloadURL": {
            "title": "Download URL",
            "description": "URL providing direct access to a downloadable file of a dataset.",
            "type": "string",
            "format": "uri"
          },
          "accessURL": {
            "title": "Access URL",
            "description": "URL providing indirect access to a dataset.",
            "type": "string",
            "format": "uri"
          },
          "conformsTo": {
            "title": "Data Standard",
            "description": "URI used to identify a standardized specification the distribution conforms to.",
            "type": "string",
            "format": "uri"
          },
          "describedBy": {
            "title": "Data Dictionary",
            "description": "URL to the data dictionary for the distribution found at the downloadURL.",
            "type": "string",
            "format": "uri"
          },
          "describedByType": {
            "title": "Data Dictionary Type",
            "description": "The machine-readable file format (IANA Media Type or MIME Type) of the distribution’s describedBy URL.",
            "pattern": "^[a-z\\/\\.\\+]+?$",
            "type": "string"
          }
        },
        "uniqueItems": true
      },
      "minItems": 1
    },

**UI Schema File Example:**

    "distribution": {
      "ui:options": {
        "description": "A distribution is a container for the metadata specific to the data resource being shared. Each distribution should contain one <strong>Access URL</strong> or <strong>Download URL</strong>. When providing a Download URL, also include the format of the file. A distribution containing a Download URL to a csv or tsv file will generate queues that will import the data into a database table, this is referred to as a datastore. The datastore provides an API endpoint for users to run queries against the data."
      },
      "items": {
        "@type": {
          "ui:options": {
            "widget": "hidden"
          }
        },
        "title":  {
          "ui:options": {
            "title": "File Title",
            "description": ""
          }
        },
        "mediaType": {
          "ui:options": {
            "widget": "hidden"
          }
        },
        "description": {
          "ui:options": {
            "widget": "textarea",
            "rows": 5,
            "title": "File Description",
            "description": ""
          }
        },
        "format": {
          "ui:options": {
            "title": "File Format",
            "widget": "list",
            "type": "select_other",
            "other_type": "textfield",
            "description": "CSV files must be encoded in UTF-8 format to be imported correctly. UTF-8 encoding is an established standard that provides optimal compatibility between applications and operating systems. Note that Excel provides a <strong>CSV UTF-8</strong> option when saving data files.",
            "source": {
              "enum": [
                "arcgis",
                "csv",
                "esri rest",
                "geojson",
                "json",
                "kml",
                "pdf",
                "tsv",
                "xls",
                "xlsx",
                "xml",
                "zip"
              ]
            }
          }
        },
        "downloadURL": {
          "ui:options": {
            "widget": "upload_or_link",
            "extensions": "csv html xls json xlsx doc docx rdf txt jpg png gif tiff pdf odf ods odt tsv tab geojson xml zip kml kmz shp",
            "progress_indicator": "bar",
            "description": "URL providing direct access to a downloadable file."
          }
        },
        "accessURL": {
          "ui:options": {
            "description": "URL providing indirect access to the data, for example via API or a graphical interface."
          }
        },
        "describedBy": {
          "ui:options": {
            "description": "URL to the data dictionary for the file found at the Download URL."
          }
        },
        "describedByType": {
          "ui:options": {
            "description": "The machine-readable file format (IANA Media Type or MIME Type) of the distribution’s Data Dictionary URL."
          }
        }
      }
    },

UI options:
- description
  - Overrides the description from dataset.json.
- widget: hidden
  - Keeps the property from displaying in the form.
- title: "File Title"
  - Overrides the title for the field in the schema file and displays the value of the JSON property in the schema ui file instead.
- widget: textarea
  - Provide a textarea.
- rows: 5
  - The textarea will have a hight of 5 rows.
- widget: list
  - Create a list element.
- type: select_other
  - Creates a dropdown select (list) field with an "other" option
- other_type: textfield
  - The "other" option in the above mentioned select_other list, when chosen by the person filling out the form, appears as a text area.
- source
  - Provide a source for list options.
- widget: upload_or_link
  - Signifies that this will be a field that allows for the upload of a file or a link to a file (URL)
- extensions:
  - The allowed file extensions/types.
- progress_indicator: bar
  - The UI element that will show how long it is taking to upload the file.
- source
  - metestoreSchema: publisher
    - Where the predetermined options for the select list come from
      - In this instance they would be sourced from the "publisher" metastore. Note if you do not have any datasets saved, no suggestions will show here as it would not have any information to derive the suggestions from.

**Form Element:**

![Screenshot of a "Distribution" Drupal form dropdown box with multiple fields used to show how a "fieldset" box with a select list with an other option and an upload or link field can be created using the JSON Form Widget module.](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/json_form_widget/object-fieldset-select-other-upload-link.png)


## Diagrams
[View code diagrams](https://github.com/GetDKAN/dkan/blob/json-widget-readme/modules/json_form_widget/CodeFlowDiagrams.md)

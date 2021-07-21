## Schema Updater

Testing using a CSV file with this content:

```
lon,lat,Unit_Type,Dist_Name,Prov_Name,Dist_ID,last
61.33,32.4,District,Qala-e-Kah,Farah,3106,32
62.06,32.49,District,Pusht Rod,Farah,3105,32
61.37,32.11,District,Shib Koh,Farah,3107,33
```

And the frictionless schema used is:

```
{
  "schema": {
    "fields": [
      {
        "name": "lon",
        "type": "float"
      },
      {
        "name": "lat",
        "type": "float"
      },
      {
        "name": "last",
        "type": "integer"
      }
    ]
  }
}
```

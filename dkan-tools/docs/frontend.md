# Decoupled frontend

### Download the DKAN frontend app to src/frontend.

By default DKAN uses [data-catalog-app](https://github.com/GetDKAN/data-catalog-app) for the front end application, as specified in the composer.json file:

```
"extra": {
    "dkan-frontend": {
        "type": "vcs",
        "url": "https://github.com/GetDKAN/data-catalog-app",
        "ref": "1.0.3"
    }
}
```

To download it simply run:

```
dktl frontend:get
```

If you would like to use a specific tag, branch or commit of data-catalog-app, you can add that change manually to your DKAN composer.json file or run:

```
dktl frontend:get --ref=BranchName
```

To use an entirely different frontend application, pass the github URL:

```
dktl frontend:get --url=https://github.com/org/alternate-frontend
```

# Test user accounts

To create test user accounts when running tests, add a file to the root of your project called testUsers.json

Add the accounts for each role you will be testing.

```
[
  {
    "name": "testapiuser",
    "mail": "testapiuser@test.com",
    "role": "api_user"
  },
  {
    "name": "testadmin",
    "mail": "testadmin@test.com",
    "role": "administrator"
  }
]
```

When you run `dktl dkan:test-cypress` for core tests, or `dktl project:test-cypress` for project tests,
the users will be generated, the tests will run, then then user accounts will be deleted.

## Create Test Users
To create the accounts without running tests, run `dktl dkan:qa-users-create` or `dktl qauc`.

## Delete Test Users
To delete the accounts, run `dktl dkan:qa-users-delete` or `dktl qaud`

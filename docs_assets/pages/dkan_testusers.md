# dkan-test-users {#dkantestusers}

If you are using the [DKAN DDEV Add-On](https://github.com/GetDKAN/dkan-ddev-addon), you can create and delete test user accounts with the following commands.

#### Add users

`ddev dkan-test-users`

#### Remove users

`ddev dkan-test-users --remove`

You can define your own custom test users by adding a testuser.json file to the root of your project. These commands will generate and remove the users specified, if no file is found, the DKAN default user accounts will be used.

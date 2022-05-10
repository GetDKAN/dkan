@page dkantestusers dkan:qa-users-create

DKAN ships with two test users: testapiuser and testadmin.

You can define your own custom test users by adding a testuser.json file to the root of your project. These commands will generate and remove the users specified, if no file is found, the DKAN default user accounts will be used.

#### Add users

`dkan:qa-users-create`

@alias `qauc`

#### Remove users

`dkan:qa-users-delete`

@alias `qaud`

# Install DKAN on Pantheon

With this tutorial you will spin a new DKAN site on [Pantheon](http://getpanthen.com).

## Why Pantheon?

Pantheon is the fastest and easiest to maintain way to get up and running with DKAN. Once you’ve setup an account you can launch your DKAN portal for as little as $25 per month.

### 1\. Automated DKAN updates

DKAN updates are sent right to your development site. You test them safely with no risk. Updates can be merged without coding or using a command line (after initial setup).

This image shows the Pantheon dashboard where new updates are applied: ![dkan-update](https://cloud.githubusercontent.com/assets/512243/23308560/96fda7da-fa79-11e6-9c75-10a1df088f76.PNG)

### 2\. No-coding or Devops necessary

Pantheon takes care of all your hosting needs including backups, dev/test/prod instances, CLI access if you need it. DKAN supplies updates to your site. You can leverage all of the power of DKAN without code and without setting up or deploying servers.

### 4\. $25 a month hosting

You can host your production-ready portal for as little as $25 a month.

## Prerequisites

### 1\. Knowledge of Git, command line tools, and developer tools

While anyone could follow the step by step instructions below it is helpful to have experience with git, cli tools, and developer tools and practices.

### 2\. Create free Pantheon Account

[Create your free Pantheon account](https://pantheon.io/) to get started.

### 3\. Install drush locally

[Install drush](http://docs.drush.org/en/master/install/) to your local computer or server.

### 4\. Add your ssh key to your Pantheon account

[Add your ssh key](https://pantheon.io/docs/ssh-keys/) to your Pantheon account.

### 5\. Install Terminus (recommended)

It is recommended to [install Terminus](https://github.com/pantheon-systems/terminus#installation), Pantheon’s CLI tool.

## Step by Step Process

### 1\. Create DKAN site on Pantheon

[Create your DKAN site](https://dashboard.getpantheon.com/products/dkan/spinup) using this form. This should only take several minutes.

Add your site name and url:

![dkan-pantheon-1](https://cloud.githubusercontent.com/assets/512243/23308400/03d040a8-fa79-11e6-8176-4d6beac38eec.PNG)

### 2\. Update your drush alias files

Once your site is created you need to update your local drush alias files.

#### 2a) Download your drush alias files

Visit your [Pantheon Dashboard](https://dashboard.pantheon.io) and click “Drush Aliases”.

![dkan-pantheon-3](https://cloud.githubusercontent.com/assets/512243/23308529/7a8cb12c-fa79-11e6-91f8-f0a1854c6099.PNG)

#### 2b) Update your aliases using Terminus (recommended)

##### Authenticate with Terminus

<div class="highlighter-rouge">

    > terminus auth:login

</div>

If you are doing this for the first time you will need to generate a machine token. Just follow the link provided.

#### Update drush aliases

<div class="highlighter-rouge">

    > terminus aliases

</div>

With either method you should be able to type:

<div class="highlighter-rouge">

    > drush sa

</div>

see a list of aliases including .dev/.test/.prod instances of your site.

### 3\. Change “Connection Mode” to SFTP

Go to your dev instance in Pantheon and click “SFTP”. This is to get around a bug in Pantheon that won’t let you install from the CLI while in Git mode.

### 4\. Install DKAN

Run the installation command:

<div class="highlighter-rouge">

    > drush si @mysitealias.dev dkan -vy

</div>

If you want to specify a root user or password you can add “–account-name” and “–account-pass” arguments. If you don’t specify the password you will have to look for it in the CLI output.

The installation process should take about 10 minutes.

### 5\. Login to your new DKAN site!

Visit the url on your development instance dashboard in Panteon or type

<div class="highlighter-rouge">

    > drush @mysitealias.dev uli

</div>

## Follow-up Steps

### 1\. Switch “Connection Mode” back to git

Go back to your dev instance and switch “Connection mode” back to git.

### 2\. Move your database and code from dev to test and prod

See “Deploy to test and live” in [Pantheon docs](https://pantheon.io/docs/get-started/)

### 3\. Start adding to your DKAN site

Visit the [DKAN Docs](http://docs.getdkan.com/en/latest/index.html) to see how to setup your site.

### 4\. Prepare for launch!

See Pantheon docs on [Going live](https://pantheon.io/docs/going-live).

### 5\. Visit our slack channel

Visit us at [our Slack channel](https://dkansignup.herokuapp.com) and say hello, ask questions, or join the community.
Ahoy
====

[Ahoy]:[ahoy] is an open source build tool written in Go that we use to abstract
out the build steps for the project.  If you are familiar with gnu `make`, then
the basic use of this tool is the same. The main advantage of `ahoy` over `make`
is that ahoy has a simple yaml format that is familiar to the modern developer
and also it is easier to organize commands into logical sets.

Ahoy is not required to develop with DKAN, and at this point we are not
recommending contributing developers to adopt it.  For further details please
read the ahoy documentation on the ahoy site. The contents of this README are
merely 

## Why we use Ahoy:
* Standardize workflow steps so that everyone is doing things exactly the same way
* Abstract more complex workflow steps and scripts behind higher level commands, which:
  *  Makes it easier for developers, as they can see a list of commands available along with descriptions of each
  * Makes it easier for the devops team to improve the processes without having to retrain developers or change documentation
* Reuse abstracted commands in scripts like CircleCI, or even other ahoy commands.
* Make it easier for anyone to add or tweak the commands without knowledge of bash, php, etc
* Allow projects (or users) to have their own custom commands in addition to the defaults.

[ahoy]:https://github.com/devinci-code/ahoy

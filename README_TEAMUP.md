# TEAMUP REAME

This is a fork of the sabre/vobject project (https://github.com/sabre-io/vobject) that we created to apply some of our own modifications.

## Upgrade to the latest from the fork source

1. Fetch the latest from upstream master (https://github.com/sabre-io/vobject)

2. Rebased each Teamup branch on top of the new master head.

3. Merge all Teamup branches into branch teamup-prod. teamup-prod is the branch that we are using in the calendar project.



## Upgrade the calendar project to the latest of our sabre/vobject fork

1. Update the sabre-vobject dependency in the core module: `composer update sabre/vobject`. This will create a new version of the core/composer.lock file. Commit this change and push to the repo. 

2. Update the teamup/core dependency in the calendar module: `composer update teamup/core.`. Then, update the sabre/vobject dependency in the calendar module: `composer update sabre/vobject`. This will create a new version of the calendar/composer.lock file. Commit this change and push to the repo.



## Create a new modification to the sabre/vobject project

Create a new feature branch with name teamup-XXX for each modification. This will make it much easier to keep track of modifications and rebase them when needed.

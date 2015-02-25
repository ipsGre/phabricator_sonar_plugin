# Phabricator Sonar Plugin

This extension allows you to display sonar issues directly into your Phabricator code review. 
##VERSION

###Phabricator

###Sonar
Version 5.0.

##HOW IT WORKS
As the use of the extensions repository is not detailed, we count on the principle that it is possible to redeclare a php class in this repository.

###Files
	- DifferentialLineNumberRendered.php 
	- DifferentialChangesetTwoUpRenderer.php 	(redeclared)
	- DiffusionCommitChangeTableView.php 		(redeclared)
The main core of the extension is processed in the new class *DifferentialLineNumberRendered*. Knowing a file path, it sends a request to your sonar server to gather issues related to this file. Tags are created directly in the class constructor.

The class *DifferentialChangesetTwoUpRenderer* redefinition modifies the differential render method to display sonar issues before line numbers. 

![Figure 1: Change preview](https://github.com/ipsGre/phabricator_sonar_plugin/blob/master/doc/Change.png)

The class *DiffusionCommitChangeTableView* redefinition modifies the change render method to display a summary of sonar issues in the table of affected files. 

 ![Figure 2: Differential preview](https://github.com/ipsGre/phabricator_sonar_plugin/blob/master/doc/Diff.png)

##INSTALLATION
To install the extension you just need to extract it in the extensions repository of your Phabricator installation.
Basically, you will find it in your server at `/var/www/html/phabricator/phabricator/src/extensions/`.

To setup the extension, first you need to change constant `$SONAR_URL` to your sonar server URL. 
####URL example: 
```php
const SONAR_URL = 'http://yolosonar.com'
```
Then **you have to adjust the pattern of file path** given by Phabricator to make the sonar API request works.

By default, we consider that the file path given by Phabricator matches the pattern `$PROJECT_NAME/trunk/$FILE_PATH_FROM_PROJECT_ROOT`. This path is transformed into `$PROJECT_NAME:$FILE_PATH_FROM_PROJECT_ROOT`.
```php
  $src = str_replace('/trunk/', ':', $path);
```

In fact, you need to make sure that the following request returns the list of sonar issues related to your file:
`$SONAR_URL/api/issues/search?components=$PROJECTS_ROOT:$PROJECT_NAME:$FILE_PATH_FROM_PROJECT_ROOT`

####Setting example: 
```php
  const SONAR_URL = 'http://yolosonar.com';
  const PROJECTS_ROOT = 'com.yoloprojects:'
  const SEARCH_REQUEST = '/api/issues/search?components=';
```
  For a phabricator file path looking like `YOLOPROJECT1/trunk/src/yolo.php` the request would be:
  http://yolosonar.com/api/issues/search?components=com.yoloprojects:YOLOPROJECT1:src/yolo.php
##LIMITS
As there is no custom field for the display changes made in *DifferentialChangesetTwoUpRenderer* and *DiffusionCommitChangeTableView* classes, redeclaring those classes should be enough to resist phabricator future updates. But if those classes were changed, it would be necessary to readapt the extension. The creation of the needed custom fields would settle this update issue.

##SOURCES
- http://phabricator.org/
- http://www.sonarqube.org/

##LICENSE
This plugin is released under GNU GPL 3.0 License.
